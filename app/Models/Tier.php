<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tier extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'creator_id',
        'name',
        'description',
        'tier_level',
        'price_atomic',
        'yearly_price_atomic',
        'yearly_duration_days',
        'currency',
        'duration_days',
        'is_active',
        'position',
        'is_most_popular',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_most_popular' => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
}
