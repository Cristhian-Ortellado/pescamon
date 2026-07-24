<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\FishCatch;
use App\Models\Like;
use App\Models\Species;
use App\Models\User;
use App\Services\AchievementService;
use App\Services\LevelingService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

/**
 * Seeds a handful of demo anglers, each with a coherent trail of activity:
 * verified catches (with XP, personal-best flags, achievements and levels
 * computed exactly like the real flow), plus a social graph of follows,
 * likes and comments between them.
 */
class DemoContentSeeder extends Seeder
{
    /** Sample comments left on other anglers' catches. */
    private const COMMENTS = [
        '¡Qué ejemplar! 🎣',
        'Tremenda captura, felicitaciones.',
        '¿Con qué carnada lo sacaste?',
        'Envidia sana total 😄',
        'Ese lugar rinde siempre.',
        '¡Récord bien merecido!',
        'Hermoso pez, buen laburo.',
        'Contame el spot 👀',
        'Se ve que estuvo peleador.',
        '¡A seguir así, campeón!',
    ];

    public function run(): void
    {
        $leveling = app(LevelingService::class);
        $achievements = app(AchievementService::class);

        $speciesIds = Species::pluck('id')->all();

        if (empty($speciesIds)) {
            $this->command?->warn('DemoContentSeeder: no hay especies, se omite.');

            return;
        }

        $profiles = [
            ['name' => 'Mateo Fernández', 'email' => 'mateo@example.com', 'catches' => 16],
            ['name' => 'Lucía Gómez', 'email' => 'lucia@example.com', 'catches' => 12],
            ['name' => 'Diego Sosa', 'email' => 'diego@example.com', 'catches' => 9],
            ['name' => 'Valentina Ríos', 'email' => 'valentina@example.com', 'catches' => 13],
            ['name' => 'Joaquín Benítez', 'email' => 'joaquin@example.com', 'catches' => 6],
            ['name' => 'Camila Duarte', 'email' => 'camila@example.com', 'catches' => 10],
        ];

        /** @var Collection<int, User> $users */
        $users = new Collection;

        // Give the pre-existing test user some activity too, if present.
        $testUser = User::where('email', 'test@example.com')->first();
        if ($testUser) {
            $this->seedCatchesFor($testUser, 8, $speciesIds, $leveling, $achievements);
            $users->push($testUser);
        }

        foreach ($profiles as $profile) {
            $user = User::factory()->create([
                'name' => $profile['name'],
                'email' => $profile['email'],
            ]);

            $this->seedCatchesFor($user, $profile['catches'], $speciesIds, $leveling, $achievements);
            $users->push($user);
        }

        $this->seedSocialGraph($users);
    }

    /**
     * Create verified catches for a user in chronological order, awarding XP,
     * personal-best flags and achievements exactly like the live pipeline.
     *
     * @param  array<int, int>  $speciesIds
     */
    private function seedCatchesFor(
        User $user,
        int $count,
        array $speciesIds,
        LevelingService $leveling,
        AchievementService $achievements,
    ): void {
        $dates = collect(range(1, $count))
            ->map(fn () => now()->subDays(rand(1, 700))->subHours(rand(0, 23)))
            ->sort()
            ->values();

        foreach ($dates as $caughtAt) {
            $catch = FishCatch::factory()->create([
                'user_id' => $user->id,
                'species_id' => $speciesIds[array_rand($speciesIds)],
                'caught_at' => $caughtAt,
                'status' => 'verified',
                'xp_earned' => 0,
                'is_personal_best' => false,
                'image_path' => 'storage/app/public/images/species/amarillo-placeholder.png',
            ]);

            $reward = $leveling->calculateCatchReward($user, $catch);

            $catch->update([
                'is_personal_best' => $reward['is_personal_best'],
                'xp_earned' => $reward['xp'],
            ]);

            $leveling->addXp($user, $reward['xp']);
            $achievements->checkAfterCatch($user, $catch);
        }
    }

    /**
     * Wire up follows, likes and comments between the seeded users.
     *
     * @param  Collection<int, User>  $users
     */
    private function seedSocialGraph(Collection $users): void
    {
        // Follows: each angler follows a random subset of the others.
        foreach ($users as $user) {
            $others = $users->where('id', '!=', $user->id);
            $toFollow = $others->random(min(rand(1, 3), $others->count()));

            $user->following()->syncWithoutDetaching($toFollow->pluck('id')->all());
        }

        $catches = FishCatch::query()->verified()->get(['id', 'user_id']);

        foreach ($catches as $catch) {
            $potentialFans = $users->where('id', '!=', $catch->user_id)->values();

            if ($potentialFans->isEmpty()) {
                continue;
            }

            // Likes from a few different anglers.
            $likers = $potentialFans->random(min(rand(0, 4), $potentialFans->count()));
            foreach ($likers as $liker) {
                Like::firstOrCreate([
                    'catch_id' => $catch->id,
                    'user_id' => $liker->id,
                ]);
            }

            // Comments on roughly a third of the catches.
            if (rand(1, 100) <= 35) {
                $commenters = $potentialFans->random(min(rand(1, 2), $potentialFans->count()));
                foreach ($commenters as $commenter) {
                    Comment::create([
                        'catch_id' => $catch->id,
                        'user_id' => $commenter->id,
                        'body' => self::COMMENTS[array_rand(self::COMMENTS)],
                    ]);
                }
            }
        }
    }
}
