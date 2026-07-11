<?php

namespace App\Livewire\Quizzes;

use App\Models\Quiz;
use Livewire\Component;

class LiveQuizWidget extends Component
{
    public ?Quiz $activeQuiz = null;

    public function mount()
    {
        $this->checkForLiveQuiz();
    }

    public function checkForLiveQuiz()
    {
        $this->activeQuiz = Quiz::where('mode', 'live')
            ->where('status', 'live')
            ->latest('live_started_at')
            ->first();
    }

    public function render()
    {
        return <<<'HTML'
        <div wire:poll.15s="checkForLiveQuiz">
            @if($activeQuiz)
            <div class="mb-8">
                <div class="bg-gradient-to-r from-red-600 to-red-900 rounded-3xl shadow-xl overflow-hidden relative">
                    <!-- Dynamic background elements -->
                    <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10 mix-blend-overlay"></div>
                    <div class="absolute -right-20 -top-20 w-64 h-64 bg-red-500 rounded-full mix-blend-multiply filter blur-3xl opacity-50 animate-pulse"></div>
                    
                    <div class="relative p-6 md:p-8 flex flex-col md:flex-row items-center justify-between gap-6 text-white">
                        <div class="flex items-center gap-6">
                            <div class="relative w-16 h-16 rounded-2xl bg-white/20 border border-white/30 flex items-center justify-center shrink-0 backdrop-blur-md shadow-inner">
                                <span class="absolute inline-flex h-full w-full rounded-2xl bg-white opacity-20 animate-ping"></span>
                                <x-icon name="o-signal" class="w-8 h-8 text-white" />
                            </div>
                            <div>
                                <div class="flex items-center gap-3 mb-1">
                                    <span class="px-2 py-0.5 rounded-md bg-white/20 text-xs font-black tracking-wider uppercase backdrop-blur-sm border border-white/20">Live Event</span>
                                    <span class="text-white/70 text-sm font-medium">Started {{ $activeQuiz->live_started_at?->diffForHumans() }}</span>
                                </div>
                                <h2 class="text-2xl font-black mb-1 drop-shadow-sm">{{ $activeQuiz->title }}</h2>
                                <p class="text-white/80 font-medium">Join now to participate in real-time and climb the leaderboard!</p>
                            </div>
                        </div>
                        <div class="w-full md:w-auto shrink-0 flex gap-3">
                            <a href="{{ route('app.quiz.live', $activeQuiz) }}" wire:navigate class="btn bg-white hover:bg-gray-100 text-red-700 border-none rounded-xl flex-1 md:flex-none shadow-lg px-8 text-base shadow-red-900/50 hover:scale-105 transition-all">
                                Join Quiz
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
        HTML;
    }
}
