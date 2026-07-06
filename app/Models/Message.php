<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Message extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'conversation_id',
        'user_id',
        'parent_id',
        'body',
        'read_at',
        'edited_at',
        'is_deleted',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'edited_at' => 'datetime',
        'is_deleted' => 'boolean',
    ];

    protected $with = ['reactions'];

    /**
     * Register media collections.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('attachments')
            ->useDisk('public');
    }

    /**
     * Get the conversation that owns the message.
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Get the user that sent the message.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent message (for threading).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'parent_id');
    }

    /**
     * Get replies to this message.
     */
    public function replies(): HasMany
    {
        return $this->hasMany(Message::class, 'parent_id');
    }

    /**
     * Get attachments for the message (legacy - kept for compatibility).
     * Use getMedia('attachments') instead.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(MessageAttachment::class);
    }

    /**
     * Check if message has attachments.
     */
    public function hasAttachments(): bool
    {
        return $this->getMedia('attachments')->count() > 0;
    }

    /**
     * Get reactions for the message.
     */
    public function reactions(): HasMany
    {
        return $this->hasMany(MessageReaction::class);
    }

    /**
     * Scope to get only non-deleted messages.
     */
    public function scopeNotDeleted(Builder $query): Builder
    {
        return $query->where('is_deleted', false);
    }

    /**
     * Scope to get only parent messages (not replies).
     */
    public function scopeParentMessages(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Mark message as read.
     */
    public function markAsRead(): void
    {
        if (!$this->read_at) {
            $this->update(['read_at' => now()]);
        }
    }

    /**
     * Check if message is edited.
     */
    public function isEdited(): bool
    {
        return !is_null($this->edited_at);
    }

    /**
     * Soft delete message (keep in DB but mark as deleted).
     */
    public function softDeleteMessage(): void
    {
        $this->update([
            'is_deleted' => true,
            'body' => null,
        ]);

        // Delete media attachments
        $this->clearMediaCollection('attachments');
        
        // Delete legacy attachments if any
        $this->attachments()->delete();
    }

    /**
     * Get grouped reactions with counts.
     */
    public function getGroupedReactions(): array
    {
        return $this->reactions()
            ->selectRaw('emoji, count(*) as count, GROUP_CONCAT(user_id) as user_ids')
            ->groupBy('emoji')
            ->get()
            ->map(function ($reaction) {
                return [
                    'emoji' => $reaction->emoji,
                    'count' => $reaction->count,
                    'user_ids' => explode(',', $reaction->user_ids),
                ];
            })
            ->toArray();
    }

    /**
     * Get formatted body with rich text support (safe HTML)
     */
    public function getFormattedBodyAttribute(): string
    {
        if (!$this->body) {
            return '';
        }

        // Escape HTML first for security
        $formatted = e($this->body);
        
        // Convert URLs to clickable links
        $formatted = preg_replace(
            '/(https?:\/\/[^\s]+)/i',
            '<a href="$1" target="_blank" rel="noopener noreferrer" class="text-blue-500 hover:underline break-all">$1</a>',
            $formatted
        );
        
        // Convert **bold** to <strong>
        $formatted = preg_replace(
            '/\*\*(.+?)\*\*/s',
            '<strong class="font-bold">$1</strong>',
            $formatted
        );
        
        // Convert *italic* to <em>
        $formatted = preg_replace(
            '/\*(.+?)\*/s',
            '<em class="italic">$1</em>',
            $formatted
        );
        
        // Convert `code` to <code>
        $formatted = preg_replace(
            '/`(.+?)`/s',
            '<code class="px-1.5 py-0.5 bg-base-300 rounded text-xs font-mono">$1</code>',
            $formatted
        );
        
        // Convert line breaks
        $formatted = nl2br($formatted);
        
        return $formatted;
    }
}
