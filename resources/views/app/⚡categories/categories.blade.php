<div class="space-y-6">
  <x-header :title="__('Categories')" :subtitle="__('Manage categories with hierarchical structure. Create, edit, and organize categories for your content.')" separator />

  <x-card>
    <div class="grid md:grid-cols-5 gap-3 mb-4">
      <div class="md:col-span-2">
        <x-input wire:model.live.debounce.400ms="search" :label="__('Search')" icon="o-magnifying-glass" :placeholder="__('Name or slug...')" />
      </div>
      <div>
        <x-select :label="__('Status')" wire:model.live="statusFilter" :options="[
          ['id' => null, 'name' => __('All statuses')],
          ['id' => 'active', 'name' => __('Active')],
          ['id' => 'inactive', 'name' => __('Inactive')],
        ]" />
      </div>
      <div>
        <x-select :label="__('Parent Category')" wire:model.live="parentFilter" :options="array_merge(
          [['id' => null, 'name' => __('All categories')]],
          [['id' => 0, 'name' => __('Root categories only')]],
          $this->rootCategories
        )" />
      </div>
      <div class="flex items-end justify-end">
        @can('categories.create')
          <x-button class="btn-primary" icon="o-plus" wire:click="create">{{ __('New Category') }}</x-button>
        @endcan
      </div>
    </div>

    <div class="flex items-center justify-between mb-2">
      <div class="text-sm opacity-70">{{ __('Sort') }}: <span class="font-medium">{{ $sortField }}</span> <span class="badge badge-ghost">{{ strtoupper($sortDirection) }}</span></div>
      <x-select :label="__('Per page')" wire:model.live="perPage" :options="[['id' => 10, 'name' => '10'], ['id' => 25, 'name' => '25'], ['id' => 50, 'name' => '50']]" />
    </div>

    <div class="overflow-x-auto">
      <table class="table w-full">
        <thead>
          <tr>
            <th class="cursor-pointer" wire:click="sortBy('name')">{{ __('Name') }}</th>
            <th class="cursor-pointer" wire:click="sortBy('slug')">{{ __('Slug') }}</th>
            <th>{{ __('Parent') }}</th>
            <th class="text-center">{{ __('Subcategories') }}</th>
            <th class="text-center">{{ __('Status') }}</th>
            <th class="text-right">{{ __('Actions') }}</th>
          </tr>
        </thead>
        <tbody>
          @forelse($this->categories as $category)
            <tr>
              <td class="font-medium">
                @if($category->parent_id)
                  <span class="text-base-content/40 mr-1">↳</span>
                @endif
                {{ $category->name }}
              </td>
              <td class="text-base-content/80 text-sm font-mono">{{ $category->slug }}</td>
              <td class="text-base-content/70">
                @if($category->parent)
                  <span class="badge badge-ghost badge-sm">{{ $category->parent->name }}</span>
                @else
                  <span class="text-base-content/40 text-sm">—</span>
                @endif
              </td>
              <td class="text-center">
                @if($category->children_count > 0)
                  <span class="badge badge-primary badge-sm">{{ $category->children_count }}</span>
                @else
                  <span class="text-base-content/40 text-sm">0</span>
                @endif
              </td>
              <td class="text-center">
                <div x-data="{ isActive: {{ $category->is_active ? 'true' : 'false' }} }">
                  <button
                    @click="isActive = !isActive"
                    wire:click="toggleStatus({{ $category->id }})"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center"
                    title="{{ $category->is_active ? __('Click to deactivate') : __('Click to activate') }}"
                  >
                    <span
                      class="w-10 h-6 rounded-full p-1 transition-colors duration-200 ease-in-out"
                      :class="isActive ? 'bg-success' : 'bg-base-300'"
                    >
                      <span
                        class="block w-4 h-4 rounded-full bg-white shadow transition-transform duration-200 ease-in-out"
                        :class="isActive ? 'translate-x-4' : 'translate-x-0'"
                      ></span>
                    </span>
                  </button>
                  <span class="text-xs ml-2" :class="isActive ? 'text-success' : 'text-base-content/50'" x-text="isActive ? '{{ __('Active') }}' : '{{ __('Inactive') }}'"></span>
                </div>
              </td>
              <td class="text-right space-x-1">
                @can('categories.update')
                  <x-button class="btn-ghost btn-sm" icon="o-pencil-square" wire:click="edit({{ $category->id }})">{{ __('Edit') }}</x-button>
                @endcan
                @can('categories.delete')
                  <x-button class="btn-ghost btn-sm text-error" icon="o-trash" wire:click="confirmDelete({{ $category->id }})" :disabled="$category->children_count > 0">{{ __('Delete') }}</x-button>
                @endcan
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="text-center text-base-content/60 py-8">
                <div class="flex flex-col items-center gap-2">
                  <x-icon name="o-folder-open" class="w-12 h-12 opacity-30" />
                  <p>{{ __('No categories found.') }}</p>
                  @can('categories.create')
                    <x-button class="btn-primary btn-sm mt-2" icon="o-plus" wire:click="create">{{ __('Create your first category') }}</x-button>
                  @endcan
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-4">{{ $this->categories->onEachSide(1)->links() }}</div>
  </x-card>

  <!-- Create/Edit Modal -->
  <x-modal wire:model="showForm" :title="$isEditing ? __('Edit Category') : __('New Category')" :subtitle="$isEditing ? __('Update the category details.') : __('Create a new category to organize your content.')">
    <div class="space-y-4">
      <!-- Name Input with Auto-slug -->
      <div x-data="{ name: @entangle('name'), slug: @entangle('slug'), isEditing: @entangle('isEditing') }" x-init="$watch('name', value => { if (!isEditing || slug === '') slug = value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '') })">
        <x-input :label="__('Name')" wire:model.defer="name" required :placeholder="__('Category name')" />
        
        <div class="mt-2 flex items-center gap-2 text-sm">
          <span class="text-base-content/60">{{ __('Slug preview:') }}</span>
          <code class="bg-base-200 px-2 py-1 rounded text-xs" x-text="slug || '{{ __('auto-generated') }}'"></code>
        </div>
      </div>

      <!-- Slug Input -->
      <x-input :label="__('Slug')" wire:model.defer="slug" required :placeholder="__('category-slug')" :hint="__('Used in URLs. Auto-generated from name if empty.')" />

      <!-- Parent Category -->
      <x-select 
        :label="__('Parent Category (Optional)')" 
        wire:model.defer="parent_id" 
        :options="array_merge(
          [['id' => null, 'name' => __('None - Root Category')]],
          collect($this->parentCategoryOptions)
            ->reject(fn($opt) => $isEditing && $opt['id'] === $selectedId)
            ->values()
            ->toArray()
        )"
        :hint="__('Select a parent to create a subcategory.')"
      />

      <!-- Active Toggle -->
      <div x-data="{ isActive: @entangle('is_active') }" class="flex items-center gap-4 p-4 bg-base-200 rounded-lg">
        <div class="flex-1">
          <div class="font-medium">{{ __('Active Status') }}</div>
          <div class="text-sm text-base-content/60">{{ __('Inactive categories are hidden from public view.') }}</div>
        </div>
        <button
          @click="isActive = !isActive"
          type="button"
          class="inline-flex items-center"
        >
          <span
            class="w-12 h-7 rounded-full p-1 transition-colors duration-200 ease-in-out"
            :class="isActive ? 'bg-success' : 'bg-base-300'"
          >
            <span
              class="block w-5 h-5 rounded-full bg-white shadow transition-transform duration-200 ease-in-out"
              :class="isActive ? 'translate-x-5' : 'translate-x-0'"
            ></span>
          </span>
        </button>
        <input type="hidden" wire:model.defer="is_active" :value="isActive ? 1 : 0">
        <span class="text-sm font-medium w-16 text-right" :class="isActive ? 'text-success' : 'text-base-content/50'" x-text="isActive ? '{{ __('Active') }}' : '{{ __('Inactive') }}'"></span>
      </div>
    </div>

    <x-slot:actions>
      <x-button class="btn-ghost" icon="o-x-mark" wire:click="$set('showForm', false)">{{ __('Cancel') }}</x-button>
      <x-button class="btn-primary" icon="o-check" wire:click="save" spinner="save">{{ $isEditing ? __('Update') : __('Create') }}</x-button>
    </x-slot:actions>
  </x-modal>

  <!-- Delete confirm modal -->
  <x-modal wire:model="confirmingDeleteId" :title="__('Delete Category')" :subtitle="__('This action cannot be undone.')">
    <div class="space-y-2">
      <p>{{ __('Are you sure you want to delete this category?') }}</p>
      <p class="text-sm text-base-content/60">{{ __('Categories with subcategories cannot be deleted.') }}</p>
    </div>
    <x-slot:actions>
      <x-button class="btn-ghost" wire:click="$set('confirmingDeleteId', null)" icon="o-x-mark">{{ __('Cancel') }}</x-button>
      <x-button class="btn-error" wire:click="deleteConfirmed" icon="o-trash">{{ __('Delete') }}</x-button>
    </x-slot:actions>
  </x-modal>
</div>
