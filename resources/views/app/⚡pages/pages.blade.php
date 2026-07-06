<div x-data="page()" class="m-0 md:m-2">
    {{-- Header Section --}}
    <x-header :title="__('Pages')" :subtitle="__('Manage your website pages')" separator>
        <x-slot:middle class="!justify-end">
            <div class="flex items-center gap-2">
                {{-- Filter Toggle Button --}}
                <x-button
                    @click="filtersOpen = !filtersOpen"
                    icon="o-funnel"
                    class="btn-ghost btn-sm gap-2"
                    x-bind:class="{ 'btn-active': filtersOpen }">
                    {{ __('Filters') }}
                    @if($search || $itemStatus)
                        <x-badge value="{{ ($search ? 1 : 0) + ($itemStatus ? 1 : 0) }}" class="badge-primary badge-sm" />
                    @endif
                </x-button>

                @can('pages.create')
                    <x-button @click="toggleModal" icon="o-plus" class="btn-primary btn-sm">
                        <span class="hidden sm:inline">{{ __('Add New Page') }}</span>
                        <span class="sm:hidden">{{ __('Add') }}</span>
                    </x-button>
                @endcan
            </div>
        </x-slot:middle>
    </x-header>

    {{-- Filters Section (Collapsible) --}}
    <div x-show="filtersOpen"
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-4"
         class="mb-6">
        <x-card class="border-l-4 border-primary">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                    {{ __('Filter & Search') }}
                </h3>
                @if($search || $itemStatus)
                    <x-button
                        wire:click="$set('search', ''); $set('itemStatus', null)"
                        icon="o-x-mark"
                        class="btn-ghost btn-xs">
                        {{ __('Clear All') }}
                    </x-button>
                @endif
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="lg:col-span-2">
                    <x-input
                        wire:model.live.debounce.500ms="search"
                        :label="__('Search')"
                        :placeholder="__('Search pages...')"
                        icon="o-magnifying-glass"
                        clearable />
                </div>
                <div>
                    <x-select
                        wire:model.live="itemStatus"
                        :label="__('Status')"
                        :options="[
                            ['id' => null, 'name' => __('All Status')],
                            ['id' => 'draft', 'name' => __('Draft')],
                            ['id' => 'published', 'name' => __('Published')]
                        ]"
                        icon="o-funnel" />
                </div>
                <div>
                    <x-select
                        wire:model.live="itemPerPage"
                        :label="__('Per Page')"
                        :options="[
                            ['id' => 10, 'name' => '10'],
                            ['id' => 25, 'name' => '25'],
                            ['id' => 50, 'name' => '50'],
                            ['id' => 100, 'name' => '100']
                        ]"
                        icon="o-bars-3" />
                </div>
            </div>
        </x-card>
    </div>

    {{-- Bulk Actions Alert --}}
@if (count($selectedRows) > 0)
    <x-alert class="mb-6 alert-info shadow-sm rounded-lg" icon="o-information-circle">
        <div class="flex flex-wrap items-center justify-between gap-3 w-full">
            <span class="font-medium inline-flex items-center gap-2">
                {{ __(':count selected', ['count' => count($selectedRows)]) }}
                <x-badge value="{{ count($selectedRows) }}" class="badge-primary badge-sm" />
            </span>
            <div class="flex items-center gap-2">
                <x-button
                    @click="rows = []; selectPage = false"
                    wire:click="$set('selectedRows', [])"
                    icon="o-x-mark"
                    class="btn-ghost btn-sm">
                    {{ __('Clear Selection') }}
                </x-button>
                @can('pages.delete')
                    <x-button
                        @click="confirmDeleteMultiple({{ count($selectedRows) }})"
                        icon="o-trash"
                        class="btn-sm btn-error"
                        tooltip="{{ __('Delete Selected') }}">
                        {{ __('Delete Selected') }}
                    </x-button>
                @endcan
            </div>
        </div>
    </x-alert>
@endif
    {{-- Table Section --}}
    <x-card class="!p-0 !mx-0">

        <div class="overflow-x-auto ">
            <table class="table table-sm">
                <thead>
                    <tr class="text-center items-center font-semibold">
                        <th>
                            <x-checkbox
                                x-model="selectPage"
                                wire:model.live="selectPageRows" />
                        </th>
                      <x-field :OB="$orderBy" :OD="$orderDirection" :field="'title'">@lang('title')</x-field>
                      <x-field :OB="$orderBy" :OD="$orderDirection" :field="'slug'">@lang('slug')</x-field>
                      <x-field :OB="$orderBy" :OD="$orderDirection" :field="'status'">@lang('status')</x-field>
                      <x-field :OB="$orderBy" :OD="$orderDirection" :field="'updated_at'">@lang('last update')</x-field>
                      <x-field>@lang('action')</x-field>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->items as $item)
                        <tr wire:key="item-{{ $item->id }}" id="item-id-{{ $item->id }}" class="text-center items-center" :class="{'bg-base-300 rounded-md': rows.includes('{{$item->id}}') }">
                            <td>
                                <x-checkbox
                                    x-model="rows"
                                    value="{{ $item->id }}"
                                    wire:model.live="selectedRows" />
                            </td>
                            <td class="max-w-12 truncate">
                                <p class="font-medium table-sm">{{ $item->title }}</p>
                                @if($item->meta_title && $item->meta_title !== $item->title)
                                    <p class="text-xs w-24 opacity-60 ">{{ __('Meta') }}: {{ Str::limit($item->meta_title, 40) }}</p>
                                @endif
                            </td>
                            <td>
                                <code class="text-xs">{{ $item->slug }}</code>
                            </td>
                            <td>
                                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full {{ $item->status == 'published' ? 'bg-emerald-100/60 dark:bg-emerald-900/20' : 'bg-gray-100 dark:bg-gray-700' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $item->status == 'published' ? 'bg-emerald-500' : 'bg-gray-500' }}"></span>
                                    <button type="button"
                                            wire:click="changeStatus({{ $item->id }})"
                                            class="text-xs font-normal cursor-pointer {{ $item->status == 'published' ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-600 dark:text-gray-400' }}">
                                        {{ ucfirst($item->status) }}
                                    </button>
                                </div>
                            </td>
                            <td>
                                <div class="text-xs">{{ $item->created_at->format('M d, Y') }}</div>
                                <div class="text-xs opacity-60">{{ $item->created_at->diffForHumans() }}</div>
                            </td>
                            <td class="text-center items-center">
                                <div class="flex items-center justify-center gap-2">
                                  <x-button
                                    :link="route('web.page', $item->slug)"
                                    icon="o-eye"
                                    class="btn-ghost btn-sm"
                                    tooltip="{{ __('View') }}" />
                                    @can('pages.edit')
                                        <x-button
                                            @click="editModal({{ $item->id }})"
                                            icon="o-pencil"
                                            class="btn-ghost btn-sm"
                                            tooltip="{{ __('Edit') }}" />
                                    @endcan
                                    @can('pages.delete')
                                        <x-button
                                            @click="confirmDelete({{ $item->id }}, '{{ addslashes($item->title) }}')"
                                            icon="o-trash"
                                            class="btn-ghost btn-sm text-error"
                                            tooltip="{{ __('Delete') }}" />
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-base-content/60">
                                <x-icon name="o-document-text" class="w-12 h-12 mx-auto mb-2 opacity-20" />
                                <div>{{ __('No pages found') }}</div>
                                @if($search || $itemStatus)
                                    <x-button
                                        wire:click="$set('search', ''); $set('itemStatus', null)"
                                        class="mt-2 btn-sm btn-ghost">
                                        {{ __('Clear filters') }}
                                    </x-button>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $this->items->links() }}
        </div>
    </x-card>

    {{-- Modal (Alpine.js controlled) --}}
    <div x-show="isOpen"
         x-cloak
         @click.self="closeModal"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto bg-black/50 backdrop-blur-sm">
        <div @click.stop
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="w-full max-w-4xl bg-white rounded-lg shadow-xl dark:bg-gray-800">

            {{-- Modal Header --}}
            <div class="flex items-center justify-between p-6 border-b dark:border-gray-700">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                    <span x-show="!editMode">{{ __('Add New Page') }}</span>
                    <span x-show="editMode">{{ __('Edit Page') }}</span>
                </h3>
                <button @click="closeModal" type="button" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                    <x-icon name="o-x-mark" class="w-6 h-6" />
                </button>
            </div>

            {{-- Modal Body --}}
            <form @submit.prevent="editMode ? $wire.editData() : $wire.saveData()">
                <div class="p-6 space-y-6 max-h-[70vh] overflow-y-auto">

                    {{-- Basic Info --}}
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <x-input
                                wire:model.live.debounce.300ms="title"
                                :label="__('Title')"
                                icon="o-document-text"
                                :placeholder="__('Enter page title')"
                                required />
                        </div>

                        <div class="md:col-span-2">
                            <x-input
                                wire:model="slug"
                                :label="__('Slug')"
                                icon="o-link"
                                :placeholder="__('auto-generated-slug')"
                                :hint="__('Auto-generated from title')"
                                required />
                        </div>
                    </div>

                    {{-- Content Editor --}}

                      <x-editor wire:model.defer="content" :label="__('Description')" :hint="__('The full description')" :config="config('editor.default')" />

                    {{-- Settings --}}
                    <div class="grid gap-4 md:grid-cols-3">
                        <x-select
                            wire:model="status"
                            :label="__('Status')"
                            icon="o-check-circle"
                            :options="[
                                ['id' => 'draft', 'name' => __('Draft')],
                                ['id' => 'published', 'name' => __('Published')]
                            ]" />

                        <x-input
                            wire:model="order"
                            type="number"
                            :label="__('Order')"
                            icon="o-bars-3"
                            min="0"
                            :hint="__('Lower numbers appear first')" />

                        <x-input
                            wire:model="published_at"
                            type="datetime-local"
                            :label="__('Published At')"
                            icon="o-calendar" />
                    </div>

                    {{-- SEO Accordion --}}
                    <div x-data="{ seoOpen: true }" class="border rounded-lg dark:border-gray-700">
                        <button @click="seoOpen = !seoOpen" type="button"
                                class="flex items-center justify-between w-full p-4 text-left hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <div class="flex items-center gap-2">
                                <x-icon name="o-magnifying-glass" class="w-5 h-5" />
                                <span class="font-medium">{{ __('SEO Settings') }}</span>
                            </div>
                            <x-icon name="o-chevron-down" class="w-5 h-5 transition-transform"
                                    x-bind:class="{ 'rotate-180': seoOpen }" />
                        </button>

                        <div x-show="seoOpen"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 -translate-y-2"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-cloak
                             class="p-4 space-y-4 border-t dark:border-gray-700">
                            <x-input
                                wire:model="meta_title"
                                :label="__('Meta Title')"
                                :placeholder="__('Auto-filled from title')"
                                :hint="__('Recommended: 50-60 characters')" />

                            <x-textarea
                                wire:model="meta_description"
                                :label="__('Meta Description')"
                                rows="3"
                                :placeholder="__('Auto-filled from content')"
                                :hint="__('Recommended: 150-160 characters')" />

                            <x-input
                                wire:model="meta_keywords"
                                :label="__('Meta Keywords')"
                                :placeholder="__('keyword1, keyword2, keyword3')"
                                :hint="__('Comma-separated keywords')" />
                        </div>
                    </div>

                    {{-- Featured Image Accordion --}}
                    <div x-data="{ imageOpen: true }" class="border rounded-lg dark:border-gray-700">
                        <button @click="imageOpen = !imageOpen" type="button"
                                class="flex items-center justify-between w-full p-4 text-left hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <div class="flex items-center gap-2">
                                <x-icon name="o-photo" class="w-5 h-5" />
                                <span class="font-medium">{{ __('Featured Image') }}</span>
                            </div>
                            <x-icon name="o-chevron-down" class="w-5 h-5 transition-transform"
                                    x-bind:class="{ 'rotate-180': imageOpen }" />
                        </button>

                        <div x-show="imageOpen"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 -translate-y-2"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-cloak
                             class="p-4 space-y-4 border-t dark:border-gray-700">

                            <x-input
                                wire:model="image_url"
                                type="url"
                                :label="__('Image URL')"
                                icon="o-link"
                                :placeholder="__('https://example.com/image.jpg')" />

                            <div class="text-sm text-center text-gray-500">{{ __('OR') }}</div>

                            <x-avatar-upload
                                :label="__('Featured Image')" model="photo"
                                :image="$photo ? $photo->temporaryUrl() : getImage($page, 'featured_image', 'thumb')"
                                :hint="__('PNG, JPG, WEBP up to 2MB')"
                            />
                        </div>
                    </div>
                </div>
{{--              @if($page && $page->exists )--}}

{{--                @dd($page->getFirstMediaUrl('featured_image', 'thumb'))--}}
{{--              @endif--}}
                {{-- Modal Footer --}}
                <div class="flex items-center gap-3 p-6 border-t dark:border-gray-700">
                    <x-button
                        wire:loading.remove
                        wire:target="editData,saveData"
                        type="submit"
                        class="btn-primary"
                        icon="o-check">
                        <span x-show="!editMode">{{ __('Create Page') }}</span>
                        <span x-show="editMode">{{ __('Update Page') }}</span>
                    </x-button>
                    <x-button
                        wire:loading
                        wire:target="editData,saveData"
                        type="button"
                        disabled
                        class="btn-primary">
                        <span class="loading loading-spinner"></span>
                        {{ __('Processing...') }}
                    </x-button>
                    <x-button
                        @click="closeModal"
                        type="button"
                        class="btn-ghost"
                        icon="o-x-mark">
                        {{ __('Cancel') }}
                    </x-button>
                </div>
            </form>
        </div>
    </div>

    {{-- Confirmation Dialog Modal --}}
    <div x-show="confirmOpen"
         x-cloak
         @click.self="closeConfirm"
         class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div @click.stop
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="w-full max-w-md bg-white rounded-lg shadow-xl dark:bg-gray-800">

            {{-- Modal Header --}}
            <div class="flex items-center gap-3 p-6 border-b dark:border-gray-700">
                <div class="flex items-center justify-center w-12 h-12 rounded-full"
                     :class="{
                         'bg-red-100 dark:bg-red-900/20': confirmType === 'danger',
                         'bg-yellow-100 dark:bg-yellow-900/20': confirmType === 'warning',
                         'bg-blue-100 dark:bg-blue-900/20': confirmType === 'info'
                     }">
                    <x-icon name="o-exclamation-triangle"
                            class="w-6 h-6"
                            x-bind:class="{
                                'text-red-600 dark:text-red-400': confirmType === 'danger',
                                'text-yellow-600 dark:text-yellow-400': confirmType === 'warning',
                                'text-blue-600 dark:text-blue-400': confirmType === 'info'
                            }" />
                </div>
                <h3 class="flex-1 text-lg font-semibold text-gray-900 dark:text-white" x-text="confirmTitle"></h3>
                <button @click="closeConfirm"
                        type="button"
                        class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                    <x-icon name="o-x-mark" class="w-6 h-6" />
                </button>
            </div>

            {{-- Modal Body --}}
            <div class="p-6">
                <p class="text-sm text-gray-600 dark:text-gray-400" x-text="confirmMessage"></p>
            </div>

            {{-- Modal Footer --}}
            <div class="flex items-center justify-end gap-3 p-6 border-t dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                <x-button
                    @click="closeConfirm"
                    type="button"
                    class="btn-ghost">
                    {{ __('Cancel') }}
                </x-button>
                <x-button
                    @click="executeConfirm"
                    type="button"
                    class="gap-2"
                    x-bind:class="{
                        'btn-error': confirmType === 'danger',
                        'btn-warning': confirmType === 'warning',
                        'btn-info': confirmType === 'info'
                    }">
                    <x-icon name="o-trash" class="w-4 h-4" />
                    <span x-show="confirmType === 'danger'">{{ __('Delete') }}</span>
                    <span x-show="confirmType === 'warning'">{{ __('Proceed') }}</span>
                    <span x-show="confirmType === 'info'">{{ __('Confirm') }}</span>
                </x-button>
            </div>
        </div>
    </div>


@script
<script>
    Alpine.data('page', () => ({
        isOpen: false,
        editMode: false,
        selectPage: false,
        rows: [],
        filtersOpen: false,

        // Confirmation modal
        confirmOpen: false,
        confirmTitle: '',
        confirmMessage: '',
        confirmAction: null,
        confirmType: 'danger', // danger, warning, info

        init() {
            // Listen for dataAdded event
            $wire.on('dataAdded', (e) => {
                this.isOpen = false;
                this.editMode = false;

                $nextTick(() => {
                    const element = document.getElementById(e.dataId);
                    if (element) {
                        element.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        element.classList.add('animate-pulse');

                        setTimeout(() => {
                            element.classList.remove('animate-pulse');
                        }, 5000);
                    }
                });
            });
        },

        toggleModal() {
            this.isOpen = !this.isOpen;
            if (!this.isOpen) {
                this.editMode = false;
                $wire.resetData();
            }
        },

        closeModal() {
            this.isOpen = false;
            this.editMode = false;
            $wire.resetData();
        },

        editModal(id) {
            $wire.loadData(id);
            this.isOpen = true;
            this.editMode = true;
        },

        // Confirmation modal methods
        confirmDelete(id, title = 'Delete Page') {
            this.confirmTitle = '{{ __("Confirm Deletion") }}';
            this.confirmMessage = `{{ __("Are you sure you want to delete") }} "${title}"? {{ __("This action cannot be undone.") }}`;
            this.confirmType = 'danger';
            this.confirmAction = () => {
                $wire.deleteSingle(id);
                this.closeConfirm();
            };
            this.confirmOpen = true;
        },

        confirmDeleteMultiple(count) {
            this.confirmTitle = '{{ __("Confirm Bulk Deletion") }}';
            this.confirmMessage = `{{ __("Are you sure you want to delete") }} ${count} {{ __("selected pages?") }} {{ __("This action cannot be undone.") }}`;
            this.confirmType = 'danger';
            this.confirmAction = () => {
                $wire.deleteMultiple();
                this.closeConfirm();
            };
            this.confirmOpen = true;
        },

        executeConfirm() {
            if (this.confirmAction) {
                this.confirmAction();
            }
        },

        closeConfirm() {
            this.confirmOpen = false;
            this.confirmTitle = '';
            this.confirmMessage = '';
            this.confirmAction = null;
            this.confirmType = 'danger';
        }
    }));
</script>
@endscript
</div>
