<?php

namespace App\Listeners;

use App\Events\CatchProcessed;
use App\Notifications\CatchRewardNotification;
use App\Services\AchievementService;
use App\Services\LevelingService;

/**
 * Runs after a catch image is processed.
 *
 * Responsibilities:
 *  - Marks is_personal_best on the catch
 *  - Awards XP via LevelingService
 *  - Checks and unlocks achievements via AchievementService
 *  - Sends an in-app database notification to the user
 *
 * Runs synchronously (not queued) so the reward is ready by the time the
 * user lands on the catch page after upload.
 */
class AwardCatchRewards
{
    public function __construct(
        private readonly LevelingService $levelingService,
        private readonly AchievementService $achievementService
    ) {}

    public function handle(CatchProcessed $event): void
    {
        $catch = $event->catch->fresh(['species', 'user']);
        $user = $catch->user;

        // Only award rewards once the catch is verified
        if ($catch->status !== 'verified') {
            return;
        }

        // Calculate XP and personal best flag
        $reward = $this->levelingService->calculateCatchReward($user, $catch);

        $catch->update([
            'is_personal_best' => $reward['is_personal_best'],
            'xp_earned' => $reward['xp'],
        ]);

        // Track level before any XP so we can detect a level-up across both the
        // catch reward AND any achievement rewards awarded below.
        $levelBefore = $user->level;

        // Award XP and check for level-up
        $this->levelingService->addXp($user, $reward['xp']);

        // Check achievements (each unlock also awards its XP)
        $newAchievements = $this->achievementService->checkAfterCatch($user, $catch);

        $levelsGained = $user->level - $levelBefore;

        // Build a summary notification message
        $messages = ["+{$reward['xp']} XP por tu captura de {$catch->species->name}"];

        if ($reward['is_new_species']) {
            $messages[] = '¡Nueva especie en tu PescaDex!';
        }

        if ($reward['is_personal_best']) {
            $messages[] = '¡Nuevo récord personal!';
        }

        if ($levelsGained > 0) {
            $messages[] = "¡Subiste al nivel {$user->level}!";
        }

        foreach ($newAchievements as $achievement) {
            $messages[] = "Logro desbloqueado: {$achievement->name}";
        }

        // Structured achievement payload drives the celebration overlay.
        $achievementsData = array_map(fn ($a) => [
            'name' => $a->name,
            'description' => $a->description,
            'xp' => $a->xp_reward,
        ], $newAchievements);

        // Store as a standard Laravel database notification
        $user->notify(new CatchRewardNotification(
            $catch,
            $messages,
            $reward['xp'],
            $levelsGained > 0,
            $user->level,
            $achievementsData,
        ));
    }
}
