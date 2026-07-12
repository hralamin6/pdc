<div class="min-h-screen bg-slate-50 dark:bg-slate-950 py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        
        {{-- Header & Sub-navigation --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-slate-200 dark:border-slate-800 pb-5">
            <div>
                <h1 class="text-3xl font-black text-slate-800 dark:text-slate-100 tracking-tight">{{ __('Islamic Quizzes') }}</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ __('Test your knowledge, compete with others, and learn more about Islam.') }}</p>
            </div>
            
            <div class="flex items-center gap-2 bg-slate-100 dark:bg-slate-900 p-1.5 rounded-xl self-start md:self-auto border border-slate-200/60 dark:border-slate-800/60">
                <a href="{{ route('web.quizzes') }}" wire:navigate class="px-4 py-2 rounded-lg text-xs font-bold bg-white dark:bg-slate-800 text-primary shadow-sm border border-slate-200/40 dark:border-slate-700/40">
                    <span class="flex items-center gap-1.5">
                        <x-icon name="o-academic-cap" class="w-4 h-4" /> {{ __('Quizzes') }}
                    </span>
                </a>
                <a href="{{ route('web.quizzes.leaderboard') }}" wire:navigate class="px-4 py-2 rounded-lg text-xs font-bold text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white transition-colors">
                    <span class="flex items-center gap-1.5">
                        <x-icon name="o-trophy" class="w-4 h-4" /> {{ __('Leaderboard') }}
                    </span>
                </a>
                @auth
                    <a href="{{ route('web.my-quizzes') }}" wire:navigate class="px-4 py-2 rounded-lg text-xs font-bold text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white transition-colors">
                        <span class="flex items-center gap-1.5">
                            <x-icon name="o-chart-bar" class="w-4 h-4" /> {{ __('My Quizzes') }}
                        </span>
                    </a>
                @endauth
            </div>
        </div>

        {{-- Filters & Search Section --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800/60 rounded-3xl p-4 shadow-sm flex flex-col md:flex-row gap-4 items-center justify-between">
            <div class="relative w-full md:w-72">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <x-icon name="o-magnifying-glass" class="w-4 h-4 text-slate-400" />
                </span>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="{{ __('Search quizzes...') }}"
                    class="w-full pl-9 pr-4 py-2 text-sm bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-slate-700 dark:text-slate-300">
            </div>

            <div class="flex flex-wrap gap-2 items-center w-full md:w-auto">
                {{-- Mode selector --}}
                <div class="flex items-center gap-1 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-1">
                    <button wire:click="$set('mode', 'all')" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $mode === 'all' ? 'bg-white dark:bg-slate-700 text-slate-850 dark:text-white shadow-sm' : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-200' }}">{{ __('All') }}</button>
                    <button wire:click="$set('mode', 'live')" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $mode === 'live' ? 'bg-white dark:bg-slate-700 text-slate-850 dark:text-white shadow-sm' : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-200' }}">{{ __('Live Matches') }}</button>
                    <button wire:click="$set('mode', 'async')" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $mode === 'async' ? 'bg-white dark:bg-slate-700 text-slate-850 dark:text-white shadow-sm' : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-200' }}">{{ __('Practice') }}</button>
                </div>
            </div>
        </div>

        {{-- Quizzes Grid --}}
        @if($quizzes->isEmpty())
            <div class="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800/60 rounded-3xl p-12 text-center shadow-sm max-w-lg mx-auto">
                <div class="w-16 h-16 bg-slate-50 dark:bg-slate-850 rounded-full flex items-center justify-center mx-auto mb-4 border border-slate-200 dark:border-slate-700">
                    <x-icon name="o-beaker" class="w-8 h-8 text-slate-400" />
                </div>
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">{{ __('No quizzes found') }}</h3>
                <p class="text-sm text-slate-400 mt-1 max-w-xs mx-auto">{{ __("We couldn't find any quizzes matching your search or filters.") }}</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($quizzes as $quiz)
                    @php 
                        $myAttempt = $quiz->my_attempt;
                        $hasCompleted = $myAttempt && in_array($myAttempt->status, ['submitted', 'timed_out']);
                        $isInProgress = $myAttempt && $myAttempt->status === 'in_progress';
                        $halaqah = $quiz->quizzable instanceof \App\Models\Halaqah ? $quiz->quizzable : \App\Models\Halaqah::first();
                    @endphp
                    <div class="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800/60 rounded-3xl p-5 shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between relative overflow-hidden group">
                        
                        <div>
                            {{-- Topic / Badge Bar --}}
                            <div class="flex items-center justify-between gap-2 mb-4">
                                @if($quiz->mode === 'live')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-rose-50 dark:bg-rose-950/30 text-rose-600 dark:text-rose-400 border border-rose-100 dark:border-rose-900/30 animate-pulse">
                                        <span class="w-1.5 h-1.5 bg-rose-500 rounded-full"></span> {{ __('Live') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-indigo-50 dark:bg-indigo-950/30 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-900/30">
                                        {{ __('Practice') }}
                                    </span>
                                @endif

                                @if($hasCompleted)
                                    <span class="badge badge-success text-white font-bold text-[10px] px-2 py-0.5 rounded-md">{{ __('Score') }}: {{ round($myAttempt->score_percentage) }}%</span>
                                @elseif($isInProgress)
                                    <span class="badge badge-warning text-white font-bold text-[10px] px-2 py-0.5 rounded-md">{{ __('In Progress') }}</span>
                                @endif
                            </div>

                            {{-- Title & Description --}}
                            <a href="{{ route('web.quizzes.show', $quiz) }}" wire:navigate>
                                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100 line-clamp-1 group-hover:text-primary transition-colors">{{ $quiz->title }}</h3>
                            </a>
                            <p class="text-xs text-slate-400 mt-1.5 line-clamp-2 leading-relaxed min-h-[32px]">{{ $quiz->description ?: __('No description provided.') }}</p>

                            {{-- Metadata attributes --}}
                            <div class="grid grid-cols-3 gap-2 border-t border-slate-100 dark:border-slate-800/80 pt-4 mt-4 text-[10px] text-slate-400 font-bold uppercase tracking-wider">
                                <div class="text-center bg-slate-50 dark:bg-slate-900/50 py-1.5 rounded-xl border border-slate-100 dark:border-slate-850">
                                    <span class="block text-slate-600 dark:text-slate-300 font-black text-xs">{{ $quiz->questions->count() }}</span>
                                    <span>{{ __('Items') }}</span>
                                </div>
                                <div class="text-center bg-slate-50 dark:bg-slate-900/50 py-1.5 rounded-xl border border-slate-100 dark:border-slate-850">
                                    <span class="block text-slate-600 dark:text-slate-300 font-black text-xs">
                                        @if($quiz->time_limit_minutes)
                                            {{ $quiz->time_limit_minutes }}m
                                        @else
                                            ∞
                                        @endif
                                    </span>
                                    <span>{{ __('Limit') }}</span>
                                </div>
                                <div class="text-center bg-slate-50 dark:bg-slate-900/50 py-1.5 rounded-xl border border-slate-100 dark:border-slate-850">
                                    <span class="block text-slate-600 dark:text-slate-300 font-black text-xs">{{ $quiz->total_marks }}</span>
                                    <span>{{ __('Marks') }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Action buttons --}}
                        <div class="mt-5 pt-3 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between gap-4">
                            @if($halaqah)
                                <span class="text-[10px] font-bold text-slate-400 flex items-center gap-1 min-w-0">
                                    <x-icon name="o-academic-cap" class="w-3.5 h-3.5 flex-shrink-0" />
                                    <span class="truncate">{{ $halaqah->title }}</span>
                                </span>
                            @else
                                <span></span>
                            @endif

                            @if(!auth()->check())
                                <a href="{{ route('web.quizzes.show', $quiz) }}" wire:navigate class="btn btn-sm btn-primary text-white text-xs font-bold rounded-xl px-4 py-1.5 flex items-center gap-1 shadow-md shadow-primary/10">
                                    {{ __('Details') }}
                                </a>
                            @elseif($hasCompleted)
                                <a href="{{ route('web.quizzes.review', [$quiz, $myAttempt]) }}" wire:navigate class="btn btn-sm btn-ghost border border-slate-200 dark:border-slate-700/60 text-xs font-bold text-slate-650 dark:text-slate-350 rounded-xl px-3 py-1.5 flex items-center gap-1">
                                    <x-icon name="o-eye" class="w-3.5 h-3.5" /> {{ __('Review') }}
                                </a>
                            @elseif($isInProgress)
                                <a href="{{ $quiz->mode === 'live' ? route('web.quiz.live', $quiz) : route('web.quiz.take', $quiz) }}" wire:navigate class="btn btn-sm btn-warning text-white text-xs font-bold rounded-xl px-4 py-1.5 flex items-center gap-1 shadow-md shadow-warning/10">
                                    {{ __('Resume') }}
                                </a>
                            @else
                                <a href="{{ $quiz->mode === 'live' ? route('web.quiz.live', $quiz) : route('web.quiz.take', $quiz) }}" wire:navigate class="btn btn-sm btn-primary text-white text-xs font-bold rounded-xl px-4 py-1.5 flex items-center gap-1 shadow-md shadow-primary/10">
                                    <x-icon name="o-play-circle" class="w-3.5 h-3.5" /> {{ __('Start') }}
                                </a>
                            @endif
                        </div>

                    </div>
                @endforeach
            </div>
            
            {{-- Pagination links --}}
            <div class="mt-6">
                {{ $quizzes->links() }}
            </div>
        @endif

    </div>
</div>
