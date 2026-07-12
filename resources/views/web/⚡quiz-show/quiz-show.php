<?php

use App\Models\Halaqah;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Quiz Details | PSTU Dawah')] #[Layout('layouts.web')] class extends Component
{
    public Quiz $quiz;

    public function mount(Quiz $quiz): void
    {
        $this->quiz = $quiz->load(['questions.options', 'quizzable']);
        
        // Ensure the quiz is published or live
        abort_unless(in_array($this->quiz->status, ['published', 'live', 'closed']), 404);
    }

    public function startQuiz(): void
    {
        if (!auth()->check()) {
            // Option A: Seamless Intended Redirect
            session()->put('url.intended', route('web.quizzes.show', $this->quiz));
            $this->redirect(route('login'), navigate: true);
            return;
        }

        $halaqah = $this->quiz->quizzable instanceof Halaqah ? $this->quiz->quizzable : Halaqah::first();

        if ($this->quiz->mode === 'live') {
            $this->redirectRoute('web.quiz.live', $this->quiz, navigate: true);
        } else {
            $this->redirectRoute('web.quiz.take', $this->quiz, navigate: true);
        }
    }

    public function with(): array
    {
        $user = auth()->user();

        // Fetch user's attempt on this quiz
        $myAttempt = $user ? QuizAttempt::where('quiz_id', $this->quiz->id)
            ->where('user_id', $user->id)
            ->latest()
            ->first() : null;

        // Fetch top 5 attempts for this quiz
        $leaderboard = QuizAttempt::with('user')
            ->where('quiz_id', $this->quiz->id)
            ->whereIn('status', ['submitted', 'timed_out'])
            ->orderByDesc('score_percentage')
            ->orderBy('time_taken_seconds')
            ->take(5)
            ->get();

        // Total participants on this quiz
        $participantsCount = QuizAttempt::where('quiz_id', $this->quiz->id)
            ->whereIn('status', ['submitted', 'timed_out'])
            ->count();

        return [
            'myAttempt' => $myAttempt,
            'leaderboard' => $leaderboard,
            'participantsCount' => $participantsCount,
            'halaqah' => $this->quiz->quizzable instanceof Halaqah ? $this->quiz->quizzable : Halaqah::first(),
        ];
    }
}
