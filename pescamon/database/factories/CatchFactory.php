<?php

namespace Database\Factories;

use App\Models\FishCatch;
use App\Models\Species;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CatchFactory extends Factory
{
    protected $model = FishCatch::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'species_id' => Species::factory(),
            'weight_kg' => round($this->faker->randomFloat(3, 0.1, 30), 3),
            'length_cm' => round($this->faker->randomFloat(2, 5, 150), 2),
            'image_path' => 'catches/placeholder.jpg',
            'thumbnail_path' => 'thumbnails/placeholder.jpg',
            'latitude' => $this->faker->latitude(-60, 60),
            'longitude' => $this->faker->longitude(-180, 180),
            'location_name' => $this->faker->city(),
            'caught_at' => $this->faker->dateTimeBetween('-2 years', 'now'),
            'method' => $this->faker->randomElement(['spinning', 'fly', 'bait_casting', 'trolling', 'other']),
            'bait_used' => $this->faker->randomElement(['Gusano', 'Cuchara', 'Streamer', 'Popper', null]),
            'water_body_type' => $this->faker->randomElement(['river', 'lake', 'sea', 'reservoir', 'other']),
            'is_personal_best' => false,
            'status' => 'verified',
        ];
    }

    public function pending(): static
    {
        return $this->state(['status' => 'pending']);
    }

    public function personalBest(): static
    {
        return $this->state(['is_personal_best' => true]);
    }
}
