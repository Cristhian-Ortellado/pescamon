<?php

namespace App\Http\Controllers;

use App\Models\FishCatch;
use App\Models\Species;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SpeciesController extends Controller
{
    public function show(Request $request, Species $species): Response
    {
        $user = $request->user();

        $userCaught = $user->catches()
            ->where('species_id', $species->id)
            ->where('status', 'verified')
            ->exists();

        $personalBest = $user->catches()
            ->where('species_id', $species->id)
            ->where('status', 'verified')
            ->orderByDesc('weight_kg')
            ->first(['id', 'weight_kg', 'length_cm', 'caught_at', 'image_path', 'thumbnail_path']);

        // The user's own catch history for this species (most recent first).
        $userCatches = $user->catches()
            ->where('species_id', $species->id)
            ->where('status', 'verified')
            ->latest('caught_at')
            ->limit(20)
            ->get(['id', 'weight_kg', 'length_cm', 'caught_at', 'image_path', 'thumbnail_path', 'location_name', 'is_personal_best']);

        // Map catches for this species (respecting privacy settings)
        $mapCatches = FishCatch::query()
            ->where('species_id', $species->id)
            ->where('status', 'verified')
            ->whereNotNull('latitude')
            ->whereHas('user', fn ($q) => $q->where('location_privacy', '!=', 'hidden'))
            ->with('user:id,name,location_privacy')
            ->latest('caught_at')
            ->limit(200)
            ->get()
            ->map(fn (FishCatch $c) => [
                'id' => $c->id,
                'caught_at' => $c->caught_at->toISOString(),
                'latitude' => $c->user->location_privacy === 'approximate'
                    ? round((float) $c->latitude + (rand(-50, 50) / 10000), 2)
                    : round((float) $c->latitude, 5),
                'longitude' => $c->user->location_privacy === 'approximate'
                    ? round((float) $c->longitude + (rand(-50, 50) / 10000), 2)
                    : round((float) $c->longitude, 5),
                'user_name' => $c->user->name,
            ]);

        $totalCatches = FishCatch::where('species_id', $species->id)->where('status', 'verified')->count();
        $avgWeight = FishCatch::where('species_id', $species->id)->where('status', 'verified')->avg('weight_kg');

        return Inertia::render('species/Show', [
            'species' => $species,
            'userCaught' => $userCaught,
            'personalBest' => $personalBest,
            'userCatches' => $userCatches,
            'mapCatches' => $mapCatches,
            'stats' => [
                'total_catches' => $totalCatches,
                'avg_weight_kg' => round((float) $avgWeight, 2),
            ],
        ]);
    }
}
