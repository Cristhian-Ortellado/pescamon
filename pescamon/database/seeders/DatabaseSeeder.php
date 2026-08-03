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
        (new ProductSeeder)->run();

        User::factory()->create([
            'name' => 'Cristhian',
            'email' => 'cortelladofernandez@gmail.com',
            'is_superadmin' => true,
            'password' => bcrypt('Cristhian123*'),
        ]);

        // Multiple demo anglers, each with catches, follows, likes and comments.
        (new DemoContentSeeder)->run();
    }
}
