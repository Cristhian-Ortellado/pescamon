<?php

namespace App\Http\Controllers;

use App\Models\FishCatch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MapController extends Controller
{
    // Max markers returned per viewport request.
    private const LIMIT = 400;

    /**
     * Verified catches with public coordinates inside the requested map viewport.
     * Privacy rules: approximate → snap to ~1km grid + deterministic offset,
     * hidden → excluded. Loaded lazily by the map as the user pans/zooms.
     */
    public function catches(Request $request): JsonResponse
    {
        $bounds = $request->validate([
            'north' => ['nullable', 'numeric', 'between:-90,90'],
            'south' => ['nullable', 'numeric', 'between:-90,90'],
            'east' => ['nullable', 'numeric', 'between:-180,180'],
            'west' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $query = FishCatch::query()
            ->with(['species:id,name,slug,rarity', 'user:id,name,location_privacy,avatar_path'])
            ->where('status', 'verified')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereHas('user', fn ($q) => $q->where('location_privacy', '!=', 'hidden'));

        // Restrict to the visible bounding box when the four edges are provided.
        if (isset($bounds['north'], $bounds['south'], $bounds['east'], $bounds['west'])) {
            $query->whereBetween('latitude', [$bounds['south'], $bounds['north']]);

            // A normal viewport has west <= east; a viewport crossing the
            // antimeridian wraps, so match either side of ±180°.
            if ($bounds['west'] <= $bounds['east']) {
                $query->whereBetween('longitude', [$bounds['west'], $bounds['east']]);
            } else {
                $query->where(fn ($q) => $q
                    ->where('longitude', '>=', $bounds['west'])
                    ->orWhere('longitude', '<=', $bounds['east']));
            }
        }

        $catches = $query
            ->latest('caught_at')
            ->limit(self::LIMIT)
            ->get()
            ->map(fn (FishCatch $c) => [
                'id' => $c->id,
                'species' => $c->species->only(['id', 'name', 'slug', 'rarity']),
                'weight_kg' => (float) $c->weight_kg,
                'image_path' => $c->thumbnail_path ?: $c->image_path,
                'location_name' => $c->location_name,
                'caught_at' => $c->caught_at->toISOString(),
                'latitude' => $this->applyPrivacy($c->user->location_privacy, (float) $c->latitude, 'lat', $c->id),
                'longitude' => $this->applyPrivacy($c->user->location_privacy, (float) $c->longitude, 'lng', $c->id),
                'user' => [
                    'id' => $c->user->id,
                    'name' => $c->user->name,
                    'avatar' => $c->user->avatar,
                ],
            ]);

        return response()->json(['catches' => $catches]);
    }

    /**
     * Blur exact coordinates for anglers who opted into approximate location.
     * The offset is derived from the catch id (not rand()) so a marker keeps
     * the same position across viewport refetches instead of jittering on
     * every pan or zoom.
     */
    private function applyPrivacy(string $privacy, float $coord, string $axis, int $seed): float
    {
        if ($privacy === 'approximate') {
            $offset = ((crc32($axis.':'.$seed) % 101) - 50) / 10000; // ~[-500m, +500m]

            return round($coord + $offset, 2);
        }

        return round($coord, 5);
    }
}
