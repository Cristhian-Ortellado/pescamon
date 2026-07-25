<?php

namespace App\Http\Controllers;

use App\Models\FishCatch;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        // Verified catches with public coordinates for the map.
        // Privacy rules are applied: approximate → round to 2 decimals + random offset,
        // hidden → exclude from results.
        $mapCatches = FishCatch::query()
            ->with(['species:id,name,slug,rarity,image_locked_path,image_reference_path', 'user:id,name,location_privacy,avatar_path'])
            ->where('status', 'verified')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereHas('user', fn ($q) => $q->where('location_privacy', '!=', 'hidden'))
            ->latest('caught_at')
            ->limit(500)
            ->get()
            ->map(fn (FishCatch $c) => [
                'id' => $c->id,
                'species' => $c->species->only(['id', 'name', 'slug', 'rarity']),
                'weight_kg' => (float) $c->weight_kg,
                'image_path' => $c->thumbnail_path ?: $c->image_path,
                'location_name' => $c->location_name,
                'caught_at' => $c->caught_at->toISOString(),
                'latitude' => $this->applyPrivacy($c->user->location_privacy, (float) $c->latitude, 'lat'),
                'longitude' => $this->applyPrivacy($c->user->location_privacy, (float) $c->longitude, 'lng'),
                'user' => [
                    'id' => $c->user->id,
                    'name' => $c->user->name,
                    'avatar' => $c->user->avatar,
                ],
            ]);

        return Inertia::render('dashboard/Map', [
            'mapCatches' => $mapCatches,
            'userLevel' => $user->level,
            'userXp' => $user->xp,
        ]);
    }

    private function applyPrivacy(string $privacy, float $coord, string $axis): float
    {
        if ($privacy === 'approximate') {
            // Round to 2 decimals (~1km precision) + small random offset within ~500m
            $offset = (rand(-50, 50) / 10000);

            return round($coord + $offset, 2);
        }

        return round($coord, 5);
    }
}
