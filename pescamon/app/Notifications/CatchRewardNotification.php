<?php

namespace App\Notifications;

use App\Models\FishCatch;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CatchRewardNotification extends Notification
{
    use Queueable;

    /**
     * @param  array<int, string>  $messages
     * @param  array<int, array{name: string, description: string, xp: int}>  $achievements
     */
    public function __construct(
        public readonly FishCatch $catch,
        public readonly array $messages,
        public readonly int $xpEarned,
        public readonly bool $leveledUp,
        public readonly int $level = 1,
        public readonly array $achievements = [],
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'catch_id' => $this->catch->id,
            'species_name' => $this->catch->species->name,
            'messages' => $this->messages,
            'xp_earned' => $this->xpEarned,
            'leveled_up' => $this->leveledUp,
            'level' => $this->level,
            'achievements' => $this->achievements,
        ];
    }
}
