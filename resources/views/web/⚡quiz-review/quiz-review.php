<?php

use App\Models\Quiz;
use App\Models\QuizAttempt;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Review Attempt | PSTU Dawah')] #[Layout('layouts.web')] class extends Component
{
    public Quiz $quiz;
    public QuizAttempt $attempt;
    public array $answers = [];

    public function mount(Quiz $quiz, QuizAttempt $attempt): void
    {
        abort_unless(auth()->check(), 401);

        // Security check: Only the owner or Admins/Mentors can review this attempt
        abort_unless(
            $attempt->user_id === auth()->id() || auth()->user()->hasAnyRole(['super-admin', 'admin', 'mentor']),
            403
        );

        $this->quiz = $quiz->load(['questions.options']);
        $this->attempt = $attempt->load(['answers']);

        // Load answers keyed by question_id
        foreach ($this->attempt->answers as $ans) {
            $this->answers[$ans->question_id] = [
                'selected_option_ids' => $ans->selected_option_ids ?? [],
                'text_answer' => $ans->text_answer ?? '',
                'is_correct' => $ans->is_correct,
                'marks_awarded' => $ans->marks_awarded,
            ];
        }
    }

    public function orderedQuestions()
    {
        if (empty($this->attempt->question_order)) {
            return $this->quiz->questions;
        }

        return collect($this->attempt->question_order)
            ->map(fn ($id) => $this->quiz->questions->firstWhere('id', $id))
            ->filter();
    }
}
