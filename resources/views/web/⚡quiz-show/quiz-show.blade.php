<div class="min-h-screen bg-slate-50 dark:bg-slate-950 py-8">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        
        {{-- Back navigation --}}
        <div>
            <a href="{{ route('web.quizzes') }}" wire:navigate class="inline-flex items-center gap-1.5 text-slate-400 hover:text-slate-650 dark:hover:text-white text-xs font-bold transition-colors">
                <x-icon name="o-arrow-left" class="w-3.5 h-3.5" /> {{ __('Back to Quizzes') }}
            </a>
        </div>

        {{-- Quiz Hero Card --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-200/65 dark:border-slate-800/60 rounded-3xl p-6 sm:p-8 shadow-sm relative overflow-hidden">
            <div class="absolute inset-0 opacity-[0.02] bg-[radial-gradient(#000_1px,transparent_1px)] dark:bg-[radial-gradient(#fff_1px,transparent_1px)] [background-size:16px_16px] pointer-events-none"></div>

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 relative">
                <div class="space-y-3 min-w-0 flex-grow">
                    <div class="flex items-center gap-2">
                        @if($quiz->mode === 'live')
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-rose-50 dark:bg-rose-950/30 text-rose-600 dark:text-rose-450 border border-rose-100 dark:border-rose-900/30 animate-pulse">
                                <span class="w-1.5 h-1.5 bg-rose-500 rounded-full"></span> {{ __('Live Quiz') }}
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-indigo-50 dark:bg-indigo-950/30 text-indigo-600 dark:text-indigo-440 border border-indigo-100 dark:border-indigo-900/30">
                                {{ __('Practice Quiz') }}
                            </span>
                        @endif

                        @if($quiz->status === 'closed')
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-slate-100 dark:bg-slate-800 text-slate-500 border border-slate-200 dark:border-slate-705">
                                {{ __('Closed') }}
                            </span>
                        @endif
                    </div>
                    
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-805 dark:text-slate-100 tracking-tight leading-tight">{{ $quiz->title }}</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 max-w-2xl leading-relaxed">{{ $quiz->description ?: __('Test your grasp of this topic by taking the quiz.') }}</p>
                </div>

                {{-- Action CTA --}}
                <div class="flex-shrink-0">
                    @if(!auth()->check())
                        <button wire:click="startQuiz" class="btn btn-primary text-white font-bold rounded-2xl px-6 py-3 w-full md:w-auto shadow-lg shadow-primary/20 flex items-center gap-2">
                            <x-icon name="o-arrow-right-on-rectangle" class="w-5 h-5" /> {{ __('Sign In to Start') }}
                        </button>
                    @elseif($myAttempt && in_array($myAttempt->status, ['submitted', 'timed_out']))
                        <div class="flex flex-col sm:flex-row items-center gap-3">
                            <div class="text-center font-bold px-4 py-2 bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-900/30 rounded-2xl">
                                <span class="block text-[8px] text-emerald-600 uppercase tracking-widest">{{ __('Your Score') }}</span>
                                <span class="text-base text-emerald-700 dark:text-emerald-400 font-black">{{ round($myAttempt->score_percentage) }}%</span>
                            </div>
                            <a href="{{ route('web.quizzes.review', [$quiz, $myAttempt]) }}" wire:navigate class="btn btn-ghost border border-slate-200 dark:border-slate-750 font-bold rounded-2xl px-6 py-3 flex items-center gap-2">
                                <x-icon name="o-eye" class="w-5 h-5" /> {{ __('Review Answers') }}
                            </a>
                        </div>
                    @elseif($myAttempt && $myAttempt->status === 'in_progress')
                        <button wire:click="startQuiz" class="btn btn-warning text-white font-bold rounded-2xl px-6 py-3 w-full md:w-auto shadow-lg shadow-warning/20 flex items-center gap-2">
                            {{ __('Resume Attempt') }}
                        </button>
                    @else
                        @if($quiz->status === 'closed')
                            <button disabled class="btn btn-disabled font-bold rounded-2xl px-6 py-3 w-full md:w-auto flex items-center gap-2">
                                {{ __('Quiz Closed') }}
                            </button>
                        @else
                            <button wire:click="startQuiz" class="btn btn-primary text-white font-bold rounded-2xl px-6 py-3 w-full md:w-auto shadow-lg shadow-primary/20 flex items-center gap-2">
                                <x-icon name="o-play-circle" class="w-5 h-5" /> {{ __('Start Quiz') }}
                            </button>
                        @endif
                    @endif
                </div>
            </div>

            {{-- Grid of quick stats --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-8 pt-6 border-t border-slate-100 dark:border-slate-800/80">
                <div class="bg-slate-50 dark:bg-slate-900/50 p-4 rounded-2xl border border-slate-100 dark:border-slate-850">
                    <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider">{{ __('Total Questions') }}</span>
                    <span class="text-xl font-black text-slate-800 dark:text-slate-250 mt-1 block">{{ $quiz->questions->count() }} {{ __('Items') }}</span>
                </div>
                <div class="bg-slate-50 dark:bg-slate-900/50 p-4 rounded-2xl border border-slate-100 dark:border-slate-850">
                    <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider">{{ __('Time Limit') }}</span>
                    <span class="text-xl font-black text-slate-800 dark:text-slate-250 mt-1 block">
                        @if($quiz->time_limit_minutes)
                            {{ $quiz->time_limit_minutes }} {{ __('Minutes') }}
                        @else
                            {{ __('No Limit') }}
                        @endif
                    </span>
                </div>
                <div class="bg-slate-50 dark:bg-slate-900/50 p-4 rounded-2xl border border-slate-100 dark:border-slate-850">
                    <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider">{{ __('Points / Marks') }}</span>
                    <span class="text-xl font-black text-slate-800 dark:text-slate-250 mt-1 block">{{ $quiz->total_marks }} {{ __('Max Marks') }}</span>
                </div>
                <div class="bg-slate-50 dark:bg-slate-900/50 p-4 rounded-2xl border border-slate-100 dark:border-slate-850">
                    <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider">{{ __('Pass Criteria') }}</span>
                    <span class="text-xl font-black text-slate-800 dark:text-slate-250 mt-1 block">{{ $quiz->pass_mark_percent }}% {{ __('Correct') }}</span>
                </div>
            </div>
        </div>

        {{-- Details Columns --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            {{-- Left column --}}
            <div class="lg:col-span-2 space-y-6">
                
                {{-- Rules card --}}
                <div class="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800/60 rounded-3xl p-6 shadow-sm space-y-4">
                    <h3 class="text-base font-black text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        <x-icon name="o-shield-check" class="w-5 h-5 text-primary" /> {{ __('Quiz Rules & Instructions') }}
                    </h3>
                    <ul class="space-y-3 text-xs text-slate-600 dark:text-slate-350 font-medium">
                        <li class="flex items-start gap-2.5">
                            <span class="w-1.5 h-1.5 bg-primary rounded-full mt-1.5 flex-shrink-0"></span>
                            <span>{{ __('Make sure you have a stable internet connection before starting.') }}</span>
                        </li>
                        @if($quiz->time_limit_minutes)
                            <li class="flex items-start gap-2.5">
                                <span class="w-1.5 h-1.5 bg-primary rounded-full mt-1.5 flex-shrink-0"></span>
                                <span>{{ __('The timer will run continuously once you click start. Leaving the page does NOT pause the timer.') }}</span>
                            </li>
                        @endif
                        @if($quiz->shuffle_questions)
                            <li class="flex items-start gap-2.5">
                                <span class="w-1.5 h-1.5 bg-primary rounded-full mt-1.5 flex-shrink-0"></span>
                                <span>{{ __('Questions are shuffled automatically for every attempt.') }}</span>
                            </li>
                        @endif
                        @if($quiz->negative_marking)
                            <li class="flex items-start gap-2.5 text-rose-500 dark:text-rose-400">
                                <span class="w-1.5 h-1.5 bg-rose-500 rounded-full mt-1.5 flex-shrink-0"></span>
                                <span>{{ __('Warning: Negative marking is active! Incorrect responses will deduct marks.') }}</span>
                            </li>
                        @endif
                        <li class="flex items-start gap-2.5">
                            <span class="w-1.5 h-1.5 bg-primary rounded-full mt-1.5 flex-shrink-0"></span>
                            <span>{{ __('Each question has standard marks. Short text answers will be graded by AI or system mentors.') }}</span>
                        </li>
                    </ul>
                </div>

                {{-- Associated Halaqah Session card --}}
                @if($halaqah)
                    <div class="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800/60 rounded-3xl p-6 shadow-sm flex items-start gap-4">
                        <div class="w-12 h-12 bg-slate-50 dark:bg-slate-850 rounded-2xl flex items-center justify-center border border-slate-200 dark:border-slate-750 flex-shrink-0">
                            <x-icon name="o-academic-cap" class="w-6 h-6 text-primary" />
                        </div>
                        <div class="space-y-1 min-w-0 flex-grow">
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Associated Study Session') }}</span>
                            <h4 class="text-sm font-bold text-slate-800 dark:text-slate-100 truncate mt-0.5">{{ $halaqah->title }}</h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-1">{{ __('Topic:') }} {{ $halaqah->topic ?: __('Islamic Knowledge') }}</p>
                            <a href="{{ route('web.halaqah.show', $halaqah) }}" wire:navigate class="inline-flex items-center gap-1 text-[10px] font-bold text-primary hover:underline mt-2">
                                {{ __('View Session details') }} <x-icon name="o-arrow-right" class="w-3 h-3" />
                            </a>
                        </div>
                    </div>
                @endif

            </div>

            {{-- Right column --}}
            <div class="space-y-6">
                
                {{-- Local Leaderboard card --}}
                <div class="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800/60 rounded-3xl p-6 shadow-sm space-y-4">
                    <div>
                        <h3 class="text-base font-black text-slate-850 dark:text-slate-100 flex items-center gap-2">
                            <x-icon name="o-trophy" class="w-5 h-5 text-amber-500" /> {{ __('Quiz Leaderboard') }}
                        </h3>
                        <p class="text-[10px] text-slate-400 font-bold uppercase mt-1">{{ number_format($participantsCount) }} {{ __('total takers') }}</p>
                    </div>

                    @if($leaderboard->isEmpty())
                        <div class="text-center py-6 text-slate-400">
                            <p class="text-xs font-bold">{{ __('No participants yet') }}</p>
                            <p class="text-[10px] mt-0.5">{{ __('Be the first to secure a rank!') }}</p>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($leaderboard as $index => $attempt)
                                @php 
                                    $rank = $index + 1;
                                    $u = $attempt->user;
                                @endphp
                                <div class="flex items-center justify-between gap-3 bg-slate-50/50 dark:bg-slate-900/50 hover:bg-slate-100/50 dark:hover:bg-slate-850/50 px-3.5 py-2.5 rounded-2xl border border-slate-100 dark:border-slate-850 transition-colors">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <span class="w-6 h-6 rounded-lg text-xs font-black flex items-center justify-center flex-shrink-0
                                            {{ $rank === 1 ? 'bg-amber-100 text-amber-600 dark:bg-amber-950/30' : '' }}
                                            {{ $rank === 2 ? 'bg-slate-200/60 text-slate-600 dark:bg-slate-800' : '' }}
                                            {{ $rank === 3 ? 'bg-orange-100 text-orange-600 dark:bg-orange-950/30' : '' }}
                                            {{ $rank > 3 ? 'bg-slate-100 text-slate-400 dark:bg-slate-800' : '' }}">
                                            @if($rank === 1) 🥇 @elseif($rank === 2) 🥈 @elseif($rank === 3) 🥉 @else {{ $rank }} @endif
                                        </span>
                                        <div class="min-w-0">
                                            <p class="text-xs font-black text-slate-800 dark:text-slate-250 truncate">{{ $u->name }}</p>
                                            <p class="text-[8px] text-slate-400 uppercase font-bold">{{ __('Time:') }} {{ gmdate($attempt->time_taken_seconds >= 3600 ? 'H:i:s' : 'i:s', $attempt->time_taken_seconds) }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right flex-shrink-0">
                                        <p class="text-xs font-black text-slate-800 dark:text-slate-250">{{ round($attempt->score_percentage) }}%</p>
                                        <p class="text-[8px] text-primary font-bold uppercase">+{{ $attempt->points_awarded ?? 0 }} {{ __('pts') }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            </div>

        </div>

    </div>
</div>
