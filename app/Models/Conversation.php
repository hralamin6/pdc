<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    /**
     * Get the first user in the conversation.
     */
    public function userOne(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_one_id');
    }

    /**
     * Get the second user in the conversation.
     */
    public function userTwo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_two_id');
    }

    /**
     * Get all messages in the conversation.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Get users in the conversation with pivot data.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'conversation_user')
            ->withPivot('last_read_at', 'is_typing', 'typing_at', 'is_blocked')
            ->withTimestamps();
    }

    /**
     * Get the other user in the conversation.
     */
    public function getOtherUser(int $userId): ?User
    {
        if ($this->user_one_id === $userId) {
            return $this->userTwo;
        }
        return $this->userOne;
    }

    /**
     * Check if user is part of this conversation.
     */
    public function hasUser(int $userId): bool
    {
        return $this->user_one_id === $userId || $this->user_two_id === $userId;
    }

    /**
     * Get unread messages count for a user.
     */
    public function getUnreadCount(int $userId): int
    {
        return $this->messages()
            ->where('user_id', '!=', $userId)
            ->whereNull('read_at')
            ->where('is_deleted', false)
            ->count();
    }

    /**
     * Mark conversation as read for a user.
     */
    public function markAsRead(int $userId): void
    {
        $this->users()->updateExistingPivot($userId, [
            'last_read_at' => now(),
        ]);
    }

    /**
     * Get or create a conversation between two users.
     */
    public static function findOrCreateBetween(int $userOneId, int $userTwoId): self
    {
        // Ensure consistent ordering
        $ids = [$userOneId, $userTwoId];
        sort($ids);

        $conversation = self::where(function ($query) use ($ids) {
            $query->where('user_one_id', $ids[0])
                  ->where('user_two_id', $ids[1]);
        })->first();

        if (!$conversation) {
            $conversation = self::create([
                'user_one_id' => $ids[0],
                'user_two_id' => $ids[1],
            ]);

            // Attach both users to conversation_user pivot
            $conversation->users()->attach([
                $ids[0] => ['created_at' => now(), 'updated_at' => now()],
                $ids[1] => ['created_at' => now(), 'updated_at' => now()],
            ]);
        }

        return $conversation;
    }

    /**
     * Get the latest message in the conversation.
     */
    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }
}
