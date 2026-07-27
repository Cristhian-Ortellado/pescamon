<?php

namespace App\Services;

use App\Models\Achievement;
use App\Models\FishCatch;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Checks and unlocks achievements after each verified catch.
 *
 * Each check method returns the Achievement if it should be unlocked now,
 * or null if the condition is not met or already earned.
 *
 * Available achievements (by code):
 *   FIRST_CATCH        – First catch ever submitted
 *   CATCH_100          – 100 catches registered
 *   CATCH_500          – 500 catches registered
 *   SPECIES_10         – 10 distinct species in Pokédex
 *   SPECIES_25         – 25 distinct species in Pokédex
 *   SPECIES_50         – 50 distinct species in Pokédex
 *   SPECIES_75         – 75 distinct species in Pokédex
 *   FOUR_SEASONS       – Catches in all 4 calendar seasons
 *   STREAK_7           – Catches on 7 consecutive calendar days
 *   STREAK_30          – Catches on 30 consecutive calendar days
 *   PERFECT_WEEKEND    – Catches on the Saturday and Sunday of one weekend
 *   ABOVE_AVG_WEIGHT   – Catch heavier than species average weight
 *   DOUBLE_WEIGHT      – Catch at least twice the species average weight
 *   PERSONAL_BEST_5    – 5 personal bests across any species
 *   PERSONAL_BEST_10   – 10 personal bests across any species
 *   NIGHT_FISHER       – Catch registered between 22:00 and 05:00
 *   DAWN_PATROL        – Catch registered between 05:00 and 07:00
 *   LEGENDARY_CATCH    – Catch a legendary rarity species
 *   MULTI_SPECIES_DAY  – 5 distinct species on the same day
 *   RARE_COMBO         – 2 distinct rare-or-better species on the same day
 *   ALL_TACKLE         – 5 distinct baits/lures used
 *   FIRST_RIVER_CATCH  – First catch in a river
 *   FIRST_LAKE_CATCH   – First catch in a lake
 *   FIRST_SEA_CATCH    – First catch at sea
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
            $this->checkCatchCount($user, 100, 'CATCH_100'),
            $this->checkCatchCount($user, 500, 'CATCH_500'),
            $this->checkSpeciesCount($user, 10, 'SPECIES_10'),
            $this->checkSpeciesCount($user, 25, 'SPECIES_25'),
            $this->checkSpeciesCount($user, 50, 'SPECIES_50'),
            $this->checkSpeciesCount($user, 75, 'SPECIES_75'),
            $this->checkFourSeasons($user),
            $this->checkStreak($user, 7, 'STREAK_7'),
            $this->checkStreak($user, 30, 'STREAK_30'),
            $this->checkPerfectWeekend($user),
            $this->checkAboveAverageWeight($catch),
            $this->checkDoubleAverageWeight($catch),
            $this->checkPersonalBests($user, 5, 'PERSONAL_BEST_5'),
            $this->checkPersonalBests($user, 10, 'PERSONAL_BEST_10'),
            $this->checkNightFisher($catch),
            $this->checkDawnPatrol($catch),
            $this->checkLegendaryCatch($catch),
            $this->checkMultiSpeciesDay($user, $catch),
            $this->checkRareCombo($user, $catch),
            $this->checkDistinctBaits($user),
            $this->checkWaterBody($catch, 'river', 'FIRST_RIVER_CATCH'),
            $this->checkWaterBody($catch, 'lake', 'FIRST_LAKE_CATCH'),
            $this->checkWaterBody($catch, 'sea', 'FIRST_SEA_CATCH'),
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
    private function checkPersonalBests(User $user, int $count, string $code): ?Achievement
    {
        $bests = $user->catches()
            ->where('status', 'verified')
            ->where('is_personal_best', true)
            ->count();

        return $bests >= $count ? Achievement::firstWhere('code', $code) : null;
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

    /** Registered N verified catches in total. */
    private function checkCatchCount(User $user, int $count, string $code): ?Achievement
    {
        $total = $user->catches()->where('status', 'verified')->count();

        return $total >= $count ? Achievement::firstWhere('code', $code) : null;
    }

    /** Caught something on $days consecutive calendar days at any point. */
    private function checkStreak(User $user, int $days, string $code): ?Achievement
    {
        $dates = $this->catchDates($user);

        if ($dates->count() < $days) {
            return null;
        }

        $longest = 1;
        $run = 1;
        $previous = null;

        foreach ($dates as $date) {
            if ($previous !== null) {
                $run = (int) $previous->diffInDays($date) === 1 ? $run + 1 : 1;
            }

            $longest = max($longest, $run);
            $previous = $date;
        }

        return $longest >= $days ? Achievement::firstWhere('code', $code) : null;
    }

    /**
     * Fished on both the Saturday and the Sunday of one weekend. Grouped by ISO
     * week so a Sat/Sun pair always lands in the same bucket.
     */
    private function checkPerfectWeekend(User $user): ?Achievement
    {
        $complete = $this->catchDates($user)
            ->filter(fn (CarbonInterface $date) => $date->isSaturday() || $date->isSunday())
            ->groupBy(fn (CarbonInterface $date) => $date->format('o-W'))
            ->contains(fn (Collection $week) => $week->contains(fn (CarbonInterface $d) => $d->isSaturday())
                && $week->contains(fn (CarbonInterface $d) => $d->isSunday()));

        return $complete ? Achievement::firstWhere('code', 'PERFECT_WEEKEND') : null;
    }

    /** Catch is at least double the species' average weight. */
    private function checkDoubleAverageWeight(FishCatch $catch): ?Achievement
    {
        $avg = $catch->species->average_weight_kg;

        if ($avg === null || (float) $catch->weight_kg < (float) $avg * 2) {
            return null;
        }

        return Achievement::firstWhere('code', 'DOUBLE_WEIGHT');
    }

    /** Catch was registered between 05:00 and 07:00. */
    private function checkDawnPatrol(FishCatch $catch): ?Achievement
    {
        $hour = (int) $catch->caught_at->format('G');

        return $hour >= 5 && $hour < 7 ? Achievement::firstWhere('code', 'DAWN_PATROL') : null;
    }

    /**
     * 5 distinct species on the day of this catch. Scoped to the catch's own day
     * so back-dated uploads still complete the day they belong to.
     */
    private function checkMultiSpeciesDay(User $user, FishCatch $catch): ?Achievement
    {
        $distinct = $user->catches()
            ->where('status', 'verified')
            ->whereDate('caught_at', $catch->caught_at->toDateString())
            ->distinct('species_id')
            ->count('species_id');

        return $distinct >= 5 ? Achievement::firstWhere('code', 'MULTI_SPECIES_DAY') : null;
    }

    /** 2 distinct rare-or-better species on the day of this catch. */
    private function checkRareCombo(User $user, FishCatch $catch): ?Achievement
    {
        $rare = $user->catches()
            ->where('status', 'verified')
            ->whereDate('caught_at', $catch->caught_at->toDateString())
            ->whereHas('species', fn ($query) => $query->whereIn('rarity', ['rare', 'legendary']))
            ->distinct('species_id')
            ->count('species_id');

        return $rare >= 2 ? Achievement::firstWhere('code', 'RARE_COMBO') : null;
    }

    /**
     * 5 distinct baits or lures used. bait_used is free text, so values are
     * trimmed and lower-cased before counting.
     */
    private function checkDistinctBaits(User $user): ?Achievement
    {
        $baits = $user->catches()
            ->where('status', 'verified')
            ->whereNotNull('bait_used')
            ->pluck('bait_used')
            ->map(fn (string $bait) => mb_strtolower(trim($bait)))
            ->filter()
            ->unique();

        return $baits->count() >= 5 ? Achievement::firstWhere('code', 'ALL_TACKLE') : null;
    }

    /** Catch was made in a given water body type (river / lake / sea). */
    private function checkWaterBody(FishCatch $catch, string $type, string $code): ?Achievement
    {
        return $catch->water_body_type === $type ? Achievement::firstWhere('code', $code) : null;
    }

    /**
     * Distinct calendar days with a verified catch, ascending.
     *
     * @return Collection<int, CarbonInterface>
     */
    private function catchDates(User $user): Collection
    {
        return $user->catches()
            ->where('status', 'verified')
            ->pluck('caught_at')
            ->map(fn (CarbonInterface $date) => $date->startOfDay())
            ->unique(fn (CarbonInterface $date) => $date->toDateString())
            ->sort()
            ->values();
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
