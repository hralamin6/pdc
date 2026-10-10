<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NexusChat extends Model
{
    protected $fillable = [
        'nexus_workspace_id', 'user_id', 'title', 'provider', 'model',
        'system_prompt', 'total_tokens_in', 'total_tokens_out',
        'message_count', 'last_message_at', 'is_pinned', 'is_archived',
        'branch_of_message_id',
    ];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
            'is_pinned'       => 'boolean',
            'is_archived'     => 'boolean',
        ];
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(NexusWorkspace::class, 'nexus_workspace_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(NexusMessage::class);
    }

    public function branchMessage(): BelongsTo
    {
        return $this->belongsTo(NexusMessage::class, 'branch_of_message_id');
    }

    public function getDisplayTitle(): string
    {
        if ($this->title) {
            return $this->title;
        }
        $first = $this->messages()->where('role', 'user')->first();

        return $first ? \Str::limit($first->content, 45) : 'New Chat';
    }

    public function getTotalTokens(): int
    {
        return $this->total_tokens_in + $this->total_tokens_out;
    }

    public function incrementTokens(int $in, int $out): void
    {
        $this->increment('total_tokens_in', $in);
        $this->increment('total_tokens_out', $out);
        $this->increment('message_count');
    }
}
