<div class="min-h-screen bg-slate-50 dark:bg-slate-950 py-8">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        
        {{-- Navigation back button --}}
        <div>
            <a href="{{ route('web.quizzes.history') }}" wire:navigate class="inline-flex items-center gap-1.5 text-slate-400 hover:text-slate-600 dark:hover:text-white text-xs font-bold transition-colors">
                <x-icon name="o-arrow-left" class="w-3.5 h-3.5" /> {{ __('Back to history') }}
            </a>
        </div>

        {{-- Overview Summary Card --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800/60 rounded-3xl p-6 sm:p-8 shadow-sm flex flex-col md:flex-row items-center justify-between gap-6 relative overflow-hidden">
            <div class="absolute inset-0 opacity-[0.02] bg-[radial-gradient(#000_1px,transparent_1px)] dark:bg-[radial-gradient(#fff_1px,transparent_1px)] [background-size:16px_16px] pointer-events-none"></div>

            <div class="space-y-2 text-center md:text-left min-w-0 flex-grow">
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-emerald-50 dark:bg-emerald-950/20 text-emerald-600 dark:text-emerald-400 border border-emerald-100/40 dark:border-emerald-900/20">{{ __('Attempt Reviewed') }}</span>
                <h1 class="text-xl sm:text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tight leading-tight mt-1">{{ $quiz->title }}</h1>
                <p class="text-xs text-slate-400 font-bold uppercase tracking-wider">{{ __('Completed:') }} {{ $attempt->submitted_at->format('M d, Y h:i A') }}</p>
            </div>

            <div class="flex items-center gap-6 flex-shrink-0">
                <div class="text-center font-bold">
                    <p class="text-[9px] text-slate-400 uppercase tracking-widest">{{ __('Points Awarded') }}</p>
                    <p class="text-2xl font-black text-primary mt-1">+{{ $attempt->points_awarded ?? 0 }}</p>
                </div>
                
                <div class="text-center font-bold">
                    <p class="text-[9px] text-slate-400 uppercase tracking-widest">{{ __('Rank') }}</p>
                    <p class="text-2xl font-black text-slate-855 dark:text-slate-200 mt-1">
                        @if($attempt->rank === 1) 🥇 @elseif($attempt->rank === 2) 🥈 @elseif($attempt->rank === 3) 🥉 @else #{{ $attempt->rank ?: '-' }} @endif
                    </p>
                </div>

                {{-- Score radial percentage --}}
                @php $pct = round($attempt->score_percentage); @endphp
                <div class="relative w-16 h-16 flex-shrink-0 font-bold">
                    <svg class="w-full h-full -rotate-90" viewBox="0 0 36 36">
                        <path class="text-slate-100 dark:text-slate-800" stroke-width="3.5" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        <path class="{{ $attempt->passed ? 'text-emerald-500' : 'text-rose-500' }}" stroke-dasharray="{{ $pct }}, 100" stroke-width="3.5" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-sm font-black text-slate-800 dark:text-slate-250">{{ $pct }}%</span>
                        <span class="text-[7px] text-slate-400 font-bold uppercase">{{ $attempt->score_raw }}/{{ $quiz->total_marks }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Questions Breakdown --}}
        <div class="space-y-6">
            @foreach($this->orderedQuestions()->values() as $qi => $q)
                @php 
                    $ans = $answers[$q->id] ?? [];
                    $selectedIds = $ans['selected_option_ids'] ?? [];
                    $textAnswer = $ans['text_answer'] ?? '';
                    $isCorrect = $ans['is_correct'] ?? false;
                    $marksAwarded = $ans['marks_awarded'] ?? 0.0;
                @endphp
                <div class="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800/60 rounded-3xl p-5 sm:p-6 shadow-sm space-y-4 relative">
                    
                    {{-- Question Header --}}
                    <div class="flex items-start justify-between gap-4 border-b border-slate-100 dark:border-slate-800/80 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-500 font-black text-xs flex items-center justify-center">{{ $qi + 1 }}</span>
                            <span class="badge badge-ghost text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md">{{ str_replace('_', ' ', $q->type) }}</span>
                        </div>
                        <div class="text-xs font-bold">
                            @if($q->type !== 'short_text')
                                @if($marksAwarded >= $q->marks)
                                    <span class="text-emerald-600 dark:text-emerald-450 font-black">+{{ $marksAwarded }} / {{ $q->marks }} {{ __('Marks') }}</span>
                                @elseif($marksAwarded > 0)
                                    <span class="text-amber-600 dark:text-amber-500 font-black">+{{ $marksAwarded }} / {{ $q->marks }} {{ __('Marks') }}</span>
                                @else
                                    <span class="text-rose-600 dark:text-rose-500 font-black">{{ $marksAwarded }} / {{ $q->marks }} {{ __('Marks') }}</span>
                                @endif
                            @else
                                {{-- Short text manual grading message --}}
                                @if(is_null($marksAwarded))
                                    <span class="text-slate-400 font-bold uppercase tracking-wider text-[10px]">{{ __('Pending Grade') }}</span>
                                @elseif($marksAwarded > 0)
                                    <span class="text-emerald-600 font-black">+{{ $marksAwarded }} / {{ $q->marks }} {{ __('Marks') }}</span>
                                @else
                                    <span class="text-rose-600 font-black">0 / {{ $q->marks }} {{ __('Marks') }}</span>
                                @endif
                            @endif
                        </div>
                    </div>

                    {{-- Question Text --}}
                    <p class="text-base font-semibold text-slate-850 dark:text-slate-100 leading-relaxed">{{ $q->question_text }}</p>

                    {{-- Options or Text Box --}}
                    @if($q->type !== 'short_text')
                        <div class="space-y-2 mt-4">
                            @foreach($q->options as $oi => $option)
                                @php 
                                    $isSelected = in_array($option->id, $selectedIds);
                                    $isCorrectOption = $option->is_correct;
                                @endphp
                                <div class="flex items-center justify-between gap-4 px-4 py-3 rounded-2xl border-2 transition-colors
                                    {{ $isCorrectOption ? 'bg-emerald-50/40 dark:bg-emerald-950/15 border-emerald-500/60 text-emerald-800 dark:text-emerald-400 font-medium' : '' }}
                                    {{ !$isCorrectOption && $isSelected ? 'bg-rose-50/40 dark:bg-rose-950/15 border-rose-500/60 text-rose-800 dark:text-rose-400' : '' }}
                                    {{ !$isCorrectOption && !$isSelected ? 'border-slate-100 dark:border-slate-800/80 text-slate-650 dark:text-slate-350 bg-slate-50/20 dark:bg-slate-900/30' : '' }}
                                ">
                                    <div class="flex items-center gap-3">
                                        <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-black flex-shrink-0
                                            {{ $isCorrectOption ? 'bg-emerald-500 text-white' : '' }}
                                            {{ !$isCorrectOption && $isSelected ? 'bg-rose-500 text-white' : '' }}
                                            {{ !$isCorrectOption && !$isSelected ? 'bg-slate-200/60 dark:bg-slate-800 text-slate-400' : '' }}">
                                            {{ chr(65 + $oi) }}
                                        </span>
                                        <span class="text-sm">{{ $option->option_text }}</span>
                                    </div>

                                    <div class="flex items-center gap-2 flex-shrink-0">
                                        @if($isSelected)
                                            <span class="text-[9px] font-bold uppercase tracking-wider bg-slate-200/50 dark:bg-slate-800 text-slate-500 dark:text-slate-400 px-2 py-0.5 rounded-md">{{ __('Your Answer') }}</span>
                                        @endif

                                        @if($isCorrectOption)
                                            <x-icon name="o-check-circle" class="w-5 h-5 text-emerald-500" />
                                        @elseif($isSelected)
                                            <x-icon name="o-x-circle" class="w-5 h-5 text-rose-500" />
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        {{-- Short text display --}}
                        <div class="space-y-2 mt-4 bg-slate-50 dark:bg-slate-850 border border-slate-200/50 dark:border-slate-800 p-4 rounded-2xl">
                            <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Your Text Response') }}</span>
                            <p class="text-sm italic text-slate-700 dark:text-slate-300 whitespace-pre-wrap leading-relaxed mt-1">{{ $textAnswer ?: __('(No response submitted)') }}</p>
                        </div>
                    @endif

                    {{-- AI Explanation Block --}}
                    @if($q->ai_explanation)
                        <div class="mt-4 bg-blue-50/40 dark:bg-blue-950/15 border border-blue-150/40 dark:border-blue-900/20 p-4 rounded-2xl flex items-start gap-3">
                            <x-icon name="o-light-bulb" class="w-5 h-5 text-blue-500 mt-0.5 flex-shrink-0" />
                            <div class="space-y-1">
                                <span class="block text-[10px] font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider">{{ __('Answer Explanation') }}</span>
                                <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">{{ $q->ai_explanation }}</p>
                            </div>
                        </div>
                    @endif

                </div>
            @endforeach
        </div>

        {{-- Bottom navigation controls --}}
        <div class="flex justify-center pt-4">
            <x-button :label="__('Back to History')" icon="o-arrow-left" class="btn-ghost" :href="route('web.quizzes.history')" wire:navigate />
        </div>

    </div>
</div>
