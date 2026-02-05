<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreatorProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bio',
        'tagline',
        'avatar_media_id',
        'banner_media_id',
        'categories',
        'tags',
        'social_links',
        'wallet_xmr_address',
        'default_subscription_price_atomic',
        'currency',
        'allow_free_content',
        'allow_registered_only',
        'allow_ppv',
        'allow_tips',
    ];

    protected $casts = [
        'categories' => 'array',
        'tags' => 'array',
        'social_links' => 'array',
        'allow_free_content' => 'boolean',
        'allow_registered_only' => 'boolean',
        'allow_ppv' => 'boolean',
        'allow_tips' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function avatarMedia(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'avatar_media_id');
    }

    public function bannerMedia(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'banner_media_id');
    }
}
