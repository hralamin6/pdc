<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Quiz extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'available_from' => 'datetime',
            'available_until' => 'datetime',
            'live_started_at' => 'datetime',
            'shuffle_questions' => 'boolean',
            'shuffle_options' => 'boolean',
            'show_answers_after' => 'boolean',
            'negative_marking' => 'boolean',
            'ai_generated' => 'boolean',
            'bonus_points_for_rank' => 'array',
        ];
    }

    // ─── Relationships ──────────────────────────────────────────────────────────

    /**
     * The parent resource this quiz belongs to (Halaqah, HalaqahSeries, or null).
     */
    public function quizzable(): MorphTo
    {
        return $this->morphTo();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class)->orderBy('order');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    // ─── Scopes ─────────────────────────────────────────────────────────────────

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopeLive(Builder $query): Builder
    {
        return $query->where('status', 'live');
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->where(fn ($q) => $q->whereNull('available_from')->orWhere('available_from', '<=', now()))
            ->where(fn ($q) => $q->whereNull('available_until')->orWhere('available_until', '>=', now()));
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────────

    /**
     * Calculate total possible marks for the quiz.
     */
    public function getTotalMarksAttribute(): float
    {
        return $this->questions()->sum('marks');
    }

    /**
     * Get the user's existing attempt, if any.
     */
    public function userAttempt(?int $userId = null): ?QuizAttempt
    {
        return $this->attempts()
            ->where('user_id', $userId ?? auth()->id())
            ->latest()
            ->first();
    }

    /**
     * Check if the quiz is currently open for taking.
     */
    public function isAvailable(): bool
    {
        if (! in_array($this->status, ['published', 'live'])) {
            return false;
        }

        if ($this->available_from && $this->available_from->isFuture()) {
            return false;
        }

        if ($this->available_until && $this->available_until->isPast()) {
            return false;
        }

        return true;
    }
}
