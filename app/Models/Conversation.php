<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_one_id',
        'user_two_id',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function userOne(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_one_id');
    }

    public function userTwo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_two_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'asc');
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    /**
     * Check if a user is a participant in this conversation.
     */
    public function hasParticipant(int $userId): bool
    {
        return $this->user_one_id === $userId || $this->user_two_id === $userId;
    }

    /**
     * Get the other participant in the conversation.
     */
    public function otherUser(int $currentUserId): BelongsTo
    {
        $otherUserId = $this->user_one_id === $currentUserId
            ? $this->user_two_id
            : $this->user_one_id;

        return $this->belongsTo(User::class, $this->user_one_id === $currentUserId ? 'user_two_id' : 'user_one_id');
    }

    /**
     * Get the other participant's user ID.
     */
    public function otherUserId(int $currentUserId): int
    {
        return $this->user_one_id === $currentUserId
            ? $this->user_two_id
            : $this->user_one_id;
    }

    /**
     * Find or create a conversation between two users.
     */
    public static function findOrCreateBetween(int $userOneId, int $userTwoId): self
    {
        $ids = [min($userOneId, $userTwoId), max($userOneId, $userTwoId)];

        return self::firstOrCreate([
            'user_one_id' => $ids[0],
            'user_two_id' => $ids[1],
        ]);
    }

    /**
     * Scope: conversations for a given user.
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_one_id', $userId)
            ->orWhere('user_two_id', $userId);
    }
}
