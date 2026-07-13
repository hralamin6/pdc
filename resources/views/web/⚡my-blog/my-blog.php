<?php

use App\Models\Category;
use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Mary\Traits\Toast;

new #[Title('My Blog')] #[Layout('layouts.web')] class extends Component
{
    use Toast, WithFileUploads, WithPagination;

    // Modals
    public bool $editModal = false;

    // Form Properties
    public ?Post $editingPost = null;
    public string $postTitle = '';
    public ?int $postCategoryId = null;
    public string $postContent = '';
    public string $postExcerpt = '';
    public bool $postIsPublished = false;
    public string $postMetaTitle = '';
    public string $postMetaDescription = '';
    public string $postMetaKeywords = '';
    
    // Media uploads
    public $featuredImageFile = null;
    public ?string $existingFeaturedImageUrl = null;
    public ?string $featuredImageUrl = '';

    // Search and Filters for the list
    public string $search = '';

    public function getFeaturedImagePreviewUrl(): ?string
    {
        if (!$this->featuredImageFile) {
            return null;
        }
        try {
            return $this->featuredImageFile->temporaryUrl();
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function mount(): void
    {
        if (!auth()->check()) {
            abort(403);
        }
    }

    #[Computed]
    public function myPosts()
    {
        return Post::with(['category'])
            ->where('user_id', auth()->id())
            ->when($this->search, function ($q) {
                $q->where(function ($inner) {
                    $inner->where('title', 'like', "%{$this->search}%")
                        ->orWhere('excerpt', 'like', "%{$this->search}%")
                        ->orWhere('content', 'like', "%{$this->search}%");
                });
            })
            ->latest()
            ->paginate(10);
    }

    #[Computed]
    public function categories(): Collection
    {
        return Category::where('is_active', true)->orderBy('name')->get();
    }

    public function openCreateModal(): void
    {
        $this->resetErrorBag();
        $this->editingPost = null;
        $this->postTitle = '';
        $this->postCategoryId = null;
        $this->postContent = '';
        $this->postExcerpt = '';
        $this->postIsPublished = false;
        $this->postMetaTitle = '';
        $this->postMetaDescription = '';
        $this->postMetaKeywords = '';
        $this->featuredImageFile = null;
        $this->existingFeaturedImageUrl = null;
        $this->featuredImageUrl = '';
        $this->editModal = true;
    }

    public function openEditModal(int $id): void
    {
        $this->resetErrorBag();
        $this->editingPost = Post::where('user_id', auth()->id())->findOrFail($id);
        
        $this->postTitle = $this->editingPost->title;
        $this->postCategoryId = $this->editingPost->category_id;
        $this->postContent = $this->editingPost->content;
        $this->postExcerpt = $this->editingPost->excerpt ?? '';
        $this->postIsPublished = !is_null($this->editingPost->published_at);
        $this->postMetaTitle = $this->editingPost->meta_title ?? '';
        $this->postMetaDescription = $this->editingPost->meta_description ?? '';
        $this->postMetaKeywords = $this->editingPost->meta_keywords ?? '';
        
        $this->featuredImageFile = null;
        $this->existingFeaturedImageUrl = $this->editingPost->getFirstMediaUrl('featured_image');
        $this->featuredImageUrl = '';
        
        $this->editModal = true;
    }

    public function savePost(): void
    {
        $rules = [
            'postTitle' => 'required|string|max:255',
            'postCategoryId' => 'required|exists:categories,id',
            'postContent' => 'required|string',
            'postExcerpt' => 'nullable|string|max:500',
            'postMetaTitle' => 'nullable|string|max:255',
            'postMetaDescription' => 'nullable|string|max:500',
            'postMetaKeywords' => 'nullable|string|max:255',
            'featuredImageFile' => 'nullable|image|max:2048',
            'featuredImageUrl' => 'nullable|url',
        ];

        $this->validate($rules);

        if ($this->featuredImageUrl && !checkImageUrl($this->featuredImageUrl)) {
            $this->addError('featuredImageUrl', __('Invalid image URL or the URL is not a direct link to an image.'));
            return;
        }

        $publishedAt = null;
        if ($this->postIsPublished) {
            $publishedAt = $this->editingPost?->published_at ?: now();
        }

        $data = [
            'title' => $this->postTitle,
            'category_id' => $this->postCategoryId,
            'content' => $this->postContent,
            'excerpt' => $this->postExcerpt ?: Str::limit(strip_tags($this->postContent), 200),
            'published_at' => $publishedAt,
            'meta_title' => $this->postMetaTitle ?: null,
            'meta_description' => $this->postMetaDescription ?: null,
            'meta_keywords' => $this->postMetaKeywords ?: null,
        ];

        if ($this->editingPost) {
            $this->editingPost->update($data);
            $post = $this->editingPost;
            $this->success(__('Post updated successfully!'));
        } else {
            $data['user_id'] = auth()->id();
            $data['slug'] = Str::slug($this->postTitle . '-' . uniqid());
            $post = Post::create($data);
            $this->success(__('Post created successfully!'));
        }

        // Attach Image
        if ($this->featuredImageFile) {
            $post->clearMediaCollection('featured_image');
            $extension = $this->featuredImageFile->getClientOriginalExtension() ?: 'jpg';
            $post->addMedia($this->featuredImageFile)
                ->usingFileName('cover_' . now()->timestamp . '_' . uniqid() . '.' . $extension)
                ->toMediaCollection('featured_image');
        } elseif ($this->featuredImageUrl) {
            $post->clearMediaCollection('featured_image');
            $extension = pathinfo(parse_url($this->featuredImageUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
            $post->addMediaFromUrl($this->featuredImageUrl)
                ->usingFileName('cover_' . now()->timestamp . '_' . uniqid() . '.' . $extension)
                ->toMediaCollection('featured_image');
        }

        $this->editModal = false;
        unset($this->myPosts);
    }

    public function deletePost(int $id): void
    {
        $post = Post::where('user_id', auth()->id())->findOrFail($id);
        $post->delete();
        $this->warning(__('Post deleted successfully.'));
    }
};
