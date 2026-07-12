<?php

use App\Models\Author;
use App\Models\BookCategory;
use App\Models\Publication;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

new #[Title('Library Metadata')] #[Layout('layouts.app')] class extends Component
{
    use WithFileUploads, Toast;

    public function mount()
    {
        $this->authorize('library.manage');
    }

    public string $activeTab = 'authors';

    // Shared modal
    public bool $modal = false;
    public ?int $editingId = null;

    // Author Fields
    public string $authorName = '';
    public string $authorBio = '';
    public $authorPhoto;

    // Publication Fields
    public string $pubName = '';
    public string $pubDesc = '';
    public $pubLogo;

    // Category Fields
    public string $catName = '';
    public string $catIcon = 'o-book-open';
    public array $iconOptions = [
        'o-book-open' => 'Book', 'o-academic-cap' => 'Academic', 'o-star' => 'Star',
        'o-heart' => 'Heart', 'o-globe-alt' => 'Globe', 'o-users' => 'People',
        'o-bookmark' => 'Bookmark', 'o-sparkles' => 'Sparkles', 'o-fire' => 'Fire'
    ];

    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetForm();
    }

    public function openModal(?int $id = null): void
    {
        $this->resetForm();
        $this->modal = true;

        if ($id) {
            $this->editingId = $id;
            if ($this->activeTab === 'authors') {
                $author = Author::findOrFail($id);
                $this->authorName = $author->name;
                $this->authorBio = $author->bio ?? '';
            } elseif ($this->activeTab === 'publications') {
                $pub = Publication::findOrFail($id);
                $this->pubName = $pub->name;
                $this->pubDesc = $pub->description ?? '';
            } elseif ($this->activeTab === 'categories') {
                $cat = BookCategory::findOrFail($id);
                $this->catName = $cat->name;
                $this->catIcon = $cat->icon ?? 'o-book-open';
            }
        }
    }

    public function save(): void
    {
        if ($this->activeTab === 'authors') {
            $this->validate([
                'authorName' => 'required|string|max:255',
                'authorPhoto' => 'nullable|image|max:2048',
            ]);
            $author = Author::updateOrCreate(['id' => $this->editingId], [
                'name' => $this->authorName,
                'slug' => Str::slug($this->authorName),
                'bio' => $this->authorBio ?: null,
            ]);
            if ($this->authorPhoto) {
                $author->addMedia($this->authorPhoto)->toMediaCollection('photo');
            }
        } elseif ($this->activeTab === 'publications') {
            $this->validate([
                'pubName' => 'required|string|max:255',
                'pubLogo' => 'nullable|image|max:2048',
            ]);
            $pub = Publication::updateOrCreate(['id' => $this->editingId], [
                'name' => $this->pubName,
                'slug' => Str::slug($this->pubName),
                'description' => $this->pubDesc ?: null,
            ]);
            if ($this->pubLogo) {
                $pub->addMedia($this->pubLogo)->toMediaCollection('logo');
            }
        } elseif ($this->activeTab === 'categories') {
            $this->validate([
                'catName' => 'required|string|max:255',
                'catIcon' => 'required|string',
            ]);
            BookCategory::updateOrCreate(['id' => $this->editingId], [
                'name' => $this->catName,
                'slug' => Str::slug($this->catName),
                'icon' => $this->catIcon,
            ]);
        }

        $this->success('Saved successfully.');
        $this->modal = false;
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        if ($this->activeTab === 'authors') {
            $model = Author::withCount('books')->findOrFail($id);
        } elseif ($this->activeTab === 'publications') {
            $model = Publication::withCount('books')->findOrFail($id);
        } else {
            $model = BookCategory::withCount('books')->findOrFail($id);
        }

        if ($model->books_count > 0) {
            $this->error('Cannot delete: this record is linked to books.');
            return;
        }

        $model->delete();
        $this->warning('Deleted successfully.');
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->authorName = $this->authorBio = '';
        $this->authorPhoto = null;
        $this->pubName = $this->pubDesc = '';
        $this->pubLogo = null;
        $this->catName = '';
        $this->catIcon = 'o-book-open';
    }

    public function with(): array
    {
        return [
            'authors' => Author::withCount('books')->orderBy('name')->get(),
            'publications' => Publication::withCount('books')->orderBy('name')->get(),
            'categories' => BookCategory::withCount('books')->orderBy('name')->get(),
        ];
    }
};
