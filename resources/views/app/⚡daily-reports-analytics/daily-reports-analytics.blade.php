<div class="max-w-7xl mx-auto py-6 space-y-8">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-base-100 p-6 rounded-3xl border border-base-content/5 shadow-sm">
        <div class="flex items-center gap-4">
            <x-button icon="o-arrow-left" class="btn-circle btn-ghost" :link="route('app.dashboard')" wire:navigate />
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-base-content tracking-tight">{{ __('Community Analytics & Insights') }}</h1>
                <p class="text-xs sm:text-sm text-base-content/60 mt-0.5">{{ __('Analyze aggregate habit consistency and report analytics.') }}</p>
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-base-100 p-5 rounded-2xl border border-base-content/5 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center text-primary shrink-0">
                <x-icon name="o-users" class="w-6 h-6" />
            </div>
            <div>
                <p class="text-2xl font-black text-base-content">{{ $stats['total_users'] }}</p>
                <p class="text-xs font-bold text-base-content/50 uppercase">{{ __('Total Members') }}</p>
            </div>
        </div>

        <div class="bg-base-100 p-5 rounded-2xl border border-base-content/5 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-success/10 flex items-center justify-center text-success shrink-0">
                <x-icon name="o-check-circle" class="w-6 h-6" />
            </div>
            <div>
                <p class="text-2xl font-black text-base-content">{{ $stats['submitted_today'] }}</p>
                <p class="text-xs font-bold text-base-content/50 uppercase">{{ __('Submitted Today') }}</p>
            </div>
        </div>

        <div class="bg-base-100 p-5 rounded-2xl border border-base-content/5 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-secondary/10 flex items-center justify-center text-secondary shrink-0">
                <x-icon name="o-chart-bar" class="w-6 h-6" />
            </div>
            <div>
                <p class="text-2xl font-black text-base-content">{{ $stats['rate_today'] }}%</p>
                <p class="text-xs font-bold text-base-content/50 uppercase">{{ __("Today's Submission Rate") }}</p>
            </div>
        </div>
    </div>

    {{-- Main Analytics section --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Submission Trend --}}
        <div class="bg-base-100 p-6 rounded-3xl border border-base-content/5 shadow-sm space-y-4 lg:col-span-2">
            <h2 class="text-lg font-bold text-base-content flex items-center gap-2">
                <x-icon name="o-arrow-trending-up" class="w-5 h-5 text-indigo-500" />
                <span>{{ __('7-Day Submission Trend') }}</span>
            </h2>

            <div class="flex items-end justify-between h-48 pt-4 gap-2">
                @foreach($last7Days as $day)
                    <div class="flex flex-col items-center flex-1 group">
                        <span class="text-[10px] font-bold text-base-content/60 mb-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            {{ $day['rate'] }}% ({{ $day['count'] }})
                        </span>
                        <div class="w-full bg-base-200 rounded-lg overflow-hidden h-32 flex items-end">
                            <div class="bg-indigo-500 hover:bg-indigo-600 transition-colors w-full rounded-t-md" style="height: {{ $day['rate'] }}%"></div>
                        </div>
                        <span class="text-[10px] font-bold text-base-content/50 mt-2 truncate w-full text-center">{{ $day['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Category Breakdown --}}
        <div class="bg-base-100 p-6 rounded-3xl border border-base-content/5 shadow-sm space-y-4">
            <h2 class="text-lg font-bold text-base-content flex items-center gap-2">
                <x-icon name="o-chart-pie" class="w-5 h-5 text-rose-500" />
                <span>{{ __('Category Performance') }}</span>
            </h2>

            <div class="space-y-4 pt-2">
                @forelse($categoryBreakdown as $cat)
                    <div class="space-y-1.5">
                        <div class="flex justify-between items-center text-xs font-bold">
                            <span class="text-base-content/80">{{ $cat['name'] }}</span>
                            <span class="text-indigo-500">{{ $cat['rate'] }}%</span>
                        </div>
                        <div class="w-full bg-base-200 rounded-full h-2">
                            <div class="bg-indigo-500 h-2 rounded-full" style="width: {{ $cat['rate'] }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-base-content/40 italic py-4 text-center">{{ __('No category stats available.') }}</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Top vs Bottom Habits --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Top 5 --}}
        <div class="bg-base-100 p-6 rounded-3xl border border-base-content/5 shadow-sm space-y-4">
            <h2 class="text-lg font-bold text-emerald-600 flex items-center gap-2">
                <x-icon name="o-hand-thumb-up" class="w-5 h-5" />
                <span>{{ __('Most Consistent Habits') }}</span>
            </h2>
            <div class="divide-y divide-base-content/5">
                @forelse($top5 as $item)
                    <div class="py-3 flex justify-between items-center">
                        <div>
                            <p class="font-bold text-sm text-base-content">{{ $item['title'] }}</p>
                            <p class="text-[10px] text-base-content/40 uppercase tracking-wider mt-0.5">{{ $item['category'] }}</p>
                        </div>
                        <span class="badge badge-success font-black text-xs">{{ $item['rate'] }}%</span>
                    </div>
                @empty
                    <p class="text-xs text-base-content/40 italic py-4 text-center">{{ __('No habit stats available.') }}</p>
                @endforelse
            </div>
        </div>

        {{-- Bottom 5 --}}
        <div class="bg-base-100 p-6 rounded-3xl border border-base-content/5 shadow-sm space-y-4">
            <h2 class="text-lg font-bold text-warning flex items-center gap-2">
                <x-icon name="o-hand-thumb-down" class="w-5 h-5" />
                <span>{{ __('Least Consistent Habits') }}</span>
            </h2>
            <div class="divide-y divide-base-content/5">
                @forelse($bottom5 as $item)
                    <div class="py-3 flex justify-between items-center">
                        <div>
                            <p class="font-bold text-sm text-base-content">{{ $item['title'] }}</p>
                            <p class="text-[10px] text-base-content/40 uppercase tracking-wider mt-0.5">{{ $item['category'] }}</p>
                        </div>
                        <span class="badge badge-warning font-black text-xs">{{ $item['rate'] }}%</span>
                    </div>
                @empty
                    <p class="text-xs text-base-content/40 italic py-4 text-center">{{ __('No habit stats available.') }}</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
