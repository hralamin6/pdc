<?php

use App\Events\QuizLeaderboardUpdated;
use App\Events\QuizLiveStarted;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\QuizAttempt;
use App\Models\User;
use App\Services\QuizPointsService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

new #[Title('Live Quiz — Host Control')] #[Layout('layouts.app')] class extends Component
{
    use Toast;

    public Quiz $quiz;

    /** @var array Live participant list (user_id → data) */
    public array $participants = [];

    /** @var array Live leaderboard */
    public array $leaderboard = [];

    public int $submittedCount = 0;
    public bool $confirmEndModal = false;

    public function mount(Quiz $quiz): void
    {
        abort_if(! auth()->user()->can('quiz.live.host'), 403);
        $this->quiz = $quiz->load('questions');
        $this->refreshStats();
    }

    // ─── Computed ──────────────────────────────────────────────────────────────

    public function with(): array
    {
        return [
            'totalParticipants' => $this->totalParticipants(),
            'answeredPerQuestion' => $this->answeredPerQuestion(),
        ];
    }

    public function totalParticipants(): int
    {
        return QuizAttempt::where('quiz_id', $this->quiz->id)->count();
    }

    public function answeredPerQuestion(): array
    {
        return $this->quiz->questions->map(function ($q) {
            $answered = QuizAnswer::whereHas(
                'attempt',
                fn ($a) => $a->where('quiz_id', $this->quiz->id)
            )->where('question_id', $q->id)->count();

            return [
                'id' => $q->id,
                'text' => \Illuminate\Support\Str::limit($q->question_text, 50),
                'answered' => $answered,
            ];
        })->toArray();
    }

    // ─── Actions ───────────────────────────────────────────────────────────────

    public function startLiveQuiz(): void
    {
        abort_if(! auth()->user()->can('quiz.live.host'), 403);

        $this->quiz->update([
            'status' => 'live',
            'live_started_at' => now(),
        ]);

        broadcast(new QuizLiveStarted($this->quiz))->toOthers();

        $this->success(__('⚡ Live quiz started! All waiting members have been notified.'));
        $this->refreshStats();
    }

    public function forceEndQuiz(): void
    {
        $this->confirmEndModal = false;

        // Auto-submit all in-progress attempts
        $inProgress = QuizAttempt::where('quiz_id', $this->quiz->id)
            ->where('status', 'in_progress')
            ->get();

        foreach ($inProgress as $attempt) {
            QuizAnswer::with('question.options')
                ->where('attempt_id', $attempt->id)
                ->each(fn ($a) => $a->grade());

            $attempt->calculateAndSaveScore();
        }

        // Recalculate ranks and award points for all
        $first = $inProgress->first();
        if ($first) {
            $first->recalculateRanks();
        }

        $allAttempts = QuizAttempt::where('quiz_id', $this->quiz->id)
            ->where('status', 'submitted')
            ->get();

        foreach ($allAttempts as $attempt) {
            app(QuizPointsService::class)->award($attempt);
        }

        $this->quiz->update(['status' => 'closed']);
        $this->broadcastLeaderboard();

        $this->warning(__('Quiz ended and all attempts auto-submitted.'));
        $this->refreshStats();
    }

    public function broadcastLeaderboard(): void
    {
        $this->refreshStats();
        broadcast(new QuizLeaderboardUpdated($this->quiz, $this->leaderboard));
    }

    public function refreshStats(): void
    {
        $this->submittedCount = QuizAttempt::where('quiz_id', $this->quiz->id)
            ->where('status', 'submitted')
            ->count();

        $attempts = QuizAttempt::with('user')
            ->where('quiz_id', $this->quiz->id)
            ->where('status', 'submitted')
            ->orderBy('rank')
            ->get();

        $this->leaderboard = $attempts->map(fn ($a) => [
            'rank' => $a->rank,
            'user_id' => $a->user_id,
            'name' => $a->user->name,
            'avatar' => $a->user->avatar_url,
            'score_percentage' => $a->score_percentage,
            'score_raw' => $a->score_raw,
            'time_taken_seconds' => $a->time_taken_seconds,
            'points_awarded' => $a->points_awarded,
        ])->toArray();

        $inProgress = QuizAttempt::where('quiz_id', $this->quiz->id)
            ->where('status', 'in_progress')
            ->with('user')
            ->get();

        $this->participants = $inProgress->map(fn ($a) => [
            'user_id' => $a->user_id,
            'name' => $a->user->name,
            'avatar' => $a->user->avatar_url,
            'started_at' => $a->started_at->diffForHumans(),
        ])->toArray();
    }
};
