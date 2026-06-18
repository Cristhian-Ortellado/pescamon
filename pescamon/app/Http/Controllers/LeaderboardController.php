<?php

namespace App\Http\Controllers;

use App\Models\FishCatch;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class LeaderboardController extends Controller
{
    public function index(): Response
    {
        // Top users by XP
        $topByXp = User::orderByDesc('xp')
            ->limit(20)
            ->get(['id', 'name', 'level', 'xp']);

        // Top users by number of verified catches
        $topByCatches = User::withCount(['catches as verified_catches_count' => fn ($q) => $q->where('status', 'verified')])
            ->orderByDesc('verified_catches_count')
            ->limit(20)
            ->get(['id', 'name', 'level', 'xp']);

        // Top users by species count (Pokédex completion)
        $topBySpecies = User::withCount(['catches as species_count' => function ($q) {
            $q->where('status', 'verified')
                ->selectRaw('COUNT(DISTINCT species_id)');
        }])
            ->orderByDesc('species_count')
            ->limit(20)
            ->get(['id', 'name', 'level', 'xp']);

        // Biggest single catch (heaviest)
        $biggestCatch = FishCatch::with(['user:id,name', 'species:id,name,slug'])
            ->where('status', 'verified')
            ->orderByDesc('weight_kg')
            ->limit(10)
            ->get(['id', 'user_id', 'species_id', 'weight_kg', 'caught_at', 'thumbnail_path']);

        return Inertia::render('leaderboard/Index', [
            'topByXp' => $topByXp,
            'topByCatches' => $topByCatches,
            'topBySpecies' => $topBySpecies,
            'biggestCatch' => $biggestCatch,
        ]);
    }
}
