<div class="min-h-screen bg-slate-50 dark:bg-slate-950 pb-16">

    {{-- HEADER --}}
    <div class="bg-amber-600 text-white py-12 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-amber-700 to-amber-500 opacity-90"></div>
        <div class="absolute right-0 bottom-0 w-64 h-64 bg-yellow-400/20 rounded-full blur-3xl"></div>
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <a href="{{ route('web.my-report') }}" wire:navigate class="w-12 h-12 rounded-full bg-white/10 hover:bg-white/20 border border-white/20 flex items-center justify-center transition-colors">
                    <x-icon name="o-arrow-left" class="w-6 h-6 text-white" />
                </a>
                <div>
                    <h1 class="text-3xl md:text-4xl font-black tracking-tight">{{ __('Performance & Stats') }}</h1>
                    <p class="text-amber-100 mt-1 font-medium">{{ __('Deep dive into your 30-day habits and tracking trends.') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 mt-8 space-y-8">

        {{-- TOP STATS --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-2">{{ __('Total Reports') }}</p>
                <p class="text-4xl font-black text-slate-900 dark:text-white">{{ $this->generalStats['total_reports'] }}</p>
                <p class="text-xs text-slate-400 mt-1">{{ __('All time submitted logs') }}</p>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-widest text-emerald-500 mb-2">{{ __('Avg Completion') }}</p>
                <p class="text-4xl font-black text-emerald-600 dark:text-emerald-400">{{ $this->generalStats['avg_completion'] }}%</p>
                <p class="text-xs text-slate-400 mt-1">{{ __('Across all submitted reports') }}</p>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-widest text-indigo-500 mb-2">{{ __('This Month') }}</p>
                <div class="flex items-end gap-3">
                    <p class="text-4xl font-black text-indigo-600 dark:text-indigo-400">{{ $this->generalStats['this_month'] }}</p>
                    <p class="text-sm font-bold {{ $this->generalStats['growth'] >= 0 ? 'text-emerald-500' : 'text-rose-500' }} mb-1">
                        {{ $this->generalStats['growth'] >= 0 ? '+' : '' }}{{ $this->generalStats['growth'] }}%
                    </p>
                </div>
                <p class="text-xs text-slate-400 mt-1">{{ __('Compared to last month') }}</p>
            </div>
        </div>

        {{-- ITEM TRENDS BY CATEGORY --}}
        <div class="space-y-6">
            <h2 class="text-2xl font-black text-slate-900 dark:text-white">{{ __('30-Day Item Performance') }}</h2>
            
            @php $categoryIcons = ['Ibadah' => 'o-sparkles', 'Quran & Adhkar' => 'o-book-open', 'Study & Action' => 'o-academic-cap', 'Personal & Community' => 'o-user-group']; @endphp

            @foreach($this->itemTrends as $category => $items)
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50 flex items-center gap-3">
                    <div class="p-2 bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400 rounded-xl">
                        <x-icon :name="$categoryIcons[$category] ?? 'o-list-bullet'" class="w-5 h-5" />
                    </div>
                    <h3 class="font-black text-lg text-slate-900 dark:text-white">{{ __($category) }}</h3>
                </div>
                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($items as $item)
                    <div class="p-6 hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors flex flex-col md:flex-row md:items-center justify-between gap-6">
                        <div class="w-full md:w-1/3">
                            <p class="font-bold text-slate-900 dark:text-white text-lg">{{ $item['title'] }}</p>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">{{ __('Type:') }} {{ $item['type'] }}</p>
                        </div>
                        
                        <div class="w-full md:w-1/3">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-bold text-slate-500">{{ __('Consistency Rate') }}</span>
                                <span class="font-black text-emerald-600 dark:text-emerald-400">{{ $item['completion_rate'] }}%</span>
                            </div>
                            <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2 overflow-hidden">
                                <div class="bg-emerald-500 h-full rounded-full transition-all" style="width: {{ $item['completion_rate'] }}%"></div>
                            </div>
                            <p class="text-[10px] font-bold text-slate-400 mt-2">{{ $item['times_completed'] }} {{ __('completions in last 30 days') }}</p>
                        </div>

                        <div class="w-full md:w-1/4">
                            @if($item['type'] === 'number')
                                <div class="flex items-center gap-6">
                                    <div>
                                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">{{ __('30d Total') }}</p>
                                        <p class="font-black text-xl text-indigo-600 dark:text-indigo-400">{{ number_format($item['total_sum']) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">{{ __('Daily Avg') }}</p>
                                        <p class="font-black text-xl text-indigo-600 dark:text-indigo-400">{{ $item['avg_value'] }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
