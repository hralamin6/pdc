<?php

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new
#[Layout('layouts.web')]
class extends Component
{
    /**
     * Get featured posts
     */
    #[Computed]
    public function featuredPosts()
    {
        return Post::with(['user', 'category'])
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->latest('published_at')
            ->take(6)
            ->get();
    }

    /**
     * Get active categories
     */
    #[Computed]
    public function categories()
    {
        return Category::where('is_active', true)
            ->withCount('posts')
            ->orderBy('posts_count', 'desc')
            ->take(8)
            ->get();
    }

    /**
     * Get top contributors (users with most posts)
     */
    #[Computed]
    public function topContributors()
    {
        return User::whereHas('posts')
            ->withCount('posts')
            ->orderBy('posts_count', 'desc')
            ->take(6)
            ->get();
    }

    /**
     * Get stats
     */
    #[Computed]
    public function stats()
    {
        return [
            'posts' => Post::whereNotNull('published_at')->count(),
            'categories' => Category::where('is_active', true)->count(),
            'users' => User::count(),
            'views' => Post::sum('views_count'),
        ];
    }
};
