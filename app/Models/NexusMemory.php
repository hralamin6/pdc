<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NexusMemory extends Model
{
    protected $fillable = [
        'user_id', 'key', 'value', 'category', 'source_chat_id', 'embedding',
    ];

    protected function casts(): array
    {
        return ['embedding' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sourceChat(): BelongsTo
    {
        return $this->belongsTo(NexusChat::class, 'source_chat_id');
    }
}
