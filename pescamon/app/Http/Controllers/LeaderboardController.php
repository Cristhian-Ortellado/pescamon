<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeaderboardController extends Controller
{
    public function index(Request $request): Response
    {
        $me = $request->user();
        $scope = $request->query('scope') === 'friends' ? 'friends' : 'global';

        // Rank users by distinct verified species (Pokédex completion).
        $query = User::withCount(['catches as species_count' => function ($q) {
            $q->where('status', 'verified')->selectRaw('COUNT(DISTINCT species_id)');
        }]);

        if ($scope === 'friends') {
            // Only the people the current user follows, plus themself.
            $friendIds = $me->following()->pluck('users.id')->push($me->id);
            $query->whereIn('id', $friendIds);
        }

        $ranked = $query
            ->orderByDesc('species_count')
            ->orderByDesc('xp')
            ->get(['id', 'name', 'level', 'xp']);

        $ranking = $ranked->values()->map(fn (User $u, int $i) => [
            'rank' => $i + 1,
            'id' => $u->id,
            'name' => $u->name,
            'level' => $u->level,
            'species_count' => (int) $u->species_count,
            'is_current_user' => $u->id === $me->id,
        ]);

        $meEntry = $ranking->firstWhere('is_current_user', true);

        return Inertia::render('leaderboard/Index', [
            'ranking' => $ranking->take(20)->values(),
            'me' => $meEntry,
            'scope' => $scope,
        ]);
    }
}
