<?php

use App\Models\Book;
use App\Models\BookCategory;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Library Catalog')] #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    public string $search = '';
    public $category_id = null;

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedCategoryId()
    {
        $this->resetPage();
    }

    public function with(): array
    {
        $query = Book::with(['author', 'category'])->where('status', 'approved')->latest();

        if ($this->search) {
            $query->where('title', 'like', "%{$this->search}%");
        }
        if ($this->category_id) {
            $query->where('book_category_id', $this->category_id);
        }

        return [
            'books' => $query->paginate(12),
            'categories' => BookCategory::whereHas('books', fn($q) => $q->where('status', 'approved'))->get(),
        ];
    }
};
