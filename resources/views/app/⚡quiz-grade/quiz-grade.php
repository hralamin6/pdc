<?php

namespace App\Livewire;

use App\Models\QuizAnswer;
use App\Models\QuizAttempt;
use App\Services\QuizPointsService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

new #[Title('Grade Short Answers')] #[Layout('layouts.app')] class extends Component
{
    use Toast, WithPagination;

    public string $filterStatus = 'pending'; // pending, graded
    public int $quizId = 0; // 0 = all quizzes

    // Form inputs for overriding grades [answer_id => numeric_grade_0_to_1]
    public array $adminGrades = [];

    public function mount(): void
    {
        abort_if(! auth()->user()->can('quiz.grade'), 403);
    }

    #[\Livewire\Attributes\Computed]
    public function quizzes()
    {
        return \App\Models\Quiz::whereHas('questions', function ($q) {
            $q->where('type', 'short_text');
        })->orderByDesc('created_at')->get(['id', 'title']);
    }

    #[\Livewire\Attributes\Computed]
    public function answers()
    {
        $query = QuizAnswer::with(['question', 'attempt.user', 'attempt.quiz'])
            ->whereHas('question', function ($q) {
                $q->where('type', 'short_text');
            });

        if ($this->quizId) {
            $query->whereHas('attempt', function ($q) {
                $q->where('quiz_id', $this->quizId);
            });
        }

        if ($this->filterStatus === 'pending') {
            $query->whereNull('admin_grade');
        } else {
            $query->whereNotNull('admin_grade');
        }

        $results = $query->orderBy('created_at', 'desc')->paginate(15);

        foreach ($results as $ans) {
            if (! isset($this->adminGrades[$ans->id])) {
                // If it's pending, pre-fill with AI's grade (if any). If graded, use admin's grade.
                $this->adminGrades[$ans->id] = $ans->admin_grade ?? $ans->ai_grade ?? 0.0;
            }
        }

        return $results;
    }

    public function saveGrade(int $answerId)
    {
        $grade = $this->adminGrades[$answerId] ?? null;

        if ($grade === null || $grade < 0 || $grade > 1) {
            $this->error(__('Grade must be between 0.0 and 1.0'));

            return;
        }

        $answer = QuizAnswer::with('question', 'attempt')->findOrFail($answerId);
        $answer->admin_grade = (float) $grade;
        $answer->marks_awarded = $answer->question->marks * $answer->admin_grade;
        $answer->is_correct = $answer->admin_grade > 0;
        $answer->save();

        $attempt = $answer->attempt;
        $attempt->calculateAndSaveScore();
        $attempt->recalculateRanks();

        $this->success(__('Grade saved and score updated.'));
    }

    public function autoConfirmHighConfidence()
    {
        $answers = QuizAnswer::with('question', 'attempt')
            ->whereHas('question', fn ($q) => $q->where('type', 'short_text'))
            ->whereNull('admin_grade')
            ->where('ai_grade', '>=', 0.85)
            ->get();

        $count = 0;
        foreach ($answers as $answer) {
            $answer->admin_grade = $answer->ai_grade;
            $answer->marks_awarded = $answer->question->marks * $answer->admin_grade;
            $answer->is_correct = true;
            $answer->save();

            $answer->attempt->calculateAndSaveScore();
            $count++;
        }

        if ($count > 0) {
            $quizzes = $answers->pluck('attempt.quiz_id')->unique();
            foreach ($quizzes as $qid) {
                $a = $answers->where('attempt.quiz_id', $qid)->first();
                $a?->attempt->recalculateRanks();
            }
        }

        $this->success(__('Auto-confirmed :count high-confidence answers.', ['count' => $count]));
    }

    public function evaluatePendingWithAi()
    {
        $answers = QuizAnswer::with('question')
            ->whereHas('question', fn ($q) => $q->where('type', 'short_text'))
            ->whereNull('admin_grade')
            ->whereNull('ai_grade')
            ->get();

        if ($answers->isEmpty()) {
            $this->warning(__('No pending un-evaluated answers found.'));
            return;
        }

        $count = 0;
        $aiService = app(\App\Services\QuizAiService::class);

        foreach ($answers as $answer) {
            try {
                $result = $aiService->gradeShortText($answer->question, $answer->text_answer ?? '');
                $answer->ai_grade = $result['grade'];
                $answer->ai_grade_reason = $result['reason'];
                $answer->save();
                $count++;
            } catch (\Exception $e) {
                // Keep evaluating others
            }
        }

        $this->success(__('AI evaluated :count pending answers.', ['count' => $count]));
    }

    public function reevaluateAllWithAi()
    {
        $answers = QuizAnswer::with('question')
            ->whereHas('question', fn ($q) => $q->where('type', 'short_text'))
            ->whereNull('admin_grade')
            ->get();

        if ($answers->isEmpty()) {
            $this->warning(__('No pending answers found to re-evaluate.'));
            return;
        }

        $count = 0;
        $aiService = app(\App\Services\QuizAiService::class);

        foreach ($answers as $answer) {
            try {
                $result = $aiService->gradeShortText($answer->question, $answer->text_answer ?? '');
                $answer->ai_grade = $result['grade'];
                $answer->ai_grade_reason = $result['reason'];
                $answer->save();
                $count++;
            } catch (\Exception $e) {
                // Keep evaluating others
            }
        }

        $this->success(__('AI re-evaluated :count answers.', ['count' => $count]));
    }
};
