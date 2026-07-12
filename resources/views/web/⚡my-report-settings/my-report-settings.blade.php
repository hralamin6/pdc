<div class="min-h-screen bg-slate-50 dark:bg-slate-950 pb-16">

    {{-- HEADER --}}
    <div class="bg-indigo-950 text-white py-12 relative overflow-hidden">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <a href="{{ route('web.my-report') }}" wire:navigate class="w-12 h-12 rounded-full bg-white/10 hover:bg-white/20 border border-white/20 flex items-center justify-center transition-colors">
                    <x-icon name="o-arrow-left" class="w-6 h-6 text-white" />
                </a>
                <div>
                    <h1 class="text-3xl md:text-4xl font-black tracking-tight">{{ __('Tracking Settings') }}</h1>
                    <p class="text-indigo-200 mt-1 font-medium">{{ __('Customize which activities and habits you want to track daily.') }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" wire:click="resetDefaults" wire:confirm="{{ __('Are you sure you want to reset your tracked items to system defaults?') }}" class="btn bg-white/10 hover:bg-white/20 text-white border-none rounded-xl px-5 font-black">
                    <x-icon name="o-arrow-path" class="w-5 h-5" />
                    {{ __('Reset') }}
                </button>
                <button type="button" wire:click="$set('showCustomModal', true)" class="btn bg-indigo-500 hover:bg-indigo-400 text-white border-none rounded-xl px-6 font-black shadow-lg shadow-indigo-500/30">
                    <x-icon name="o-plus" class="w-5 h-5" />
                    {{ __('Add Custom Item') }}
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 mt-8 space-y-8">

        {{-- SYSTEM DEFAULT TEMPLATES --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 space-y-6">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <h2 class="text-xl font-black text-slate-900 dark:text-white flex items-center gap-2">
                    <x-icon name="o-list-bullet" class="w-6 h-6 text-indigo-500" />
                    {{ __('System Standard Items') }}
                </h2>
                <span class="text-xs font-bold text-slate-400">{{ __('Toggle to enable or disable from your daily form') }}</span>
            </div>

            @php
                $userItemsByTemplate = collect($userItems)->keyBy('daily_report_template_id');
                $groupedTemplates = collect($templates)->groupBy('category');
            @endphp

            @foreach($groupedTemplates as $category => $items)
                <div class="space-y-3">
                    <h3 class="font-black text-[10px] text-slate-400 uppercase tracking-widest bg-slate-100 dark:bg-slate-800 px-3 py-1.5 rounded-lg inline-block">{{ __($category) }}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($items as $template)
                            @php
                                $userItem = $userItemsByTemplate->get($template['id']);
                                $isActive = $userItem ? (bool)$userItem['is_active'] : false;
                            @endphp
                            <div class="flex items-center justify-between p-4 rounded-2xl border-2 transition-all {{ $isActive ? 'bg-indigo-50 dark:bg-indigo-900/20 border-indigo-200 dark:border-indigo-800' : 'bg-slate-50 dark:bg-slate-800/40 border-slate-200 dark:border-slate-700' }}">
                                <div>
                                    <p class="font-bold text-slate-900 dark:text-white">{{ $template['title'] }}</p>
                                    <p class="text-xs font-bold text-slate-400 capitalize mt-0.5">{{ __('Input:') }} {{ $template['type'] }}</p>
                                </div>
                                <div class="shrink-0 ml-4">
                                    <x-toggle wire:click="toggleTemplate({{ $template['id'] }})" :checked="$isActive" class="toggle-indigo" />
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        {{-- PERSONAL CUSTOM ITEMS --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 space-y-6">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <h2 class="text-xl font-black text-slate-900 dark:text-white flex items-center gap-2">
                    <x-icon name="o-sparkles" class="w-6 h-6 text-emerald-500" />
                    {{ __('Personal Custom Items') }}
                </h2>
            </div>

            @php $customs = collect($userItems)->whereNull('daily_report_template_id')->values(); @endphp

            @if($customs->isEmpty())
                <div class="text-center py-12 bg-slate-50 dark:bg-slate-800/40 rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-700">
                    <x-icon name="o-sparkles" class="w-10 h-10 text-slate-300 dark:text-slate-600 mx-auto mb-3" />
                    <p class="text-lg font-black text-slate-700 dark:text-slate-300">{{ __('No personal custom items added yet.') }}</p>
                    <p class="text-sm font-medium text-slate-400 mt-1 mb-6">{{ __('Create custom goals like "Read 5 pages of Seerah" or "Drink 2L Water".') }}</p>
                    <button type="button" wire:click="$set('showCustomModal', true)" class="btn bg-white dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-xl font-black hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                        <x-icon name="o-plus" class="w-4 h-4" /> {{ __('Add Your First Item') }}
                    </button>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($customs as $index => $custom)
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 rounded-2xl border-2 transition-all gap-4 {{ $custom['is_active'] ? 'bg-emerald-50 dark:bg-emerald-900/10 border-emerald-200 dark:border-emerald-800' : 'bg-slate-50 dark:bg-slate-800/40 border-slate-200 dark:border-slate-700' }}">
                            
                            <div class="flex items-center gap-4">
                                <div class="flex flex-col gap-1 shrink-0">
                                    <button type="button" wire:click="moveUp({{ $custom['id'] }})" class="p-1 hover:bg-white dark:hover:bg-slate-800 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">
                                        <x-icon name="o-chevron-up" class="w-4 h-4" />
                                    </button>
                                    <button type="button" wire:click="moveDown({{ $custom['id'] }})" class="p-1 hover:bg-white dark:hover:bg-slate-800 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">
                                        <x-icon name="o-chevron-down" class="w-4 h-4" />
                                    </button>
                                </div>
                                <div>
                                    <p class="font-bold text-slate-900 dark:text-white text-lg">{{ $custom['custom_title'] }}</p>
                                    <p class="text-xs font-bold text-slate-400 capitalize mt-0.5">{{ __('Input:') }} {{ $custom['type'] }}</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-4 self-end sm:self-auto">
                                <x-toggle wire:click="toggleCustomItem({{ $custom['id'] }})" :checked="$custom['is_active']" class="toggle-emerald" />
                                <div class="w-px h-6 bg-slate-200 dark:bg-slate-700"></div>
                                <button type="button" wire:click="deleteCustomItem({{ $custom['id'] }})" wire:confirm="{{ __('Delete this custom item permanently?') }}" class="btn btn-ghost btn-circle text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/20">
                                    <x-icon name="o-trash" class="w-5 h-5" />
                                </button>
                            </div>
                            
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- CUSTOM ITEM MODAL --}}
    <x-modal wire:model="showCustomModal" title="{{ __('Create Personal Trackable') }}" class="backdrop-blur">
        <div class="space-y-5 pt-2">
            <div>
                <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-2">{{ __('Activity Title') }}</label>
                <input type="text" wire:model="customTitle" placeholder="{{ __('e.g. Read 5 pages of Seerah, Morning Jog') }}" class="w-full rounded-2xl border-2 border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 focus:border-indigo-500 focus:ring-0 font-bold p-4 transition-colors" />
            </div>

            <div>
                <label class="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-2">{{ __('Input Format') }}</label>
                <x-select
                    wire:model="customType"
                    :options="[
                        ['id' => 'boolean', 'name' => __('Checkbox (Done / Not Done)')],
                        ['id' => 'number', 'name' => __('Numeric Stepper (Amount / Pages / Minutes)')],
                        ['id' => 'text', 'name' => __('Text Field (Reflection / Journal Note)')],
                        ['id' => 'mixed', 'name' => __('Checkbox + Text Note')],
                    ]"
                    class="rounded-2xl border-2 border-slate-200 dark:border-slate-700 font-bold focus:border-indigo-500"
                />
            </div>
        </div>

        <x-slot:actions>
            <x-button :label="__('Cancel')" wire:click="$set('showCustomModal', false)" class="btn-ghost rounded-xl font-bold" />
            <button type="button" wire:click="saveCustomItem" wire:loading.attr="disabled" class="btn bg-indigo-600 hover:bg-indigo-700 text-white border-none rounded-xl px-6 font-black shadow-lg shadow-indigo-600/30">
                <span wire:loading.remove wire:target="saveCustomItem">{{ __('Save Trackable') }}</span>
                <span wire:loading wire:target="saveCustomItem"><x-icon name="o-arrow-path" class="w-5 h-5 animate-spin" /></span>
            </button>
        </x-slot:actions>
    </x-modal>
</div>
