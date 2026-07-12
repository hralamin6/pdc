<div class="min-h-screen bg-slate-50 dark:bg-slate-950 pb-16">

    {{-- HERO HEADER --}}
    <div class="bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 text-white py-16 relative overflow-hidden">
        <div class="absolute top-0 right-1/4 w-72 h-72 bg-emerald-500/10 rounded-full blur-[120px]"></div>
        <div class="absolute bottom-0 left-1/4 w-64 h-64 bg-indigo-500/10 rounded-full blur-[100px]"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-white/10 rounded-full text-[10px] font-black uppercase tracking-widest mb-4 border border-white/10">
                        <x-icon name="o-chart-bar-square" class="w-4 h-4 text-emerald-400" />
                        {{ __('Spiritual & Growth') }}
                    </div>
                    <h1 class="text-4xl md:text-5xl font-black mb-3 tracking-tight">{{ __('My Daily Report') }}</h1>
                    <p class="text-white/60 font-medium max-w-xl">
                        {{ __('Track your Ibadah, Quran, and habits daily. Build consistency and grow spiritually.') }}
                    </p>
                </div>

                {{-- Quick Actions --}}
                <div class="flex items-center gap-3 shrink-0">
                    @if($this->todayReport && $this->todayReport->status === 'submitted')
                        <div class="flex items-center gap-2 px-4 py-2 bg-emerald-500/20 border border-emerald-500/30 rounded-2xl">
                            <x-icon name="o-check-circle" class="w-5 h-5 text-emerald-400" />
                            <span class="text-emerald-300 font-bold text-sm">{{ __("Today's log submitted") }}</span>
                        </div>
                    @else
                        <div class="flex items-center gap-2 px-4 py-2 bg-amber-500/20 border border-amber-500/30 rounded-2xl">
                            <x-icon name="o-clock" class="w-5 h-5 text-amber-400" />
                            <span class="text-amber-300 font-bold text-sm">{{ __("Pending today's log") }}</span>
                        </div>
                    @endif
                    
                    <a href="{{ route('web.my-report.settings') }}" wire:navigate class="w-10 h-10 rounded-2xl bg-white/10 hover:bg-white/20 border border-white/20 flex items-center justify-center transition-colors tooltip tooltip-bottom" data-tip="{{ __('Settings') }}">
                        <x-icon name="o-cog-6-tooth" class="w-5 h-5 text-white" />
                    </a>
                    
                    <a href="{{ route('web.my-report.fill') }}" wire:navigate
                       class="btn bg-emerald-500 hover:bg-emerald-400 text-white border-none rounded-2xl px-6 font-black shadow-lg shadow-emerald-500/30 hover:-translate-y-0.5 transition-transform">
                        <x-icon name="o-pencil-square" class="w-5 h-5" />
                        {{ $this->todayReport && $this->todayReport->status === 'submitted' ? __('Edit Log') : __("Fill Today's Log") }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8 relative z-10 space-y-8">

        {{-- STREAK + SUMMARY CARDS --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">

            {{-- Streak Card --}}
            <div class="bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 rounded-3xl border border-orange-200 dark:border-orange-800/50 p-6 shadow-sm relative overflow-hidden">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-orange-500/10 rounded-full blur-xl"></div>
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-orange-500 dark:text-orange-400 mb-2">{{ __('Active Streak') }}</p>
                        <p class="text-5xl font-black text-slate-900 dark:text-white leading-none">{{ $this->streak->current_streak }}</p>
                        <p class="text-sm font-bold text-slate-500 mt-1">{{ __('days') }}</p>
                        <p class="text-xs text-slate-400 mt-2">{{ __('Best:') }} <strong>{{ $this->streak->longest_streak }}d</strong></p>
                    </div>
                    <div class="p-3 bg-orange-500 text-white rounded-2xl shadow-lg shadow-orange-500/30">
                        <x-icon name="o-fire" class="w-7 h-7" />
                    </div>
                </div>
            </div>

            {{-- Today's Status --}}
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-widest text-indigo-500 mb-2">{{ __("Today's Reflection") }}</p>
                @if($this->todayReport && $this->todayReport->status === 'submitted')
                    <p class="text-3xl font-black text-slate-900 dark:text-white leading-none">{{ $this->todayReport->completion_percentage }}%</p>
                    <p class="text-sm font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ __('Complete') }}</p>
                    <p class="text-xs text-slate-400 mt-2">{{ $this->todayReport->completed_count }}/{{ $this->todayReport->total_items_count }} {{ __('items') }}</p>
                @else
                    <p class="text-3xl font-black text-slate-400 dark:text-slate-500 leading-none">—</p>
                    <p class="text-sm font-bold text-amber-600 dark:text-amber-400 mt-1">{{ __('Pending') }}</p>
                    <p class="text-xs text-slate-400 mt-2">{{ now()->format('M j, Y') }}</p>
                @endif
            </div>

            {{-- 30-Day Rate --}}
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-widest text-teal-500 mb-2">{{ __('30-Day Rate') }}</p>
                <p class="text-5xl font-black text-slate-900 dark:text-white leading-none">{{ $this->summaryStats['rate'] }}%</p>
                <p class="text-sm font-bold text-slate-500 mt-1">{{ __('Submission rate') }}</p>
                <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2 mt-3 overflow-hidden">
                    <div class="bg-teal-500 h-full rounded-full" style="width: {{ $this->summaryStats['rate'] }}%"></div>
                </div>
            </div>

            {{-- Total Reports --}}
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-widest text-violet-500 mb-2">{{ __('Avg Completion') }}</p>
                <p class="text-5xl font-black text-slate-900 dark:text-white leading-none">{{ $this->summaryStats['avg_completion'] }}%</p>
                <p class="text-sm font-bold text-slate-500 mt-1">{{ __('Per submitted report') }}</p>
                <p class="text-xs text-slate-400 mt-2">{{ $this->summaryStats['submitted'] }} {{ __('reports this month') }}</p>
            </div>
        </div>

        {{-- MILESTONE BADGES --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
            <h2 class="text-lg font-black text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                <x-icon name="o-trophy" class="w-5 h-5 text-amber-500" />
                {{ __('Milestone Achievements') }}
            </h2>
            <div class="flex flex-wrap gap-3">
                @foreach($this->milestones as $badge)
                <div class="flex items-center gap-2 px-4 py-2.5 rounded-2xl border font-bold text-sm transition-all
                    {{ $badge['unlocked']
                        ? $badge['color'] . ' shadow-sm'
                        : 'bg-slate-100 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-400 opacity-60' }}"
                    title="{{ $badge['description'] }}">
                    <x-icon :name="$badge['icon']" class="w-4 h-4" />
                    <span>{{ $badge['name'] }}</span>
                    @if($badge['unlocked'])
                        <x-icon name="o-check-badge" class="w-4 h-4" />
                    @else
                        <span class="text-[10px] opacity-70">{{ $badge['target'] }}d</span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        {{-- 30-DAY HEATMAP --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
                <div>
                    <h2 class="text-lg font-black text-slate-900 dark:text-white flex items-center gap-2">
                        <x-icon name="o-calendar-days" class="w-5 h-5 text-indigo-500" />
                        {{ __('30-Day History') }}
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">{{ __('Click any date to view the full report') }}</p>
                </div>
                <div class="flex items-center gap-4 text-xs font-bold">
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-md bg-emerald-500 inline-block"></span>{{ __('Submitted') }}</span>
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-md bg-amber-400 inline-block"></span>{{ __('Draft') }}</span>
                    <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-md bg-slate-200 dark:bg-slate-700 inline-block"></span>{{ __('Missed') }}</span>
                </div>
            </div>

            <div class="grid grid-cols-5 sm:grid-cols-6 md:grid-cols-10 gap-2">
                @foreach($this->last30Days as $day)
                <button type="button"
                    @if($day['report_id']) wire:click="viewReport({{ $day['report_id'] }})" @endif
                    class="flex flex-col items-center p-2.5 rounded-2xl border text-center transition-all duration-200 hover:scale-105
                        @if($day['status'] === 'submitted') bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 dark:border-emerald-700 shadow-sm
                        @elseif($day['status'] === 'draft') bg-amber-50 dark:bg-amber-900/20 border-amber-200 dark:border-amber-700
                        @elseif($day['is_today']) bg-indigo-50 dark:bg-indigo-900/20 border-indigo-300 border-dashed
                        @else bg-slate-50 dark:bg-slate-800/50 border-slate-100 dark:border-slate-800 opacity-60
                        @endif">
                    <span class="text-[9px] font-black uppercase text-slate-400">{{ $day['date']->format('D') }}</span>
                    <span class="text-sm font-black text-slate-900 dark:text-white my-0.5">{{ $day['date']->format('d') }}</span>
                    @if($day['status'] === 'submitted')
                        <span class="text-[9px] font-black text-emerald-600 dark:text-emerald-400">{{ $day['completion_percentage'] }}%</span>
                    @elseif($day['is_today'])
                        <span class="text-[9px] font-black text-indigo-500">{{ __('Now') }}</span>
                    @else
                        <span class="text-[9px] text-slate-300 dark:text-slate-600">—</span>
                    @endif
                </button>
                @endforeach
            </div>
        </div>

        {{-- CATEGORY STATS --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
            <h2 class="text-lg font-black text-slate-900 dark:text-white mb-5 flex items-center gap-2">
                <x-icon name="o-chart-pie" class="w-5 h-5 text-rose-500" />
                {{ __('Category Consistency (Last 30 Days)') }}
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach($this->categoryStats as $name => $cat)
                @php $pct = $cat['total'] > 0 ? (int) round(($cat['completed'] / $cat['total']) * 100) : 0; @endphp
                <div class="p-5 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-100 dark:border-slate-700/50 space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 bg-{{ $cat['color'] }}-100 dark:bg-{{ $cat['color'] }}-500/20 text-{{ $cat['color'] }}-600 dark:text-{{ $cat['color'] }}-400 rounded-xl flex items-center justify-center">
                                <x-icon :name="$cat['icon']" class="w-4 h-4" />
                            </div>
                            <span class="font-bold text-sm text-slate-700 dark:text-slate-300">{{ $name }}</span>
                        </div>
                        <span class="text-sm font-black text-{{ $cat['color'] }}-600 dark:text-{{ $cat['color'] }}-400">{{ $pct }}%</span>
                    </div>
                    <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2 overflow-hidden">
                        <div class="bg-{{ $cat['color'] }}-500 h-full rounded-full transition-all duration-700" style="width: {{ $pct }}%"></div>
                    </div>
                    <p class="text-[10px] text-slate-400 font-bold">{{ $cat['completed'] }}/{{ $cat['total'] }} {{ __('items completed') }}</p>
                </div>
                @endforeach
            </div>
        </div>

        {{-- QUICK NAV TO OTHER PAGES --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <a href="{{ route('web.my-report.fill') }}" wire:navigate class="group bg-gradient-to-br from-emerald-500 to-teal-600 rounded-3xl p-6 text-white shadow-lg shadow-emerald-500/20 hover:-translate-y-1 transition-transform">
                <x-icon name="o-pencil-square" class="w-8 h-8 mb-3" />
                <h3 class="font-black text-xl mb-1">{{ __("Fill Today's Log") }}</h3>
                <p class="text-emerald-100 text-sm">{{ __('Record your daily ibadah and activities') }}</p>
            </a>
            <a href="{{ route('web.my-report.history') }}" wire:navigate class="group bg-gradient-to-br from-indigo-500 to-violet-600 rounded-3xl p-6 text-white shadow-lg shadow-indigo-500/20 hover:-translate-y-1 transition-transform">
                <x-icon name="o-clock" class="w-8 h-8 mb-3" />
                <h3 class="font-black text-xl mb-1">{{ __('History') }}</h3>
                <p class="text-indigo-100 text-sm">{{ __('Browse all past reports and reflections') }}</p>
            </a>
            <a href="{{ route('web.my-report.stats') }}" wire:navigate class="group bg-gradient-to-br from-amber-500 to-orange-600 rounded-3xl p-6 text-white shadow-lg shadow-amber-500/20 hover:-translate-y-1 transition-transform">
                <x-icon name="o-chart-bar" class="w-8 h-8 mb-3" />
                <h3 class="font-black text-xl mb-1">{{ __('Stats & Charts') }}</h3>
                <p class="text-amber-100 text-sm">{{ __('Per-item trends, charts, and deep analytics') }}</p>
            </a>
        </div>

    </div>

    {{-- REPORT MODAL --}}
    <x-modal wire:model="selectedReportId" title="{{ __('Reflection Log') }}" class="backdrop-blur">
        @if($selectedReportDetails)
        <div class="space-y-5">
            <div class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-100 dark:border-slate-700">
                <div>
                    <p class="text-xs font-black text-slate-400 uppercase mb-1">{{ __('Date') }}</p>
                    <h4 class="text-lg font-black text-slate-900 dark:text-white">{{ $selectedReportDetails['date'] }}</h4>
                </div>
                <div class="text-right">
                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 rounded-full text-xs font-black">
                        <x-icon name="o-check-circle" class="w-3.5 h-3.5" />
                        {{ strtoupper($selectedReportDetails['status']) }}
                    </span>
                    <p class="text-xs text-slate-400 mt-1 capitalize">{{ str_replace('_', ' ', $selectedReportDetails['privacy_level']) }}</p>
                </div>
            </div>

            <div class="flex items-center gap-4 p-4 bg-indigo-50 dark:bg-indigo-900/20 rounded-2xl border border-indigo-100 dark:border-indigo-800">
                <div class="w-14 h-14 rounded-2xl bg-indigo-500 text-white flex items-center justify-center font-black text-xl shrink-0">
                    {{ $selectedReportDetails['completion_percentage'] }}%
                </div>
                <div>
                    <p class="font-black text-slate-900 dark:text-white">{{ __('Completion Rate') }}</p>
                    <p class="text-sm text-slate-500">{{ $selectedReportDetails['completed_count'] }}/{{ $selectedReportDetails['total_items_count'] }} {{ __('items') }}</p>
                </div>
            </div>

            <div class="space-y-4 max-h-64 overflow-y-auto pr-1">
                @foreach($selectedReportDetails['entries'] as $category => $entries)
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 border-b border-slate-100 dark:border-slate-800 pb-1.5 mb-2">{{ $category }}</p>
                    <div class="space-y-1.5">
                        @foreach($entries as $entry)
                        <div class="flex items-center justify-between px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800/40 text-sm">
                            <div class="flex items-center gap-2">
                                @if($entry['is_done'])
                                    <x-icon name="o-check-circle" class="w-4 h-4 text-emerald-500 shrink-0" />
                                @else
                                    <x-icon name="o-x-circle" class="w-4 h-4 text-slate-300 dark:text-slate-600 shrink-0" />
                                @endif
                                <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $entry['title'] }}</span>
                            </div>
                            @if($entry['numeric_value'] !== null)
                                <span class="text-xs font-black text-indigo-500 bg-indigo-50 dark:bg-indigo-900/30 px-2 py-0.5 rounded-lg">{{ $entry['numeric_value'] }}</span>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>

            @if(!empty($selectedReportDetails['notes']))
            <div class="p-4 bg-amber-50 dark:bg-amber-900/20 rounded-2xl border border-amber-100 dark:border-amber-800">
                <p class="text-[10px] font-black uppercase tracking-widest text-amber-600 dark:text-amber-400 mb-2">{{ __('Reflection Notes') }}</p>
                <p class="text-sm text-slate-700 dark:text-slate-300 italic leading-relaxed">"{{ $selectedReportDetails['notes'] }}"</p>
            </div>
            @endif
        </div>
        @endif
        <x-slot:actions>
            <x-button :label="__('Close')" wire:click="closeModal" class="btn-ghost rounded-xl" />
            <a href="{{ route('web.my-report.fill') }}" wire:navigate class="btn btn-primary rounded-xl font-bold">{{ __('Edit This Report') }}</a>
        </x-slot:actions>
    </x-modal>

</div>
