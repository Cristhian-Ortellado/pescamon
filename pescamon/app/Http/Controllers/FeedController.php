<?php

namespace App\Http\Controllers;

use App\Models\FishCatch;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FeedController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        // Followed users + self. Falls back to the global feed when the user
        // follows nobody yet, so the screen is never empty.
        $followingIds = $user->following()->pluck('users.id')->push($user->id);

        $query = FishCatch::query()
            ->with(['user:id,name,level', 'species:id,name,slug,rarity'])
            ->withCount(['likes', 'comments'])
            ->where('status', 'verified')
            ->latest('caught_at')
            ->limit(30);

        $scoped = (clone $query)->whereIn('user_id', $followingIds)->get();
        $catches = $scoped->isNotEmpty() ? $scoped : $query->get();

        $likedIds = $user->likes()->pluck('catch_id')->flip();
        $followingSet = $followingIds->flip();

        $posts = $catches->map(fn (FishCatch $c) => [
            'id' => $c->id,
            'author' => [
                'id' => $c->user->id,
                'name' => $c->user->name,
                'level' => $c->user->level,
            ],
            'species' => $c->species->only(['id', 'name', 'slug', 'rarity']),
            'weight_kg' => (float) $c->weight_kg,
            'location_name' => $c->location_name,
            'caught_at' => $c->caught_at->toISOString(),
            'image_path' => $c->thumbnail_path ?: $c->image_path,
            'likes_count' => $c->likes_count,
            'comments_count' => $c->comments_count,
            'is_liked' => $likedIds->has($c->id),
            'is_personal_best' => (bool) $c->is_personal_best,
            'is_own' => $c->user_id === $user->id,
            'is_following' => $c->user_id !== $user->id && $followingSet->has($c->user_id),
        ]);

        return Inertia::render('feed/Index', [
            'posts' => $posts->values(),
            'unreadCount' => $user->unreadNotifications()->count(),
        ]);
    }
}
