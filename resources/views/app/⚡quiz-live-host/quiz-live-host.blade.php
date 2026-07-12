<div>
    <x-header :title="'⚡ Host Control — ' . $quiz->title" subtitle="Live quiz control panel" separator>
        <x-slot:actions>
            <x-button icon="o-arrow-path" label="Refresh" wire:click="refreshStats" class="btn-ghost btn-sm" spinner="refreshStats" />
            @if($quiz->status === 'live')
                <x-button icon="o-signal" label="Push Leaderboard" wire:click="broadcastLeaderboard" class="btn-primary btn-sm" spinner="broadcastLeaderboard" />
                <x-button icon="o-stop" label="Force End Quiz" wire:click="$set('confirmEndModal', true)" class="btn-error btn-sm" />
            @elseif($quiz->status === 'published')
                <x-button icon="o-play" label="Start Live Quiz" wire:click="startLiveQuiz" class="btn-success shadow-lg shadow-success/30" spinner="startLiveQuiz" />
            @endif
        </x-slot:actions>
    </x-header>

    {{-- Status Banner --}}
    <div class="mb-6 p-4 rounded-2xl border font-bold flex items-center gap-3
        {{ $quiz->status === 'live' ? 'bg-error/10 border-error/30 text-error animate-pulse' :
           ($quiz->status === 'closed' ? 'bg-base-200 border-base-content/10 text-base-content/50' :
           'bg-warning/10 border-warning/30 text-warning') }}"
        @if($quiz->status === 'published' && $quiz->available_from)
           x-data="{ 
              end: new Date('{{ $quiz->available_from->toIso8601String() }}').getTime(),
              now: new Date().getTime(),
              started: false,
              format() {
                  let d = Math.max(0, this.end - this.now) / 1000;
                  if (d <= 0 && !this.started) {
                      this.started = true;
                      $wire.startLiveQuiz();
                  }
                  if (d <= 0) return '00:00';
                  let m = Math.floor(d / 60);
                  let s = Math.floor(d % 60);
                  return String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0');
              }
           }"
           x-init="setInterval(() => now = new Date().getTime(), 1000)"
        @endif
    >
        <x-icon name="{{ $quiz->status === 'live' ? 'o-signal' : ($quiz->status === 'closed' ? 'o-lock-closed' : 'o-clock') }}" class="w-5 h-5" />
        @if($quiz->status === 'live')
            LIVE — Started {{ $quiz->live_started_at?->diffForHumans() }}
        @elseif($quiz->status === 'closed')
            CLOSED — Quiz ended
        @else
            READY — Click "Start Live Quiz" when participants have joined
            @if($quiz->available_from && $quiz->available_from > now())
                <span class="ml-2 font-black text-warning">Auto-starting in: <span x-text="format()" class="font-mono bg-warning/20 px-2 py-0.5 rounded ml-1"></span></span>
            @endif
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- LEFT: Stats + Question Progress --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Overview stats --}}
            <div class="grid grid-cols-3 gap-4">
                <div class="bg-base-100 rounded-2xl p-4 text-center border border-base-content/5">
                    <div class="text-3xl font-black text-primary">{{ $totalParticipants }}</div>
                    <div class="text-xs text-base-content/50 mt-1">Total Joined</div>
                </div>
                <div class="bg-base-100 rounded-2xl p-4 text-center border border-base-content/5">
                    <div class="text-3xl font-black text-warning">{{ count($participants) }}</div>
                    <div class="text-xs text-base-content/50 mt-1">In Progress</div>
                </div>
                <div class="bg-base-100 rounded-2xl p-4 text-center border border-base-content/5">
                    <div class="text-3xl font-black text-success">{{ $submittedCount }}</div>
                    <div class="text-xs text-base-content/50 mt-1">Submitted</div>
                </div>
            </div>

            {{-- Per-question answer progress --}}
            <div class="bg-base-100 rounded-2xl border border-base-content/5 p-5">
                <h3 class="font-bold mb-4 flex items-center gap-2">
                    <x-icon name="o-chart-bar" class="w-4 h-4 text-primary" /> Answer Progress by Question
                </h3>
                <div class="space-y-3">
                    @foreach($answeredPerQuestion as $qi => $qStat)
                        @php $pct = $totalParticipants > 0 ? round($qStat['answered'] / $totalParticipants * 100) : 0; @endphp
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs text-base-content/60">Q{{ $qi + 1 }}: {{ $qStat['text'] }}</span>
                                <span class="text-xs font-bold">{{ $qStat['answered'] }}/{{ $totalParticipants }} ({{ $pct }}%)</span>
                            </div>
                            <div class="w-full h-2 bg-base-200 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-primary to-secondary rounded-full transition-all"
                                    style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- In-progress participants --}}
            @if(!empty($participants))
            <div class="bg-base-100 rounded-2xl border border-base-content/5 p-5">
                <h3 class="font-bold mb-4 flex items-center gap-2">
                    <x-icon name="o-users" class="w-4 h-4 text-warning" /> Currently Taking ({{ count($participants) }})
                </h3>
                <div class="flex flex-wrap gap-3">
                    @foreach($participants as $p)
                        <div class="flex items-center gap-2 bg-base-200 rounded-full px-3 py-1.5">
                            <div class="w-6 h-6 rounded-full bg-primary text-primary-content flex items-center justify-center text-xs font-bold overflow-hidden flex-shrink-0">
                                @if($p['avatar'])
                                    <img src="{{ $p['avatar'] }}" class="w-full h-full object-cover" />
                                @else
                                    {{ substr($p['name'], 0, 1) }}
                                @endif
                            </div>
                            <span class="text-sm font-medium">{{ $p['name'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- RIGHT: Live Leaderboard --}}
        <div class="bg-base-100 rounded-2xl border border-base-content/5 p-5">
            <h3 class="font-bold mb-4 flex items-center gap-2">
                <x-icon name="o-trophy" class="w-4 h-4 text-warning" /> Live Leaderboard
            </h3>
            @if(empty($leaderboard))
                <div class="text-center py-8 text-base-content/30 text-sm">
                    No submissions yet
                </div>
            @else
                <div class="space-y-2">
                    @foreach($leaderboard as $entry)
                        <div class="flex items-center gap-3 p-2 rounded-xl
                            {{ $entry['rank'] === 1 ? 'bg-warning/10 border border-warning/20' :
                               ($entry['rank'] === 2 ? 'bg-base-200/80' :
                               ($entry['rank'] === 3 ? 'bg-base-200/50' : '')) }}">
                            <span class="w-7 text-center font-black text-sm
                                {{ $entry['rank'] <= 3 ? 'text-warning' : 'text-base-content/30' }}">
                                @if($entry['rank'] === 1) 🥇
                                @elseif($entry['rank'] === 2) 🥈
                                @elseif($entry['rank'] === 3) 🥉
                                @else #{{ $entry['rank'] }}
                                @endif
                            </span>
                            <div class="w-8 h-8 rounded-full bg-primary text-primary-content flex items-center justify-center text-xs font-bold overflow-hidden flex-shrink-0">
                                @if($entry['avatar'])
                                    <img src="{{ $entry['avatar'] }}" class="w-full h-full object-cover" />
                                @else
                                    {{ substr($entry['name'], 0, 1) }}
                                @endif
                            </div>
                            <div class="flex-grow min-w-0">
                                <p class="text-sm font-bold truncate">{{ $entry['name'] }}</p>
                                <p class="text-xs text-base-content/40">{{ gmdate('i:s', $entry['time_taken_seconds'] ?? 0) }}</p>
                            </div>
                            <span class="text-sm font-black text-primary">{{ round($entry['score_percentage'] ?? 0) }}%</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Force End Confirmation --}}
    <x-modal wire:model="confirmEndModal" title="Force End Quiz?" box-class="max-w-sm">
        <div class="text-center space-y-3 py-2">
            <div class="text-5xl">⚠️</div>
            <p class="font-bold">This will auto-submit all {{ count($participants) }} in-progress attempt(s) and close the quiz.</p>
            <p class="text-sm text-base-content/50">This action cannot be undone.</p>
        </div>
        <x-slot:actions>
            <x-button label="Cancel" wire:click="$set('confirmEndModal', false)" class="btn-ghost" />
            <x-button label="End Quiz Now" icon="o-stop" wire:click="forceEndQuiz" class="btn-error" spinner="forceEndQuiz" />
        </x-slot:actions>
    </x-modal>

    {{-- Auto-refresh every 10 seconds while live --}}
    @if($quiz->status === 'live')
        <div wire:poll.10s="refreshStats"></div>
    @endif
</div>
