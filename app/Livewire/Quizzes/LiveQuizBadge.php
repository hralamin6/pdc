<?php

namespace App\Livewire\Quizzes;

use App\Models\Quiz;
use Livewire\Component;

class LiveQuizBadge extends Component
{
    public ?Quiz $activeQuiz = null;

    public function mount()
    {
        $this->checkForLiveQuiz();
    }

    public function checkForLiveQuiz()
    {
        $this->activeQuiz = Quiz::where('mode', 'live')->where('status', 'live')->latest('live_started_at')->first();
    }

    public function render()
    {
        return <<<'HTML'
        <div wire:poll.30s="checkForLiveQuiz">
            @if($activeQuiz)
                <a href="{{ route('app.quiz.live', $activeQuiz) }}" wire:navigate class="flex items-center justify-between px-4 py-2 mt-2 mx-4 rounded-xl bg-error/10 text-error hover:bg-error/20 transition-all font-bold text-sm group border border-error/20 cursor-pointer">
                    <div class="flex items-center gap-2">
                        <span class="relative flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-error opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-error"></span>
                        </span>
                        Live Quiz Active
                    </div>
                    <x-icon name="o-arrow-right" class="w-4 h-4 opacity-0 -ml-4 group-hover:opacity-100 group-hover:ml-0 transition-all" />
                </a>
            @endif
        </div>
        HTML;
    }
}
