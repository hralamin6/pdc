<?php

use App\Models\Post;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new
#[Layout('layouts.web')]
class extends Component
{
    public string $slug;

    /**
     * Get the post
     */
    #[Computed]
    public function post()
    {
        $post = Post::with(['user', 'category'])
            ->where('slug', $this->slug)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->firstOrFail();

        // Increment views
        $post->incrementViews();

        return $post;
    }

    /**
     * Get related posts
     */
    #[Computed]
    public function relatedPosts()
    {
        return Post::with(['user', 'category'])
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where('id', '!=', $this->post->id)
            ->where(function ($query) {
                $query->where('category_id', $this->post->category_id)
                    ->orWhereHas('category', function ($q) {
                        $q->where('parent_id', $this->post->category?->parent_id);
                    });
            })
            ->latest('published_at')
            ->take(3)
            ->get();
    }

    /**
     * Get share URL
     */
    #[Computed]
    public function shareUrl()
    {
        return urlencode(route('web.post', $this->post->slug));
    }

    /**
     * Get share text
     */
    #[Computed]
    public function shareText()
    {
        return urlencode($this->post->title);
    }
};
