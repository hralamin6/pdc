<?php

use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\QuizAttempt;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use App\Services\QuizPointsService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

new #[Title('Take Quiz')] #[Layout('layouts.web')] class extends Component
{
    use Toast;

    // ─── Page State Machine ───────────────────────────────────────────────────
    // 'intro' | 'taking' | 'results'
    public string $screen = 'intro';

    // ─── Quiz & Attempt ───────────────────────────────────────────────────────
    public Quiz $quiz;
    public ?QuizAttempt $attempt = null;

    /** @var array Ordered question IDs for this attempt (shuffled snapshot) */
    public array $questionOrder = [];

    /** @var int Index of current question being displayed (0-based) */
    public int $currentIndex = 0;

    /** @var array<int, array> Flat answer state keyed by question_id */
    public array $answers = [];

    /** @var array<int, bool> Flagged question IDs for review */
    public array $flagged = [];

    // ─── Timer ───────────────────────────────────────────────────────────────
    public int $remainingSeconds = 0;
    public bool $timerExpired = false;

    // ─── Results ─────────────────────────────────────────────────────────────
    public ?array $results = null;

    // ─── UI State ────────────────────────────────────────────────────────────
    public bool $showSubmitConfirm = false;
    public bool $showNavigator = false;

    // ─── Mount ───────────────────────────────────────────────────────────────

    public function mount(Quiz $quiz): void
    {
        $this->quiz = $quiz->load(['questions.options']);

        // Gate check
        abort_if(! auth()->user()->can('quiz.attempt'), 403);
        abort_unless($this->quiz->isAvailable() || $this->quiz->status === 'closed', 404);

        // If already attempted and completed — go straight to results
        $existing = $this->quiz->userAttempt();
        if ($existing && $existing->status === 'submitted') {
            $this->attempt = $existing;
            $this->loadResults();
            $this->screen = 'results';

            return;
        }

        // Resume in-progress attempt
        if ($existing && $existing->status === 'in_progress') {
            if ($existing->isExpired()) {
                $this->autoSubmit($existing);
            } else {
                $this->attempt = $existing;
                $this->questionOrder = $existing->question_order ?? $this->quiz->questions->pluck('id')->toArray();
                $this->loadAnswersFromDb();
                $this->syncTimer();
                $this->screen = 'taking';
            }
        }
    }

    // ─── Computed ─────────────────────────────────────────────────────────────

    public function with(): array
    {
        return [
            'orderedQuestions' => $this->orderedQuestions(),
            'currentQuestion' => $this->currentQuestion(),
            'totalQuestions' => $this->totalQuestions(),
            'answeredCount' => $this->answeredCount(),
            'unansweredCount' => $this->unansweredCount(),
            'progressPercent' => $this->progressPercent(),
            'backUrl' => $this->backUrl(),
        ];
    }

    public function backUrl(): string
    {
        if ($this->quiz->quizzable_type === \App\Models\Halaqah::class) {
            return route('web.halaqah.show', $this->quiz->quizzable_id);
        }
        if ($this->quiz->quizzable_type === \App\Models\HalaqahSeries::class) {
            return route('web.course.show', $this->quiz->quizzable_id);
        }
        return route('web.my-quizzes');
    }

    /**
     * Get ordered questions for this attempt.
     *
     * @return Collection<QuizQuestion>
     */
    public function orderedQuestions(): Collection
    {
        if (empty($this->questionOrder)) {
            return $this->quiz->questions;
        }

        return collect($this->questionOrder)->map(
            fn ($id) => $this->quiz->questions->firstWhere('id', $id)
        )->filter();
    }

    public function currentQuestion(): ?QuizQuestion
    {
        return $this->orderedQuestions()->values()->get($this->currentIndex);
    }

    public function totalQuestions(): int
    {
        return $this->orderedQuestions()->count();
    }

    public function answeredCount(): int
    {
        return collect($this->answers)->filter(function ($ans) {
            return ! empty($ans['selected_option_ids']) || ! empty(trim($ans['text_answer']));
        })->count();
    }

    public function unansweredCount(): int
    {
        return $this->totalQuestions() - $this->answeredCount();
    }

    public function progressPercent(): int
    {
        return $this->totalQuestions() > 0
            ? (int) round(($this->answeredCount() / $this->totalQuestions()) * 100)
            : 0;
    }

    // ─── Intro Actions ────────────────────────────────────────────────────────

    public function startQuiz(): void
    {
        abort_if(! auth()->user()->can('quiz.attempt'), 403);

        if ($this->quiz->questions->count() === 0) {
            $this->error('This quiz is empty and cannot be started.');
            return;
        }

        // Prevent re-attempt
        if ($this->quiz->userAttempt()?->status === 'submitted') {
            $this->error('You have already completed this quiz.');

            return;
        }

        // Determine question order
        $ids = $this->quiz->questions->pluck('id')->toArray();
        if ($this->quiz->shuffle_questions) {
            shuffle($ids);
        }

        $this->questionOrder = $ids;

        // Create attempt record
        $this->attempt = QuizAttempt::create([
            'quiz_id' => $this->quiz->id,
            'user_id' => auth()->id(),
            'started_at' => now(),
            'status' => 'in_progress',
            'question_order' => $ids,
        ]);

        // Init answer slots (skipped = null)
        foreach ($ids as $qid) {
            $this->answers[$qid] = [
                'selected_option_ids' => [],
                'text_answer' => '',
                'answer_id' => null,
            ];
        }

        $this->currentIndex = 0;
        $this->syncTimer();
        $this->screen = 'taking';
    }

    // ─── Navigation ───────────────────────────────────────────────────────────

    public function goToQuestion(int $index): void
    {
        $this->saveCurrentAnswer();
        $this->currentIndex = max(0, min($index, $this->totalQuestions() - 1));
        $this->showNavigator = false;
    }

    public function nextQuestion(): void
    {
        $this->saveCurrentAnswer();
        if ($this->currentIndex < $this->totalQuestions() - 1) {
            $this->currentIndex++;
        }
    }

    public function prevQuestion(): void
    {
        $this->saveCurrentAnswer();
        if ($this->currentIndex > 0) {
            $this->currentIndex--;
        }
    }

    public function toggleFlag(): void
    {
        $qid = $this->currentQuestion()?->id;
        if (! $qid) {
            return;
        }
        $this->flagged[$qid] = ! ($this->flagged[$qid] ?? false);
    }

    // ─── Answer Recording ────────────────────────────────────────────────────

    public function selectOption(int $optionId): void
    {
        $q = $this->currentQuestion();
        if (! $q) {
            return;
        }

        $qid = $q->id;

        if ($q->isMultiSelect()) {
            // Toggle in multi-select
            $current = $this->answers[$qid]['selected_option_ids'] ?? [];
            if (in_array($optionId, $current)) {
                $this->answers[$qid]['selected_option_ids'] = array_values(array_filter($current, fn ($id) => $id !== $optionId));
            } else {
                $this->answers[$qid]['selected_option_ids'][] = $optionId;
            }
        } else {
            // Single-select: replace
            $this->answers[$qid]['selected_option_ids'] = [$optionId];
        }
    }

    private function saveCurrentAnswer(): void
    {
        if (! $this->attempt || ! $this->currentQuestion()) {
            return;
        }

        $q = $this->currentQuestion();
        $qid = $q->id;
        $answerData = $this->answers[$qid] ?? [];

        $selectedIds = $answerData['selected_option_ids'] ?? [];
        $textAnswer = $answerData['text_answer'] ?? '';

        // Don't save empty answers
        if (empty($selectedIds) && empty(trim($textAnswer))) {
            return;
        }

        $record = QuizAnswer::updateOrCreate(
            [
                'attempt_id' => $this->attempt->id,
                'question_id' => $qid,
            ],
            [
                'selected_option_ids' => $selectedIds ?: null,
                'text_answer' => $textAnswer ?: null,
                'answered_at' => now(),
            ]
        );

        $this->answers[$qid]['answer_id'] = $record->id;
    }

    // ─── Submission ───────────────────────────────────────────────────────────

    public function confirmSubmit(): void
    {
        $this->saveCurrentAnswer();
        $this->showSubmitConfirm = true;
    }

    public function submitQuiz(): void
    {
        if (! $this->attempt) {
            return;
        }

        $this->saveCurrentAnswer();
        $this->showSubmitConfirm = false;

        // Grade all answers
        $answers = QuizAnswer::with('question.options')
            ->where('attempt_id', $this->attempt->id)
            ->get();

        foreach ($answers as $answer) {
            $answer->grade();
        }

        // Calculate final score
        $this->attempt->calculateAndSaveScore();

        // Recalculate all ranks for this quiz
        $this->attempt->recalculateRanks();
        $this->attempt->refresh();

        // Award gamification points
        app(QuizPointsService::class)->award($this->attempt);

        $this->loadResults();
        $this->screen = 'results';
    }

    public function timerTick(): void
    {
        if (! $this->attempt || $this->timerExpired) {
            return;
        }

        $this->remainingSeconds = $this->calcRemainingSeconds();

        if ($this->remainingSeconds <= 0) {
            $this->timerExpired = true;
            $this->autoSubmit($this->attempt);
        }
    }

    private function autoSubmit(QuizAttempt $attempt): void
    {
        $this->attempt = $attempt;
        $attempt->update(['status' => 'timed_out', 'submitted_at' => now()]);

        // Grade what we have
        QuizAnswer::with('question.options')
            ->where('attempt_id', $attempt->id)
            ->each(fn ($a) => $a->grade());

        $attempt->calculateAndSaveScore();
        $attempt->recalculateRanks();
        $attempt->refresh();
        app(QuizPointsService::class)->award($attempt);

        $this->loadResults();
        $this->screen = 'results';

        $this->warning('Time is up! Your quiz was auto-submitted.');
    }

    // ─── Results Loading ─────────────────────────────────────────────────────

    private function loadResults(): void
    {
        $attempt = $this->attempt->load(['answers.question.options']);
        $quiz = $this->quiz->load('questions.options');

        $questionBreakdown = [];

        foreach ($attempt->answers as $answer) {
            $q = $answer->question;
            if (! $q) {
                continue;
            }

            $correctOptionIds = $q->options->where('is_correct', true)->pluck('id')->toArray();
            $selectedIds = $answer->selected_option_ids ?? [];

            $questionBreakdown[] = [
                'question_text' => $q->question_text,
                'type' => $q->type,
                'marks' => $q->marks,
                'marks_awarded' => $answer->marks_awarded,
                'is_correct' => $answer->is_correct,
                'selected_option_ids' => $selectedIds,
                'correct_option_ids' => $correctOptionIds,
                'text_answer' => $answer->text_answer,
                'ai_grade' => $answer->ai_grade,
                'ai_grade_reason' => $answer->ai_grade_reason,
                'admin_grade' => $answer->admin_grade,
                'ideal_answer' => $q->ideal_answer,
                'explanation' => $q->ai_explanation,
                'options' => $q->options->map(fn ($o) => [
                    'id' => $o->id,
                    'option_text' => $o->option_text,
                    'is_correct' => $o->is_correct,
                ])->toArray(),
            ];
        }

        // Rank among this quiz's submitted attempts
        $totalAttempts = QuizAttempt::where('quiz_id', $this->quiz->id)
            ->where('status', 'submitted')
            ->count();

        $this->results = [
            'score_raw' => $attempt->score_raw,
            'score_percentage' => $attempt->score_percentage,
            'total_marks' => $quiz->total_marks,
            'passed' => $attempt->passed,
            'pass_mark_percent' => $quiz->pass_mark_percent,
            'rank' => $attempt->rank,
            'total_participants' => $totalAttempts,
            'time_taken_seconds' => $attempt->time_taken_seconds,
            'status' => $attempt->status,
            'points_awarded' => $attempt->points_awarded,
            'questions' => $questionBreakdown,
            'negative_marking' => $quiz->negative_marking,
            'show_answers_after' => $quiz->show_answers_after,
        ];
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function syncTimer(): void
    {
        $this->remainingSeconds = $this->calcRemainingSeconds();
    }

    private function calcRemainingSeconds(): int
    {
        if (! $this->quiz->time_limit_minutes || ! $this->attempt) {
            return PHP_INT_MAX;
        }

        $deadline = $this->attempt->started_at->addMinutes($this->quiz->time_limit_minutes);

        return max(0, (int) now()->diffInSeconds($deadline, false));
    }

    private function loadAnswersFromDb(): void
    {
        $existing = QuizAnswer::where('attempt_id', $this->attempt->id)->get();
        foreach ($existing as $ans) {
            $this->answers[$ans->question_id] = [
                'selected_option_ids' => $ans->selected_option_ids ?? [],
                'text_answer' => $ans->text_answer ?? '',
                'answer_id' => $ans->id,
            ];
        }

        // Fill missing question slots
        foreach ($this->questionOrder as $qid) {
            if (! isset($this->answers[$qid])) {
                $this->answers[$qid] = ['selected_option_ids' => [], 'text_answer' => '', 'answer_id' => null];
            }
        }
    }
};
