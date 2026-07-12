<?php

use App\Models\Book;
use App\Models\BookCategory;
use App\Models\LibraryHub;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new
#[Title('Islamic Library — PSTU Dawah')]
#[Layout('layouts.web')]
class extends Component
{
    use WithPagination;

    #[Url(as: 's')]
    public string $search = '';

    #[Url(as: 'cat')]
    public ?int $categoryId = null;

    #[Url(as: 'type')]
    public string $typeFilter = ''; // ebook, physical, both

    #[Url(as: 'sort')]
    public string $sort = 'latest'; // latest, rating, title

    public string $viewMode = 'grid';

    #[Computed]
    public function books()
    {
        $query = Book::with(['author', 'category'])
            ->withAvg('interactions', 'rating')
            ->where('status', 'approved');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', "%{$this->search}%")
                    ->orWhereHas('author', fn ($a) => $a->where('name', 'like', "%{$this->search}%"));
            });
        }

        if ($this->categoryId) {
            $query->where('book_category_id', $this->categoryId);
        }

        if ($this->typeFilter) {
            $query->where('type', $this->typeFilter);
        }

        match ($this->sort) {
            'rating' => $query->orderByDesc('interactions_avg_rating'),
            'title'  => $query->orderBy('title'),
            default  => $query->latest(),
        };

        $perPage = $this->viewMode === 'grid' ? 16 : 20;

        return $query->paginate($perPage);
    }

    #[Computed]
    public function categories()
    {
        return BookCategory::whereHas('books', fn ($q) => $q->where('status', 'approved'))
            ->withCount(['books' => fn ($q) => $q->where('status', 'approved')])
            ->orderByDesc('books_count')
            ->get();
    }

    #[Computed]
    public function hubs()
    {
        return LibraryHub::where('is_active', true)->withCount('bookCopies')->orderBy('name')->get();
    }

    #[Computed]
    public function topUsers()
    {
        return \App\Models\User::withCount(['bookCopies' => function ($q) {
                $q->where('is_borrowable', true);
            }])
            ->having('book_copies_count', '>', 0)
            ->orderByDesc('book_copies_count')
            ->take(10)
            ->get();
    }

    #[Computed]
    public function stats(): array
    {
        return [
            'total'    => Book::where('status', 'approved')->count(),
            'ebooks'   => Book::where('status', 'approved')->where('type', 'ebook')->count(),
            'physical' => Book::where('status', 'approved')->whereIn('type', ['physical', 'both'])->count(),
        ];
    }

    public function mount(): void
    {
        $this->authorize('library.view');
    }

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedCategoryId(): void { $this->resetPage(); }
    public function updatedTypeFilter(): void { $this->resetPage(); }
    public function updatedSort(): void { $this->resetPage(); }

    public function resetFilters(): void
    {
        $this->reset(['search', 'categoryId', 'typeFilter', 'sort']);
        $this->resetPage();
    }
};
