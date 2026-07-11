<div>
{{-- ══════════════════════ INTRO SCREEN ══════════════════════ --}}
@if($screen === 'intro')
<div class="max-w-2xl mx-auto py-8">
    <div class="bg-base-100 rounded-3xl border border-base-content/5 shadow-xl overflow-hidden">
        <div class="h-2 w-full bg-gradient-to-r from-primary to-secondary"></div>
        <div class="p-8">
            {{-- Mode badge --}}
            <div class="flex items-center gap-3 mb-5">
                <span class="badge badge-primary border-none font-bold">{{ $quiz->mode === 'live' ? '⚡ Live Quiz' : '📝 Async Quiz' }}</span>
                @if($quiz->negative_marking)
                    <span class="badge badge-error border-none text-xs">⚠ Negative Marking</span>
                @endif
            </div>

            <h1 class="text-3xl font-black mb-3 leading-tight">{{ $quiz->title }}</h1>
            @if($quiz->description)
                <p class="text-base-content/60 mb-6">{{ $quiz->description }}</p>
            @endif

            {{-- Stats grid --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
                <div class="bg-base-200/50 rounded-xl p-3 text-center">
                    <div class="text-2xl font-black text-primary">{{ $quiz->questions->count() }}</div>
                    <div class="text-xs text-base-content/50 mt-0.5">Questions</div>
                </div>
                <div class="bg-base-200/50 rounded-xl p-3 text-center">
                    <div class="text-2xl font-black text-secondary">{{ $quiz->total_marks }}</div>
                    <div class="text-xs text-base-content/50 mt-0.5">Total Marks</div>
                </div>
                <div class="bg-base-200/50 rounded-xl p-3 text-center">
                    @if($quiz->time_limit_minutes)
                        <div class="text-2xl font-black text-warning">{{ $quiz->time_limit_minutes }}m</div>
                    @else
                        <div class="text-2xl font-black text-success">∞</div>
                    @endif
                    <div class="text-xs text-base-content/50 mt-0.5">Time Limit</div>
                </div>
                <div class="bg-base-200/50 rounded-xl p-3 text-center">
                    @if($quiz->pass_mark_percent)
                        <div class="text-2xl font-black text-accent">{{ $quiz->pass_mark_percent }}%</div>
                    @else
                        <div class="text-2xl font-black text-base-content/30">—</div>
                    @endif
                    <div class="text-xs text-base-content/50 mt-0.5">Pass Mark</div>
                </div>
            </div>

            {{-- Rules --}}
            <div class="space-y-2 mb-8">
                @if($quiz->shuffle_questions)
                    <div class="flex items-center gap-2 text-sm text-base-content/60">
                        <x-icon name="o-arrows-up-down" class="w-4 h-4 flex-shrink-0 text-primary/60" /> Questions are shuffled for each attempt
                    </div>
                @endif
                @if($quiz->negative_marking)
                    <div class="flex items-center gap-2 text-sm text-error">
                        <x-icon name="o-minus-circle" class="w-4 h-4 flex-shrink-0" /> Wrong answers deduct <strong>{{ $quiz->negative_mark_value * 100 }}%</strong> of the question marks
                    </div>
                @endif
                @if($quiz->show_answers_after)
                    <div class="flex items-center gap-2 text-sm text-base-content/60">
                        <x-icon name="o-eye" class="w-4 h-4 flex-shrink-0 text-success/60" /> Correct answers revealed after submission
                    </div>
                @endif
                <div class="flex items-center gap-2 text-sm text-base-content/60">
                    <x-icon name="o-lock-closed" class="w-4 h-4 flex-shrink-0 text-warning/60" /> One attempt only — cannot retake
                </div>
            </div>

            <x-button label="Start Quiz" icon="o-play" class="btn-primary w-full text-lg py-4 rounded-2xl shadow-lg shadow-primary/30 hover:scale-[1.02] transition-transform" wire:click="startQuiz" spinner="startQuiz" />
        </div>
    </div>
</div>

{{-- ══════════════════════ TAKING SCREEN ══════════════════════ --}}
@elseif($screen === 'taking')
<div class="max-w-3xl mx-auto"
    x-data="{
        remaining: @entangle('remainingSeconds').live,
        hasTimer: {{ $quiz->time_limit_minutes ? 'true' : 'false' }},
        get formatted() {
            if (!this.hasTimer || this.remaining >= 999999) return null;
            let m = Math.floor(this.remaining / 60);
            let s = this.remaining % 60;
            return String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0');
        }
    }"
    x-init="hasTimer && setInterval(() => { if (remaining > 0) { remaining--; } else { $wire.timerTick(); } }, 1000)">

    {{-- Sticky Header --}}
    <div class="sticky top-0 z-20 bg-base-200/80 backdrop-blur-md border-b border-base-content/5 py-3 px-4 mb-6 -mx-4 md:-mx-8">
        <div class="flex items-center gap-3">
            {{-- Progress --}}
            <div class="flex-grow">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-bold text-base-content/50">Q {{ $currentIndex + 1 }} / {{ $totalQuestions }}</span>
                    <span class="text-xs text-base-content/50">{{ $answeredCount }} answered</span>
                </div>
                <div class="w-full h-2 bg-base-300 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-primary to-secondary transition-all duration-500 rounded-full"
                        style="width: {{ $progressPercent }}%"></div>
                </div>
            </div>

            {{-- Timer --}}
            <template x-if="hasTimer && formatted">
                <div :class="remaining < 60 ? 'text-error animate-pulse' : (remaining < 300 ? 'text-warning' : 'text-base-content')"
                    class="font-mono font-black text-xl min-w-16 text-center bg-base-100 px-3 py-1 rounded-xl border border-base-content/10">
                    <span x-text="formatted"></span>
                </div>
            </template>

            {{-- Navigator toggle --}}
            <button wire:click="$toggle('showNavigator')" class="btn btn-ghost btn-sm btn-circle" title="Question grid">
                <x-icon name="o-squares-2x2" class="w-5 h-5" />
            </button>
        </div>
    </div>

    {{-- Question Navigator Overlay --}}
    @if($showNavigator)
    <div class="bg-base-100 rounded-2xl border border-base-content/10 p-4 mb-4 shadow-xl">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-bold text-sm">Question Navigator</h3>
            <div class="flex items-center gap-3 text-xs text-base-content/50">
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-primary inline-block"></span> Answered</span>
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-warning inline-block"></span> Flagged</span>
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-base-300 inline-block"></span> Unanswered</span>
            </div>
        </div>
        <div class="grid grid-cols-8 gap-1.5">
            @foreach($orderedQuestions->values() as $qi => $q)
                @php
                    $ans = $answers[$q->id] ?? [];
                    $isAnswered = !empty($ans['selected_option_ids']) || !empty($ans['text_answer']);
                    $isFlagged = $flagged[$q->id] ?? false;
                    $isCurrent = $qi === $currentIndex;
                @endphp
                <button wire:click="goToQuestion({{ $qi }})"
                    class="w-full aspect-square rounded-lg text-xs font-bold transition-all
                    {{ $isCurrent ? 'ring-2 ring-primary ring-offset-1' : '' }}
                    {{ $isFlagged ? 'bg-warning text-warning-content' : ($isAnswered ? 'bg-primary text-primary-content' : 'bg-base-200 text-base-content/50 hover:bg-base-300') }}">
                    {{ $qi + 1 }}
                </button>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Question Card --}}
    @if($currentQuestion)
    @php $q = $currentQuestion; $qid = $q->id; $ans = $answers[$qid] ?? []; @endphp
    <div class="bg-base-100 rounded-2xl border border-base-content/5 shadow-sm overflow-hidden">
        {{-- Question header --}}
        <div class="flex items-center justify-between px-6 py-3 bg-base-200/50 border-b border-base-content/5">
            <div class="flex items-center gap-2">
                <span class="badge badge-ghost text-[10px] font-bold">{{ strtoupper(str_replace('_',' ',$q->type)) }}</span>
                <span class="text-xs text-base-content/40">{{ $q->marks }} {{ $q->marks == 1 ? 'mark' : 'marks' }}</span>
                @if($quiz->negative_marking && $q->type !== 'short_text')
                    <span class="text-xs text-error">(-{{ $q->marks * $quiz->negative_mark_value }} wrong)</span>
                @endif
            </div>
            <button wire:click="toggleFlag" class="btn btn-ghost btn-xs gap-1 {{ ($flagged[$qid] ?? false) ? 'text-warning' : 'text-base-content/30' }}">
                <x-icon name="{{ ($flagged[$qid] ?? false) ? 's-flag' : 'o-flag' }}" class="w-4 h-4" />
                {{ ($flagged[$qid] ?? false) ? 'Flagged' : 'Flag' }}
            </button>
        </div>

        <div class="p-6 space-y-5">
            {{-- Question text --}}
            <p class="text-lg font-semibold leading-relaxed">{{ $q->question_text }}</p>

            {{-- MCQ / True-False / Multi-Select options --}}
            @if($q->type !== 'short_text')
                @if($q->type === 'multi_select')
                    <p class="text-xs text-info font-medium">Select all that apply</p>
                @endif
                <div class="space-y-2">
                    @foreach($q->options as $oi => $option)
                        @php
                            $selected = in_array($option->id, $ans['selected_option_ids'] ?? []);
                            $letter = chr(65 + $oi);
                        @endphp
                        <button wire:click="selectOption({{ $option->id }})"
                            class="w-full flex items-center gap-4 px-4 py-3 rounded-xl border-2 text-left transition-all
                            {{ $selected
                                ? 'border-primary bg-primary/10 text-primary font-semibold shadow-sm'
                                : 'border-base-content/10 hover:border-primary/30 hover:bg-primary/5' }}">
                            <span class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-black flex-shrink-0 transition-all
                                {{ $selected ? 'bg-primary text-primary-content' : 'bg-base-200 text-base-content/40' }}">
                                {{ $letter }}
                            </span>
                            <span class="flex-1">{{ $option->option_text }}</span>
                            @if($selected)
                                <x-icon name="{{ $q->isMultiSelect() ? 's-check-circle' : 's-check-circle' }}" class="w-5 h-5 text-primary flex-shrink-0" />
                            @endif
                        </button>
                    @endforeach
                </div>
            @else
                {{-- Short text answer --}}
                <div>
                    <p class="text-xs text-base-content/40 mb-2">Type your answer below. It will be reviewed by AI and admin.</p>
                    <textarea wire:model.blur="answers.{{ $qid }}.text_answer"
                        rows="4"
                        placeholder="Write your answer here..."
                        class="textarea textarea-bordered w-full rounded-xl text-sm resize-none focus:border-primary"></textarea>
                </div>
            @endif
        </div>
    </div>

    {{-- Navigation Buttons --}}
    <div class="flex items-center justify-between mt-5">
        <x-button icon="o-arrow-left" label="Previous" class="btn-ghost" wire:click="prevQuestion"
            :disabled="$currentIndex === 0" />

        <div class="flex gap-2">
            @if($currentIndex < $totalQuestions - 1)
                <x-button icon-right="o-arrow-right" label="Next" class="btn-primary" wire:click="nextQuestion" />
            @else
                <x-button icon="o-check-circle" label="Submit Quiz" class="btn-success shadow-lg shadow-success/30" wire:click="confirmSubmit" />
            @endif
        </div>
    </div>
    @endif

    {{-- Submit Confirmation Modal --}}
    <x-modal wire:model="showSubmitConfirm" title="Submit Quiz?" box-class="max-w-sm">
        <div class="text-center space-y-4">
            <div class="text-6xl">📋</div>
            <div class="space-y-1">
                <p class="font-bold text-lg">{{ $answeredCount }} / {{ $totalQuestions }} questions answered</p>
                @if($unansweredCount > 0)
                    <p class="text-warning text-sm font-medium">{{ $unansweredCount }} unanswered question(s) will score 0.</p>
                @else
                    <p class="text-success text-sm font-medium">All questions answered!</p>
                @endif
            </div>
            <p class="text-sm text-base-content/50">This cannot be undone. Are you ready to submit?</p>
        </div>
        <x-slot:actions>
            <x-button label="Keep Reviewing" wire:click="$set('showSubmitConfirm', false)" class="btn-ghost" />
            <x-button label="Submit Now" icon="o-check" class="btn-success" wire:click="submitQuiz" spinner="submitQuiz" />
        </x-slot:actions>
    </x-modal>
</div>

{{-- ══════════════════════ RESULTS SCREEN ══════════════════════ --}}
@elseif($screen === 'results' && $results)
<div class="max-w-3xl mx-auto space-y-6">

    {{-- Score Card --}}
    <div class="bg-base-100 rounded-3xl border border-base-content/5 shadow-xl overflow-hidden">
        <div class="h-2 w-full {{ $results['passed'] ? 'bg-gradient-to-r from-success to-emerald-400' : 'bg-gradient-to-r from-error to-orange-400' }}"></div>
        <div class="p-8 text-center">
            {{-- Pass / Fail --}}
            @if(!is_null($results['passed']))
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full font-bold text-sm mb-4
                    {{ $results['passed'] ? 'bg-success/15 text-success' : 'bg-error/15 text-error' }}">
                    <x-icon name="{{ $results['passed'] ? 's-check-badge' : 'o-x-circle' }}" class="w-5 h-5" />
                    {{ $results['passed'] ? 'PASSED' : 'NOT PASSED' }}
                    @if($results['pass_mark_percent'])
                        (pass mark: {{ $results['pass_mark_percent'] }}%)
                    @endif
                </div>
            @endif

            {{-- Score ring --}}
            <div class="relative inline-flex items-center justify-center w-36 h-36 mx-auto mb-6"
                x-data="{ pct: 0, target: {{ (int) round($results['score_percentage'] ?? 0) }} }"
                x-init="setTimeout(() => { let step = () => { if (pct < target) { pct = Math.min(pct + 2, target); requestAnimationFrame(step); } }; step(); }, 200)">
                <svg class="w-full h-full -rotate-90" viewBox="0 0 100 100">
                    <circle cx="50" cy="50" r="42" fill="none" stroke="currentColor" stroke-width="6" class="text-base-200" />
                    <circle cx="50" cy="50" r="42" fill="none"
                        stroke="{{ $results['passed'] ? '#22c55e' : ($results['score_percentage'] >= 50 ? '#f59e0b' : '#ef4444') }}"
                        stroke-width="6" stroke-linecap="round"
                        stroke-dasharray="263.9"
                        :stroke-dashoffset="263.9 - (263.9 * pct / 100)" />
                </svg>
                <div class="absolute text-center">
                    <div class="text-3xl font-black" x-text="pct + '%'">{{ round($results['score_percentage'] ?? 0) }}%</div>
                    <div class="text-xs text-base-content/50">{{ $results['score_raw'] }} / {{ $results['total_marks'] }}</div>
                </div>
            </div>

            {{-- Stats row --}}
            <div class="flex justify-center gap-6 flex-wrap text-sm">
                @if($results['rank'])
                    <div class="text-center">
                        <div class="text-2xl font-black {{ $results['rank'] <= 3 ? 'text-warning' : 'text-base-content' }}">
                            @if($results['rank'] === 1) 🥇
                            @elseif($results['rank'] === 2) 🥈
                            @elseif($results['rank'] === 3) 🥉
                            @else #{{ $results['rank'] }}
                            @endif
                        </div>
                        <div class="text-xs text-base-content/50">Rank of {{ $results['total_participants'] }}</div>
                    </div>
                @endif
                @if($results['time_taken_seconds'])
                    <div class="text-center">
                        <div class="text-2xl font-black">{{ gmdate('i:s', $results['time_taken_seconds']) }}</div>
                        <div class="text-xs text-base-content/50">Time Taken</div>
                    </div>
                @endif
                @if($results['points_awarded'] > 0)
                    <div class="text-center">
                        <div class="text-2xl font-black text-primary">+{{ $results['points_awarded'] }}</div>
                        <div class="text-xs text-base-content/50">Points Earned</div>
                    </div>
                @endif
            </div>

            @if($results['status'] === 'timed_out')
                <div class="mt-4 p-2 rounded-lg bg-warning/10 text-warning text-sm font-medium">
                    ⏰ Time expired — quiz was auto-submitted
                </div>
            @endif
        </div>
    </div>

    {{-- Per-Question Breakdown --}}
    @if($results['show_answers_after'] && !empty($results['questions']))
    <div>
        <h2 class="font-black text-lg mb-4 flex items-center gap-2">
            <x-icon name="o-clipboard-document-list" class="w-5 h-5 text-primary" /> Answer Breakdown
        </h2>
        <div class="space-y-3">
            @foreach($results['questions'] as $qi => $qr)
            <div class="bg-base-100 rounded-2xl border border-base-content/5 overflow-hidden">
                <div class="flex items-center gap-3 px-4 py-2.5 border-b border-base-content/5
                    {{ is_null($qr['is_correct']) ? 'bg-base-200/50' : ($qr['is_correct'] ? 'bg-success/10' : 'bg-error/10') }}">
                    <span class="font-black text-sm w-6 text-center">{{ $qi + 1 }}</span>
                    <div class="flex-grow min-w-0">
                        <p class="font-semibold text-sm truncate">{{ $qr['question_text'] }}</p>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        @if(!is_null($qr['is_correct']))
                            <x-icon name="{{ $qr['is_correct'] ? 's-check-circle' : 'o-x-circle' }}"
                                class="w-5 h-5 {{ $qr['is_correct'] ? 'text-success' : 'text-error' }}" />
                        @else
                            <span class="badge badge-warning text-xs">Pending Grade</span>
                        @endif
                        <span class="text-xs font-bold {{ $qr['marks_awarded'] > 0 ? 'text-success' : ($qr['marks_awarded'] < 0 ? 'text-error' : 'text-base-content/40') }}">
                            {{ $qr['marks_awarded'] >= 0 ? '+' : '' }}{{ $qr['marks_awarded'] ?? 0 }} / {{ $qr['marks'] }}
                        </span>
                    </div>
                </div>

                {{-- Option details --}}
                @if($qr['type'] !== 'short_text' && !empty($qr['options']))
                <div class="px-4 py-3 space-y-1.5">
                    @foreach($qr['options'] as $opt)
                        @php
                            $wasSelected = in_array($opt['id'], $qr['selected_option_ids'] ?? []);
                            $isCorrect = $opt['is_correct'];
                        @endphp
                        <div class="flex items-center gap-2 text-sm rounded-lg px-2 py-1
                            {{ $isCorrect ? 'text-success font-semibold' : ($wasSelected && !$isCorrect ? 'text-error' : 'text-base-content/50') }}">
                            @if($isCorrect)
                                <x-icon name="s-check-circle" class="w-4 h-4 flex-shrink-0" />
                            @elseif($wasSelected)
                                <x-icon name="o-x-circle" class="w-4 h-4 flex-shrink-0" />
                            @else
                                <span class="w-4 h-4 flex-shrink-0"></span>
                            @endif
                            {{ $opt['option_text'] }}
                            @if($wasSelected && !$isCorrect) <span class="text-xs">(your answer)</span> @endif
                        </div>
                    @endforeach
                </div>
                @endif

                {{-- Short text answer --}}
                @if($qr['type'] === 'short_text' && $qr['text_answer'])
                <div class="px-4 py-3">
                    <p class="text-xs font-bold text-base-content/40 mb-1">Your answer:</p>
                    <p class="text-sm italic">{{ $qr['text_answer'] }}</p>
                    @if($qr['ai_grade_reason'])
                        <p class="text-xs text-base-content/50 mt-1">AI: {{ $qr['ai_grade_reason'] }}</p>
                    @endif
                </div>
                @endif

                {{-- AI Explanation --}}
                @if(!empty($qr['explanation']))
                <div class="px-4 py-3 bg-info/5 border-t border-info/10">
                    <p class="text-xs font-bold text-info mb-1 flex items-center gap-1">
                        <x-icon name="o-sparkles" class="w-3 h-3" /> Explanation
                    </p>
                    <p class="text-sm text-base-content/70">{{ $qr['explanation'] }}</p>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Action buttons --}}
    <div class="flex gap-3 justify-center pb-8">
        <x-button label="Back to Quizzes" icon="o-arrow-left" class="btn-ghost" :href="route('app.quiz.manage')" wire:navigate />
    </div>
</div>
@endif

</div>
