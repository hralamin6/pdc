<?php

use App\Models\Post;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Mary\Traits\Toast;

new
#[Layout('layouts.web')]
class extends Component
{
    use Toast;

    public string $slug;

    // Comment fields
    public string $newCommentContent = '';
    public ?int $replyingToId = null;
    public string $newReplyContent = '';

    /**
     * Get the post
     */
    #[Computed]
    public function post()
    {
        $post = Post::with(['user.detail', 'category'])
            ->where('slug', $this->slug)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->firstOrFail();

        // Increment views
        $post->incrementViews();

        return $post;
    }

    /**
     * Get comments (only root comments, with approved replies and user details)
     */
    #[Computed]
    public function comments()
    {
        return $this->post->comments()
            ->whereNull('parent_id')
            ->where('status', 'approved')
            ->with(['user.detail', 'replies' => function ($query) {
                $query->where('status', 'approved')->with('user.detail')->orderBy('created_at', 'asc');
            }])
            ->orderBy('created_at', 'desc')
            ->get();
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
     * Get trending posts
     */
    #[Computed]
    public function trendingPosts()
    {
        return Post::with(['user', 'category'])
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where('id', '!=', $this->post->id)
            ->orderByDesc('views_count')
            ->take(5)
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

    public function submitComment(): void
    {
        if (!auth()->check()) {
            $this->redirectRoute('login');
            return;
        }

        $this->validate([
            'newCommentContent' => 'required|string|min:2|max:1000',
        ]);

        \App\Models\Comment::create([
            'user_id' => auth()->id(),
            'post_id' => $this->post->id,
            'content' => $this->newCommentContent,
            'status' => 'approved',
        ]);

        $this->reset(['newCommentContent']);
        $this->success('Comment posted successfully.');
    }

    public function setReplyingTo(?int $commentId): void
    {
        $this->replyingToId = $commentId;
        $this->reset(['newReplyContent']);
    }

    public function submitReply(int $parentId): void
    {
        if (!auth()->check()) {
            $this->redirectRoute('login');
            return;
        }

        $this->validate([
            'newReplyContent' => 'required|string|min:2|max:1000',
        ]);

        \App\Models\Comment::create([
            'user_id' => auth()->id(),
            'post_id' => $this->post->id,
            'parent_id' => $parentId,
            'content' => $this->newReplyContent,
            'status' => 'approved',
        ]);

        $this->reset(['newReplyContent', 'replyingToId']);
        $this->success('Reply posted successfully.');
    }

    public function deleteComment(int $commentId): void
    {
        if (!auth()->check()) {
            return;
        }

        $comment = \App\Models\Comment::findOrFail($commentId);
        
        $isAuthor = $comment->user_id === auth()->id();
        $isAdmin = auth()->user()->can('posts.delete');

        if (!$isAuthor && !$isAdmin) {
            $this->error('Unauthorized action.');
            return;
        }

        $comment->delete();
        $this->success('Comment deleted.');
    }

    /**
     * Get reaction counts for this post
     */
    #[Computed]
    public function reactionCounts(): array
    {
        $counts = $this->post->reactions()
            ->select('type', \DB::raw('count(*) as count'))
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        return [
            'like' => $counts['like'] ?? 0,
            'love' => $counts['love'] ?? 0,
            'insightful' => $counts['insightful'] ?? 0,
            'inspiring' => $counts['inspiring'] ?? 0,
            'total' => array_sum($counts)
        ];
    }

    /**
     * Get user's current reaction type
     */
    #[Computed]
    public function userReactionType(): ?string
    {
        if (!auth()->check()) {
            return null;
        }

        return $this->post->reactions()
            ->where('user_id', auth()->id())
            ->value('type');
    }

    /**
     * Toggle or update user reaction
     */
    public function react(string $type): void
    {
        if (!auth()->check()) {
            $this->redirectRoute('login');
            return;
        }

        if (!in_array($type, ['like', 'love', 'insightful', 'inspiring'])) {
            return;
        }

        $existing = \App\Models\PostReaction::where('user_id', auth()->id())
            ->where('post_id', $this->post->id)
            ->first();

        if ($existing) {
            if ($existing->type === $type) {
                // Toggle off
                $existing->delete();
            } else {
                // Update reaction type
                $existing->update(['type' => $type]);
            }
        } else {
            // Create new reaction
            \App\Models\PostReaction::create([
                'user_id' => auth()->id(),
                'post_id' => $this->post->id,
                'type' => $type,
            ]);
        }
    }
};
