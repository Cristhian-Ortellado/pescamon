<?php

namespace Database\Factories;

use App\Models\Species;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SpeciesFactory extends Factory
{
    protected $model = Species::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->word().' '.$this->faker->word();

        return [
            'name' => ucwords($name),
            'scientific_name' => ucfirst($this->faker->word()).' '.$this->faker->word(),
            'slug' => Str::slug($name).'-'.rand(1, 9999),
            'habitat_description' => $this->faker->sentence(12),
            'rarity' => $this->faker->randomElement(['common', 'uncommon', 'rare', 'legendary']),
            'average_weight_kg' => round($this->faker->randomFloat(2, 0.1, 50), 2),
            'is_active' => true,
        ];
    }
}
