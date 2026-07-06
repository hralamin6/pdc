<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class AiConversation extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'user_id',
        'title',
        'ai_provider',
        'model',
        'system_prompt',
        'total_tokens',
        'last_message_at',
    ];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
        ];
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(AiMessage::class);
    }

    // Helper Methods
    public function getDisplayTitle(): string
    {
        if ($this->title) {
            return $this->title;
        }

        $firstMessage = $this->messages()->where('role', 'user')->first();
        return $firstMessage ? \Str::limit($firstMessage->content, 50) : 'New Conversation';
    }

    public function incrementTokens(int $tokens): void
    {
        $this->increment('total_tokens', $tokens);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('attachments');
    }
}
