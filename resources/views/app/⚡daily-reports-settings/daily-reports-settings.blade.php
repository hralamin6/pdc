<div class="max-w-4xl mx-auto py-6">
    <div class="flex items-center gap-4 mb-8">
        <x-button icon="o-arrow-left" class="btn-circle btn-ghost" :link="route('app.daily-reports')" wire:navigate />
        <div>
            <h1 class="text-3xl font-bold text-base-content">{{ __('Tracking Settings') }}</h1>
            <p class="text-base-content/70 mt-1">{{ __('Choose which activities you want to track daily.') }}</p>
        </div>
    </div>

    {{-- System Defaults --}}
    <div class="bg-base-100 rounded-2xl shadow-sm border border-base-content/5 p-6 mb-8">
        <h2 class="text-xl font-bold text-base-content mb-4 flex items-center gap-2">
            <x-icon name="o-list-bullet" class="w-6 h-6 text-primary" /> System Items
        </h2>
        
        @php
            $grouped = collect($templates)->groupBy('category');
        @endphp

        @foreach($grouped as $category => $items)
            <div class="mb-6 last:mb-0">
                <h3 class="font-semibold text-sm text-base-content/50 uppercase tracking-wider mb-3">{{ $category }}</h3>
                <div class="space-y-3">
                    @foreach($items as $template)
                        @php
                            $isActive = isset($userItems[$template['id']]) ? $userItems[$template['id']]['is_active'] : false;
                        @endphp
                        <div class="flex items-center justify-between p-3 rounded-xl hover:bg-base-200/50 transition-colors border border-transparent {{ $isActive ? 'bg-primary/5 border-primary/20' : '' }}">
                            <div class="flex items-center gap-3">
                                <div>
                                    <p class="font-medium text-base-content">{{ $template['title'] }}</p>
                                    <p class="text-xs text-base-content/50 capitalize">Input: {{ $template['type'] }}</p>
                                </div>
                            </div>
                            <x-toggle 
                                wire:click="toggleTemplate({{ $template['id'] }})" 
                                :checked="$isActive" 
                                class="toggle-primary" 
                            />
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    {{-- Custom Items --}}
    <div class="bg-base-100 rounded-2xl shadow-sm border border-base-content/5 p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-base-content flex items-center gap-2">
                <x-icon name="o-sparkles" class="w-6 h-6 text-secondary" /> Personal Custom Items
            </h2>
            <x-button icon="o-plus" class="btn-sm btn-primary rounded-full" label="Add New" wire:click="$set('showCustomModal', true)" />
        </div>

        @php
            $customs = collect($userItems)->whereNull('daily_report_template_id')->all();
        @endphp

        @if(empty($customs))
            <div class="text-center py-6 text-base-content/50 text-sm">
                You haven't added any custom tracking items yet.
            </div>
        @else
            <div class="space-y-3">
                @foreach($customs as $custom)
                    <div class="flex items-center justify-between p-3 rounded-xl hover:bg-base-200/50 transition-colors border border-transparent {{ $custom['is_active'] ? 'bg-primary/5 border-primary/20' : '' }}">
                        <div class="flex items-center gap-3">
                            <div>
                                <p class="font-medium text-base-content">{{ $custom['custom_title'] }}</p>
                                <p class="text-xs text-base-content/50 capitalize">Input: {{ $custom['type'] }}</p>
                            </div>
                        </div>
                        <x-toggle 
                            wire:click="toggleCustomItem({{ $custom['id'] }})" 
                            :checked="$custom['is_active']" 
                            class="toggle-primary" 
                        />
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Modal for Custom Items --}}
    <x-modal wire:model="showCustomModal" title="Create Custom Trackable" class="backdrop-blur">
        <div class="space-y-4">
            <x-input label="Item Title" wire:model="customTitle" placeholder="e.g. Read 5 pages of seerah" />
            
            <x-select label="Input Type" wire:model="customType" :options="[
                ['id' => 'boolean', 'name' => 'Checkbox (Yes/No)'],
                ['id' => 'number', 'name' => 'Number Input'],
                ['id' => 'text', 'name' => 'Text / Notes'],
                ['id' => 'mixed', 'name' => 'Checkbox + Text'],
            ]" />
        </div>
        
        <x-slot:actions>
            <x-button label="Cancel" wire:click="$set('showCustomModal', false)" class="btn-ghost" />
            <x-button label="Save Item" wire:click="saveCustomItem" class="btn-primary" spinner />
        </x-slot:actions>
    </x-modal>
</div>
