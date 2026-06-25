<?php

namespace App\Http\Middleware;

use App\Notifications\CatchRewardNotification;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
            ],
            // Latest unconsumed catch reward that warrants a celebration overlay.
            // Lazily evaluated so partial reloads (only: ['reward']) work.
            'reward' => fn () => $this->pendingReward($request),
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function pendingReward(Request $request): ?array
    {
        $user = $request->user();

        if (! $user) {
            return null;
        }

        $notification = $user->unreadNotifications()
            ->where('type', CatchRewardNotification::class)
            ->latest()
            ->first();

        if (! $notification) {
            return null;
        }

        $data = $notification->data;

        // Only surface an overlay when something celebration-worthy happened.
        if (empty($data['leveled_up']) && empty($data['achievements'])) {
            return null;
        }

        return [
            'id' => $notification->id,
            'leveled_up' => (bool) ($data['leveled_up'] ?? false),
            'level' => (int) ($data['level'] ?? $user->level),
            'xp_earned' => (int) ($data['xp_earned'] ?? 0),
            'achievements' => $data['achievements'] ?? [],
        ];
    }
}
