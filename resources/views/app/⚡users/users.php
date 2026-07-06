<?php

use App\Models\User as UserModel;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;
use Spatie\Permission\Models\Role;

new
#[Title('Users')]
#[Layout('layouts.app')]
class extends Component
{
    use Toast;
    use WithPagination;

    // Table state
    public string $search = '';

    public int $perPage = 10;

    public string $sortField = 'name';

    public string $sortDirection = 'asc';

    public ?string $roleFilter = null; // role name

    // Form state
    public ?int $selectedUserId = null;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public array $selectedRoles = []; // role IDs for x-choices component

    // UI
    public bool $showForm = false;

    public bool $isEditing = false;

    public ?int $confirmingDeleteId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
        'sortField' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
        'roleFilter' => ['except' => null],
    ];

    public function rules(): array
    {
        $passwordRules = $this->isEditing
            ? ['nullable', 'string', 'min:6', 'confirmed']
            : ['required', 'string', 'min:6', 'confirmed'];

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($this->selectedUserId),
            ],
            'password' => $passwordRules,
            'selectedRoles' => ['array'],
            'selectedRoles.*' => ['integer', 'exists:roles,id'],
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function updatingRoleFilter(): void
    {
        $this->resetPage();
    }

    public function mount(): void
    {
        $this->authorize('users.view');
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function create(): void
    {
        $this->authorize('users.create');

        $this->reset(['selectedUserId', 'name', 'email', 'password', 'password_confirmation', 'selectedRoles']);
        $this->isEditing = false;
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $this->authorize('users.update');

        $user = UserModel::query()->with('roles')->findOrFail($id);
        $this->selectedUserId = $user->id;
        $this->name = (string) $user->name;
        $this->email = (string) $user->email;
        $this->password = '';
        $this->password_confirmation = '';
        $this->selectedRoles = $user->roles->pluck('id')->values()->all();
        $this->isEditing = true;
        $this->showForm = true;
    }

    public function save(): void
    {
        if ($this->isEditing) {
            $this->authorize('users.update');
        } else {
            $this->authorize('users.create');
        }

        $this->validate();

        if ($this->isEditing && $this->selectedUserId) {
            $user = UserModel::findOrFail($this->selectedUserId);
            $user->name = $this->name;
            $user->email = $this->email;
            if (! empty($this->password)) {
                $user->password = Hash::make($this->password);
            }
            $user->save();
        } else {
            $user = UserModel::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
            ]);
            $this->selectedUserId = $user->id;
        }

        // Sync roles
        if (auth()->user()->can('users.assign-roles')) {
            $roles = Role::query()->whereIn('id', $this->selectedRoles)->pluck('name')->all();
            $user->syncRoles($roles);
        }

        $this->success(__('User saved successfully.'), position: 'toast-bottom');
        $this->showForm = false;
        $this->reset(['selectedUserId', 'name', 'email', 'password', 'password_confirmation', 'selectedRoles', 'isEditing']);
        $this->resetPage();
    }

    public function confirmDelete(int $id): void
    {
        $this->confirmingDeleteId = $id;
    }

    public function deleteConfirmed(): void
    {
        $this->authorize('users.delete');

        if (! $this->confirmingDeleteId) {
            return;
        }
        $user = UserModel::findOrFail($this->confirmingDeleteId);
        // Guard against deleting yourself
        if (auth()->id() === $user->id) {
            $this->error(__("You can't delete your own account."));
            $this->confirmingDeleteId = null;

            return;
        }
        $user->delete();
        $this->confirmingDeleteId = null;
        $this->success(__('User deleted.'), position: 'toast-bottom');
        $this->resetPage();
    }

    public function getRoleOptionsProperty(): array
    {
        return Role::query()
            ->orderBy('name')
            ->get()
            ->map(fn ($role) => [
                'id' => $role->id,
                'name' => $role->name,
            ])
            ->toArray();
    }

    public function getAllRolesProperty(): array
    {
        return Role::query()
            ->orderBy('name')
            ->get()
            ->map(fn ($role) => [
                'id' => $role->id,
                'name' => $role->name,
            ])
            ->toArray();
    }

        #[Computed]
    public function users()
    {
        $query = UserModel::query()->with('roles');

        if ($this->search) {
            $s = "%{$this->search}%";
            $query->where(function ($q) use ($s) {  
                $q->where('name', 'like', $s)
                    ->orWhere('email', 'like', $s);
            });
        }

        if ($this->roleFilter) {
            $query->whereHas('roles', fn ($q) => $q->where('name', $this->roleFilter));
        }

        $allowedSorts = ['name', 'email', 'created_at'];
        $field = in_array($this->sortField, $allowedSorts, true) ? $this->sortField : 'name';
        $dir = $this->sortDirection === 'desc' ? 'desc' : 'asc';

        $users = $query->orderBy($field, $dir)->paginate($this->perPage);
        return $users;
    }
};
