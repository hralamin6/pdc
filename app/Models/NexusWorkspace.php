<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NexusWorkspace extends Model
{
    protected $fillable = [
        'user_id', 'name', 'description', 'icon', 'color',
        'default_provider', 'default_model', 'system_prompt',
        'settings', 'is_default', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'settings'   => 'array',
            'is_default' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function chats(): HasMany
    {
        return $this->hasMany(NexusChat::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(NexusDocument::class);
    }

    public function getTemperature(): float
    {
        return $this->settings['temperature'] ?? 0.7;
    }

    public function getMaxTokens(): int
    {
        return $this->settings['max_tokens'] ?? 2048;
    }

    public function getEnabledTools(): array
    {
        return $this->settings['enabled_tools'] ?? ['web_search', 'calculator', 'weather', 'image_generator', 'code_executor'];
    }
}
