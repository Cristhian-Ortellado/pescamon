<?php

namespace App\Http\Controllers;

use App\Models\FishCatch;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class FeedController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        // Authors whose posts belong in the main feed: people the user follows,
        // plus themself.
        $feedAuthorIds = $user->following()->pluck('users.id')->push($user->id);

        $base = FishCatch::query()
            ->with(['user:id,name,level,avatar_path', 'species:id,name,slug,rarity'])
            ->withCount(['likes', 'comments'])
            ->where('status', 'verified');

        // Main feed: catches from followed users (and self), newest first.
        $followedCatches = (clone $base)
            ->whereIn('user_id', $feedAuthorIds)
            ->latest('caught_at')
            ->limit(30)
            ->get();

        // Suggestions: 2–5 recent catches from anglers the user does NOT follow,
        // one per author, so they can discover new people to follow.
        $suggestionCatches = (clone $base)
            ->whereNotIn('user_id', $feedAuthorIds)
            ->latest('caught_at')
            ->limit(50)
            ->get()
            ->unique('user_id')
            ->take(random_int(2, 5))
            ->values();

        $likedIds = $user->likes()->pluck('catch_id')->flip();
        $followingSet = $feedAuthorIds->flip();

        $followedPosts = $followedCatches
            ->map(fn (FishCatch $c) => $this->toPost($c, $user->id, $likedIds, $followingSet, false))
            ->values();

        $suggestionPosts = $suggestionCatches
            ->map(fn (FishCatch $c) => $this->toPost($c, $user->id, $likedIds, $followingSet, true))
            ->values();

        $posts = $this->interleaveSuggestions($followedPosts, $suggestionPosts);

        return Inertia::render('feed/Index', [
            'posts' => $posts->values(),
            'unreadCount' => $user->unreadNotifications()->count(),
        ]);
    }

    /**
     * Weave suggestion posts through the followed feed so they surface for
     * discovery, while guaranteeing every suggestion appears even when the
     * followed feed is short or empty.
     *
     * @param  Collection<int, array<string, mixed>>  $followed
     * @param  Collection<int, array<string, mixed>>  $suggestions
     * @return Collection<int, array<string, mixed>>
     */
    private function interleaveSuggestions(Collection $followed, Collection $suggestions): Collection
    {
        $result = new Collection;
        $gap = 4; // Insert a suggestion after every N followed posts.
        $next = 0;

        foreach ($followed->values() as $i => $post) {
            $result->push($post);

            if (($i + 1) % $gap === 0 && $next < $suggestions->count()) {
                $result->push($suggestions[$next++]);
            }
        }

        // Append any suggestions that didn't fit (short/empty followed feed).
        while ($next < $suggestions->count()) {
            $result->push($suggestions[$next++]);
        }

        return $result;
    }

    /**
     * @param  Collection<int, mixed>  $likedIds
     * @param  Collection<int, mixed>  $followingSet
     * @return array<string, mixed>
     */
    private function toPost(FishCatch $c, int $userId, Collection $likedIds, Collection $followingSet, bool $isSuggestion): array
    {
        return [
            'id' => $c->id,
            'author' => [
                'id' => $c->user->id,
                'name' => $c->user->name,
                'level' => $c->user->level,
                'avatar' => $c->user->avatar,
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
            'is_own' => $c->user_id === $userId,
            'is_following' => $c->user_id !== $userId && $followingSet->has($c->user_id),
            'is_suggestion' => $isSuggestion,
        ];
    }
}
