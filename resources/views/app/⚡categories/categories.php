<?php

use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

new
#[Title('Categories')]
#[Layout('layouts.app')]
class extends Component
{
    use Toast;
    use WithPagination;

    // ==========================================
    // TABLE STATE
    // ==========================================
    public string $search = '';

    public int $perPage = 10;

    public string $sortField = 'name';

    public string $sortDirection = 'asc';

    // ==========================================
    // FILTERS
    // ==========================================
    public ?string $statusFilter = null; // 'active', 'inactive', or null for all

    public ?int $parentFilter = null; // parent category id

    // ==========================================
    // FORM STATE
    // ==========================================
    public ?int $selectedId = null;

    public string $name = '';

    public string $slug = '';

    public ?int $parent_id = null;

    public bool $is_active = true;

    // ==========================================
    // UI STATE
    // ==========================================
    public bool $showForm = false;

    public bool $isEditing = false;

    public ?int $confirmingDeleteId = null;

    // ==========================================
    // QUERY STRING
    // ==========================================
    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
        'sortField' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
        'statusFilter' => ['except' => null],
        'parentFilter' => ['except' => null],
    ];

    // ==========================================
    // VALIDATION RULES
    // ==========================================
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'slug' => [
                'required',
                'string',
                'max:120',
                Rule::unique('categories', 'slug')->ignore($this->selectedId),
            ],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            'is_active' => ['boolean'],
        ];
    }

    // ==========================================
    // LIFECYCLE HOOKS
    // ==========================================
    public function mount(): void
    {
        $this->authorize('categories.view');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingParentFilter(): void
    {
        $this->resetPage();
    }

    // ==========================================
    // SORTING
    // ==========================================
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

    // ==========================================
    // COMPUTED PROPERTIES
    // ==========================================
    #[Computed]
    public function categories()
    {
        $query = Category::query()->with(['parent', 'children']);

        // Search
        if ($this->search) {
            $s = "%{$this->search}%";
            $query->where(function ($q) use ($s): void {
                $q->where('name', 'like', $s)
                    ->orWhere('slug', 'like', $s);
            });
        }

        // Status filter
        if ($this->statusFilter !== null) {
            $query->where('is_active', $this->statusFilter === 'active');
        }

        // Parent filter
        if ($this->parentFilter !== null) {
            if ($this->parentFilter === 0) {
                $query->whereNull('parent_id'); // Root categories only
            } else {
                $query->where('parent_id', $this->parentFilter);
            }
        }

        // Sorting
        $allowedSorts = ['name', 'slug', 'created_at'];
        $field = in_array($this->sortField, $allowedSorts, true) ? $this->sortField : 'name';
        $dir = $this->sortDirection === 'desc' ? 'desc' : 'asc';

        return $query->orderBy($field, $dir)->paginate($this->perPage);
    }

    #[Computed]
    public function parentCategoryOptions(): array
    {
        return Category::query()
            ->orderBy('name')
            ->get()
            ->map(fn ($cat) => [
                'id' => $cat->id,
                'name' => $cat->hierarchy_name,
            ])
            ->toArray();
    }

    #[Computed]
    public function rootCategories(): array
    {
        return Category::query()
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get()
            ->map(fn ($cat) => [
                'id' => $cat->id,
                'name' => $cat->name,
            ])
            ->toArray();
    }

    // ==========================================
    // FORM ACTIONS
    // ==========================================
    public function create(): void
    {
        $this->authorize('categories.create');

        $this->reset(['selectedId', 'name', 'slug', 'parent_id', 'is_active']);
        $this->is_active = true;
        $this->isEditing = false;
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $this->authorize('categories.update');

        $category = Category::query()->findOrFail($id);

        // Prevent editing self as parent
        if ($category->children()->exists()) {
            // Category has children - we can still edit but shouldn't set parent to a child
        }

        $this->selectedId = $category->id;
        $this->name = $category->name;
        $this->slug = $category->slug;
        $this->parent_id = $category->parent_id;
        $this->is_active = $category->is_active;
        $this->isEditing = true;
        $this->showForm = true;
    }

    public function save(): void
    {
        if ($this->isEditing) {
            $this->authorize('categories.update');
        } else {
            $this->authorize('categories.create');
        }

        $validated = $this->validate();

        // Prevent circular reference - can't set parent to self or descendant
        if ($this->isEditing && $this->selectedId) {
            $descendantIds = $this->getDescendantIds($this->selectedId);
            if (in_array($validated['parent_id'], $descendantIds, true) || $validated['parent_id'] === $this->selectedId) {
                $this->error(__('Cannot set parent to self or a descendant category.'), position: 'toast-bottom');

                return;
            }
        }

        if ($this->isEditing && $this->selectedId) {
            $category = Category::findOrFail($this->selectedId);
            $category->update($validated);
            $this->success(__('Category updated successfully.'), position: 'toast-bottom');
        } else {
            Category::create($validated);
            $this->success(__('Category created successfully.'), position: 'toast-bottom');
        }

        $this->showForm = false;
        $this->reset(['selectedId', 'name', 'slug', 'parent_id', 'is_active']);
        $this->resetPage();
    }

    public function toggleStatus(int $id): void
    {
        $this->authorize('categories.update');

        $category = Category::findOrFail($id);
        $category->is_active = ! $category->is_active;
        $category->save();

        $status = $category->is_active ? __('activated') : __('deactivated');
        $this->success(__('Category :status.', ['status' => $status]), position: 'toast-bottom');
    }

    // ==========================================
    // DELETE ACTIONS
    // ==========================================
    public function confirmDelete(int $id): void
    {
        $this->confirmingDeleteId = $id;
    }

    public function deleteConfirmed(): void
    {
        $this->authorize('categories.delete');

        if (! $this->confirmingDeleteId) {
            return;
        }

        $category = Category::findOrFail($this->confirmingDeleteId);

        // Check for children
        if ($category->children()->exists()) {
            $this->error(__('Cannot delete category with subcategories. Please delete or move them first.'), position: 'toast-bottom');
            $this->confirmingDeleteId = null;

            return;
        }

        $category->delete();
        $this->confirmingDeleteId = null;
        $this->success(__('Category deleted successfully.'), position: 'toast-bottom');
        $this->resetPage();
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================
    public function updatedName(): void
    {
        if (! $this->isEditing || empty($this->slug)) {
            $this->slug = Str::slug($this->name);
        }
    }

    protected function getDescendantIds(int $categoryId): array
    {
        $ids = [];
        $children = Category::where('parent_id', $categoryId)->get();

        foreach ($children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $this->getDescendantIds($child->id));
        }

        return $ids;
    }
};
