<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $items = $user->notifications()
            ->latest()
            ->limit(50)
            ->get()
            ->map(function (DatabaseNotification $n) {
                $data = $n->data;
                $messages = $data['messages'] ?? [];
                $leveledUp = $data['leveled_up'] ?? false;
                $hasAchievement = ! empty($data['achievements']);

                // First message bold, the rest as plain runs.
                $segments = [];
                foreach (array_values($messages) as $i => $msg) {
                    $segments[] = ['text' => ($i === 0 ? '' : ' · ').$msg, 'bold' => $i === 0];
                }
                if (empty($segments)) {
                    $segments[] = ['text' => '¡Recompensa por tu captura!', 'bold' => true];
                }

                [$icon, $tone] = match (true) {
                    $leveledUp => ['zap', 'accent'],
                    $hasAchievement => ['gem', 'teal'],
                    default => ['fish', 'teal'],
                };

                return [
                    'id' => $n->id,
                    'icon' => $icon,
                    'tone' => $tone,
                    'segments' => $segments,
                    'time' => $n->created_at->diffForHumans(),
                    'unread' => $n->read_at === null,
                    'period' => $n->created_at->isToday() ? 'today' : 'week',
                    'catch_id' => $data['catch_id'] ?? null,
                ];
            });

        return Inertia::render('notifications/Index', [
            'notifications' => $items->values(),
        ]);
    }

    /** Mark all notifications as read (clears the celebration reward too). */
    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back();
    }

    /** Mark a single notification as read (e.g. when the user taps it). */
    public function markRead(Request $request, string $notification): RedirectResponse
    {
        $request->user()->notifications()
            ->where('id', $notification)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back();
    }
}
