<?php

namespace App\Listeners;

use App\Events\CatchProcessed;
use App\Services\AchievementService;
use App\Services\LevelingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Notification;

/**
 * Runs after a catch image is processed.
 *
 * Responsibilities:
 *  - Marks is_personal_best on the catch
 *  - Awards XP via LevelingService
 *  - Checks and unlocks achievements via AchievementService
 *  - Sends an in-app database notification to the user
 */
class AwardCatchRewards implements ShouldQueue
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

        $catch->update(['is_personal_best' => $reward['is_personal_best']]);

        // Award XP and check for level-up
        $levelsGained = $this->levelingService->addXp($user, $reward['xp']);

        // Check achievements
        $newAchievements = $this->achievementService->checkAfterCatch($user, $catch);

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

        // Store as a standard Laravel database notification
        $user->notify(new \App\Notifications\CatchRewardNotification(
            $catch,
            $messages,
            $reward['xp'],
            $levelsGained > 0,
        ));
    }
}
