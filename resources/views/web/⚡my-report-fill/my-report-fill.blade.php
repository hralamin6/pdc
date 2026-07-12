<div class="min-h-screen bg-slate-50 dark:bg-slate-950 pb-16">

    {{-- HERO HEADER --}}
    <div class="bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 text-white py-16 relative overflow-hidden">
        <div class="absolute top-0 right-1/4 w-72 h-72 bg-emerald-500/10 rounded-full blur-[120px]"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <a href="{{ route('web.my-report') }}" wire:navigate class="w-12 h-12 rounded-full bg-white/10 hover:bg-white/20 border border-white/20 flex items-center justify-center transition-colors">
                    <x-icon name="o-arrow-left" class="w-6 h-6 text-white" />
                </a>
                <div>
                    <div class="flex items-center gap-2 text-rose-400 font-bold text-xs uppercase tracking-widest mb-1.5">
                        <a href="{{ route('web.my-report') }}" wire:navigate class="hover:underline">{{ __('My Report') }}</a>
                        <span>/</span>
                        <span>{{ __('Fill') }}</span>
                    </div>
                    <h1 class="text-3xl md:text-5xl font-black tracking-tight">{{ __('Daily Reflection Log') }}</h1>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                <label class="text-xs font-black uppercase tracking-widest text-white/50">{{ __('Date') }}</label>
                <div class="bg-white/10 border border-white/20 rounded-2xl overflow-hidden backdrop-blur-md">
                    <x-select 
                        wire:model.live="date" 
                        :options="$availableDates" 
                        class="select-sm !border-none bg-transparent !text-white font-black w-44 focus:ring-0" 
                    />
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8 relative z-10 space-y-6">

        {{-- PROGRESS TRACKER --}}
        @php
            $totalItems = count($trackingItems);
            $completedCount = 0;
            foreach ($trackingItems as $item) {
                $e = $entries[$item->id] ?? [];
                if (($e['boolean_value'] ?? false) || (isset($e['numeric_value']) && (int)$e['numeric_value'] > 0) || !empty(trim($e['text_value'] ?? ''))) {
                    $completedCount++;
                }
            }
            $progressPct = $totalItems > 0 ? (int) round(($completedCount / $totalItems) * 100) : 0;
        @endphp

        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm flex flex-col md:flex-row items-center gap-6">
            <div class="w-16 h-16 rounded-2xl bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 border border-emerald-200 dark:border-emerald-800">
                <x-icon name="o-sparkles" class="w-8 h-8" />
            </div>
            <div class="flex-grow w-full">
                <div class="flex items-center justify-between mb-3">
                    <span class="font-black text-slate-900 dark:text-white">{{ __('Daily Progress') }}</span>
                    <span class="font-black text-emerald-600 dark:text-emerald-400">{{ $completedCount }} / {{ $totalItems }} ({{ $progressPct }}%)</span>
                </div>
                <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-3 overflow-hidden">
                    <div class="bg-gradient-to-r from-emerald-400 to-teal-500 h-full rounded-full transition-all duration-700" style="width: {{ $progressPct }}%"></div>
                </div>
            </div>
        </div>

        <form wire:submit="saveReport" class="space-y-6">
            
            {{-- GROUPED ITEMS --}}
            @php
                $groupedItems = collect($trackingItems)->groupBy(fn($item) => $item->template->category ?? 'General');
                $categoryIcons = ['Ibadah' => 'o-sparkles', 'Quran & Adhkar' => 'o-book-open', 'Study & Action' => 'o-academic-cap', 'Personal & Community' => 'o-user-group'];
            @endphp

            @foreach($groupedItems as $category => $items)
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50 dark:bg-slate-900/50">
                    <div class="flex items-center gap-3">
                        <div class="p-2.5 bg-indigo-100 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 rounded-xl">
                            <x-icon :name="$categoryIcons[$category] ?? 'o-list-bullet'" class="w-5 h-5" />
                        </div>
                        <h3 class="font-black text-xl text-slate-900 dark:text-white">{{ __($category) }}</h3>
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 px-3 py-1 bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700">{{ $items->count() }} {{ __('items') }}</span>
                </div>

                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($items as $item)
                        @php
                            $title = $item->custom_title ?? $item->template->title;
                            $type = $item->type;
                            $entry = $entries[$item->id] ?? [];
                            $isDone = ($entry['boolean_value'] ?? false) || (isset($entry['numeric_value']) && (int)$entry['numeric_value'] > 0) || !empty(trim($entry['text_value'] ?? ''));
                        @endphp
                        <div class="p-6 hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors {{ $isDone ? 'bg-emerald-50/30 dark:bg-emerald-900/5' : '' }}">
                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-5">
                                
                                <div class="flex items-center gap-4">
                                    @if(in_array($type, ['boolean', 'mixed']))
                                        <div class="shrink-0">
                                            <input type="checkbox" wire:model.live="entries.{{ $item->id }}.boolean_value" 
                                                class="w-6 h-6 rounded-lg border-2 border-slate-300 dark:border-slate-600 text-emerald-500 focus:ring-emerald-500 focus:ring-offset-0 bg-transparent transition-colors cursor-pointer" />
                                        </div>
                                    @else
                                        <div class="shrink-0 w-6 h-6 rounded-full border-2 border-slate-200 dark:border-slate-700 flex items-center justify-center text-[10px] font-black text-slate-400">#</div>
                                    @endif
                                    <div>
                                        <p class="font-bold text-base md:text-lg text-slate-900 dark:text-white {{ $isDone ? 'text-emerald-600 dark:text-emerald-400' : '' }}">{{ $title }}</p>
                                    </div>
                                </div>

                                <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                                    @if($type === 'number')
                                        <div class="flex items-center bg-white dark:bg-slate-900 border-2 border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden shrink-0">
                                            <button type="button" wire:click="adjustNumber({{ $item->id }}, -1)" class="w-10 h-10 flex items-center justify-center text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 font-black transition-colors">-</button>
                                            <input type="number" wire:model.live="entries.{{ $item->id }}.numeric_value" placeholder="0" class="w-16 h-10 text-center border-x-2 border-slate-200 dark:border-slate-700 !border-y-0 bg-slate-50 dark:bg-slate-800 font-black focus:ring-0 !p-0" />
                                            <button type="button" wire:click="adjustNumber({{ $item->id }}, 1)" class="w-10 h-10 flex items-center justify-center text-indigo-500 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 font-black transition-colors">+</button>
                                        </div>
                                    @endif
                                    @if(in_array($type, ['text', 'mixed']))
                                        <div class="flex-grow md:w-64">
                                            <input type="text" wire:model.live="entries.{{ $item->id }}.text_value" placeholder="{{ __('Add details...') }}" 
                                                class="w-full h-10 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 focus:border-indigo-500 focus:ring-0 text-sm font-medium px-4 transition-colors" />
                                        </div>
                                    @endif
                                </div>

                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endforeach

            {{-- REFLECTION & PRIVACY --}}
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 space-y-6">
                <h3 class="font-black text-xl text-slate-900 dark:text-white flex items-center gap-2">
                    <x-icon name="o-pencil" class="w-5 h-5 text-indigo-500" />
                    {{ __('Reflection & Privacy') }}
                </h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-2">{{ __('Notes / Journal') }}</label>
                        <textarea wire:model="notes" rows="3" placeholder="{{ __('Reflect on your struggles, achievements, or intentions today...') }}" 
                            class="w-full rounded-2xl border-2 border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 focus:border-indigo-500 focus:ring-0 font-medium p-4 transition-colors resize-none"></textarea>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-2">{{ __('Visibility') }}</label>
                        <x-select
                            wire:model="privacy_level"
                            :options="[
                                ['id' => 'private', 'name' => __('🔒 Private (Only Visible to Me)')],
                                ['id' => 'mentor_only', 'name' => __('👥 Mentor Only (Visible to Me & Assigned Mentors)')],
                                ['id' => 'public', 'name' => __('🌐 Public (Community Insights)')],
                            ]"
                            class="rounded-xl border-2 border-slate-200 dark:border-slate-700 font-bold focus:border-indigo-500"
                        />
                    </div>
                </div>
            </div>

            {{-- ACTIONS --}}
            <div class="flex items-center justify-end gap-3 pt-4">
                <a href="{{ route('web.my-report') }}" wire:navigate class="btn bg-white dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-2xl px-8 font-black hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                    {{ __('Cancel') }}
                </a>
                <button type="submit" wire:loading.attr="disabled" class="btn bg-indigo-600 hover:bg-indigo-700 text-white border-none rounded-2xl px-10 font-black shadow-lg shadow-indigo-600/30 transition-transform hover:-translate-y-0.5">
                    <span wire:loading.remove wire:target="saveReport">{{ __('Save Log') }}</span>
                    <span wire:loading wire:target="saveReport"><x-icon name="o-arrow-path" class="w-5 h-5 animate-spin" /></span>
                </button>
            </div>

        </form>
    </div>
</div>
