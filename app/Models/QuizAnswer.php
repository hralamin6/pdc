<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizAnswer extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'selected_option_ids' => 'array',
            'is_correct' => 'boolean',
            'answered_at' => 'datetime',
        ];
    }

    // ─── Relationships ──────────────────────────────────────────────────────────

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(QuizAttempt::class, 'attempt_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(QuizQuestion::class, 'question_id');
    }

    // ─── Grading ─────────────────────────────────────────────────────────────────

    /**
     * Calculate and save marks for this answer based on quiz scoring rules.
     * Handles MCQ, True/False, Multi-select, and Short-text.
     */
    public function grade(): void
    {
        $question = $this->question;
        $quiz = $this->attempt->quiz;
        $negativeMarkValue = $quiz->negative_marking ? $quiz->negative_mark_value : 0;

        $marksAwarded = 0;
        $isCorrect = null;

        if ($question->isShortText()) {
            // Short text: graded externally (AI or admin), marks_awarded set separately
            return;
        }

        $correctIds = $question->correct_option_ids;
        $selectedIds = $this->selected_option_ids ?? [];

        if ($question->isMultiSelect()) {
            // Partial marking for multi-select
            $numCorrect = count($correctIds);
            $marksPerOption = $numCorrect > 0 ? $question->marks / $numCorrect : 0;

            foreach ($selectedIds as $selectedId) {
                if (in_array($selectedId, $correctIds)) {
                    $marksAwarded += $marksPerOption;
                } else {
                    $marksAwarded -= $question->marks * $negativeMarkValue;
                }
            }

            $isCorrect = empty(array_diff($correctIds, $selectedIds))
                && empty(array_diff($selectedIds, $correctIds));
        } else {
            // MCQ / True-False: single correct answer
            $selectedId = $selectedIds[0] ?? null;
            if ($selectedId && in_array($selectedId, $correctIds)) {
                $marksAwarded = $question->marks;
                $isCorrect = true;
            } elseif ($selectedId) {
                $marksAwarded = -($question->marks * $negativeMarkValue);
                $isCorrect = false;
            } else {
                $marksAwarded = 0; // Skipped
                $isCorrect = false;
            }
        }

        $this->update([
            'is_correct' => $isCorrect,
            'marks_awarded' => $marksAwarded,
        ]);
    }
}
