<?php

use App\Models\Category;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new
#[Layout('layouts.web')]
#[Title('Categories - Browse All Topics')]
class extends Component
{
    use WithPagination;

    public string $search = '';

    /**
     * Get filtered categories
     */
    #[Computed]
    public function categories()
    {
        return Category::where('is_active', true)
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%');
            })
            ->withCount('posts')
            ->orderBy('posts_count', 'desc')
            ->paginate(12);
    }

    /**
     * Reset pagination when search changes
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }
};
