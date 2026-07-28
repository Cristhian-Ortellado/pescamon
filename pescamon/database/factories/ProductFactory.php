<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = ucwords($this->faker->unique()->words(3, true));

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.$this->faker->unique()->numberBetween(1, 99999),
            'description' => $this->faker->paragraph(3),
            // Whole guaraníes, rounded to the nearest thousand.
            'price' => $this->faker->numberBetween(50, 3000) * 1000,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }

    /** Attach a number of gallery images in order. */
    public function withImages(int $count = 3): static
    {
        return $this->afterCreating(function (Product $product) use ($count) {
            for ($i = 0; $i < $count; $i++) {
                $product->images()->create([
                    'path' => 'products/'.Str::uuid().'.jpg',
                    'position' => $i,
                ]);
            }
        });
    }
}
