<?php

namespace App\Http\Controllers;

use App\Models\Species;
use App\Models\User;
use App\Services\LevelingService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function __construct(
        private readonly LevelingService $levelingService
    ) {}

    /** Own profile */
    public function show(Request $request): Response
    {
        return $this->renderProfile($request->user(), $request->user());
    }

    /** Public profile of another user */
    public function showUser(Request $request, User $user): Response
    {
        return $this->renderProfile($user, $request->user());
    }

    private function renderProfile(User $profileUser, User $authUser): Response
    {
        $profileUser->loadCount(['catches', 'followers', 'following']);

        // All species in the catalogue
        $allSpecies = Species::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'rarity', 'image_locked_path', 'image_reference_path']);

        // Species the profile user has caught (Pescadex)
        $caughtSpeciesIds = $profileUser->catches()
            ->where('status', 'verified')
            ->distinct()
            ->pluck('species_id')
            ->flip();

        $pokedex = $allSpecies->map(fn (Species $s) => [
            'id' => $s->id,
            'name' => $s->name,
            'slug' => $s->slug,
            'rarity' => $s->rarity,
            'image_path' => $caughtSpeciesIds->has($s->id) ? $s->image_reference_path : $s->image_locked_path,
            'caught' => $caughtSpeciesIds->has($s->id),
        ]);

        // Recent verified catches, shaped as feed-style posts (newest first).
        $likedIds = $authUser->likes()->pluck('catch_id')->flip();

        $posts = $profileUser->catches()
            ->with('species:id,name,slug,rarity')
            ->withCount(['likes', 'comments'])
            ->where('status', 'verified')
            ->latest('caught_at')
            ->limit(20)
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'species' => $c->species->only(['id', 'name', 'slug', 'rarity']),
                'weight_kg' => (float) $c->weight_kg,
                'location_name' => $c->location_name,
                'caught_at' => $c->caught_at->toISOString(),
                'image_path' => $c->thumbnail_path ?: $c->image_path,
                'likes_count' => $c->likes_count,
                'comments_count' => $c->comments_count,
                'is_liked' => $likedIds->has($c->id),
                'is_personal_best' => (bool) $c->is_personal_best,
            ])
            ->values();

        // Achievements
        $achievements = $profileUser->achievements()
            ->get(['achievements.id', 'achievements.code', 'achievements.name', 'achievements.description', 'achievements.icon_path', 'user_achievements.unlocked_at']);

        $isFollowing = $authUser->id !== $profileUser->id
            && $authUser->following()->where('followed_id', $profileUser->id)->exists();

        $recordKg = $profileUser->catches()->where('status', 'verified')->max('weight_kg');

        return Inertia::render('profile/Show', [
            'profileUser' => [
                ...$profileUser->only(['id', 'name', 'level', 'xp', 'catches_count', 'followers_count', 'following_count']),
                'avatar' => $profileUser->avatar,
                'is_own_profile' => $profileUser->id === $authUser->id,
                'is_following' => $isFollowing,
            ],
            'pokedex' => $pokedex,
            'posts' => $posts,
            'achievements' => $achievements,
            'stats' => [
                'species_caught' => $caughtSpeciesIds->count(),
                'species_total' => $allSpecies->count(),
                'record_kg' => $recordKg !== null ? round((float) $recordKg, 1) : 0,
            ],
            'levelProgress' => $this->levelingService->levelProgress($profileUser),
            'xpToNextLevel' => $this->levelingService->xpToNextLevel($profileUser),
            'xpForNextLevel' => $this->levelingService->xpRequiredForLevel($profileUser->level + 1),
        ]);
    }
}
