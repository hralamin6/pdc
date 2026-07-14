<?php

use App\Models\Quiz;
use App\Models\QuizAttempt;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Islamic Quizzes | PSTU Dawah')] #[Layout('layouts.web')] class extends Component
{
    use WithPagination;

    public string $search = '';
    public string $mode = 'all'; // 'all' | 'async' | 'live'

    public function mount(): void
    {
        // $this->authorize('quiz.view');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingMode(): void
    {
        $this->resetPage();
    }

    public function with(): array
    {
        $user = auth()->user();

        // Query published/live quizzes
        $query = Quiz::with(['quizzable', 'questions'])
            ->whereIn('status', ['published', 'live']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->mode !== 'all') {
            $query->where('mode', $this->mode);
        }

        $quizzes = $query->latest()->paginate(9);

        // Map attempts info to quizzes if user is logged in
        $quizzes->getCollection()->transform(function ($quiz) use ($user) {
            $quiz->my_attempt = $user ? QuizAttempt::where('quiz_id', $quiz->id)
                ->where('user_id', $user->id)
                ->latest()
                ->first() : null;
            return $quiz;
        });

        return [
            'quizzes' => $quizzes,
        ];
    }
};
