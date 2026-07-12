<div class="max-w-7xl mx-auto py-6 space-y-8">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-base-100 p-6 rounded-3xl border border-base-content/5 shadow-sm">
        <div class="flex items-center gap-4">
            <x-button icon="o-arrow-left" class="btn-circle btn-ghost" :link="route('app.dashboard')" wire:navigate />
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-base-content tracking-tight">{{ __('Halaqah Programs & Series') }}</h1>
                <p class="text-xs sm:text-sm text-base-content/60 mt-0.5">{{ __('Create and manage long-running spiritual development curriculums.') }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <x-button
                :label="__('Create Series')"
                icon="o-plus"
                wire:click="openModal"
                class="btn-primary btn-sm rounded-xl font-bold"
            />
        </div>
    </div>

    {{-- Filter controls --}}
    <div class="bg-base-100 p-4 rounded-2xl border border-base-content/5 shadow-sm flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-4">
        <x-input
            wire:model.live.debounce.300ms="search"
            :placeholder="__('Search series by title or description...')"
            icon="o-magnifying-glass"
            class="input-sm rounded-xl border-base-content/10 w-full sm:w-80"
        />
    </div>

    {{-- List of Series --}}
    <div class="bg-base-100 rounded-3xl border border-base-content/5 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead>
                    <tr class="bg-base-200/50 text-xs font-bold text-base-content/60 uppercase">
                        <th>{{ __('Program / Title') }}</th>
                        <th>{{ __('Mentor') }}</th>
                        <th>{{ __('Target Level') }}</th>
                        <th>{{ __('Sessions') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-base-content/5">
                    @forelse($series as $item)
                        <tr class="hover:bg-base-200/30 transition-colors">
                            <td>
                                <div class="flex items-center gap-3">
                                    @if($item->banner_path)
                                        <div class="w-12 h-12 rounded-xl overflow-hidden shrink-0 border border-base-content/10">
                                            <img src="{{ Storage::url($item->banner_path) }}" alt="{{ $item->title }}" class="object-cover w-full h-full" />
                                        </div>
                                    @else
                                        <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center text-primary shrink-0">
                                            <x-icon name="o-academic-cap" class="w-6 h-6" />
                                        </div>
                                    @endif
                                    <div>
                                        <div class="font-bold text-sm text-base-content">{{ $item->title }}</div>
                                        <div class="text-xs text-base-content/50 line-clamp-1 max-w-sm">{{ $item->description }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="text-sm font-semibold text-base-content/85">{{ $item->mentor?->name ?? __('Unassigned') }}</span>
                            </td>
                            <td>
                                <span class="badge badge-sm font-bold capitalize badge-ghost">{{ $item->target_audience_level }}</span>
                            </td>
                            <td class="text-sm font-semibold text-base-content/75">
                                {{ $item->halaqahs_count }} {{ __('sessions') }}
                            </td>
                            <td>
                                @if($item->status === 'active')
                                    <span class="badge badge-success badge-sm font-bold">{{ __('Active') }}</span>
                                @elseif($item->status === 'draft')
                                    <span class="badge badge-warning badge-sm font-bold">{{ __('Draft') }}</span>
                                @elseif($item->status === 'completed')
                                    <span class="badge badge-info badge-sm font-bold">{{ __('Completed') }}</span>
                                @else
                                    <span class="badge badge-error badge-sm font-bold">{{ __('Cancelled') }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="flex items-center justify-end gap-1">
                                    <x-button
                                        icon="o-eye"
                                        :link="route('app.halaqah-series.show', ['series' => $item->id])"
                                        class="btn-ghost btn-circle btn-sm text-indigo-500"
                                        tooltip="{{ __('Manage details') }}"
                                        wire:navigate
                                    />
                                    <x-button
                                        icon="o-pencil"
                                        wire:click="openModal({{ $item->id }})"
                                        class="btn-ghost btn-circle btn-sm text-primary"
                                        tooltip="{{ __('Edit') }}"
                                    />
                                    <x-button
                                        icon="o-trash"
                                        wire:click="deleteSeries({{ $item->id }})"
                                        wire:confirm="{{ __('Are you sure you want to delete this program series? All associated halaqah sessions will have their series pointer cleared.') }}"
                                        class="btn-ghost btn-circle btn-sm text-error"
                                        tooltip="{{ __('Delete') }}"
                                    />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 text-base-content/50 text-sm">
                                {{ __('No halaqah series programs found.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-base-content/5">
            {{ $series->links() }}
        </div>
    </div>

    {{-- Form Modal --}}
    <x-modal wire:model="showModal" :title="$editingId ? __('Edit Halaqah Series') : __('Create Halaqah Series')" class="backdrop-blur">
        <div class="space-y-4 pt-2">
            <x-input
                :label="__('Program Title')"
                wire:model="title"
                :placeholder="__('e.g. Journey of the Soul (Tafsir of Surah Yaseen)')"
                class="rounded-xl border-base-content/10"
            />

            <x-textarea
                :label="__('Description & Curriculum Outline')"
                wire:model="description"
                rows="4"
                :placeholder="__('Briefly describe what this series aims to cover...')"
                class="rounded-xl border-base-content/10"
            />

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-select
                    :label="__('Mentor / Lead Instructor')"
                    wire:model="mentor_id"
                    :options="$mentors"
                    placeholder="{{ __('Select Mentor') }}"
                    class="rounded-xl border-base-content/10"
                />

                <x-select
                    :label="__('Target Level')"
                    wire:model="target_audience_level"
                    :options="$levelOptions"
                    class="rounded-xl border-base-content/10"
                />
            </div>

            <x-select
                :label="__('Program Status')"
                wire:model="status"
                :options="$statusOptions"
                class="rounded-xl border-base-content/10"
            />

            <div class="space-y-2">
                <label class="label text-xs font-bold text-base-content/70">{{ __('Banner / Cover Image') }}</label>
                @if($existing_banner_path && !$banner_file)
                    <div class="w-full h-32 rounded-xl overflow-hidden border mb-2">
                        <img src="{{ Storage::url($existing_banner_path) }}" class="w-full h-full object-cover" />
                    </div>
                @endif
                <x-file
                    wire:model="banner_file"
                    accept="image/*"
                    :label="__('Upload New Banner')"
                    hint="{{ __('Aspect ratio: Landscape, Max 2MB') }}"
                />
            </div>
        </div>

        <x-slot:actions>
            <x-button :label="__('Cancel')" wire:click="$set('showModal', false)" class="btn-ghost rounded-xl" />
            <x-button :label="__('Save Series')" wire:click="save" class="btn-primary rounded-xl font-bold" spinner="save" />
        </x-slot:actions>
    </x-modal>
</div>
