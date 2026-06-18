<?php

namespace App\Services;

use App\Models\FishCatch;
use App\Models\User;

/**
 * Handles XP rewards and level progression for the gamification system.
 *
 * XP rules per catch:
 *   - NEW species (first time in the user's Pokédex) → high XP (50)
 *   - PERSONAL BEST (exceeds previous weight record for that species) → medium XP (20)
 *   - REPEAT catch (no improvement) → low XP (5)
 *
 * Level curve: xp_required_for_level_N = N² × 100
 *   Level 2 requires 400 XP, Level 3 requires 900, Level 5 requires 2500, etc.
 */
class LevelingService
{
    public const XP_NEW_SPECIES = 50;

    public const XP_PERSONAL_BEST = 20;

    public const XP_REPEAT_CATCH = 5;

    /**
     * Calculate XP earned for a new verified catch and determine whether
     * the catch qualifies as a personal best.
     *
     * @return array{xp: int, is_personal_best: bool, is_new_species: bool}
     */
    public function calculateCatchReward(User $user, FishCatch $catch): array
    {
        $isNewSpecies = ! $user->catches()
            ->where('species_id', $catch->species_id)
            ->where('id', '!=', $catch->id)
            ->where('status', 'verified')
            ->exists();

        // Personal best = heavier than all previous verified catches of same species
        $previousBest = $user->catches()
            ->where('species_id', $catch->species_id)
            ->where('id', '!=', $catch->id)
            ->where('status', 'verified')
            ->max('weight_kg');

        $isPersonalBest = $previousBest === null || (float) $catch->weight_kg > (float) $previousBest;

        $xp = match (true) {
            $isNewSpecies => self::XP_NEW_SPECIES,
            $isPersonalBest => self::XP_PERSONAL_BEST,
            default => self::XP_REPEAT_CATCH,
        };

        return [
            'xp' => $xp,
            'is_personal_best' => $isPersonalBest,
            'is_new_species' => $isNewSpecies,
        ];
    }

    /**
     * Add XP to a user and level them up if the threshold is reached.
     * Returns the number of levels gained (0 if none).
     */
    public function addXp(User $user, int $xp): int
    {
        $user->xp += $xp;
        $levelsGained = 0;

        while ($user->xp >= $this->xpRequiredForLevel($user->level + 1)) {
            $user->level++;
            $levelsGained++;
        }

        $user->save();

        return $levelsGained;
    }

    /**
     * XP required to reach a given level from zero.
     * Formula: level² × 100
     */
    public function xpRequiredForLevel(int $level): int
    {
        return $level ** 2 * 100;
    }

    /**
     * XP required to advance from the current level to the next.
     */
    public function xpToNextLevel(User $user): int
    {
        $needed = $this->xpRequiredForLevel($user->level + 1);

        return max(0, $needed - $user->xp);
    }

    /**
     * Progress percentage (0–100) towards the next level.
     */
    public function levelProgress(User $user): float
    {
        $currentLevelXp = $this->xpRequiredForLevel($user->level);
        $nextLevelXp = $this->xpRequiredForLevel($user->level + 1);
        $range = $nextLevelXp - $currentLevelXp;

        if ($range <= 0) {
            return 100.0;
        }

        return round(min(100, (($user->xp - $currentLevelXp) / $range) * 100), 1);
    }
}
