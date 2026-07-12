<?php

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\UserStreak;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('My Quizzes | PSTU Dawah')] #[Layout('layouts.web')] class extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = 'all'; // 'all' | 'unattempted' | 'completed' | 'in_progress'

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function with(): array
    {
        $user = auth()->user();

        // Stats calculations
        $stats = [
            'total_points' => $user->gamification_points ?? 0,
            'completed_count' => QuizAttempt::where('user_id', $user->id)
                ->whereIn('status', ['submitted', 'timed_out'])
                ->count(),
            'avg_score' => (int) round(QuizAttempt::where('user_id', $user->id)
                ->whereIn('status', ['submitted', 'timed_out'])
                ->avg('score_percentage') ?? 0),
            'streak' => UserStreak::where('user_id', $user->id)->first()?->current_streak ?? 0,
        ];

        // Query published/live quizzes
        $query = Quiz::with(['quizzable', 'questions'])
            ->whereIn('status', ['published', 'live']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->status !== 'all') {
            $myCompletedIds = QuizAttempt::where('user_id', $user->id)
                ->whereIn('status', ['submitted', 'timed_out'])
                ->pluck('quiz_id')
                ->toArray();
                
            $myInProgressIds = QuizAttempt::where('user_id', $user->id)
                ->where('status', 'in_progress')
                ->pluck('quiz_id')
                ->toArray();

            if ($this->status === 'completed') {
                $query->whereIn('id', $myCompletedIds);
            } elseif ($this->status === 'unattempted') {
                $query->whereNotIn('id', array_merge($myCompletedIds, $myInProgressIds));
            } elseif ($this->status === 'in_progress') {
                $query->whereIn('id', $myInProgressIds);
            }
        }

        $quizzes = $query->latest()->paginate(9);

        // Map attempts info to quizzes
        $quizzes->getCollection()->transform(function ($quiz) use ($user) {
            $quiz->my_attempt = QuizAttempt::where('quiz_id', $quiz->id)
                ->where('user_id', $user->id)
                ->latest()
                ->first();
            return $quiz;
        });

        return [
            'quizzes' => $quizzes,
            'stats' => $stats,
        ];
    }
}
