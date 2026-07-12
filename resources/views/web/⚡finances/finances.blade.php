<div class="min-h-screen bg-slate-50 dark:bg-slate-950 pb-16">

    {{-- HERO --}}
    <div class="bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 text-white py-16 relative overflow-hidden">
        <div class="absolute top-0 right-1/4 w-72 h-72 bg-emerald-500/10 rounded-full blur-[120px]"></div>
        <div class="absolute bottom-0 left-1/4 w-96 h-96 bg-indigo-500/10 rounded-full blur-[100px]"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-white/10 rounded-full text-[10px] font-black uppercase tracking-widest mb-5 border border-white/10">
                <x-icon name="o-banknotes" class="w-4 h-4 text-emerald-400" />
                {{ __('Treasury Transparency') }}
            </div>
            <h1 class="text-4xl md:text-6xl font-black mb-4 tracking-tight">{{ __('Community Financial Report') }}</h1>
            <p class="text-white/60 text-base max-w-2xl font-medium">
                {{ __('Open records of all donations and expenses for complete trust and accountability. Every taka accounted for.') }}
            </p>
        </div>
    </div>

    {{-- ANIMATED HERO STATS --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-10 relative z-10">
        @php
            $s = $this->totalStats;
            $statsRow = [
                ['label' => __('Total Raised'), 'value' => '৳'.number_format($s['total_income']), 'icon' => 'o-arrow-trending-up', 'color' => 'emerald', 'sub' => __('All confirmed donations')],
                ['label' => __('Total Spent'), 'value' => '৳'.number_format($s['total_expense']), 'icon' => 'o-receipt-percent', 'color' => 'rose', 'sub' => __('Confirmed expenses')],
                ['label' => __('Net Balance'), 'value' => '৳'.number_format(abs($s['net_balance'])), 'icon' => 'o-scale', 'color' => $s['net_balance'] >= 0 ? 'teal' : 'amber', 'sub' => $s['net_balance'] >= 0 ? __('Surplus') : __('Deficit')],
                ['label' => __('Total Donors'), 'value' => $s['total_donors'], 'icon' => 'o-users', 'color' => 'indigo', 'sub' => __('Unique contributors')],
            ];
        @endphp
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach($statsRow as $st)
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                <div class="w-12 h-12 bg-{{ $st['color'] }}-100 dark:bg-{{ $st['color'] }}-500/20 text-{{ $st['color'] }}-600 dark:text-{{ $st['color'] }}-400 rounded-2xl flex items-center justify-center mb-4">
                    <x-icon name="{{ $st['icon'] }}" class="w-6 h-6" />
                </div>
                <p class="text-2xl md:text-3xl font-black text-slate-900 dark:text-white leading-none">{{ $st['value'] }}</p>
                <p class="text-xs font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mt-2">{{ $st['label'] }}</p>
                <p class="text-[10px] text-slate-400 mt-1">{{ $st['sub'] }}</p>
            </div>
            @endforeach
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-10 space-y-10">

        {{-- CHARTS ROW --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            {{-- BAR CHART: Monthly Income vs Expense --}}
            <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-black text-slate-900 dark:text-white">{{ __('Monthly Overview') }}</h2>
                        <p class="text-sm text-slate-500 mt-1">{{ __('Income vs Expenses — Last 6 months') }}</p>
                    </div>
                    <div class="flex items-center gap-4 text-xs font-black">
                        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span>{{ __('Income') }}</span>
                        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-rose-500 inline-block"></span>{{ __('Expense') }}</span>
                    </div>
                </div>
                @php
                    $chartData = $this->last6MonthsStats;
                    $maxVal = max(array_merge(array_column($chartData, 'income'), array_column($chartData, 'expense'), [1]));
                @endphp
                <div class="flex items-end gap-3 h-48" x-data>
                    @foreach($chartData as $bar)
                    @php
                        $incPct = $maxVal > 0 ? round(($bar['income'] / $maxVal) * 100) : 0;
                        $expPct = $maxVal > 0 ? round(($bar['expense'] / $maxVal) * 100) : 0;
                    @endphp
                    <div class="flex-1 flex flex-col items-center gap-1 group">
                        <div class="w-full flex items-end gap-1 h-40">
                            <div class="flex-1 bg-emerald-100 dark:bg-emerald-500/20 rounded-t-xl relative overflow-hidden hover:bg-emerald-200 dark:hover:bg-emerald-500/30 transition-colors"
                                 style="height: {{ max($incPct, 2) }}%"
                                 title="{{ __('Income') }}: ৳{{ number_format($bar['income']) }}">
                                <div class="absolute inset-0 bg-gradient-to-t from-emerald-500 to-emerald-400 opacity-80"></div>
                            </div>
                            <div class="flex-1 bg-rose-100 dark:bg-rose-500/20 rounded-t-xl relative overflow-hidden hover:bg-rose-200 dark:hover:bg-rose-500/30 transition-colors"
                                 style="height: {{ max($expPct, 2) }}%"
                                 title="{{ __('Expense') }}: ৳{{ number_format($bar['expense']) }}">
                                <div class="absolute inset-0 bg-gradient-to-t from-rose-500 to-rose-400 opacity-80"></div>
                            </div>
                        </div>
                        <p class="text-[10px] font-bold text-slate-400 text-center leading-tight">{{ $bar['label'] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- DONUT CHART: Donation Breakdown by Type --}}
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
                <h2 class="text-xl font-black text-slate-900 dark:text-white mb-1">{{ __('Donation Breakdown') }}</h2>
                <p class="text-sm text-slate-500 mb-6">{{ __('By category type') }}</p>
                @php
                    $byType = $this->totalStats['by_type'] ?? [];
                    $typeTotal = max(array_sum($byType), 1);
                    $typeColors = ['general' => 'indigo', 'campaign' => 'emerald', 'halaqah' => 'amber', 'recurring' => 'fuchsia'];
                @endphp
                <div class="space-y-4">
                    @forelse($byType as $type => $amount)
                    @php $color = $typeColors[$type] ?? 'slate'; $pct = round(($amount / $typeTotal) * 100); @endphp
                    <div>
                        <div class="flex justify-between items-center mb-1.5">
                            <span class="text-sm font-black text-slate-700 dark:text-slate-300 capitalize">{{ __($type) }}</span>
                            <span class="text-xs font-black text-slate-500">৳{{ number_format($amount) }} <span class="text-{{ $color }}-500">({{ $pct }}%)</span></span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2.5 overflow-hidden">
                            <div class="bg-{{ $color }}-500 h-full rounded-full transition-all duration-700" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                    @empty
                    <p class="text-sm text-slate-400 text-center py-8">{{ __('No donation data yet.') }}</p>
                    @endforelse
                </div>

                {{-- Net Balance Summary --}}
                <div class="mt-8 p-4 rounded-2xl {{ $this->totalStats['net_balance'] >= 0 ? 'bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-100 dark:border-emerald-800' : 'bg-amber-50 dark:bg-amber-500/10 border border-amber-100 dark:border-amber-800' }}">
                    <p class="text-[10px] font-black uppercase tracking-widest {{ $this->totalStats['net_balance'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }} mb-1">
                        {{ $this->totalStats['net_balance'] >= 0 ? __('Current Surplus') : __('Current Deficit') }}
                    </p>
                    <p class="text-2xl font-black {{ $this->totalStats['net_balance'] >= 0 ? 'text-emerald-700 dark:text-emerald-300' : 'text-amber-700 dark:text-amber-300' }}">
                        ৳{{ number_format(abs($this->totalStats['net_balance'])) }}
                    </p>
                </div>
            </div>
        </div>

        {{-- EXPENSE BREAKDOWN --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-xl font-black text-slate-900 dark:text-white">{{ __('Full Expense & Income Transparency') }}</h2>
                    <p class="text-sm text-slate-500 mt-1">{{ __('Last 6 months detailed breakdown — income vs expenses vs net') }}</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800">
                            <th class="text-left py-3 px-4 text-xs font-black uppercase tracking-widest text-slate-400">{{ __('Period') }}</th>
                            <th class="text-right py-3 px-4 text-xs font-black uppercase tracking-widest text-emerald-500">{{ __('Income') }}</th>
                            <th class="text-right py-3 px-4 text-xs font-black uppercase tracking-widest text-rose-500">{{ __('Expense') }}</th>
                            <th class="text-right py-3 px-4 text-xs font-black uppercase tracking-widest text-slate-400">{{ __('Net') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($this->last6MonthsStats as $row)
                        <tr class="border-b border-slate-50 dark:border-slate-800/50 hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                            <td class="py-3 px-4 font-bold text-slate-800 dark:text-slate-200">{{ $row['label'] }}</td>
                            <td class="py-3 px-4 text-right font-black text-emerald-600 dark:text-emerald-400">৳{{ number_format($row['income']) }}</td>
                            <td class="py-3 px-4 text-right font-black text-rose-600 dark:text-rose-400">৳{{ number_format($row['expense']) }}</td>
                            <td class="py-3 px-4 text-right font-black {{ $row['net'] >= 0 ? 'text-teal-600 dark:text-teal-400' : 'text-amber-600 dark:text-amber-400' }}">
                                {{ $row['net'] >= 0 ? '+' : '-' }}৳{{ number_format(abs($row['net'])) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- MONTHLY TREASURY REPORTS --}}
        @if($this->publishedReports->count())
        <div>
            <div class="flex items-center justify-between mb-6">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-indigo-100 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 rounded-full text-[10px] font-black uppercase tracking-widest mb-3">
                        <x-icon name="o-document-chart-bar" class="w-4 h-4" /> {{ __('Official Records') }}
                    </div>
                    <h2 class="text-3xl font-black text-slate-900 dark:text-white">{{ __('Published Monthly Reports') }}</h2>
                    <p class="text-slate-500 mt-1 text-sm">{{ __('Admin-verified monthly treasury statements') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($this->publishedReports as $report)
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group">
                    {{-- Header --}}
                    <div class="bg-gradient-to-br from-indigo-600 to-violet-700 p-6 text-white relative overflow-hidden">
                        <div class="absolute -right-8 -top-8 w-28 h-28 bg-white/10 rounded-full"></div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-indigo-200 mb-1">{{ __('Monthly Report') }}</p>
                        <h3 class="text-2xl font-black">{{ $report->month_name }}</h3>
                        @if($report->published_at)
                        <p class="text-indigo-200 text-xs mt-1">{{ __('Published') }} {{ $report->published_at->format('d M Y') }}</p>
                        @endif
                    </div>

                    {{-- Stats --}}
                    <div class="p-6 space-y-3">
                        <div class="flex justify-between items-center p-3 bg-emerald-50 dark:bg-emerald-500/10 rounded-xl">
                            <span class="text-xs font-black text-emerald-700 dark:text-emerald-400 uppercase tracking-wide">{{ __('Income') }}</span>
                            <span class="font-black text-emerald-700 dark:text-emerald-400">৳{{ number_format($report->total_income) }}</span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-rose-50 dark:bg-rose-500/10 rounded-xl">
                            <span class="text-xs font-black text-rose-700 dark:text-rose-400 uppercase tracking-wide">{{ __('Expense') }}</span>
                            <span class="font-black text-rose-700 dark:text-rose-400">৳{{ number_format($report->total_expense) }}</span>
                        </div>
                        @if($report->total_transfer_fees > 0)
                        <div class="flex justify-between items-center p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl">
                            <span class="text-xs font-black text-slate-500 uppercase tracking-wide">{{ __('Transfer Fees') }}</span>
                            <span class="font-black text-slate-500">৳{{ number_format($report->total_transfer_fees) }}</span>
                        </div>
                        @endif
                        <div class="border-t border-slate-100 dark:border-slate-800 pt-3 flex justify-between items-center">
                            <span class="text-sm font-black text-slate-600 dark:text-slate-400">{{ __('Net Surplus') }}</span>
                            <span class="text-lg font-black {{ $report->surplus >= 0 ? 'text-teal-600 dark:text-teal-400' : 'text-amber-600 dark:text-amber-400' }}">
                                {{ $report->surplus >= 0 ? '+' : '' }}৳{{ number_format($report->surplus) }}
                            </span>
                        </div>

                        @if($report->opening_balance > 0 || $report->closing_balance > 0)
                        <div class="grid grid-cols-2 gap-2 mt-2">
                            <div class="p-2 bg-slate-50 dark:bg-slate-800/40 rounded-lg text-center">
                                <p class="text-[10px] font-black text-slate-400 uppercase">{{ __('Opening') }}</p>
                                <p class="font-black text-slate-700 dark:text-slate-300 text-sm">৳{{ number_format($report->opening_balance) }}</p>
                            </div>
                            <div class="p-2 bg-slate-50 dark:bg-slate-800/40 rounded-lg text-center">
                                <p class="text-[10px] font-black text-slate-400 uppercase">{{ __('Closing') }}</p>
                                <p class="font-black text-slate-700 dark:text-slate-300 text-sm">৳{{ number_format($report->closing_balance) }}</p>
                            </div>
                        </div>
                        @endif

                        @if($report->notes)
                        <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed mt-2 italic border-l-2 border-indigo-300 pl-3">{{ $report->notes }}</p>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- COMMITMENT BANNER --}}
        <div class="bg-gradient-to-br from-slate-900 to-indigo-950 rounded-[2.5rem] p-10 md:p-16 text-white relative overflow-hidden">
            <div class="absolute -right-20 -top-20 w-80 h-80 bg-indigo-500/20 rounded-full blur-[80px]"></div>
            <div class="absolute -left-10 -bottom-10 w-60 h-60 bg-emerald-500/10 rounded-full blur-[60px]"></div>
            <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-8">
                <div class="max-w-xl">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-white/10 rounded-full text-[10px] font-black uppercase tracking-widest mb-4 border border-white/10">
                        <x-icon name="o-shield-check" class="w-4 h-4 text-emerald-400" />
                        {{ __('Our Commitment') }}
                    </div>
                    <h2 class="text-3xl md:text-4xl font-black mb-4 leading-tight">{{ __('100% Transparent. Every Taka Tracked.') }}</h2>
                    <p class="text-white/70 font-medium leading-relaxed">{{ __('We believe every community member deserves to see exactly how donated funds are managed. This page is updated monthly with verified reports from our treasury.') }}</p>
                </div>
                <a href="{{ route('web.campaigns') }}" wire:navigate
                   class="shrink-0 btn bg-emerald-500 hover:bg-emerald-400 text-white border-none rounded-2xl px-10 h-14 font-black shadow-[0_10px_25px_rgba(16,185,129,0.3)] hover:-translate-y-1 transition-transform text-base whitespace-nowrap">
                    {{ __('Support a Campaign') }}
                </a>
            </div>
        </div>

    </div>
</div>
