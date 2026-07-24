<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FishCatch extends Model
{
    use HasFactory;

    protected $table = 'catches';

    protected $fillable = [
        'user_id',
        'species_id',
        'weight_kg',
        'length_cm',
        'image_path',
        'thumbnail_path',
        'latitude',
        'longitude',
        'location_name',
        'caught_at',
        'method',
        'bait_used',
        'water_body_type',
        'exif_raw',
        'is_personal_best',
        'xp_earned',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'weight_kg' => 'decimal:3',
            'length_cm' => 'decimal:2',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'caught_at' => 'datetime',
            'exif_raw' => 'array',
            'is_personal_best' => 'boolean',
            'xp_earned' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function species(): BelongsTo
    {
        return $this->belongsTo(Species::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'catch_id');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(Like::class, 'catch_id');
    }

    public function scopeVerified($query)
    {
        return $query->where('status', 'verified');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
