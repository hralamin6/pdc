<div class="max-w-7xl mx-auto py-6 space-y-8">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-base-100 p-6 rounded-3xl border border-base-content/5 shadow-sm">
        <div>
            <div class="flex items-center gap-3">
                <div class="p-3 bg-primary/10 text-primary rounded-2xl">
                    <x-icon name="o-chart-bar-square" class="w-8 h-8" />
                </div>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-base-content tracking-tight">{{ __('Spiritual & Growth Dashboard') }}</h1>
                    <p class="text-sm text-base-content/60 mt-0.5">{{ __('Consistency in daily reflection and good deeds builds lasting character.') }}</p>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <x-button icon="o-cog-6-tooth" :link="route('app.daily-reports.settings')" wire:navigate class="btn-ghost btn-circle" tooltip="Tracking Settings" />
            @if(auth()->user()->hasRole(['mentor', 'admin', 'super-admin']))
                <x-button icon="o-shield-check" :link="route('app.daily-reports.admin')" wire:navigate class="btn-outline btn-primary rounded-xl font-bold" label="Admin Portal" />
            @endif
            <x-button 
                :label="$todayReport && $todayReport->status === 'submitted' ? 'Edit Today\'s Log' : 'Fill Today\'s Report'" 
                icon="o-pencil-square" 
                class="btn-primary rounded-xl px-6 font-bold shadow-lg shadow-primary/20" 
                :link="route('app.daily-reports.form')" 
                wire:navigate 
            />
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        {{-- Streak & Milestones --}}
        <div class="bg-gradient-to-br from-amber-500/10 via-orange-500/5 to-base-100 rounded-3xl p-6 border border-orange-500/20 shadow-sm flex flex-col justify-between relative overflow-hidden">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-extrabold text-orange-500 uppercase tracking-wider bg-orange-500/10 px-3 py-1 rounded-full border border-orange-500/20">Active Streak</span>
                    <div class="flex items-baseline gap-2 mt-3">
                        <span class="text-5xl font-black text-base-content">{{ $streak->current_streak }}</span>
                        <span class="text-lg font-bold text-base-content/60">Days</span>
                    </div>
                    <p class="text-xs text-base-content/50 mt-1">Personal Best: <strong class="text-base-content/80">{{ $streak->longest_streak }} days</strong></p>
                </div>
                <div class="p-4 bg-orange-500 text-white rounded-2xl shadow-lg shadow-orange-500/30 animate-pulse">
                    <x-icon name="o-fire" class="w-8 h-8" />
                </div>
            </div>

            {{-- Milestone Badges Row --}}
            <div class="mt-6 pt-4 border-t border-orange-500/10">
                <p class="text-xs font-semibold text-base-content/60 mb-2">Milestone Achievements</p>
                <div class="flex items-center gap-2 overflow-x-auto pb-1">
                    @foreach($milestones as $badge)
                        <div 
                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl border text-xs font-bold transition-all shrink-0 cursor-pointer {{ $badge['unlocked'] ? $badge['color'] : 'bg-base-200/50 border-base-content/10 text-base-content/30 opacity-60' }}"
                            title="{{ $badge['name'] }}: {{ $badge['description'] }}"
                        >
                            <x-icon :name="$badge['icon']" class="w-4 h-4" />
                            <span>{{ $badge['target'] }}d</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Today's Status Banner --}}
        <div class="bg-base-100 rounded-3xl p-6 border border-base-content/5 shadow-sm flex flex-col justify-between">
            <div class="flex items-start justify-between">
                <div>
                    <span class="text-xs font-extrabold text-primary uppercase tracking-wider bg-primary/10 px-3 py-1 rounded-full border border-primary/20">Today's Reflection</span>
                    <h3 class="text-xl font-extrabold text-base-content mt-3">
                        @if($todayReport && $todayReport->status === 'submitted')
                            🎉 Today's Log Complete!
                        @else
                            ⌛ Pending Today's Log
                        @endif
                    </h3>
                    <p class="text-xs text-base-content/60 mt-1">
                        @if($todayReport && $todayReport->status === 'submitted')
                            You have completed {{ $todayReport->completed_count }} out of {{ $todayReport->total_items_count }} tracked items ({{ $todayReport->completion_percentage }}%).
                        @else
                            Take a few minutes before the day ends to reflect and log your progress.
                        @endif
                    </p>
                </div>
                @if($todayReport && $todayReport->status === 'submitted')
                    <div class="w-16 h-16 rounded-2xl bg-success/10 text-success flex flex-col items-center justify-center border border-success/20 shrink-0">
                        <span class="text-lg font-black">{{ $todayReport->completion_percentage }}%</span>
                    </div>
                @else
                    <div class="w-16 h-16 rounded-2xl bg-warning/10 text-warning flex items-center justify-center border border-warning/20 shrink-0">
                        <x-icon name="o-clock" class="w-8 h-8" />
                    </div>
                @endif
            </div>

            <div class="mt-6 pt-4 border-t border-base-content/5 flex items-center justify-between text-xs text-base-content/60">
                <span>Date: <strong>{{ now()->format('M j, Y') }}</strong></span>
                <a href="{{ route('app.daily-reports.form') }}" wire:navigate class="text-primary font-bold hover:underline flex items-center gap-1">
                    {{ $todayReport && $todayReport->status === 'submitted' ? 'Review & Edit' : 'Log Now' }}
                    <x-icon name="o-arrow-right" class="w-3.5 h-3.5" />
                </a>
            </div>
        </div>

        {{-- 30-Day Overview --}}
        <div class="bg-base-100 rounded-3xl p-6 border border-base-content/5 shadow-sm flex flex-col justify-between">
            <div>
                <span class="text-xs font-extrabold text-secondary uppercase tracking-wider bg-secondary/10 px-3 py-1 rounded-full border border-secondary/20">30-Day Consistency</span>
                <div class="flex items-baseline gap-2 mt-3">
                    <span class="text-5xl font-black text-base-content">{{ $overall30DayRate }}%</span>
                    <span class="text-sm font-semibold text-base-content/60">Submission Rate</span>
                </div>
                <div class="w-full bg-base-200 rounded-full h-2.5 mt-3 overflow-hidden">
                    <div class="bg-secondary h-2.5 rounded-full transition-all duration-500" style="width: {{ $overall30DayRate }}%"></div>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-base-content/5 flex items-center justify-between text-xs text-base-content/60">
                <span>Tracking active items daily</span>
                <span class="badge badge-sm badge-ghost font-bold">Past 30 Days</span>
            </div>
        </div>
    </div>

    {{-- Interactive 30-Day History Heatmap --}}
    <div class="bg-base-100 rounded-3xl border border-base-content/5 shadow-sm p-6 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h3 class="text-lg font-bold text-base-content flex items-center gap-2">
                    <x-icon name="o-calendar" class="w-5 h-5 text-primary" />
                    <span>30-Day History & Reflection Log</span>
                </h3>
                <p class="text-xs text-base-content/60">Click on any past date to view full report entries and reflection notes.</p>
            </div>
            <div class="flex items-center gap-3 text-xs">
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-md bg-success"></span> Submitted</span>
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-md bg-warning"></span> Draft</span>
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-md bg-base-200"></span> Missed</span>
            </div>
        </div>

        <div class="grid grid-cols-5 sm:grid-cols-6 md:grid-cols-10 gap-2.5 pt-2">
            @foreach($last30Days as $day)
                <button 
                    type="button"
                    @if($day['report_id']) wire:click="viewReport({{ $day['report_id'] }})" @endif
                    class="flex flex-col items-center justify-between p-2.5 rounded-2xl border text-center transition-all duration-200 group relative
                        @if($day['status'] === 'submitted')
                            bg-success/10 border-success/30 hover:bg-success/20 hover:scale-105 shadow-sm
                        @elseif($day['status'] === 'draft')
                            bg-warning/10 border-warning/30 hover:bg-warning/20 hover:scale-105
                        @elseif($day['is_today'])
                            bg-primary/5 border-primary border-dashed hover:bg-primary/10 hover:scale-105
                        @else
                            bg-base-200/50 border-base-content/5 opacity-70 hover:opacity-100 hover:bg-base-200
                        @endif
                    "
                >
                    <span class="text-[10px] font-semibold text-base-content/50 uppercase">{{ $day['date']->format('D') }}</span>
                    <span class="text-sm font-extrabold text-base-content my-0.5">{{ $day['date']->format('d') }}</span>
                    
                    @if($day['status'] === 'submitted')
                        <span class="text-[10px] font-bold text-success">{{ $day['completion_percentage'] }}%</span>
                    @elseif($day['is_today'])
                        <span class="text-[10px] font-bold text-primary">Today</span>
                    @else
                        <span class="text-[10px] font-medium text-base-content/40">-</span>
                    @endif
                </button>
            @endforeach
        </div>
    </div>

    {{-- Category Performance Breakdown --}}
    <div class="bg-base-100 rounded-3xl border border-base-content/5 shadow-sm p-6 space-y-6">
        <div>
            <h3 class="text-lg font-bold text-base-content flex items-center gap-2">
                <x-icon name="o-list-bullet" class="w-5 h-5 text-secondary" />
                <span>Category Consistency (Last 30 Days)</span>
            </h3>
            <p class="text-xs text-base-content/60">Completion rate broken down by activity area.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($categoryStats as $name => $cat)
                @php
                    $pct = $cat['total'] > 0 ? (int) round(($cat['completed'] / $cat['total']) * 100) : 0;
                @endphp
                <div class="bg-base-200/40 border border-base-content/5 rounded-2xl p-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <x-icon :name="$cat['icon']" class="w-5 h-5 text-{{ $cat['color'] }}" />
                            <span class="font-bold text-sm text-base-content">{{ $name }}</span>
                        </div>
                        <span class="text-xs font-extrabold text-{{ $cat['color'] }}">{{ $pct }}%</span>
                    </div>

                    <div class="w-full bg-base-200 rounded-full h-2 overflow-hidden">
                        <div class="bg-{{ $cat['color'] }} h-2 rounded-full transition-all duration-500" style="width: {{ $pct }}%"></div>
                    </div>

                    <div class="flex justify-between items-center text-[11px] text-base-content/50">
                        <span>Completed: <strong>{{ $cat['completed'] }}</strong></span>
                        <span>Tracked: <strong>{{ $cat['total'] }}</strong></span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Report Inspection Modal --}}
    <x-modal wire:model="selectedReportId" title="Report Reflection Log" class="backdrop-blur">
        @if($selectedReportDetails)
            <div class="space-y-6">
                {{-- Header summary --}}
                <div class="flex items-center justify-between p-4 rounded-2xl bg-base-200/50 border border-base-content/5">
                    <div>
                        <p class="text-xs font-semibold text-base-content/50 uppercase">Log Date</p>
                        <h4 class="text-lg font-extrabold text-base-content">{{ $selectedReportDetails['date'] }}</h4>
                    </div>
                    <div class="text-right">
                        <span class="badge badge-success font-bold">{{ strtoupper($selectedReportDetails['status']) }}</span>
                        <p class="text-xs text-base-content/60 mt-1">Privacy: <strong class="capitalize">{{ str_replace('_', ' ', $selectedReportDetails['privacy_level']) }}</strong></p>
                    </div>
                </div>

                {{-- Completion progress ring --}}
                <div class="flex items-center justify-between p-4 rounded-2xl bg-primary/5 border border-primary/20">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-primary/20 text-primary flex items-center justify-center font-black text-lg">
                            {{ $selectedReportDetails['completion_percentage'] }}%
                        </div>
                        <div>
                            <p class="font-bold text-base-content text-sm">Completion Rate</p>
                            <p class="text-xs text-base-content/60">{{ $selectedReportDetails['completed_count'] }} of {{ $selectedReportDetails['total_items_count'] }} items completed</p>
                        </div>
                    </div>
                </div>

                {{-- Entries Grouped by Category --}}
                <div class="space-y-4 max-h-72 overflow-y-auto pr-1">
                    @foreach($selectedReportDetails['entries'] as $category => $entries)
                        <div class="space-y-2">
                            <h5 class="text-xs font-bold text-base-content/50 uppercase tracking-wider border-b border-base-content/10 pb-1">{{ $category }}</h5>
                            <div class="space-y-1.5">
                                @foreach($entries as $entry)
                                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-base-200/30 border border-base-content/5 text-xs">
                                        <div class="flex items-center gap-2">
                                            @if($entry['is_done'])
                                                <x-icon name="o-check-circle" class="w-4 h-4 text-success" />
                                            @else
                                                <x-icon name="o-x-circle" class="w-4 h-4 text-base-content/30" />
                                            @endif
                                            <span class="font-semibold text-base-content">{{ $entry['title'] }}</span>
                                        </div>
                                        <div class="text-right text-base-content/70">
                                            @if($entry['numeric_value'] !== null)
                                                <span class="badge badge-sm badge-outline font-bold">{{ $entry['numeric_value'] }}</span>
                                            @endif
                                            @if(!empty($entry['text_value']))
                                                <span class="italic text-xs text-base-content/60 ml-1">"{{ $entry['text_value'] }}"</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Notes / Reflection --}}
                @if(!empty($selectedReportDetails['notes']))
                    <div class="p-4 rounded-2xl bg-base-200/50 border border-base-content/5 space-y-1">
                        <p class="text-xs font-bold text-base-content/50 uppercase">Personal Reflection / Notes</p>
                        <p class="text-sm text-base-content italic">"{{ $selectedReportDetails['notes'] }}"</p>
                    </div>
                @endif
            </div>
        @endif

        <x-slot:actions>
            <x-button label="Close" wire:click="closeReportModal" class="btn-ghost rounded-xl" />
        </x-slot:actions>
    </x-modal>
</div>
