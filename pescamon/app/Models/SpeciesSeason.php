<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpeciesSeason extends Model
{
    use HasFactory;

    protected $fillable = [
        'species_id',
        'region',
        'closed_start_date',
        'closed_end_date',
    ];

    protected function casts(): array
    {
        return [
            'closed_start_date' => 'date',
            'closed_end_date' => 'date',
        ];
    }

    public function species(): BelongsTo
    {
        return $this->belongsTo(Species::class);
    }
}
