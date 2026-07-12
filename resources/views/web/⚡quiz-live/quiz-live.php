<?php

use App\Events\QuizLeaderboardUpdated;
use App\Events\QuizLiveStarted;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\QuizAttempt;
use App\Services\QuizPointsService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

new #[Title('Live Quiz')] #[Layout('layouts.web')] class extends Component
{
    use Toast;

    // 'waiting' | 'taking' | 'results'
    public string $screen = 'waiting';

    public Quiz $quiz;
    public ?QuizAttempt $attempt = null;
    public array $questionOrder = [];
    public int $currentIndex = 0;
    public array $answers = [];
    public array $leaderboard = [];
    public int $remainingSeconds = 0;
    public bool $timerExpired = false;
    public bool $showSubmitConfirm = false;

    public function mount(Quiz $quiz): void
    {
        abort_if(! auth()->user()->can('quiz.attempt'), 403);
        $this->quiz = $quiz->load(['questions.options']);

        // Already submitted?
        $existing = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('user_id', auth()->id())
            ->whereIn('status', ['submitted', 'timed_out'])
            ->first();

        if ($existing) {
            $this->attempt = $existing;
            $this->screen = 'results';
            $this->refreshLeaderboard();

            return;
        }

        // Quiz already live — join immediately
        if ($quiz->status === 'live') {
            $this->joinLiveQuiz();
        }
    }

    // ─── Reverb Listeners ─────────────────────────────────────────────────────

    /**
     * Called by Alpine/Echo when QuizLiveStarted event is received.
     */
    #[On('quiz-live-started')]
    public function onLiveStarted($quiz_id = null): void
    {
        if ($quiz_id !== $this->quiz->id) {
            return;
        }

        $this->quiz->refresh();
        $this->joinLiveQuiz();
        $this->info('⚡ The quiz has started!');
    }

    /**
     * Called by Alpine/Echo when LeaderboardUpdated event is received.
     */
    #[On('leaderboard-updated')]
    public function onLeaderboardUpdated($quiz_id = null, $leaderboard = []): void
    {
        if ($quiz_id !== $this->quiz->id) {
            return;
        }

        $this->leaderboard = $leaderboard ?? [];
    }

    // ─── Computed ─────────────────────────────────────────────────────────────

    public function with(): array
    {
        return [
            'orderedQuestions' => $this->orderedQuestions(),
            'currentQuestion' => $this->currentQuestion(),
            'totalQuestions' => $this->totalQuestions(),
            'answeredCount' => $this->answeredCount(),
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

    public function orderedQuestions(): Collection
    {
        if (empty($this->questionOrder)) {
            return $this->quiz->questions;
        }

        return collect($this->questionOrder)
            ->map(fn ($id) => $this->quiz->questions->firstWhere('id', $id))
            ->filter();
    }

    public function currentQuestion()
    {
        return $this->orderedQuestions()->values()->get($this->currentIndex);
    }

    public function totalQuestions(): int
    {
        return $this->orderedQuestions()->count();
    }

    public function answeredCount(): int
    {
        return collect($this->answers)->filter(
            fn ($a) => ! empty($a['selected_option_ids']) || ! empty($a['text_answer'])
        )->count();
    }

    public function progressPercent(): int
    {
        return $this->totalQuestions() > 0
            ? (int) round(($this->answeredCount() / $this->totalQuestions()) * 100)
            : 0;
    }

    // ─── Join / Start ─────────────────────────────────────────────────────────

    private function joinLiveQuiz(): void
    {
        $existing = QuizAttempt::where('quiz_id', $this->quiz->id)
            ->where('user_id', auth()->id())
            ->where('status', 'in_progress')
            ->first();

        if ($existing) {
            $this->attempt = $existing;
            $this->questionOrder = $existing->question_order ?? $this->quiz->questions->pluck('id')->toArray();
            $this->loadAnswersFromDb();
        } else {
            $ids = $this->quiz->questions->pluck('id')->toArray();
            if ($this->quiz->shuffle_questions) {
                shuffle($ids);
            }

            $this->questionOrder = $ids;
            $this->attempt = QuizAttempt::create([
                'quiz_id' => $this->quiz->id,
                'user_id' => auth()->id(),
                'started_at' => now(),
                'status' => 'in_progress',
                'question_order' => $ids,
            ]);

            foreach ($ids as $qid) {
                $this->answers[$qid] = ['selected_option_ids' => [], 'text_answer' => '', 'answer_id' => null];
            }
        }

        $this->syncTimer();
        $this->screen = 'taking';
    }

    // ─── Navigation ───────────────────────────────────────────────────────────

    public function selectOption(int $optionId): void
    {
        $q = $this->currentQuestion();
        if (! $q) {
            return;
        }

        $qid = $q->id;

        if ($q->isMultiSelect()) {
            $current = $this->answers[$qid]['selected_option_ids'] ?? [];
            if (in_array($optionId, $current)) {
                $this->answers[$qid]['selected_option_ids'] = array_values(array_filter($current, fn ($id) => $id !== $optionId));
            } else {
                $this->answers[$qid]['selected_option_ids'][] = $optionId;
            }
        } else {
            $this->answers[$qid]['selected_option_ids'] = [$optionId];
        }
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

    public function goToQuestion(int $index): void
    {
        $this->saveCurrentAnswer();
        $this->currentIndex = max(0, min($index, $this->totalQuestions() - 1));
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

        QuizAnswer::with('question.options')
            ->where('attempt_id', $this->attempt->id)
            ->each(fn ($a) => $a->grade());

        $this->attempt->calculateAndSaveScore();
        $this->attempt->recalculateRanks();
        $this->attempt->refresh();
        app(QuizPointsService::class)->award($this->attempt);

        $this->refreshLeaderboard();
        $this->screen = 'results';
        $this->success('Submitted! Your score is in.');
    }

    public function timerTick(): void
    {
        if (! $this->attempt || $this->timerExpired) {
            return;
        }

        $this->remainingSeconds = $this->calcRemainingSeconds();

        if ($this->remainingSeconds <= 0) {
            $this->timerExpired = true;
            $this->autoSubmit();
        }
    }

    private function autoSubmit(): void
    {
        $this->attempt?->update(['status' => 'timed_out', 'submitted_at' => now()]);

        QuizAnswer::with('question.options')
            ->where('attempt_id', $this->attempt->id)
            ->each(fn ($a) => $a->grade());

        $this->attempt->calculateAndSaveScore();
        $this->attempt->recalculateRanks();
        $this->attempt->refresh();
        app(QuizPointsService::class)->award($this->attempt);

        $this->refreshLeaderboard();
        $this->screen = 'results';
        $this->warning('⏰ Time is up! Auto-submitted.');
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function saveCurrentAnswer(): void
    {
        if (! $this->attempt || ! $this->currentQuestion()) {
            return;
        }

        $q = $this->currentQuestion();
        $qid = $q->id;
        $data = $this->answers[$qid] ?? [];
        $selected = $data['selected_option_ids'] ?? [];
        $text = $data['text_answer'] ?? '';

        if (empty($selected) && empty(trim($text))) {
            return;
        }

        $record = QuizAnswer::updateOrCreate(
            ['attempt_id' => $this->attempt->id, 'question_id' => $qid],
            ['selected_option_ids' => $selected ?: null, 'text_answer' => $text ?: null, 'answered_at' => now()]
        );

        $this->answers[$qid]['answer_id'] = $record->id;
    }

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
        foreach (QuizAnswer::where('attempt_id', $this->attempt->id)->get() as $ans) {
            $this->answers[$ans->question_id] = [
                'selected_option_ids' => $ans->selected_option_ids ?? [],
                'text_answer' => $ans->text_answer ?? '',
                'answer_id' => $ans->id,
            ];
        }

        foreach ($this->questionOrder as $qid) {
            if (! isset($this->answers[$qid])) {
                $this->answers[$qid] = ['selected_option_ids' => [], 'text_answer' => '', 'answer_id' => null];
            }
        }
    }

    private function refreshLeaderboard(): void
    {
        $this->leaderboard = QuizAttempt::with('user')
            ->where('quiz_id', $this->quiz->id)
            ->where('status', 'submitted')
            ->orderBy('rank')
            ->limit(20)
            ->get()
            ->map(fn ($a) => [
                'rank' => $a->rank,
                'name' => $a->user->name,
                'avatar' => $a->user->avatar_url,
                'score_percentage' => $a->score_percentage,
                'is_me' => $a->user_id === auth()->id(),
                'points_awarded' => $a->points_awarded,
            ])->toArray();
    }
};
