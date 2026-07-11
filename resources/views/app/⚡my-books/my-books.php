<?php

use App\Models\Book;
use App\Models\BookCopy;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

new #[Title('My Bookshelf')] #[Layout('layouts.app')] class extends Component
{
    use Toast;

    public bool $addModal = false;
    public string $search = '';
    
    public $selectedBookId = null;
    public string $condition = 'Good';
    public bool $is_borrowable = true;
    
    // Custom book fields
    public bool $isCustom = false;
    public string $customTitle = '';
    public string $customAuthor = '';
    public ?int $customCategoryId = null;

    // Computed property instead of with() for cleaner reactive fetching
    #[\Livewire\Attributes\Computed]
    public function myCopies()
    {
        return BookCopy::with(['book.category', 'book.author', 'libraryHub'])
            ->where('owner_id', auth()->id())
            ->latest()
            ->get();
    }

    #[\Livewire\Attributes\Computed]
    public function availableBooks()
    {
        return Book::where('status', 'approved')
            ->when($this->search, fn($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->limit(10)
            ->get();
    }

    #[\Livewire\Attributes\Computed]
    public function categories()
    {
        return \App\Models\BookCategory::all();
    }

    public function openAddModal()
    {
        $this->search = '';
        $this->selectedBookId = null;
        $this->condition = 'Good';
        $this->is_borrowable = true;
        $this->isCustom = false;
        $this->customTitle = '';
        $this->customAuthor = '';
        $this->customCategoryId = null;
        $this->addModal = true;
    }

    public function selectBook($id)
    {
        $this->selectedBookId = $id;
        $this->isCustom = false;
    }

    public function toggleCustom()
    {
        $this->isCustom = true;
        $this->selectedBookId = null;
        $this->customTitle = $this->search;
    }

    public function addCopy()
    {
        if ($this->isCustom) {
            $this->validate([
                'customTitle' => 'required|string|max:255',
                'customAuthor' => 'nullable|string|max:255',
                'condition' => 'required|string',
                'is_borrowable' => 'boolean',
            ]);
            
            // Create author if provided
            $authorId = null;
            if ($this->customAuthor) {
                $author = \App\Models\Author::firstOrCreate(['name' => $this->customAuthor]);
                $authorId = $author->id;
            }

            $book = Book::create([
                'title' => $this->customTitle,
                'slug' => \Illuminate\Support\Str::slug($this->customTitle . '-' . uniqid()),
                'author_id' => $authorId,
                'book_category_id' => $this->customCategoryId,
                'type' => 'physical',
                'status' => 'approved', // Instantly available per user request (admins can audit via Activity Logs)
                'uploaded_by' => auth()->id(),
            ]);
            
            $bookId = $book->id;
        } else {
            $this->validate([
                'selectedBookId' => 'required|exists:books,id',
                'condition' => 'required|string',
                'is_borrowable' => 'boolean',
            ]);
            
            $bookId = $this->selectedBookId;
            $book = Book::find($bookId);
            
            if ($book->type === 'ebook') {
                $book->update(['type' => 'both']);
            }
        }

        BookCopy::create([
            'book_id' => $bookId,
            'owner_id' => auth()->id(),
            'status' => 'available',
            'is_borrowable' => $this->is_borrowable,
            'condition' => $this->condition,
        ]);

        $this->success('Book added to your shelf!');
        $this->addModal = false;
        unset($this->myCopies); // clear cache
    }

    public function toggleBorrowable($id)
    {
        $copy = BookCopy::where('owner_id', auth()->id())->findOrFail($id);
        $copy->update(['is_borrowable' => !$copy->is_borrowable]);
        unset($this->myCopies);
    }

    public function deleteCopy($id)
    {
        $copy = BookCopy::where('owner_id', auth()->id())->findOrFail($id);
        
        if ($copy->status === 'borrowed') {
            $this->error('Cannot delete a book that is currently borrowed.');
            return;
        }
        
        $copy->delete();
        $this->warning('Removed from your shelf.');
        unset($this->myCopies);
    }
};
