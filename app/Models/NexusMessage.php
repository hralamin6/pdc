<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NexusMessage extends Model
{
    protected $fillable = [
        'nexus_chat_id', 'role', 'content', 'thinking',
        'tool_name', 'tool_input', 'tool_output', 'tool_status',
        'tokens_in', 'tokens_out', 'model_used', 'is_error',
        'parent_message_id', 'metadata', 'edited_at',
    ];

    protected function casts(): array
    {
        return [
            'tool_input'  => 'array',
            'metadata'    => 'array',
            'is_error'    => 'boolean',
            'edited_at'   => 'datetime',
        ];
    }

    public function chat(): BelongsTo
    {
        return $this->belongsTo(NexusChat::class, 'nexus_chat_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_message_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(self::class, 'parent_message_id');
    }

    // ── Role helpers ────────────────────────────────────────────────────────────
    public function isUser(): bool      { return $this->role === 'user'; }
    public function isAssistant(): bool { return $this->role === 'assistant'; }
    public function isSystem(): bool    { return $this->role === 'system'; }
    public function isTool(): bool      { return $this->role === 'tool'; }

    // ── Scope ───────────────────────────────────────────────────────────────────
    public function scopeForConversation($query, int $chatId)
    {
        return $query->where('nexus_chat_id', $chatId)->orderBy('created_at');
    }
}
