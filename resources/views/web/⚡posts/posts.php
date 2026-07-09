<?php

use App\Models\Category;
use App\Models\Post;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new
#[Layout('layouts.web')]
class extends Component
{
    use WithPagination;

    #[Url(as: 's')]
    public string $search = '';
    #[Url(as: 'category')]
    public ?string $category = null;

    #[Url(as: 'sort')]
    public string $sortBy = 'latest';

    public bool $showFilters = false;

    /**
     * Get filtered and paginated posts
     */
    #[Computed]
    public function posts()
    {
        $query = Post::with(['user', 'category'])
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());

        // Search
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', "%{$this->search}%")
                    ->orWhere('excerpt', 'like', "%{$this->search}%")
                    ->orWhere('content', 'like', "%{$this->search}%");
            });
        }

        // Category filter
        if ($this->category) {
            $query->whereHas('category', function ($q) {
                $q->where('id', $this->category);
            });
        }

        // Sorting
        match ($this->sortBy) {
            'popular' => $query->orderBy('views_count', 'desc'),
            'oldest' => $query->oldest('published_at'),
            default => $query->latest('published_at'),
        };

        return $query->paginate(12);
    }

    /**
     * Get all active categories
     */
    #[Computed]
    public function categories()
    {
        return Category::where('is_active', true)
            ->withCount('posts')
            ->orderBy('posts_count', 'desc')
            ->get();
    }

    /**
     * Get stats
     */
    #[Computed]
    public function stats()
    {
        return [
            'total' => Post::whereNotNull('published_at')->count(),
            'categories' => Category::where('is_active', true)->count(),
        ];
    }

    /**
     * Reset filters
     */
    public function resetFilters(): void
    {
        $this->reset(['search', 'category', 'sortBy']);
        $this->resetPage();
    }

    /**
     * Update search and reset pagination
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Update category and reset pagination
     */
    public function updatedCategory(): void
    {
        $this->resetPage();
    }

    /**
     * Update sort and reset pagination
     */
    public function updatedSortBy(): void
    {
        $this->resetPage();
    }
};
