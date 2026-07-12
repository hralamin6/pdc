<?php

use App\Models\Author;
use App\Models\Book;
use App\Models\BookCategory;
use App\Models\Publication;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Mary\Traits\Toast;

new #[Title('Manage Books')] #[Layout('layouts.app')] class extends Component
{
    use WithPagination, WithFileUploads, Toast;

    public function mount()
    {
        $this->authorize('library.manage');
    }

    public string $search = '';
    public string $statusFilter = 'all';

    public bool $modal = false;
    public ?int $editingId = null;

    // Form fields
    public string $title = '';
    public string $type = 'ebook';
    public $book_category_id;
    public $author_id;
    public $publication_id;
    public string $description = '';
    public string $status = 'approved';
    public string $external_link = '';
    public string $pages_count = '';
    public string $isbn = '';
    public string $publication_year = '';
    
    public $cover_file;
    public $pdf_file;

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function openModal(?int $id = null): void
    {
        $this->resetForm();
        $this->modal = true;

        if ($id) {
            $this->editingId = $id;
            $book = Book::findOrFail($id);
            $this->title = $book->title;
            $this->type = $book->type;
            $this->book_category_id = $book->book_category_id;
            $this->author_id = $book->author_id;
            $this->publication_id = $book->publication_id;
            $this->description = $book->description ?? '';
            $this->status = $book->status;
            $this->external_link = $book->external_link ?? '';
            $this->pages_count = (string) $book->pages_count;
            $this->isbn = $book->isbn ?? '';
            $this->publication_year = $book->publication_year ?? '';
        }
    }

    public function save(): void
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:ebook,physical,both',
            'book_category_id' => 'required|exists:book_categories,id',
            'author_id' => 'nullable|exists:authors,id',
            'publication_id' => 'nullable|exists:publications,id',
            'status' => 'required|in:pending,approved,rejected',
            'cover_file' => 'nullable|image|max:2048',
            'pdf_file' => 'nullable|file|mimes:pdf|max:51200', // max 50MB
        ]);

        // Auto-upgrade physical to both if PDF is provided
        if ($this->type === 'physical' && ($this->pdf_file || $this->external_link)) {
            $this->type = 'both';
        }

        $book = Book::updateOrCreate(['id' => $this->editingId], [
            'title' => $this->title,
            'type' => $this->type,
            'book_category_id' => $this->book_category_id ?: null,
            'author_id' => $this->author_id ?: null,
            'publication_id' => $this->publication_id ?: null,
            'description' => $this->description ?: null,
            'status' => $this->status,
            'external_link' => $this->external_link ?: null,
            'pages_count' => $this->pages_count ? (int) $this->pages_count : null,
            'isbn' => $this->isbn ?: null,
            'publication_year' => $this->publication_year ?: null,
            'uploaded_by' => $this->editingId ? Book::find($this->editingId)->uploaded_by : auth()->id(),
        ]);

        if ($this->cover_file) {
            $book->addMedia($this->cover_file)->toMediaCollection('cover');
        }
        if ($this->pdf_file && in_array($this->type, ['ebook', 'both'])) {
            $book->addMedia($this->pdf_file)->toMediaCollection('pdf_file');
        }

        $this->success('Book saved successfully.');
        $this->modal = false;
        $this->resetForm();
    }

    public function approve(int $id): void
    {
        Book::findOrFail($id)->update(['status' => 'approved']);
        $this->success('Approved.');
    }

    public function delete(int $id): void
    {
        Book::findOrFail($id)->delete();
        $this->warning('Book deleted.');
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->title = $this->description = $this->external_link = $this->isbn = $this->publication_year = $this->pages_count = '';
        $this->type = 'ebook';
        $this->status = 'approved';
        $this->book_category_id = $this->author_id = $this->publication_id = null;
        $this->cover_file = $this->pdf_file = null;
    }

    public function with(): array
    {
        $query = Book::with(['author', 'category', 'uploader'])->latest();

        if ($this->search) {
            $query->where('title', 'like', "%{$this->search}%");
        }
        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        return [
            'books' => $query->paginate(12),
            'authors' => Author::orderBy('name')->get(),
            'publications' => Publication::orderBy('name')->get(),
            'categories' => BookCategory::orderBy('name')->get(),
        ];
    }
};
