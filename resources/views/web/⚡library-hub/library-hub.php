<?php

namespace App\Livewire\Web;

use App\Models\LibraryHub;
use App\Models\BookCopy;
use App\Models\BorrowRequest;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Mary\Traits\Toast;

new #[Layout('layouts.web')] class extends Component
{
    use Toast;

    public LibraryHub $hub;

    public function mount(int $id): void
    {
        $this->authorize('library.view');
        $this->hub = LibraryHub::with(['manager'])->findOrFail($id);
    }

    public function title(): string
    {
        return $this->hub->name;
    }

    #[Computed]
    public function inventory(): Collection
    {
        // Books that are currently in this hub
        return BookCopy::with(['book.author', 'addedBy'])
            ->where('library_hub_id', $this->hub->id)
            ->where('is_borrowable', true)
            ->latest('updated_at')
            ->get();
    }

    #[Computed]
    public function myActiveRequests(): array
    {
        if (!auth()->check()) return [];
        return BorrowRequest::where('borrower_id', auth()->id())
            ->whereIn('status', ['pending', 'accepted', 'given', 'active'])
            ->pluck('book_copy_id')
            ->toArray();
    }

    #[Computed]
    public function stats(): array
    {
        $totalCopies = BookCopy::where('library_hub_id', $this->hub->id)->count();
        $availableCopies = BookCopy::where('library_hub_id', $this->hub->id)->where('status', 'available')->count();
        $totalBorrowed = BorrowRequest::whereHas('bookCopy', function ($q) {
            $q->where('library_hub_id', $this->hub->id);
        })->count();

        return [
            'total_books' => $totalCopies,
            'available_books' => $availableCopies,
            'total_borrowed' => $totalBorrowed,
        ];
    }

    public function requestBorrow(int $copyId): void
    {
        if (!auth()->check()) {
            $this->warning(__('Please login to borrow books.'));
            return;
        }

        $copy = BookCopy::findOrFail($copyId);
        if ($copy->status !== 'available') {
            $this->warning(__('This book is currently not available.'));
            return;
        }

        // Check if already requested
        $existing = BorrowRequest::where('borrower_id', auth()->id())
            ->where('book_copy_id', $copyId)
            ->whereIn('status', ['pending', 'accepted', 'given', 'active'])
            ->exists();
            
        if ($existing) {
            $this->warning(__('You have already requested this book.'));
            return;
        }

        BorrowRequest::create([
            'borrower_id' => auth()->id(),
            'book_copy_id' => $copyId,
            'status' => 'pending',
            'requested_days' => 14, // Default 14 days
        ]);

        unset($this->myActiveRequests);

        $this->success(__('Borrow request sent successfully! You can pick it up from the hub when approved.'));
    }
};