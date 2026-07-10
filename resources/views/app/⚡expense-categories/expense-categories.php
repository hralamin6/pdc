<?php

use App\Models\ExpenseCategory;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Expense Categories')] #[Layout('layouts.app')] class extends Component
{
    public bool $modal = false;
    public ?int $editingId = null;

    public string $name = '';
    public string $color = '#6366f1';
    public string $icon = 'o-tag';
    public string $description = '';
    public bool $is_active = true;

    public array $iconOptions = [
        'o-tag' => 'Tag', 'o-banknotes' => 'Banknotes', 'o-shopping-cart' => 'Shopping',
        'o-truck' => 'Transport', 'o-building-office' => 'Office', 'o-wrench-screwdriver' => 'Tools',
        'o-academic-cap' => 'Education', 'o-heart' => 'Charity', 'o-printer' => 'Printing',
        'o-device-phone-mobile' => 'Mobile', 'o-globe-alt' => 'Internet', 'o-users' => 'People',
        'o-calendar' => 'Events', 'o-arrows-right-left' => 'Transfers', 'o-ellipsis-horizontal-circle' => 'Other',
    ];

    public function openModal(?int $id = null): void
    {
        $this->resetForm();
        $this->modal = true;
        if ($id) {
            $this->editingId = $id;
            $cat = ExpenseCategory::findOrFail($id);
            $this->name = $cat->name;
            $this->color = $cat->color;
            $this->icon = $cat->icon;
            $this->description = $cat->description ?? '';
            $this->is_active = $cat->is_active;
        }
    }

    public function save(): void
    {
        $this->validate([
            'name'  => 'required|string|max:100',
            'color' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'icon'  => 'required|string',
        ]);

        $data = [
            'name'        => $this->name,
            'color'       => $this->color,
            'icon'        => $this->icon,
            'description' => $this->description ?: null,
            'is_active'   => $this->is_active,
            'created_by'  => auth()->id(),
        ];

        if ($this->editingId) {
            ExpenseCategory::findOrFail($this->editingId)->update($data);
            $this->js("toast('Category updated.', {type: 'success'})");
        } else {
            ExpenseCategory::create($data);
            $this->js("toast('Category created.', {type: 'success'})");
        }

        $this->modal = false;
        $this->resetForm();
    }

    public function toggleActive(int $id): void
    {
        $cat = ExpenseCategory::findOrFail($id);
        $cat->update(['is_active' => ! $cat->is_active]);
    }

    public function delete(int $id): void
    {
        $cat = ExpenseCategory::withCount('expenses')->findOrFail($id);
        if ($cat->expenses_count > 0) {
            $this->js("toast('Cannot delete: this category has expenses linked to it.', {type: 'error'})");

            return;
        }
        $cat->delete();
        $this->js("toast('Category deleted.', {type: 'warning'})");
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->color = '#6366f1';
        $this->icon = 'o-tag';
        $this->description = '';
        $this->is_active = true;
    }

    public function with(): array
    {
        return [
            'categories' => ExpenseCategory::withCount('expenses')->latest()->get(),
        ];
    }
};
