<div
    x-data="{
        remaining: @entangle('remainingSeconds').live,
        hasTimer: {{ $quiz->time_limit_minutes ? 'true' : 'false' }},
        get formatted() {
            if (!this.hasTimer || this.remaining >= 999999) return null;
            let m = Math.floor(this.remaining / 60), s = this.remaining % 60;
            return String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0');
        }
    }"
    x-init="
        hasTimer && setInterval(() => { if (remaining > 0) remaining--; else $wire.timerTick(); }, 1000);

        if (typeof Echo !== 'undefined') {
            Echo.channel('quiz.{{ $quiz->id }}')
                .listen('.QuizLiveStarted', (e) => { $wire.dispatch('quiz-live-started', e); })
                .listen('.LeaderboardUpdated', (e) => { $wire.dispatch('leaderboard-updated', e); });
        }
    ">

{{-- ══════════════ WAITING ROOM ══════════════ --}}
@if($screen === 'waiting')
<div class="max-w-2xl mx-auto py-12 text-center space-y-8">
    <div class="relative inline-flex items-center justify-center w-28 h-28 mx-auto">
        <div class="absolute inset-0 rounded-full bg-primary/20 animate-ping"></div>
        <div class="absolute inset-2 rounded-full bg-primary/30 animate-pulse"></div>
        <x-icon name="o-signal" class="w-12 h-12 text-primary relative z-10" />
    </div>
    <div>
        <h1 class="text-3xl font-black mb-2">{{ $quiz->title }}</h1>
        <p class="text-base-content/60">Waiting for the host to start the quiz…</p>
    </div>
    <div class="grid grid-cols-3 gap-4 max-w-sm mx-auto">
        <div class="bg-base-100 rounded-2xl p-4 text-center border border-base-content/5">
            <div class="text-2xl font-black text-primary">{{ $quiz->questions->count() }}</div>
            <div class="text-xs text-base-content/50 mt-1">Questions</div>
        </div>
        <div class="bg-base-100 rounded-2xl p-4 text-center border border-base-content/5">
            @if($quiz->time_limit_minutes)
                <div class="text-2xl font-black text-warning">{{ $quiz->time_limit_minutes }}m</div>
            @else
                <div class="text-2xl font-black text-success">∞</div>
            @endif
            <div class="text-xs text-base-content/50 mt-1">Time Limit</div>
        </div>
        <div class="bg-base-100 rounded-2xl p-4 text-center border border-base-content/5">
            <div class="text-2xl font-black text-accent">{{ $quiz->total_marks }}</div>
            <div class="text-xs text-base-content/50 mt-1">Total Marks</div>
        </div>
    </div>
    @if($quiz->negative_marking)
    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-error/10 text-error text-sm font-medium">
        <x-icon name="o-minus-circle" class="w-4 h-4" /> Negative marking is enabled (–{{ $quiz->negative_mark_value * 100 }}%)
    </div>
    @endif
    <div class="flex items-center justify-center gap-2 text-sm text-base-content/40">
        <span class="loading loading-dots loading-sm"></span> Waiting for host to start
    </div>
</div>

{{-- ══════════════ TAKING SCREEN ══════════════ --}}
@elseif($screen === 'taking')
<div class="max-w-4xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Main quiz area --}}
    <div class="lg:col-span-2 space-y-4">

        {{-- Header bar --}}
        <div class="bg-base-100 rounded-2xl border border-base-content/5 p-4 flex items-center gap-4">
            <div class="flex-grow">
                <div class="flex justify-between mb-1">
                    <span class="text-xs font-bold text-base-content/50">Q{{ $currentIndex + 1 }}/{{ $totalQuestions }}</span>
                    <span class="text-xs text-base-content/50">{{ $answeredCount }} answered</span>
                </div>
                <div class="w-full h-2 bg-base-200 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-primary to-secondary rounded-full transition-all"
                        style="width: {{ $progressPercent }}%"></div>
                </div>
            </div>
            <template x-if="hasTimer && formatted">
                <div :class="remaining < 60 ? 'text-error animate-pulse' : (remaining < 300 ? 'text-warning' : 'text-base-content')"
                    class="font-mono font-black text-xl min-w-16 text-center bg-base-200 px-3 py-1 rounded-xl">
                    <span x-text="formatted"></span>
                </div>
            </template>
        </div>

        {{-- Question card --}}
        @if($currentQuestion)
        @php $q = $currentQuestion; $qid = $q->id; $ans = $answers[$qid] ?? []; @endphp
        <div class="bg-base-100 rounded-2xl border border-base-content/5 shadow-sm">
            <div class="px-5 py-3 border-b border-base-content/5 flex items-center gap-2">
                <span class="badge badge-ghost text-[10px]">{{ strtoupper(str_replace('_',' ',$q->type)) }}</span>
                <span class="text-xs text-base-content/40">{{ $q->marks }} marks</span>
                @if($quiz->negative_marking && $q->type !== 'short_text')
                    <span class="text-xs text-error ml-auto">-{{ $q->marks * $quiz->negative_mark_value }} wrong</span>
                @endif
            </div>
            <div class="p-5 space-y-4">
                <p class="text-lg font-semibold leading-relaxed">{{ $q->question_text }}</p>
                @if($q->type !== 'short_text')
                    @if($q->type === 'multi_select')
                        <p class="text-xs text-info font-medium">Select all that apply</p>
                    @endif
                    <div class="space-y-2">
                        @foreach($q->options as $oi => $option)
                        @php $selected = in_array($option->id, $ans['selected_option_ids'] ?? []); @endphp
                        <button wire:click="selectOption({{ $option->id }})"
                            class="w-full flex items-center gap-4 px-4 py-3 rounded-xl border-2 text-left transition-all
                            {{ $selected ? 'border-primary bg-primary/10 font-semibold' : 'border-base-content/10 hover:border-primary/30 hover:bg-primary/5' }}">
                            <span class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-black flex-shrink-0
                                {{ $selected ? 'bg-primary text-primary-content' : 'bg-base-200 text-base-content/40' }}">
                                {{ chr(65 + $oi) }}
                            </span>
                            {{ $option->option_text }}
                        </button>
                        @endforeach
                    </div>
                @else
                    <textarea wire:model.blur="answers.{{ $qid }}.text_answer" rows="4"
                        placeholder="Type your answer..."
                        class="textarea textarea-bordered w-full rounded-xl text-sm resize-none focus:border-primary"></textarea>
                @endif
            </div>
        </div>

        {{-- Nav --}}
        <div class="flex justify-between">
            <x-button icon="o-arrow-left" label="Prev" class="btn-ghost" wire:click="prevQuestion" :disabled="$currentIndex === 0" />
            @if($currentIndex < $totalQuestions - 1)
                <x-button icon-right="o-arrow-right" label="Next" class="btn-primary" wire:click="nextQuestion" />
            @else
                <x-button icon="o-check-circle" label="Submit" class="btn-success shadow-lg shadow-success/30" wire:click="confirmSubmit" />
            @endif
        </div>
        @endif
    </div>

    {{-- Live Leaderboard sidebar --}}
    <div class="bg-base-100 rounded-2xl border border-base-content/5 p-4">
        <h3 class="font-bold text-sm mb-3 flex items-center gap-2">
            <x-icon name="o-trophy" class="w-4 h-4 text-warning" /> Live Leaderboard
        </h3>
        @if(empty($leaderboard))
            <div class="text-center py-6 text-base-content/30 text-xs">Be the first to submit!</div>
        @else
            <div class="space-y-2">
                @foreach($leaderboard as $entry)
                <div class="flex items-center gap-2 p-2 rounded-xl {{ ($entry['is_me'] ?? false) ? 'bg-primary/10 border border-primary/20' : '' }}">
                    <span class="w-5 text-center font-black text-xs {{ $entry['rank'] <= 3 ? 'text-warning' : 'text-base-content/30' }}">
                        @if($entry['rank'] === 1) 🥇 @elseif($entry['rank'] === 2) 🥈 @elseif($entry['rank'] === 3) 🥉 @else #{{ $entry['rank'] }} @endif
                    </span>
                    <div class="w-7 h-7 rounded-full bg-primary text-primary-content flex items-center justify-center text-xs font-bold flex-shrink-0">
                        {{ substr($entry['name'], 0, 1) }}
                    </div>
                    <span class="flex-grow text-xs font-medium truncate {{ ($entry['is_me'] ?? false) ? 'text-primary' : '' }}">
                        {{ $entry['name'] }} {{ ($entry['is_me'] ?? false) ? '(you)' : '' }}
                    </span>
                    <span class="text-xs font-black text-primary">{{ round($entry['score_percentage'] ?? 0) }}%</span>
                </div>
                @endforeach
            </div>
        @endif

        {{-- Question navigator --}}
        <div class="mt-4 border-t border-base-content/5 pt-4">
            <p class="text-xs font-bold text-base-content/40 mb-2">Questions</p>
            <div class="grid grid-cols-5 gap-1">
                @foreach($orderedQuestions->values() as $qi => $q)
                @php $isAnswered = !empty($answers[$q->id]['selected_option_ids']) || !empty($answers[$q->id]['text_answer']); @endphp
                <button wire:click="goToQuestion({{ $qi }})"
                    class="aspect-square rounded-lg text-xs font-bold
                    {{ $qi === $currentIndex ? 'ring-2 ring-primary ring-offset-1' : '' }}
                    {{ $isAnswered ? 'bg-primary text-primary-content' : 'bg-base-200 text-base-content/50 hover:bg-base-300' }}">
                    {{ $qi + 1 }}
                </button>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- Submit confirmation --}}
<x-modal wire:model="showSubmitConfirm" title="Submit Quiz?" box-class="max-w-sm">
    <div class="text-center space-y-3 py-2">
        <div class="text-5xl">📋</div>
        <p class="font-bold">{{ $answeredCount }}/{{ $totalQuestions }} answered</p>
        @if($totalQuestions - $answeredCount > 0)
            <p class="text-warning text-sm">{{ $totalQuestions - $answeredCount }} unanswered will score 0.</p>
        @endif
    </div>
    <x-slot:actions>
        <x-button label="Keep Going" wire:click="$set('showSubmitConfirm', false)" class="btn-ghost" />
        <x-button label="Submit Now" icon="o-check" wire:click="submitQuiz" class="btn-success" spinner="submitQuiz" />
    </x-slot:actions>
</x-modal>

{{-- ══════════════ RESULTS SCREEN ══════════════ --}}
@elseif($screen === 'results')
<div class="max-w-2xl mx-auto space-y-6 py-4">
    <div class="bg-base-100 rounded-3xl border border-base-content/5 shadow-xl p-8 text-center">
        <h1 class="text-2xl font-black mb-2">Quiz Complete!</h1>
        @php $pct = (int) round($attempt?->score_percentage ?? 0); @endphp
        <div class="relative inline-flex items-center justify-center w-32 h-32 mx-auto my-6"
            x-data="{ pct: 0, target: {{ $pct }} }"
            x-init="setTimeout(() => { let s = () => { if (pct < target) { pct = Math.min(pct+2, target); requestAnimationFrame(s); } }; s(); }, 300)">
            <svg class="w-full h-full -rotate-90" viewBox="0 0 100 100">
                <circle cx="50" cy="50" r="42" fill="none" stroke="currentColor" stroke-width="6" class="text-base-200" />
                <circle cx="50" cy="50" r="42" fill="none"
                    stroke="{{ $attempt?->passed ? '#22c55e' : '#ef4444' }}"
                    stroke-width="6" stroke-linecap="round" stroke-dasharray="263.9"
                    :stroke-dashoffset="263.9 - (263.9 * pct / 100)" />
            </svg>
            <div class="absolute text-center">
                <div class="text-3xl font-black" x-text="pct + '%'">{{ $pct }}%</div>
                <div class="text-xs text-base-content/50">{{ $attempt?->score_raw }} / {{ $quiz->total_marks }}</div>
            </div>
        </div>
        @if($attempt?->points_awarded > 0)
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-primary/10 text-primary font-bold text-sm mb-4">
                <x-icon name="o-star" class="w-4 h-4" /> +{{ $attempt->points_awarded }} points earned!
            </div>
        @endif
    </div>

    {{-- Final leaderboard --}}
    @if(!empty($leaderboard))
    <div class="bg-base-100 rounded-2xl border border-base-content/5 p-5">
        <h3 class="font-bold mb-4 flex items-center gap-2">
            <x-icon name="o-trophy" class="w-5 h-5 text-warning" /> Final Leaderboard
        </h3>
        <div class="space-y-2">
            @foreach($leaderboard as $entry)
            <div class="flex items-center gap-3 p-3 rounded-xl
                {{ ($entry['is_me'] ?? false) ? 'bg-primary/10 border border-primary/20' : ($entry['rank'] <= 3 ? 'bg-base-200/50' : '') }}">
                <span class="w-8 text-center font-black">
                    @if($entry['rank'] === 1) 🥇 @elseif($entry['rank'] === 2) 🥈 @elseif($entry['rank'] === 3) 🥉 @else <span class="text-sm text-base-content/40">#{{ $entry['rank'] }}</span> @endif
                </span>
                <div class="w-9 h-9 rounded-full bg-primary text-primary-content flex items-center justify-center font-bold flex-shrink-0">
                    {{ substr($entry['name'], 0, 1) }}
                </div>
                <div class="flex-grow">
                    <p class="font-bold text-sm {{ ($entry['is_me'] ?? false) ? 'text-primary' : '' }}">
                        {{ $entry['name'] }} {{ ($entry['is_me'] ?? false) ? '(you)' : '' }}
                    </p>
                    @if($entry['points_awarded'] > 0)
                        <p class="text-xs text-base-content/40">+{{ $entry['points_awarded'] }} pts</p>
                    @endif
                </div>
                <span class="font-black text-primary">{{ round($entry['score_percentage'] ?? 0) }}%</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="flex justify-center">
        <x-button label="Back to Quizzes" icon="o-arrow-left" class="btn-ghost" :href="route('app.quiz.manage')" wire:navigate />
    </div>
</div>
@endif

</div>
