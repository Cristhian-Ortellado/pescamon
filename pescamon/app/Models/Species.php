<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Species extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'scientific_name',
        'slug',
        'habitat_description',
        'image_locked_path',
        'image_reference_path',
        'rarity',
        'average_weight_kg',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'average_weight_kg' => 'decimal:3',
            'is_active' => 'boolean',
        ];
    }

    public function catches(): HasMany
    {
        return $this->hasMany(FishCatch::class);
    }

    public function seasons(): HasMany
    {
        return $this->hasMany(SpeciesSeason::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
