<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Content extends Model
{
    use HasFactory;

    protected $fillable = [
        'creator_id',
        'title',
        'body',
        'visibility',
        'is_published',
        'published_at',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function isPublic(): bool
    {
        return $this->visibility === 'public';
    }

    public function isRegisteredOnly(): bool
    {
        return $this->visibility === 'registered_only';
    }

    public function isSubscriberOnly(): bool
    {
        return $this->visibility === 'subscriber_only';
    }

    public function isPpv(): bool
    {
        return $this->visibility === 'ppv';
    }
}
