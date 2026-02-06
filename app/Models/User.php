<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'creator_applied_at',
        'creator_approved_at',
        'creator_rejected_at',
        'creator_rejection_reason',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'creator_applied_at' => 'datetime',
            'creator_approved_at' => 'datetime',
            'creator_rejected_at' => 'datetime',
        ];
    }

    public function creatorProfile(): HasOne
    {
        return $this->hasOne(CreatorProfile::class, 'user_id');
    }

    public function contents(): HasMany
    {
        return $this->hasMany(Content::class, 'creator_id');
    }

    public function tiers(): HasMany
    {
        return $this->hasMany(Tier::class, 'creator_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(Reaction::class);
    }

    public function isCreator(): bool
    {
        return $this->role === 'creator' && $this->creator_approved_at !== null;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
