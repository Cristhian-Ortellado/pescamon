<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Reference data first: catches and achievements depend on it.
        (new SpeciesSeeder)->run();
        (new AchievementSeeder)->run();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Multiple demo anglers, each with catches, follows, likes and comments.
        (new DemoContentSeeder)->run();
    }
}
