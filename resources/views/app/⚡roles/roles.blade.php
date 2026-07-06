<div class="space-y-6">
  <x-header title="Roles & Permissions" subtitle="Manage roles, assign permissions, and keep access under control." separator />

  <x-card>
    <div class="flex flex-col md:flex-row md:items-end gap-3 mb-4">
      <div class="flex-1">
        <x-input wire:model.debounce.400ms="search" label="Search roles" icon="o-magnifying-glass" placeholder="Search by name..." />
      </div>
      <div>
        <x-select label="Per page" wire:model.live="perPage" :options="[[ 'id' => 10, 'name' => '10' ], [ 'id' => 25, 'name' => '25' ], [ 'id' => 50, 'name' => '50' ]]" />
      </div>
      <div>
        @can('roles.create')
          <x-button class="btn-primary" icon="o-plus" wire:click="create">New Role</x-button>
        @endcan
      </div>
    </div>

    <div class="overflow-x-auto">
      <table class="table w-full">
        <thead>
          <tr>
            <th class="whitespace-nowrap">Name</th>
            <th class="whitespace-nowrap">Guard</th>
            <th class="whitespace-nowrap">Permissions</th>
            @canany(['roles.update', 'roles.delete'])
              <th class="whitespace-nowrap text-right">Actions</th>
            @endcanany
          </tr>
        </thead>
        <tbody>
        @forelse($this->roles as $role)
          <tr>
            <td class="font-medium">{{ $role->name }}</td>
            <td><span class="badge badge-ghost">{{ $role->guard_name }}</span></td>
            <td>
              <span class="badge badge-outline">{{ $role->permissions_count }}</span>
            </td>
            @canany(['roles.update', 'roles.delete'])
              <td class="text-right space-x-1">
                @can('roles.update')
                  <x-button class="btn-ghost btn-sm" icon="o-pencil-square" wire:click="edit({{ $role->id }})">Edit</x-button>
                @endcan
                @can('roles.delete')
                  <x-button class="btn-ghost btn-sm text-error" icon="o-trash" wire:click="confirmDelete({{ $role->id }})">Delete</x-button>
                @endcan
              </td>
            @endcanany
          </tr>
        @empty
          <tr>
            <td colspan="4" class="text-center text-base-content/60 py-6">No roles found.</td>
          </tr>
        @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-4">{{ $this->roles->onEachSide(1)->links() }}</div>
  </x-card>

  <!-- Create/Edit Modal -->
  <x-modal wire:model="showForm" title="{{ $isEditing ? 'Edit Role' : 'New Role' }}" subtitle="Define role name and assign permissions.">
    <div class="space-y-4">
      <div class="grid md:grid-cols-2 gap-4">
        <x-input label="Role name" wire:model.defer="name" placeholder="e.g., manager" />
        <x-input label="Guard" wire:model.defer="guard_name" disabled />
      </div>

      @can('roles.assign-permissions')
        <x-hr />
        <div class="space-y-3">
          <x-header title="Permissions" level="3" class="mb-2" />
          @php($assigned = collect($selectedPermissions))
          <div class="grid md:grid-cols-2 gap-4">
            @forelse($this->permissions as $group => $items)
              <x-card class="bg-base-100">
                <div class="flex items-center justify-between mb-2">
                  <div class="font-semibold capitalize">{{ str_replace(['-', '_'], ' ', $group) }}</div>
                  <div class="space-x-2">
                    <x-button class="btn-xs btn-ghost" wire:click="$set('selectedPermissions', array_values(array_unique(array_merge((array)$selectedPermissions, \Spatie\\Permission\\Models\\Permission::where('guard_name', $guard_name)->whereIn('name', (array) $items)->pluck('name')->toArray()))))">All</x-button>
                  </div>
                </div>
                <div class="space-y-1">
                  @foreach($items as $perm)
                    <label class="flex items-center gap-2 cursor-pointer">
                      <input type="checkbox" class="checkbox checkbox-sm" value="{{ $perm }}" wire:model.live="selectedPermissions">
                      <span class="text-sm">{{ $perm }}</span>
                    </label>
                  @endforeach
                </div>
              </x-card>
            @empty
              <div class="col-span-2 text-sm text-base-content/60">No permissions defined. Seed or create permissions first.</div>
            @endforelse
          </div>
        </div>
      @endcan
    </div>

    <x-slot:actions>
      <x-button class="btn-ghost" wire:click="$set('showForm', false)" icon="o-x-mark">Cancel</x-button>
      <x-button class="btn-primary" wire:click="save" spinner="save" icon="o-check">Save</x-button>
    </x-slot:actions>
  </x-modal>

  <!-- Delete confirm modal -->
  <x-modal wire:model="confirmingDeleteId" title="Delete role" subtitle="This action cannot be undone.">
    <div class="space-y-2">
      <p>Are you sure you want to delete this role?</p>
    </div>
    <x-slot:actions>
      <x-button class="btn-ghost" wire:click="$set('confirmingDeleteId', null)" icon="o-x-mark">Cancel</x-button>
      <x-button class="btn-error" wire:click="deleteConfirmed" icon="o-trash">Delete</x-button>
    </x-slot:actions>
  </x-modal>
</div>
