<?php

namespace App\Http\Controllers;

use App\Models\FishCatch;
use App\Models\Species;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeaderboardController extends Controller
{
    public function index(Request $request): Response
    {
        $me = $request->user();
        $scope = $request->query('scope') === 'friends' || $request->query('scope') === 'species' ? $request->query('scope') : 'global';

        // Rank users by distinct verified species (Pokédex completion).
        $query = User::withCount(['catches as species_count' => function ($q) {
            $q->where('status', 'verified')->selectRaw('COUNT(DISTINCT species_id)');
        }]);

        $species = collect();
        $rankedSpeciesFinal = collect();
        $selectedSpeciesId = null;

        if ($scope === 'species') {
            $species = Species::all(['id', 'name', 'scientific_name', 'slug', 'rarity', 'average_weight_kg']);
            $selectedSpeciesId = $request->integer('species_id') ?: $species->first()?->id;
            $selectedSpeciesName = $species->firstWhere('id', $selectedSpeciesId)?->name;

            $rankedSpeciesFinal = FishCatch::query()
                ->select('user_id', 'species_id')
                ->selectRaw('SUM(xp_earned) as total_xp')
                ->where('status', 'verified')
                ->where('species_id', $selectedSpeciesId)
                ->groupBy('user_id', 'species_id')
                ->orderByDesc('total_xp')
                ->limit(20)
                ->with(['user:id,name,avatar_path'])
                ->get()
                ->values()
                ->map(fn (FishCatch $row, int $i) => [
                    'rank' => $i + 1,
                    'id' => $row->user_id,
                    'name' => $row->user->name,
                    'avatar' => $row->user->avatar,
                    'species_id' => $row->species_id,
                    'species_name' => $selectedSpeciesName,
                    'total_xp' => (int) $row->total_xp,
                    'is_current_user' => $row->user_id === $me->id,
                ]);
        }

        if ($scope === 'friends') {
            // Only the people the current user follows, plus themself.
            $friendIds = $me->following()->pluck('users.id')->push($me->id);
            $query->whereIn('id', $friendIds);
        }

        $ranked = $query
            ->orderByDesc('species_count')
            ->orderByDesc('xp')
            ->get(['id', 'name', 'level', 'xp', 'avatar_path']);

        $ranking = $ranked->values()->map(fn (User $u, int $i) => [
            'rank' => $i + 1,
            'id' => $u->id,
            'name' => $u->name,
            'avatar' => $u->avatar,
            'level' => $u->level,
            'species_count' => (int) $u->species_count,
            'is_current_user' => $u->id === $me->id,
        ]);

        $meEntry = $ranking->firstWhere('is_current_user', true);

        return Inertia::render('leaderboard/Index', [
            'ranking' => $ranking->take(20)->values(),
            'me' => $meEntry,
            'species' => $species,
            'rankedSpecies' => $rankedSpeciesFinal,
            'selectedSpeciesId' => $selectedSpeciesId,
            'scope' => $scope,
        ]);
    }
}
