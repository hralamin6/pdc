<?php

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Mary\Traits\Toast;

new
#[Title('Posts')]
#[Layout('layouts.app')]
class extends Component
{
    use Toast;
    use WithFileUploads;
    use WithPagination;

    // Table state
    public string $search = '';

    public int $perPage = 10;

    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

    // Filters
    public ?int $categoryFilter = null;

    public ?int $authorFilter = null;

    public ?bool $featuredFilter = null;

    public string $statusFilter = 'all';

    public ?string $dateFrom = null;

    public ?string $dateTo = null;

    public bool $showTrashed = false;

    // Form state
    public ?int $selectedId = null;

    public string $title = '';

    public string $slug = '';

    public ?int $category_id = null;

    public string $excerpt = '';

    public string $content = '';

    public bool $is_featured = false;

    public ?string $published_at = null;

    public ?int $user_id = null;

    public string $meta_title = '';

    public string $meta_description = '';

    public string $meta_keywords = '';

    // Media
    public $featured_image;

    public string $featured_image_url = '';

    public bool $remove_featured_image = false;

    // UI state
    public bool $showForm = false;

    public bool $isEditing = false;

    public ?int $confirmingDeleteId = null;

    public ?int $confirmingRestoreId = null;

    public ?int $confirmingForceDeleteId = null;

    public string $activeTab = 'content';

    public ?int $lastSaved = null;

    // Bulk actions
    public array $selectedPosts = [];

    public bool $showBulkActions = false;

    public string $bulkAction = '';

    protected $queryString = [
        'search', 'perPage', 'sortField', 'sortDirection',
        'categoryFilter', 'authorFilter', 'featuredFilter',
        'statusFilter', 'dateFrom', 'dateTo', 'showTrashed',
    ];

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:280', Rule::unique('posts', 'slug')->ignore($this->selectedId)],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'is_featured' => ['boolean'],
            'published_at' => ['nullable', 'date'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'meta_title' => ['nullable', 'string', 'max:60'],
            'meta_description' => ['nullable', 'string', 'max:160'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            'featured_image' => ['nullable', 'image', 'max:10240'],
            'featured_image_url' => ['nullable', 'url'],
        ];
    }

    public function mount(): void
    {
        $this->authorize('posts.view');
        $this->user_id = Auth::id();
    }

    public function updatingFilters(): void
    {
        $this->resetPage();
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

    #[Computed]
    public function posts()
    {
        $query = $this->showTrashed ? Post::onlyTrashed() : Post::query();
        $query->with(['category', 'user', 'media']);

        if ($this->search) {
            $s = "%{$this->search}%";
            $query->where(fn ($q) => $q->where('title', 'like', $s)->orWhere('excerpt', 'like', $s)->orWhere('content', 'like', $s));
        }

        if ($this->categoryFilter) {
            $query->where('category_id', $this->categoryFilter);
        }
        if ($this->authorFilter) {
            $query->where('user_id', $this->authorFilter);
        }
        if ($this->featuredFilter !== null) {
            $query->where('is_featured', $this->featuredFilter);
        }

        if ($this->statusFilter !== 'all') {
            match ($this->statusFilter) {
                'published' => $query->published(),
                'draft' => $query->draft(),
                'scheduled' => $query->scheduled(),
            };
        }

        if ($this->dateFrom) {
            $query->whereDate('published_at', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->whereDate('published_at', '<=', $this->dateTo);
        }

        if (! Auth::user()->can('posts.view-all')) {
            $query->where('user_id', Auth::id());
        }

        $allowedSorts = ['title', 'published_at', 'views_count', 'created_at'];
        $field = in_array($this->sortField, $allowedSorts) ? $this->sortField : 'created_at';

        return $query->orderBy($field, $this->sortDirection)->paginate($this->perPage);
    }

    #[Computed]
    public function categoryOptions(): array
    {
        return Category::active()->orderBy('name')->get()->map(fn ($c) => ['id' => $c->id, 'name' => $c->hierarchy_name])->toArray();
    }

    #[Computed]
    public function authorOptions(): array
    {
        if (! Auth::user()->can('posts.view-all')) {
            return [];
        }

        return User::orderBy('name')->get()->map(fn ($u) => ['id' => $u->id, 'name' => $u->name])->toArray();
    }

    public function create(): void
    {
        $this->authorize('posts.create');
        $this->reset();
        $this->user_id = Auth::id();
        $this->isEditing = false;
        $this->showForm = true;
        $this->activeTab = 'content';
    }

    public function edit(int $id): void
    {
        $this->authorize('posts.update-own');
        $post = $this->showTrashed ? Post::onlyTrashed()->findOrFail($id) : Post::findOrFail($id);

        if (! Auth::user()->can('posts.update') && $post->user_id !== Auth::id()) {
            $this->error(__('You can only edit your own posts.'));

            return;
        }

        $this->selectedId = $post->id;
        $this->title = $post->title;
        $this->slug = $post->slug;
        $this->category_id = $post->category_id;
        $this->excerpt = $post->excerpt ?? '';
        $this->content = $post->content;
        $this->is_featured = $post->is_featured;
        $this->published_at = $post->published_at?->format('Y-m-d\TH:i');
        $this->user_id = $post->user_id;
        $this->meta_title = $post->meta_title ?? '';
        $this->meta_description = $post->meta_description ?? '';
        $this->meta_keywords = $post->meta_keywords ?? '';
        $this->isEditing = true;
        $this->showForm = true;
        $this->activeTab = 'content';
    }

    public function duplicate(int $id): void
    {
        $this->authorize('posts.create');
        $post = Post::findOrFail($id);
        $this->reset();
        $this->title = $post->title.' (Copy)';
        $this->slug = Str::slug($post->title.'-copy');
        $this->category_id = $post->category_id;
        $this->excerpt = $post->excerpt ?? '';
        $this->content = $post->content;
        $this->meta_title = $post->meta_title ?? '';
        $this->meta_description = $post->meta_description ?? '';
        $this->meta_keywords = $post->meta_keywords ?? '';
        $this->user_id = Auth::id();
        $this->isEditing = false;
        $this->showForm = true;
        $this->activeTab = 'content';
    }

    public function save(): void
    {
        $this->authorize($this->isEditing ? 'posts.update-own' : 'posts.create');
        $validated = $this->validate();

        $data = [
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'category_id' => $validated['category_id'],
            'excerpt' => $validated['excerpt'],
            'content' => $validated['content'],
            'is_featured' => $validated['is_featured'],
            'published_at' => $validated['published_at'],
            'meta_title' => $validated['meta_title'],
            'meta_description' => $validated['meta_description'],
            'meta_keywords' => $validated['meta_keywords'],
        ];

        if (Auth::user()->can('posts.update') || ! $this->isEditing) {
            $data['user_id'] = $validated['user_id'];
        }

        if ($this->isEditing) {
            $post = Post::findOrFail($this->selectedId);
            if (! Auth::user()->can('posts.update') && $post->user_id !== Auth::id()) {
                $this->error(__('Unauthorized.'));

                return;
            }
            $post->update($data);
            $this->success(__('Post updated.'));
        } else {
            $post = Post::create($data);
            $this->selectedId = $post->id;
            $this->success(__('Post created.'));
        }

        if ($this->remove_featured_image) {
            $post->clearMediaCollection('featured_image');
        }
        $this->handleFeaturedImage($post);

        $this->showForm = false;
        $this->reset();
    }

    public function autoSave(): void
    {
        if (! empty($this->title)) {
            $this->lastSaved = now()->timestamp;
        }
    }

    public function toggleFeatured(int $id): void
    {
        $this->authorize('posts.feature');
        $post = Post::findOrFail($id);
        $post->update(['is_featured' => ! $post->is_featured]);
        $this->success($post->is_featured ? __('Featured') : __('Unfeatured'));
    }

    public function publish(int $id): void
    {
        $this->authorize('posts.publish');
        Post::findOrFail($id)->update(['published_at' => now()]);
        $this->success(__('Published.'));
    }

    public function unpublish(int $id): void
    {
        $this->authorize('posts.publish');
        Post::findOrFail($id)->update(['published_at' => null]);
        $this->success(__('Unpublished.'));
    }

    public function confirmDelete(int $id): void
    {
        $this->confirmingDeleteId = $id;
    }

    public function deleteConfirmed(): void
    {
        $this->authorize('posts.delete-own');
        $post = Post::findOrFail($this->confirmingDeleteId);
        if (! Auth::user()->can('posts.delete') && $post->user_id !== Auth::id()) {
            $this->error(__('Unauthorized.'));

            return;
        }
        $post->delete();
        $this->confirmingDeleteId = null;
        $this->success(__('Trashed.'));
    }

    public function confirmRestore(int $id): void
    {
        $this->confirmingRestoreId = $id;
    }

    public function restoreConfirmed(): void
    {
        $this->authorize('posts.update-own');
        Post::onlyTrashed()->findOrFail($this->confirmingRestoreId)->restore();
        $this->confirmingRestoreId = null;
        $this->success(__('Restored.'));
    }

    public function confirmForceDelete(int $id): void
    {
        $this->confirmingForceDeleteId = $id;
    }

    public function forceDeleteConfirmed(): void
    {
        $this->authorize('posts.delete');
        Post::onlyTrashed()->findOrFail($this->confirmingForceDeleteId)->forceDelete();
        $this->confirmingForceDeleteId = null;
        $this->success(__('Deleted permanently.'));
    }

    public function updatedTitle(): void
    {
        if (! $this->isEditing || empty($this->slug)) {
            $this->slug = Str::slug($this->title);
        }
    }

    public function updatedSelectedPosts(): void
    {
        $this->showBulkActions = count($this->selectedPosts) > 0;
    }

    public function executeBulkAction(): void
    {
        if (empty($this->bulkAction) || empty($this->selectedPosts)) {
            return;
        }
        $posts = Post::whereIn('id', $this->selectedPosts)->get();
        match ($this->bulkAction) {
            'delete' => $this->bulkDelete($posts),
            'publish' => $this->bulkPublish($posts),
            'feature' => $this->bulkFeature($posts),
            'unfeature' => $this->bulkUnfeature($posts),
        };
        $this->selectedPosts = [];
        $this->showBulkActions = false;
        $this->bulkAction = '';
    }

    protected function bulkDelete($posts): void
    {
        $this->authorize('posts.delete-own');
        $count = $posts->filter(fn ($p) => Auth::user()->can('posts.delete') || $p->user_id === Auth::id())
            ->each->delete()->count();
        $this->success(__(':count trashed.', ['count' => $count]));
    }

    protected function bulkPublish($posts): void
    {
        $this->authorize('posts.publish');
        $posts->each->update(['published_at' => now()]);
        $this->success(__(':count published.', ['count' => $posts->count()]));
    }

    protected function bulkFeature($posts): void
    {
        $this->authorize('posts.feature');
        $posts->each->update(['is_featured' => true]);
        $this->success(__(':count featured.', ['count' => $posts->count()]));
    }

    protected function bulkUnfeature($posts): void
    {
        $this->authorize('posts.feature');
        $posts->each->update(['is_featured' => false]);
        $this->success(__(':count unfeatured.', ['count' => $posts->count()]));
    }

    protected function handleFeaturedImage($post): void
    {
        if ($this->featured_image_url && checkImageUrl($this->featured_image_url)) {
            $ext = pathinfo(parse_url($this->featured_image_url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
            $post->addMediaFromUrl($this->featured_image_url)->usingFileName("featured_{$post->id}.{$ext}")->toMediaCollection('featured_image');
        } elseif ($this->featured_image) {
            $post->addMedia($this->featured_image->getRealPath())->usingFileName("featured_{$post->id}.{$this->featured_image->extension()}")->toMediaCollection('featured_image');
        }
    }
};
