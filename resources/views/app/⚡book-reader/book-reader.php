<?php

use App\Models\Book;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.app')] class extends Component
{
    public Book $book;

    public function mount(string $slug)
    {
        $this->book = Book::where('slug', $slug)
            ->where('status', 'approved')
            ->where('type', 'ebook')
            ->firstOrFail();

        if (!$this->book->pdf_url && !$this->book->external_link) {
            abort(404, 'No PDF file available for this book.');
        }
    }

    public function getResolvedPdfUrlProperty()
    {
        return $this->book->pdf_url ?? $this->book->external_link;
    }

    public function getTitleAttribute()
    {
        return 'Reading: ' . $this->book->title;
    }
};
