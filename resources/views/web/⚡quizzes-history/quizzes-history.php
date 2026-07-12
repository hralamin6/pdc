<?php

use App\Models\QuizAttempt;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('My Attempt History | PSTU Dawah')] #[Layout('layouts.web')] class extends Component
{
    use WithPagination;

    public function mount(): void
    {
        $this->authorize('quiz.view');
    }

    public function with(): array
    {
        $attempts = QuizAttempt::with(['quiz.quizzable'])
            ->where('user_id', auth()->id())
            ->whereIn('status', ['submitted', 'timed_out'])
            ->orderByDesc('submitted_at')
            ->paginate(10);

        return [
            'attempts' => $attempts,
        ];
    }
}
