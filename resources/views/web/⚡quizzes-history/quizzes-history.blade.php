<div class="min-h-screen bg-slate-50 dark:bg-slate-950 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        
        {{-- Header & Sub-navigation --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-slate-200 dark:border-slate-800 pb-5">
            <div>
                <h1 class="text-3xl font-black text-slate-800 dark:text-slate-100 tracking-tight">{{ __('Quiz History') }}</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ __('Review your past scores, points awarded, and correct options.') }}</p>
            </div>
            
            <div class="flex items-center gap-2 bg-slate-100 dark:bg-slate-900 p-1.5 rounded-xl self-start md:self-auto border border-slate-200/60 dark:border-slate-800/60">
                <a href="{{ route('web.quizzes') }}" wire:navigate class="px-4 py-2 rounded-lg text-xs font-bold text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white transition-colors">
                    <span class="flex items-center gap-1.5">
                        <x-icon name="o-academic-cap" class="w-4 h-4" /> {{ __('Quizzes') }}
                    </span>
                </a>
                <a href="{{ route('web.quizzes.leaderboard') }}" wire:navigate class="px-4 py-2 rounded-lg text-xs font-bold text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white transition-colors">
                    <span class="flex items-center gap-1.5">
                        <x-icon name="o-trophy" class="w-4 h-4" /> {{ __('Leaderboard') }}
                    </span>
                </a>
                <a href="{{ route('web.quizzes.history') }}" wire:navigate class="px-4 py-2 rounded-lg text-xs font-bold bg-white dark:bg-slate-800 text-primary shadow-sm border border-slate-200/40 dark:border-slate-700/40">
                    <span class="flex items-center gap-1.5">
                        <x-icon name="o-clock" class="w-4 h-4" /> {{ __('My History') }}
                    </span>
                </a>
                <a href="{{ route('web.my-quizzes') }}" wire:navigate class="px-4 py-2 rounded-lg text-xs font-bold text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white transition-colors">
                    <span class="flex items-center gap-1.5">
                        <x-icon name="o-chart-bar" class="w-4 h-4" /> {{ __('Dashboard') }}
                    </span>
                </a>
            </div>
        </div>

        {{-- Attempt History List --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800/60 rounded-3xl p-5 shadow-sm space-y-4">
            
            @if($attempts->isEmpty())
                <div class="text-center py-12 max-w-sm mx-auto space-y-3">
                    <div class="w-12 h-12 bg-slate-50 dark:bg-slate-850 rounded-full flex items-center justify-center mx-auto border border-slate-200 dark:border-slate-700">
                        <x-icon name="o-inbox" class="w-6 h-6 text-slate-400" />
                    </div>
                    <h4 class="text-sm font-bold text-slate-800 dark:text-slate-100">{{ __('No attempts logged yet') }}</h4>
                    <p class="text-xs text-slate-450">{{ __('Complete quizzes from the browse tab to see your historical reports here.') }}</p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($attempts as $attempt)
                        @php 
                            $quiz = $attempt->quiz;
                            $halaqah = $quiz->quizzable;
                            $pct = round($attempt->score_percentage);
                        @endphp
                        <div class="bg-slate-50 dark:bg-slate-900/50 hover:bg-slate-100/50 dark:hover:bg-slate-850/50 border border-slate-200/60 dark:border-slate-800 rounded-2xl p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 transition-colors">
                            
                            <div class="space-y-1.5 min-w-0 flex-grow">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h4 class="text-sm font-bold text-slate-800 dark:text-slate-100 truncate max-w-xs">{{ $quiz->title }}</h4>
                                    @if($quiz->mode === 'live')
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-rose-50 dark:bg-rose-950/20 text-rose-500 dark:text-rose-450 border border-rose-100/40 dark:border-rose-900/20">{{ __('Live') }}</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-indigo-50 dark:bg-indigo-950/20 text-indigo-500 dark:text-indigo-450 border border-indigo-100/40 dark:border-indigo-900/20">{{ __('Async') }}</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider flex-wrap">
                                    <span>{{ __('Submitted') }}: {{ $attempt->submitted_at->diffForHumans() }}</span>
                                    @if($halaqah)
                                        <span class="hidden md:inline">•</span>
                                        <span class="truncate max-w-[150px] hidden md:inline">{{ __('Session') }}: {{ $halaqah->title }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center gap-6 flex-shrink-0 w-full sm:w-auto justify-between sm:justify-end border-t sm:border-t-0 border-slate-200/50 dark:border-slate-800/80 pt-3 sm:pt-0">
                                
                                {{-- Ranks / Points stats --}}
                                <div class="flex gap-4 items-center">
                                    <div class="text-center font-bold">
                                        <p class="text-[9px] text-slate-400 uppercase tracking-widest">{{ __('Rank') }}</p>
                                        <p class="text-sm font-black text-slate-800 dark:text-slate-200">
                                            @if($attempt->rank === 1) 🥇 @elseif($attempt->rank === 2) 🥈 @elseif($attempt->rank === 3) 🥉 @else #{{ $attempt->rank ?: '-' }} @endif
                                        </p>
                                    </div>
                                    
                                    <div class="text-center font-bold">
                                        <p class="text-[9px] text-slate-400 uppercase tracking-widest">{{ __('Points') }}</p>
                                        <p class="text-sm font-black text-primary">+{{ $attempt->points_awarded ?? 0 }}</p>
                                    </div>

                                    <div class="text-center font-bold">
                                        <p class="text-[9px] text-slate-400 uppercase tracking-widest">{{ __('Score') }}</p>
                                        <p class="text-sm font-black text-slate-800 dark:text-slate-200">{{ $pct }}%</p>
                                    </div>
                                </div>

                                {{-- Review Action --}}
                                <div>
                                    <a href="{{ route('web.quizzes.review', [$quiz, $attempt]) }}" wire:navigate class="btn btn-sm btn-ghost border border-slate-200 dark:border-slate-700/60 rounded-xl px-3 py-1.5 text-xs font-bold text-slate-650 dark:text-slate-350 hover:bg-slate-100 dark:hover:bg-slate-800 flex items-center gap-1.5">
                                        <x-icon name="o-eye" class="w-3.5 h-3.5" /> {{ __('Review') }}
                                    </a>
                                </div>

                            </div>

                        </div>
                    @endforeach
                </div>

                {{-- Pagination Links --}}
                <div class="mt-4">
                    {{ $attempts->links() }}
                </div>
            @endif

        </div>

    </div>
</div>
