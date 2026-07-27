<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AchievementController extends Controller
{
    /** Icon + tone metadata for the official badge design, keyed by code. */
    private const META = [
        'FIRST_CATCH' => ['icon' => 'fish', 'tone' => 'teal'],
        'SPECIES_10' => ['icon' => 'gem', 'tone' => 'rara'],
        'SPECIES_25' => ['icon' => 'crown', 'tone' => 'accent'],
        'FOUR_SEASONS' => ['icon' => 'sunrise', 'tone' => 'accent'],
        'ABOVE_AVG_WEIGHT' => ['icon' => 'target', 'tone' => 'accent'],
        'PERSONAL_BEST_5' => ['icon' => 'award', 'tone' => 'accent'],
        'NIGHT_FISHER' => ['icon' => 'moon', 'tone' => 'teal'],
        'LEGENDARY_CATCH' => ['icon' => 'sparkles', 'tone' => 'rara'],
        'SPECIES_50' => ['icon' => 'trophy', 'tone' => 'rara'],
        'SPECIES_75' => ['icon' => 'crown', 'tone' => 'rara'],
        'CATCH_100' => ['icon' => 'fish', 'tone' => 'accent'],
        'CATCH_500' => ['icon' => 'fish', 'tone' => 'rara'],
        'PERSONAL_BEST_10' => ['icon' => 'award', 'tone' => 'rara'],
        'STREAK_7' => ['icon' => 'flame', 'tone' => 'accent'],
        'STREAK_30' => ['icon' => 'flame', 'tone' => 'rara'],
        'PERFECT_WEEKEND' => ['icon' => 'heart', 'tone' => 'teal'],
        'DAWN_PATROL' => ['icon' => 'sun', 'tone' => 'accent'],
        'MULTI_SPECIES_DAY' => ['icon' => 'layout-grid', 'tone' => 'accent'],
        'RARE_COMBO' => ['icon' => 'gem', 'tone' => 'rara'],
        'DOUBLE_WEIGHT' => ['icon' => 'zap', 'tone' => 'accent'],
        'ALL_TACKLE' => ['icon' => 'sliders-horizontal', 'tone' => 'teal'],
        'FIRST_RIVER_CATCH' => ['icon' => 'waves', 'tone' => 'teal'],
        'FIRST_LAKE_CATCH' => ['icon' => 'map-pin', 'tone' => 'teal'],
        'FIRST_SEA_CATCH' => ['icon' => 'anchor', 'tone' => 'teal'],
    ];

    public function index(Request $request): Response
    {
        $user = $request->user();

        $all = Achievement::orderBy('id')->get();
        $unlockedIds = $user->achievements()->pluck('achievements.id')->flip();

        // Progress signals for count-based achievements.
        $distinctSpecies = $user->catches()->where('status', 'verified')
            ->distinct('species_id')->count('species_id');
        $personalBests = $user->catches()->where('status', 'verified')
            ->where('is_personal_best', true)->count();
        $totalCatches = $user->catches()->where('status', 'verified')->count();

        $progressFor = fn (string $code): ?array => match ($code) {
            'SPECIES_10' => ['current' => min($distinctSpecies, 10), 'total' => 10],
            'SPECIES_25' => ['current' => min($distinctSpecies, 25), 'total' => 25],
            'SPECIES_50' => ['current' => min($distinctSpecies, 50), 'total' => 50],
            'SPECIES_75' => ['current' => min($distinctSpecies, 75), 'total' => 75],
            'PERSONAL_BEST_5' => ['current' => min($personalBests, 5), 'total' => 5],
            'PERSONAL_BEST_10' => ['current' => min($personalBests, 10), 'total' => 10],
            'CATCH_100' => ['current' => min($totalCatches, 100), 'total' => 100],
            'CATCH_500' => ['current' => min($totalCatches, 500), 'total' => 500],
            default => null,
        };

        $achievements = $all->map(function (Achievement $a) use ($unlockedIds, $progressFor) {
            $meta = self::META[$a->code] ?? ['icon' => 'award', 'tone' => 'teal'];

            return [
                'id' => $a->id,
                'code' => $a->code,
                'name' => $a->name,
                'description' => $a->description,
                'xp_reward' => $a->xp_reward,
                'icon' => $meta['icon'],
                'tone' => $meta['tone'],
                'unlocked' => $unlockedIds->has($a->id),
                'progress' => $progressFor($a->code),
            ];
        });

        // Featured "next" = a locked, count-based achievement with progress.
        $next = $achievements->first(fn ($a) => ! $a['unlocked'] && $a['progress'] !== null);

        return Inertia::render('achievements/Index', [
            'achievements' => $achievements->values(),
            'unlockedCount' => $unlockedIds->count(),
            'totalCount' => $all->count(),
            'next' => $next,
        ]);
    }
}
