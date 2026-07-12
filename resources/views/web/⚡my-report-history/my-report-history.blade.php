<div class="min-h-screen bg-slate-50 dark:bg-slate-950 pb-16">

    {{-- HEADER --}}
    <div class="bg-indigo-950 text-white py-12 relative overflow-hidden">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <a href="{{ route('web.my-report') }}" wire:navigate class="w-12 h-12 rounded-full bg-white/10 hover:bg-white/20 border border-white/20 flex items-center justify-center transition-colors">
                    <x-icon name="o-arrow-left" class="w-6 h-6 text-white" />
                </a>
                <div>
                    <h1 class="text-3xl md:text-4xl font-black tracking-tight">{{ __('Reflection History') }}</h1>
                    <p class="text-indigo-200 mt-1 font-medium">{{ __('Browse your past daily logs and notes.') }}</p>
                </div>
            </div>
            <a href="{{ route('web.my-report.fill') }}" wire:navigate class="btn bg-emerald-500 hover:bg-emerald-400 text-white border-none rounded-2xl px-6 font-black shadow-lg shadow-emerald-500/30">
                <x-icon name="o-pencil-square" class="w-5 h-5" />
                {{ __("Fill Today's Log") }}
            </a>
        </div>
    </div>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 mt-8 space-y-6">

        @if($reports->isEmpty())
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-12 text-center shadow-sm">
                <div class="w-20 h-20 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-400">
                    <x-icon name="o-clock" class="w-10 h-10" />
                </div>
                <h3 class="text-xl font-black text-slate-900 dark:text-white mb-2">{{ __('No Reports Yet') }}</h3>
                <p class="text-slate-500 mb-6">{{ __('You haven\'t submitted any daily reports. Start tracking your progress today!') }}</p>
                <a href="{{ route('web.my-report.fill') }}" wire:navigate class="btn bg-indigo-600 hover:bg-indigo-700 text-white border-none rounded-xl px-8 font-black">
                    {{ __('Start Tracking') }}
                </a>
            </div>
        @else
            <div class="space-y-4">
                @foreach($reports as $report)
                    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-5 shadow-sm hover:shadow-md transition-shadow flex flex-col sm:flex-row sm:items-center justify-between gap-5 group">
                        
                        <div class="flex items-center gap-5">
                            <div class="w-16 h-16 rounded-2xl flex flex-col items-center justify-center shrink-0 border 
                                {{ $report->status === 'submitted' ? 'bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 dark:border-emerald-800 text-emerald-600 dark:text-emerald-400' : 'bg-amber-50 dark:bg-amber-900/20 border-amber-200 dark:border-amber-800 text-amber-600 dark:text-amber-400' }}">
                                <span class="text-[10px] font-black uppercase tracking-widest">{{ $report->date->format('M') }}</span>
                                <span class="text-xl font-black leading-none my-0.5">{{ $report->date->format('d') }}</span>
                            </div>
                            
                            <div>
                                <h3 class="font-black text-lg text-slate-900 dark:text-white">{{ $report->date->format('l') }}</h3>
                                <div class="flex items-center gap-3 mt-1">
                                    @if($report->status === 'submitted')
                                        <span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                            <x-icon name="o-check-circle" class="w-4 h-4" /> {{ __('Submitted') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-xs font-bold text-amber-600 dark:text-amber-400">
                                            <x-icon name="o-pencil" class="w-4 h-4" /> {{ __('Draft') }}
                                        </span>
                                    @endif
                                    <span class="text-slate-300 dark:text-slate-600">&bull;</span>
                                    <span class="text-xs font-bold text-slate-500">{{ $report->completed_count }}/{{ $report->total_items_count }} {{ __('items') }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-6">
                            <div class="hidden sm:block text-right">
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">{{ __('Completion') }}</p>
                                <p class="font-black text-2xl text-slate-900 dark:text-white">{{ $report->completion_percentage }}%</p>
                            </div>
                            <button type="button" wire:click="viewReport({{ $report->id }})" class="btn bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border-none rounded-xl px-6 font-black shrink-0 w-full sm:w-auto">
                                {{ __('View Log') }}
                            </button>
                        </div>

                    </div>
                @endforeach
            </div>
            
            <div class="mt-6">
                {{ $reports->links(data: ['scrollTo' => false]) }}
            </div>
        @endif
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
        </x-slot:actions>
    </x-modal>
</div>
