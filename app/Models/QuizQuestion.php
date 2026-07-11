<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizQuestion extends Model
{
    use HasFactory;

    protected $guarded = [];

    // ─── Relationships ──────────────────────────────────────────────────────────

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuizOption::class, 'question_id')->orderBy('order');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(QuizAnswer::class, 'question_id');
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────────

    /**
     * Get the correct option IDs for this question.
     *
     * @return array<int>
     */
    public function getCorrectOptionIdsAttribute(): array
    {
        return $this->options()->where('is_correct', true)->pluck('id')->toArray();
    }

    /**
     * Whether this question type requires options.
     */
    public function hasOptions(): bool
    {
        return in_array($this->type, ['mcq', 'true_false', 'multi_select']);
    }

    /**
     * Whether this question accepts text input.
     */
    public function isShortText(): bool
    {
        return $this->type === 'short_text';
    }

    /**
     * Whether this question allows multiple correct answers.
     */
    public function isMultiSelect(): bool
    {
        return $this->type === 'multi_select';
    }
}
