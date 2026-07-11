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
    #[Url(as: 'tag')]
    public ?string $tag = null;

    #[Url(as: 'sort')]
    public string $sortBy = 'latest';

    public bool $showFilters = false;

    /**
     * Get filtered and paginated posts
     */
    #[Computed]
    public function posts()
    {
        $query = Post::with(['user.detail', 'category'])
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

        // Tag filter
        if ($this->tag) {
            $query->where('meta_keywords', 'like', "%{$this->tag}%");
        }

        // Sorting
        match ($this->sortBy) {
            'popular' => $query->orderBy('views_count', 'desc'),
            'oldest' => $query->oldest('published_at'),
            default => $query->latest('published_at'),
        };

        return $query->paginate(9);
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
     * Get popular tags
     */
    #[Computed]
    public function popularTags(): array
    {
        $keywords = Post::whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereNotNull('meta_keywords')
            ->pluck('meta_keywords');

        $tags = [];
        foreach ($keywords as $list) {
            foreach (explode(',', $list) as $tag) {
                $trimmed = trim($tag);
                if (!empty($trimmed)) {
                    $tags[$trimmed] = ($tags[$trimmed] ?? 0) + 1;
                }
            }
        }
        arsort($tags);
        return array_slice(array_keys($tags), 0, 15);
    }

    /**
     * Get trending / popular posts
     */
    #[Computed]
    public function popularPosts()
    {
        return Post::with(['user', 'category'])
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderByDesc('views_count')
            ->take(5)
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
        $this->reset(['search', 'category', 'tag', 'sortBy']);
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
     * Update tag and reset pagination
     */
    public function updatedTag(): void
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
