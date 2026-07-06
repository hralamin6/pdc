<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageReaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'message_id',
        'user_id',
        'emoji',
    ];

    /**
     * Get the message that owns the reaction.
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    /**
     * Get the user that created the reaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Toggle a reaction for a message.
     */
    public static function toggle(int $messageId, int $userId, string $emoji): bool
    {
        $reaction = self::where([
            'message_id' => $messageId,
            'user_id' => $userId,
            'emoji' => $emoji,
        ])->first();

        if ($reaction) {
            $reaction->delete();
            return false; // Removed
        }

        self::create([
            'message_id' => $messageId,
            'user_id' => $userId,
            'emoji' => $emoji,
        ]);

        return true; // Added
    }
}
