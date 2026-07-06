<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class AiMessage extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'ai_conversation_id',
        'role',
        'content',
        'tokens',
        'metadata',
        'parent_id',
        'edited_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'edited_at' => 'datetime',
        ];
    }

    // Relationships
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(AiConversation::class, 'ai_conversation_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(AiMessage::class, 'parent_id');
    }

    // Helper Methods
    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    public function isAssistant(): bool
    {
        return $this->role === 'assistant';
    }

    public function isSystem(): bool
    {
        return $this->role === 'system';
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('attachments');
    }
}
