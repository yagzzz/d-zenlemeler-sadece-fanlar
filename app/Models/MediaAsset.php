<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MediaAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'creator_id',
        'content_id',
        'type',
        'status',
        'provider',
        'bucket',
        'object_key',
        'original_filename',
        'mime_type',
        'size_bytes',
        'width',
        'height',
        'duration_seconds',
        'variants',
    ];

    protected $casts = [
        'variants' => 'array',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function contents(): BelongsToMany
    {
        return $this->belongsToMany(Content::class, 'content_media')
            ->withPivot('position')
            ->withTimestamps();
    }
}
