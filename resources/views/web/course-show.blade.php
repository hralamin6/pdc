<?php

use App\Models\HalaqahSeries;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Course Details')] #[Layout('layouts.web')] class extends Component
{
    public HalaqahSeries $series;

    public function mount(HalaqahSeries $series): void
    {
        $this->series = $series->load(['mentor', 'halaqahs.speaker', 'quizzes.questions']);
    }

    public function getCourseProgressProperty(): array
    {
        $total = $this->series->halaqahs->count();
        $completed = $this->series->halaqahs->where('status', 'completed')->count();
        return [
            'completed' => $completed,
            'total' => $total,
            'percentage' => $total > 0 ? round(($completed / $total) * 100) : 0,
        ];
    }
};
?>

<div class="min-h-screen bg-slate-50 dark:bg-slate-950">

    {{-- Hero banner --}}
    <div class="relative bg-gradient-to-br from-slate-950 via-indigo-950 to-slate-900 text-white overflow-hidden">
        <div class="absolute top-0 right-0 w-96 h-96 bg-primary/15 rounded-full blur-[120px] -translate-y-1/2 translate-x-1/3 pointer-events-none"></div>
        <div class="absolute inset-0 opacity-[0.03] bg-[radial-gradient(#fff_1px,transparent_1px)] [background-size:24px_24px] pointer-events-none"></div>

        <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pt-12 pb-20">
            <a href="{{ route('web.home') }}" wire:navigate class="inline-flex items-center gap-1.5 text-white/50 hover:text-white text-sm font-bold mb-6 transition-colors">
                <x-icon name="o-arrow-left" class="w-4 h-4" /> {{ __('Back to home') }}
            </a>

            <div class="flex flex-col md:flex-row md:items-end justify-between gap-8">
                <div class="flex-grow">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="badge badge-primary border-none font-bold shadow-lg shadow-primary/20">{{ __('Course / Series') }}</span>
                    </div>
                    <h1 class="text-4xl md:text-5xl font-black mb-4 leading-tight tracking-tight text-white">{{ $series->title }}</h1>
                    @if($series->description)
                        <p class="text-lg text-white/70 max-w-2xl leading-relaxed">{{ $series->description }}</p>
                    @endif
                </div>

                {{-- Mentor Card --}}
                @if($series->mentor)
                <div class="shrink-0">
                    <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-3xl p-5 w-full md:w-64">
                        <p class="text-xs font-bold text-white/40 uppercase tracking-wider mb-3">{{ __('Mentor') }}</p>
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-full bg-primary/20 flex items-center justify-center font-bold text-lg overflow-hidden shrink-0 ring-2 ring-white/10">
                                @if($series->mentor->avatar_url)
                                    <img src="{{ $series->mentor->avatar_url }}" alt="{{ $series->mentor->name }}" class="w-full h-full object-cover" />
                                @else
                                    <span class="text-white">{{ substr($series->mentor->name, 0, 1) }}</span>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <p class="font-bold text-white text-sm truncate">{{ $series->mentor->name }}</p>
                                <p class="text-xs text-white/50 truncate">{{ $series->mentor->email }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8 pb-20">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            {{-- Left column --}}
            <div class="lg:col-span-2 space-y-8">
                
                {{-- Course Progress --}}
                <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-xl shadow-slate-200/50 dark:shadow-none border border-slate-200/60 dark:border-slate-800/60">
                    <h2 class="text-xl font-black mb-4 flex items-center gap-2 text-slate-800 dark:text-slate-100">
                        <x-icon name="o-chart-bar" class="w-6 h-6 text-primary" /> {{ __('Course Progress') }}
                    </h2>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-bold text-slate-400">{{ $this->courseProgress['completed'] }} {{ __('of') }} {{ $this->courseProgress['total'] }} {{ __('Sessions Completed') }}</span>
                        <span class="text-sm font-black text-primary">{{ $this->courseProgress['percentage'] }}%</span>
                    </div>
                    <div class="w-full h-3 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-primary to-secondary transition-all duration-1000 ease-out rounded-full" style="width: {{ $this->courseProgress['percentage'] }}%"></div>
                    </div>
                </div>

                {{-- Sessions List --}}
                <div>
                    <h2 class="text-2xl font-black mb-6 flex items-center gap-3 text-slate-800 dark:text-slate-100">
                        <x-icon name="o-book-open" class="w-7 h-7 text-primary" /> {{ __('Sessions') }} ({{ $series->halaqahs->count() }})
                    </h2>
                    
                    @if($series->halaqahs->isEmpty())
                        <div class="bg-white dark:bg-slate-900 rounded-3xl p-12 text-center border border-slate-200/60 dark:border-slate-800/60">
                            <x-icon name="o-inbox" class="w-12 h-12 mx-auto text-slate-300 mb-4" />
                            <h3 class="text-lg font-bold text-slate-600 dark:text-slate-300">{{ __('No sessions added yet') }}</h3>
                            <p class="text-slate-400 mt-2">{{ __('Check back later for new sessions in this course.') }}</p>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($series->halaqahs as $session)
                                <a href="{{ route('web.halaqah.show', $session) }}" wire:navigate class="group block bg-white dark:bg-slate-900 rounded-3xl p-5 border border-slate-200/60 dark:border-slate-800/60 hover:shadow-xl hover:shadow-slate-200/50 dark:hover:shadow-none hover:border-primary/30 transition-all duration-300 relative overflow-hidden">
                                    <div class="absolute inset-0 bg-gradient-to-r from-primary/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                    
                                    <div class="relative z-10 flex flex-col sm:flex-row sm:items-center gap-5">
                                        <div class="shrink-0 w-16 h-16 rounded-2xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex flex-col items-center justify-center font-bold">
                                            @if($session->date)
                                                <span class="text-xs text-primary uppercase">{{ $session->date->format('M') }}</span>
                                                <span class="text-xl text-slate-800 dark:text-slate-200 leading-none mt-0.5">{{ $session->date->format('d') }}</span>
                                            @else
                                                <x-icon name="o-calendar" class="w-6 h-6 text-slate-400" />
                                            @endif
                                        </div>
                                        
                                        <div class="flex-grow min-w-0">
                                            <div class="flex items-center gap-2 mb-1">
                                                @if($session->status === 'live')
                                                    <span class="badge badge-error badge-sm text-[10px] font-bold border-none uppercase animate-pulse shrink-0">{{ __('Live') }}</span>
                                                @elseif($session->status === 'completed')
                                                    <span class="badge badge-success badge-sm text-[10px] font-bold border-none text-white uppercase shrink-0">{{ __('Completed') }}</span>
                                                @else
                                                    <span class="badge badge-warning badge-sm text-[10px] font-bold border-none uppercase shrink-0">{{ __('Upcoming') }}</span>
                                                @endif
                                            </div>
                                            <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 group-hover:text-primary transition-colors truncate">{{ $session->title }}</h3>
                                            @if($session->speaker)
                                                <p class="text-sm text-slate-500 mt-1 truncate"><span class="font-medium">{{ __('Speaker:') }}</span> {{ $session->speaker->name }}</p>
                                            @endif
                                        </div>
                                        
                                        <div class="shrink-0 flex items-center justify-center sm:justify-end">
                                            <div class="w-10 h-10 rounded-full bg-slate-50 dark:bg-slate-800 flex items-center justify-center group-hover:bg-primary group-hover:text-white transition-colors">
                                                <x-icon name="o-arrow-right" class="w-5 h-5" />
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Right column --}}
            <div class="space-y-8">
                
                {{-- Course Quizzes --}}
                <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200/60 dark:border-slate-800/60 shadow-sm">
                    <h2 class="text-xl font-black mb-4 flex items-center gap-2 text-slate-800 dark:text-slate-100">
                        <x-icon name="o-academic-cap" class="w-6 h-6 text-warning" /> {{ __('Course Quizzes') }}
                    </h2>
                    
                    @if($series->quizzes->isEmpty())
                        <div class="text-center py-6 text-slate-400 text-sm">
                            {{ __('No quizzes available for this course.') }}
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($series->quizzes as $quiz)
                                @php
                                    $hasAttempted = auth()->check() && \App\Models\QuizAttempt::where('quiz_id', $quiz->id)->where('user_id', auth()->id())->where('status', 'submitted')->exists();
                                @endphp
                                <div class="p-4 rounded-2xl border border-slate-200/60 dark:border-slate-800/60 {{ $hasAttempted ? 'bg-success/5 border-success/20' : 'bg-slate-50 dark:bg-slate-800/50' }}">
                                    <div class="flex items-start justify-between gap-3 mb-3">
                                        <div>
                                            <h4 class="font-bold text-slate-800 dark:text-slate-200 line-clamp-1">{{ $quiz->title }}</h4>
                                            <p class="text-xs text-slate-500 mt-1">{{ $quiz->questions->count() }} {{ __('Questions') }} • {{ $quiz->total_marks }} {{ __('Marks') }}</p>
                                        </div>
                                        @if($quiz->mode === 'live')
                                            <span class="badge badge-error badge-sm shrink-0 border-none animate-pulse">{{ __('LIVE') }}</span>
                                        @endif
                                    </div>
                                    
                                    @if($hasAttempted)
                                        <x-button label="{{ __('Review Results') }}" icon="o-check-badge" class="btn-sm btn-success text-white w-full" link="{{ route('web.my-quizzes') }}" />
                                    @elseif($quiz->status === 'live' || $quiz->mode === 'live')
                                        <x-button label="{{ __('Join Live Quiz') }}" icon="o-play" class="btn-sm btn-primary w-full" link="{{ route('web.quiz.live', $quiz) }}" />
                                    @elseif($quiz->status === 'published')
                                        <x-button label="{{ __('Take Quiz') }}" icon="o-pencil-square" class="btn-sm btn-primary w-full" link="{{ route('web.quiz.take', $quiz) }}" />
                                    @else
                                        <x-button label="{{ __('Closed') }}" icon="o-lock-closed" class="btn-sm w-full" disabled />
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
            
        </div>
    </div>
</div>
