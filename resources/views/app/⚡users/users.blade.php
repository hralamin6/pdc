<div class="space-y-6">
  <x-header :title="__('Users')" :subtitle="__('Manage users, search, sort, and assign roles.')" separator />

  <x-card>
    <div class="grid md:grid-cols-4 gap-3 mb-4">
      <div class="md:col-span-2">
        <x-input wire:model.debounce.400ms="search" :label="__('Search')" icon="o-magnifying-glass" :placeholder="__('Name or email...')" />
      </div>
      <div>
        <x-select :label="__('Filter by role')" wire:model.live="roleFilter" :options="array_merge([[ 'id' => null, 'name' => __('All roles') ]], $this->roleOptions)" />
      </div>
      <div class="flex items-end justify-end">
        @can('users.create')
          <x-button class="btn-primary" icon="o-plus" wire:click="create">{{ __('New User') }}</x-button>
        @endcan
      </div>
    </div>

    <div class="flex items-center justify-between mb-2">
      <div class="text-sm opacity-70">{{ __('Sort') }}: <span class="font-medium">{{ $sortField }}</span> <span class="badge badge-ghost">{{ strtoupper($sortDirection) }}</span></div>
      <x-select :label="__('Per page')" wire:model.live="perPage" :options="[[ 'id' => 10, 'name' => '10' ], [ 'id' => 25, 'name' => '25' ], [ 'id' => 50, 'name' => '50' ]]" />
    </div>

    <div class="overflow-x-auto">
      <table class="table w-full">
        <thead>
          <tr>
            <th class="cursor-pointer" wire:click="sortBy('name')">{{ __('Name') }}</th>
            <th class="cursor-pointer" wire:click="sortBy('email')">{{ __('Email') }}</th>
            <th>{{ __('Roles') }}</th>
            <th class="cursor-pointer" wire:click="sortBy('created_at')">{{ __('Created') }}</th>
            @canany(['users.update', 'users.delete'])
              <th class="text-right">{{ __('Actions') }}</th>
            @endcanany
          </tr>
        </thead>
        <tbody>
          @forelse($this->users as $u)
            <tr>
              <td class="font-medium">{{ $u->name }}</td>
              <td class="text-base-content/80">{{ $u->email }}</td>
              <td class="space-x-1">
                @forelse($u->roles as $r)
                  <span class="badge badge-outline">{{ $r->name }}</span>
                @empty
                  <span class="text-xs opacity-60">—</span>
                @endforelse
              </td>
              <td class="text-sm text-base-content/70">{{ optional($u->created_at)->diffForHumans() }}</td>
              @canany(['users.update', 'users.delete'])
                <td class="text-right space-x-1">
                  @can('users.update')
                    <x-button class="btn-ghost btn-sm" icon="o-pencil-square" wire:click="edit({{ $u->id }})">{{ __('Edit') }}</x-button>
                  @endcan
                  @can('users.delete')
                    <x-button class="btn-ghost btn-sm text-error" icon="o-trash" wire:click="confirmDelete({{ $u->id }})">{{ __('Delete') }}</x-button>
                  @endcan
                </td>
              @endcanany
            </tr>
          @empty
            <tr><td colspan="5" class="text-center text-base-content/60 py-6">{{ __('No users found.') }}</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-4">{{ $this->users->onEachSide(1)->links() }}</div>
  </x-card>

  <!-- Create/Edit Modal -->
  <x-modal wire:model="showForm" :title="$isEditing ? __('Edit User') : __('New User')" :subtitle="__('Create or update a user and assign roles.')">
    <div class="space-y-4">
      <div class="grid md:grid-cols-2 gap-4">
        <x-input :label="__('Name')" wire:model.defer="name" />
        <x-input :label="__('Email')" wire:model.defer="email" type="email" />
      </div>
      <div class="grid md:grid-cols-2 gap-4">
        <x-input :label="__('Password')" wire:model.defer="password" type="password" :placeholder="$isEditing ? __('Leave blank to keep current') : ''" />
        <x-input :label="__('Confirm Password')" wire:model.defer="password_confirmation" type="password" />
      </div>
      @can('users.assign-roles')
        <div>
          <x-choices-offline
          :label="__('Roles')"
          wire:model="selectedRoles"
          :options="$this->allRoles"
          :placeholder="__('Search ...')"
          clearable
          searchable />
        </div>
      @endcan
    </div>
    <x-slot:actions>
      <x-button class="btn-ghost" icon="o-x-mark" wire:click="$set('showForm', false)">{{ __('Cancel') }}</x-button>
      <x-button class="btn-primary" icon="o-check" wire:click="save" spinner="save">{{ __('Save') }}</x-button>
    </x-slot:actions>
  </x-modal>

  <!-- Delete confirm modal -->
  <x-modal wire:model="confirmingDeleteId" :title="__('Delete user')" :subtitle="__('This action cannot be undone.')">
    <div class="space-y-2">
      <p>{{ __('Are you sure you want to delete this user?') }}</p>
    </div>
    <x-slot:actions>
      <x-button class="btn-ghost" wire:click="$set('confirmingDeleteId', null)" icon="o-x-mark">{{ __('Cancel') }}</x-button>
      <x-button class="btn-error" wire:click="deleteConfirmed" icon="o-trash">{{ __('Delete') }}</x-button>
    </x-slot:actions>
  </x-modal>
</div>
