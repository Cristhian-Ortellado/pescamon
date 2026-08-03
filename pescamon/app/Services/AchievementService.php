<?php

namespace App\Services;

use App\Models\Achievement;
use App\Models\FishCatch;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Checks and unlocks achievements after each verified catch.
 *
 * Each check method returns the Achievement if it should be unlocked now,
 * or null if the condition is not met or already earned.
 *
 * Available achievements (by code):
 *   FIRST_CATCH        – First catch ever submitted
 *   SPECIES_10         – 10 distinct species in Pescadex
 *   SPECIES_25         – 25 distinct species in Pescadex
 *   FOUR_SEASONS       – Catches in all 4 calendar seasons
 *   ABOVE_AVG_WEIGHT   – Catch heavier than species average weight
 *   PERSONAL_BEST_5    – 5 personal bests across any species
 *   NIGHT_FISHER       – Catch registered between 22:00 and 05:00
 *   LEGENDARY_CATCH    – Catch a legendary rarity species
 */
class AchievementService
{
    public function __construct(
        private readonly LevelingService $levelingService
    ) {}

    /**
     * Run all achievement checks for a user after a verified catch.
     * Returns an array of newly unlocked Achievement models.
     *
     * @return Achievement[]
     */
    public function checkAfterCatch(User $user, FishCatch $catch): array
    {
        $checks = [
            $this->checkFirstCatch($user),
            $this->checkSpeciesCount($user, 10, 'SPECIES_10'),
            $this->checkSpeciesCount($user, 25, 'SPECIES_25'),
            $this->checkFourSeasons($user),
            $this->checkAboveAverageWeight($catch),
            $this->checkPersonalBests($user, 5),
            $this->checkNightFisher($catch),
            $this->checkLegendaryCatch($catch),
        ];

        $unlocked = [];

        foreach (array_filter($checks) as $achievement) {
            if ($this->unlock($user, $achievement)) {
                $this->levelingService->addXp($user, $achievement->xp_reward);
                $unlocked[] = $achievement;
            }
        }

        return $unlocked;
    }

    /** First catch ever (any status after verification). */
    private function checkFirstCatch(User $user): ?Achievement
    {
        $totalVerified = $user->catches()->where('status', 'verified')->count();

        return $totalVerified >= 1 ? Achievement::firstWhere('code', 'FIRST_CATCH') : null;
    }

    /** Caught N distinct species. */
    private function checkSpeciesCount(User $user, int $count, string $code): ?Achievement
    {
        $distinct = $user->catches()
            ->where('status', 'verified')
            ->distinct('species_id')
            ->count('species_id');

        return $distinct >= $count ? Achievement::firstWhere('code', $code) : null;
    }

    /** Has catches in all 4 calendar seasons (Spring/Summer/Autumn/Winter). */
    private function checkFourSeasons(User $user): ?Achievement
    {
        $months = $user->catches()
            ->where('status', 'verified')
            ->pluck('caught_at')
            ->map(fn ($d) => (int) $d->format('n'))
            ->unique();

        $seasons = $months->map(fn ($m) => match (true) {
            in_array($m, [3, 4, 5]) => 'spring',
            in_array($m, [6, 7, 8]) => 'summer',
            in_array($m, [9, 10, 11]) => 'autumn',
            default => 'winter',
        })->unique();

        return $seasons->count() >= 4 ? Achievement::firstWhere('code', 'FOUR_SEASONS') : null;
    }

    /** Catch is heavier than the species' average weight. */
    private function checkAboveAverageWeight(FishCatch $catch): ?Achievement
    {
        $avg = $catch->species->average_weight_kg;

        if ($avg === null || (float) $catch->weight_kg <= (float) $avg) {
            return null;
        }

        return Achievement::firstWhere('code', 'ABOVE_AVG_WEIGHT');
    }

    /** User has accumulated N personal bests total. */
    private function checkPersonalBests(User $user, int $count): ?Achievement
    {
        $bests = $user->catches()
            ->where('status', 'verified')
            ->where('is_personal_best', true)
            ->count();

        return $bests >= $count ? Achievement::firstWhere('code', 'PERSONAL_BEST_5') : null;
    }

    /** Catch was registered between 22:00 and 05:00. */
    private function checkNightFisher(FishCatch $catch): ?Achievement
    {
        $hour = (int) $catch->caught_at->format('G');
        $isNight = $hour >= 22 || $hour < 5;

        return $isNight ? Achievement::firstWhere('code', 'NIGHT_FISHER') : null;
    }

    /** User caught a species with rarity = legendary. */
    private function checkLegendaryCatch(FishCatch $catch): ?Achievement
    {
        return $catch->species->rarity === 'legendary'
            ? Achievement::firstWhere('code', 'LEGENDARY_CATCH')
            : null;
    }

    /**
     * Unlock the achievement for the user if not already earned.
     * Returns true if it was newly unlocked, false if already owned.
     */
    private function unlock(User $user, Achievement $achievement): bool
    {
        $alreadyHas = $user->achievements()->where('achievement_id', $achievement->id)->exists();

        if ($alreadyHas) {
            return false;
        }

        $user->achievements()->attach($achievement->id, [
            'unlocked_at' => Carbon::now(),
        ]);

        return true;
    }
}
