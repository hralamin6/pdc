<div class="max-w-7xl mx-auto py-6 space-y-8">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-base-100 p-6 rounded-3xl border border-base-content/5 shadow-sm">
        <div class="flex items-center gap-4">
            <x-button icon="o-arrow-left" class="btn-circle btn-ghost" :link="route('app.dashboard')" wire:navigate />
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-base-content tracking-tight">{{ __('System Templates') }}</h1>
                <p class="text-xs sm:text-sm text-base-content/60 mt-0.5">{{ __('Manage default daily report items available to all members.') }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <x-button
                :label="__('Add New Template')"
                icon="o-plus"
                wire:click="openModal"
                class="btn-primary btn-sm rounded-xl font-bold"
            />
        </div>
    </div>

    {{-- Templates List --}}
    <div class="bg-base-100 rounded-3xl border border-base-content/5 shadow-sm p-6 space-y-8">
        @if($templates->isEmpty())
            <div class="text-center py-12 text-base-content/50">
                <x-icon name="o-list-bullet" class="w-12 h-12 mx-auto mb-3 opacity-50" />
                <p>{{ __('No system templates defined yet.') }}</p>
            </div>
        @else
            @foreach($templates as $category => $items)
                <div class="space-y-4">
                    <h2 class="text-sm font-black text-base-content/50 uppercase tracking-widest bg-base-200/50 px-4 py-2 rounded-xl inline-block">{{ $category }}</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($items as $item)
                            <div class="flex items-center justify-between p-4 rounded-2xl bg-base-200/30 border border-base-content/5 transition-colors hover:bg-base-200/50">
                                <div>
                                    <p class="font-bold text-base-content">{{ $item->title }}</p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="badge badge-sm badge-ghost font-bold capitalize">{{ $item->type }}</span>
                                        <span class="text-xs text-base-content/50">{{ __('Sort:') }} {{ $item->sort_order }}</span>
                                    </div>
                                </div>
                                <div class="flex flex-col gap-1 shrink-0 ml-4">
                                    <x-button icon="o-pencil" wire:click="openModal({{ $item->id }})" class="btn-ghost btn-circle btn-xs text-primary" tooltip="{{ __('Edit') }}" />
                                    <x-button icon="o-trash" wire:click="deleteTemplate({{ $item->id }})" wire:confirm="{{ __('Are you sure you want to delete this template? Users who already have this item will retain it as a custom item.') }}" class="btn-ghost btn-circle btn-xs text-error" tooltip="{{ __('Delete') }}" />
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    {{-- Modal --}}
    <x-modal wire:model="showModal" :title="$editingId ? __('Edit Template') : __('Create Template')" class="backdrop-blur">
        <div class="space-y-4 pt-2">
            <x-input
                :label="__('Activity Title')"
                wire:model="title"
                :placeholder="__('e.g. Recite Surah Yaseen')"
                class="rounded-xl border-base-content/10"
            />
            
            <x-input
                :label="__('Category (Grouping Name)')"
                wire:model="category"
                :placeholder="__('e.g. Ibadah')"
                class="rounded-xl border-base-content/10"
            />

            <x-select
                :label="__('Input Type')"
                wire:model="type"
                :options="[
                    ['id' => 'boolean', 'name' => __('Checkbox (Done / Not Done)')],
                    ['id' => 'number', 'name' => __('Numeric Stepper (Amount / Pages)')],
                    ['id' => 'text', 'name' => __('Text Field (Notes)')],
                    ['id' => 'mixed', 'name' => __('Checkbox + Text Note')],
                ]"
                class="rounded-xl border-base-content/10"
            />

            <x-input
                type="number"
                :label="__('Sort Order')"
                wire:model="sort_order"
                class="rounded-xl border-base-content/10"
                hint="{{ __('Lower numbers appear first.') }}"
            />
        </div>

        <x-slot:actions>
            <x-button :label="__('Cancel')" wire:click="$set('showModal', false)" class="btn-ghost rounded-xl" />
            <x-button :label="__('Save Template')" wire:click="save" class="btn-primary rounded-xl font-bold" spinner="save" />
        </x-slot:actions>
    </x-modal>
</div>
