<div class="max-w-4xl mx-auto py-6 space-y-8">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-base-100 p-6 rounded-3xl border border-base-content/5 shadow-sm">
        <div class="flex items-center gap-4">
            <x-button icon="o-arrow-left" class="btn-circle btn-ghost" :link="route('app.daily-reports')" wire:navigate />
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-base-content tracking-tight">{{ __('Tracking Preferences') }}</h1>
                <p class="text-xs sm:text-sm text-base-content/60 mt-0.5">{{ __('Customize which activities and habits you want to track daily.') }}</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <x-button
                :label="__('Reset Defaults')"
                icon="o-arrow-path"
                wire:click="resetDefaults"
                wire:confirm="{{ __('Are you sure you want to reset your tracked items to system defaults?') }}"
                class="btn-ghost btn-sm rounded-xl text-base-content/60"
            />
            <x-button
                :label="__('Add Custom Item')"
                icon="o-plus"
                wire:click="$set('showCustomModal', true)"
                class="btn-primary btn-sm rounded-xl font-bold"
            />
        </div>
    </div>

    {{-- System Default Templates --}}
    <div class="bg-base-100 rounded-3xl border border-base-content/5 shadow-sm p-6 space-y-6">
        <div class="flex items-center justify-between border-b border-base-content/5 pb-3">
            <h2 class="text-lg font-bold text-base-content flex items-center gap-2">
                <x-icon name="o-list-bullet" class="w-5 h-5 text-primary" />
                <span>{{ __('System Standard Items') }}</span>
            </h2>
            <span class="text-xs text-base-content/50">{{ __('Toggle to enable or disable from your daily form') }}</span>
        </div>

        @php
            $userItemsByTemplate = collect($userItems)->keyBy('daily_report_template_id');
            $groupedTemplates = collect($templates)->groupBy('category');
        @endphp

        @foreach($groupedTemplates as $category => $items)
            <div class="space-y-3">
                <h3 class="font-bold text-xs text-base-content/50 uppercase tracking-wider bg-base-200/50 px-3 py-1.5 rounded-xl inline-block">{{ $category }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach($items as $template)
                        @php
                            $userItem = $userItemsByTemplate->get($template['id']);
                            $isActive = $userItem ? (bool)$userItem['is_active'] : false;
                        @endphp
                        <div class="flex items-center justify-between p-3.5 rounded-2xl border transition-all {{ $isActive ? 'bg-primary/5 border-primary/20' : 'bg-base-200/20 border-base-content/5' }}">
                            <div class="flex items-center gap-3">
                                <div>
                                    <p class="font-bold text-sm text-base-content">{{ $template['title'] }}</p>
                                    <p class="text-xs text-base-content/50 capitalize">{{ __('Input:') }} {{ $template['type'] }}</p>
                                </div>
                            </div>
                            <x-toggle 
                                wire:click="toggleTemplate({{ $template['id'] }})" 
                                :checked="$isActive" 
                                class="toggle-primary toggle-sm" 
                            />
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    {{-- Personal Custom Items --}}
    <div class="bg-base-100 rounded-3xl border border-base-content/5 shadow-sm p-6 space-y-6">
        <div class="flex items-center justify-between border-b border-base-content/5 pb-3">
            <h2 class="text-lg font-bold text-base-content flex items-center gap-2">
                <x-icon name="o-sparkles" class="w-5 h-5 text-secondary" />
                <span>{{ __('Personal Custom Items') }}</span>
            </h2>
            <x-button
                icon="o-plus"
                class="btn-xs btn-outline btn-secondary rounded-lg"
                :label="__('Create New')"
                wire:click="$set('showCustomModal', true)"
            />
        </div>

        @php
            $customs = collect($userItems)->whereNull('daily_report_template_id')->values();
        @endphp

        @if($customs->isEmpty())
            <div class="text-center py-8 bg-base-200/20 rounded-2xl border border-dashed border-base-content/10 space-y-2">
                <x-icon name="o-sparkles" class="w-8 h-8 text-base-content/30 mx-auto" />
                <p class="text-sm font-semibold text-base-content/60">{{ __('No personal custom items added yet.') }}</p>
                <p class="text-xs text-base-content/40">{{ __('You can create custom goals like "Read 5 pages of Seerah" or "Drink 2L Water".') }}</p>
            </div>
        @else
            <div class="space-y-2">
                @foreach($customs as $index => $custom)
                    <div class="flex items-center justify-between p-3.5 rounded-2xl border transition-all {{ $custom['is_active'] ? 'bg-secondary/5 border-secondary/20' : 'bg-base-200/20 border-base-content/5' }}">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-1">
                                <button 
                                    type="button" 
                                    wire:click="moveUp({{ $custom['id'] }})" 
                                    class="p-1 hover:bg-base-200 rounded text-base-content/40 hover:text-base-content" 
                                    title="{{ __('Move Up') }}"
                                >
                                    <x-icon name="o-chevron-up" class="w-3.5 h-3.5" />
                                </button>
                                <button 
                                    type="button" 
                                    wire:click="moveDown({{ $custom['id'] }})" 
                                    class="p-1 hover:bg-base-200 rounded text-base-content/40 hover:text-base-content" 
                                    title="{{ __('Move Down') }}"
                                >
                                    <x-icon name="o-chevron-down" class="w-3.5 h-3.5" />
                                </button>
                            </div>
                            <div>
                                <p class="font-bold text-sm text-base-content">{{ $custom['custom_title'] }}</p>
                                <p class="text-xs text-base-content/50 capitalize">{{ __('Type:') }} {{ $custom['type'] }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <x-toggle 
                                wire:click="toggleCustomItem({{ $custom['id'] }})" 
                                :checked="$custom['is_active']" 
                                class="toggle-secondary toggle-sm" 
                            />
                            <button 
                                type="button" 
                                wire:click="deleteCustomItem({{ $custom['id'] }})" 
                                wire:confirm="{{ __('Delete this custom item permanently?') }}"
                                class="btn btn-ghost btn-circle btn-xs text-error"
                                title="{{ __('Delete Item') }}"
                            >
                                <x-icon name="o-trash" class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Modal for Custom Items --}}
    <x-modal wire:model="showCustomModal" :title="__('Create Personal Trackable')" class="backdrop-blur">
        <div class="space-y-4 pt-2">
            <x-input
                :label="__('Activity Title')"
                wire:model="customTitle"
                :placeholder="__('e.g. Read 5 pages of Seerah, Nafl Charity, Morning Jog')"
                class="rounded-xl border-base-content/10"
            />

            <x-select
                :label="__('Input Format')"
                wire:model="customType"
                :options="[
                    ['id' => 'boolean', 'name' => __('Checkbox (Done / Not Done)')],
                    ['id' => 'number', 'name' => __('Numeric Stepper (Amount / Pages / Minutes)')],
                    ['id' => 'text', 'name' => __('Text Field (Reflection / Journal Note)')],
                    ['id' => 'mixed', 'name' => __('Checkbox + Text Note')],
                ]"
                class="rounded-xl border-base-content/10"
            />
        </div>

        <x-slot:actions>
            <x-button :label="__('Cancel')" wire:click="$set('showCustomModal', false)" class="btn-ghost rounded-xl" />
            <x-button :label="__('Save Trackable')" wire:click="saveCustomItem" class="btn-primary rounded-xl font-bold" spinner />
        </x-slot:actions>
    </x-modal>
</div>
