<?php

use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role as SpatieRole;

new
#[Title('Roles')]
#[Layout('layouts.app')]
class extends Component
{
    use Toast;
    use WithPagination;

    // UI state
    public string $search = '';

    public int $perPage = 10;

    // Role form state
    public ?int $selectedRoleId = null;

    public string $name = '';

    public string $guard_name = 'web';

    // Permissions assigned to current role
    public array $selectedPermissions = [];

    // Modals / confirmations
    public bool $showForm = false;

    public bool $isEditing = false;

    public ?int $confirmingDeleteId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
    ];

    // Validation rules for role
    public function rules(): array
    {
        return [
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('roles', 'name')
                    ->where(fn ($q) => $q->where('guard_name', $this->guard_name))
                    ->ignore($this->selectedRoleId),
            ],
            'guard_name' => ['required', 'string', Rule::in(['web'])],
            'selectedPermissions' => ['array'],
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function mount(): void
    {
        $this->authorize('roles.view');
    }

    public function create(): void
    {
        $this->authorize('roles.create');

        $this->reset(['selectedRoleId', 'name', 'selectedPermissions']);
        $this->guard_name = 'web';
        $this->isEditing = false;
        $this->showForm = true;
    }

    public function edit(int $roleId): void
    {
        $this->authorize('roles.update');

        $role = SpatieRole::query()->findOrFail($roleId);
        $this->selectedRoleId = $role->id;
        $this->name = (string) $role->name;
        $this->guard_name = (string) $role->guard_name;
        $this->selectedPermissions = $role->permissions()->pluck('name')->values()->all();
        $this->isEditing = true;
        $this->showForm = true;
    }

    public function save(): void
    {
        if ($this->isEditing) {
            $this->authorize('roles.update');
        } else {
            $this->authorize('roles.create');
        }

        $this->validate();

        if ($this->isEditing && $this->selectedRoleId) {
            $role = SpatieRole::findOrFail($this->selectedRoleId);

            // Prevent renaming protected roles if desired
            if (in_array($role->name, ['super-admin'], true) && $role->name !== $this->name) {
                $this->error(__('This role is protected and cannot be renamed.'));

                return;
            }

            $role->update([
                'name' => $this->name,
                'guard_name' => $this->guard_name,
            ]);
        } else {
            $role = SpatieRole::create([
                'name' => $this->name,
                'guard_name' => $this->guard_name,
            ]);
            $this->selectedRoleId = $role->id;
        }

        // Sync permissions
        if (auth()->user()->can('roles.assign-permissions')) {
            $permissions = Permission::query()
                ->whereIn('name', $this->selectedPermissions)
                ->where('guard_name', $this->guard_name)
                ->get();
            $role->syncPermissions($permissions);
        }

        $this->success(__('Role saved successfully.'), position: 'toast-bottom');
        $this->showForm = false;
        $this->reset(['name', 'selectedPermissions', 'selectedRoleId', 'isEditing']);
        $this->resetPage();
    }

    public function confirmDelete(int $roleId): void
    {
        $this->confirmingDeleteId = $roleId;
    }

    public function deleteConfirmed(): void
    {
        $this->authorize('roles.delete');

        if (! $this->confirmingDeleteId) {
            return;
        }

        $role = SpatieRole::findOrFail($this->confirmingDeleteId);

        if (in_array($role->name, ['super-admin'], true)) {
            $this->error(__('This role is protected and cannot be deleted.'));
            $this->confirmingDeleteId = null;

            return;
        }

        $role->delete();
        $this->confirmingDeleteId = null;
        $this->success(__('Role deleted.'), position: 'toast-bottom');
        $this->resetPage();
    }

    public function togglePermission(string $permissionName): void
    {
        $this->authorize('roles.assign-permissions');

        if (! $this->selectedRoleId) {
            return;
        }

        $role = SpatieRole::findOrFail($this->selectedRoleId);
        $permission = Permission::where('name', $permissionName)
            ->where('guard_name', $this->guard_name)
            ->first();

        if (! $permission) {
            $this->error(__('Permission not found.'));

            return;
        }

        if ($role->hasPermissionTo($permission)) {
            $role->revokePermissionTo($permission);
            $this->selectedPermissions = array_values(array_diff($this->selectedPermissions, [$permissionName]));
        } else {
            $role->givePermissionTo($permission);
            if (! in_array($permissionName, $this->selectedPermissions, true)) {
                $this->selectedPermissions[] = $permissionName;
            }
        }
    }

    public function getPermissionsProperty(): array
    {
        // Group permissions by their prefix before the first dot, e.g., 'users.create' => 'users'
        $perms = Permission::query()
            ->where('guard_name', $this->guard_name)
            ->orderBy('name')
            ->get()
            ->pluck('name')
            ->all();

        $grouped = [];
        foreach ($perms as $name) {
            $parts = explode('.', $name, 2);
            $group = $parts[0] ?? 'general';
            $grouped[$group][] = $name;
        }

        ksort($grouped);

        return $grouped;
    }
    #[Computed]
    public function roles()
    {
        return SpatieRole::query()
            ->when($this->search, function ($q) {
                $q->where('name', 'like', "%{$this->search}%");
            })
            ->where('guard_name', $this->guard_name)
            ->withCount('permissions')
            ->orderBy('name')
            ->paginate($this->perPage);
    }
};
