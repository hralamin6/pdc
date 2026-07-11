<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizAttempt extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'submitted_at' => 'datetime',
            'passed' => 'boolean',
            'question_order' => 'array',
        ];
    }

    // ─── Relationships ──────────────────────────────────────────────────────────

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(QuizAnswer::class, 'attempt_id');
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────────

    /**
     * Check if this attempt is still within the allowed time window.
     */
    public function isExpired(): bool
    {
        if (! $this->quiz->time_limit_minutes) {
            return false;
        }

        return $this->started_at->addMinutes($this->quiz->time_limit_minutes)->isPast();
    }

    /**
     * Seconds remaining for the attempt.
     */
    public function getRemainingSecondsAttribute(): int
    {
        if (! $this->quiz->time_limit_minutes) {
            return PHP_INT_MAX;
        }

        $deadline = $this->started_at->addMinutes($this->quiz->time_limit_minutes);

        return max(0, (int) now()->diffInSeconds($deadline, false));
    }

    /**
     * Calculate and persist the final score for this attempt.
     */
    public function calculateAndSaveScore(): void
    {
        $quiz = $this->quiz;
        $answers = $this->answers()->with('question')->get();

        $scoreRaw = 0.0;
        $totalPossible = $quiz->total_marks;

        foreach ($answers as $answer) {
            if (! is_null($answer->marks_awarded)) {
                $scoreRaw += (float) $answer->marks_awarded;
            }
        }

        // Score cannot go below 0 unless explicitly desired
        $scoreRaw = max(0, $scoreRaw);
        $percentage = $totalPossible > 0 ? round(($scoreRaw / $totalPossible) * 100, 2) : 0;
        $passed = $quiz->pass_mark_percent ? $percentage >= $quiz->pass_mark_percent : null;
        $timeTaken = $this->started_at->diffInSeconds($this->submitted_at ?? now());

        $this->update([
            'score_raw' => $scoreRaw,
            'score_percentage' => $percentage,
            'passed' => $passed,
            'time_taken_seconds' => $timeTaken,
            'status' => 'submitted',
            'submitted_at' => $this->submitted_at ?? now(),
        ]);
    }

    /**
     * Recalculate ranks for all submitted attempts of this quiz.
     */
    public function recalculateRanks(): void
    {
        $attempts = QuizAttempt::where('quiz_id', $this->quiz_id)
            ->where('status', 'submitted')
            ->orderByDesc('score_percentage')
            ->orderBy('time_taken_seconds') // Tiebreaker: faster wins
            ->get();

        foreach ($attempts as $rank => $attempt) {
            $attempt->update(['rank' => $rank + 1]);
        }
    }
}
