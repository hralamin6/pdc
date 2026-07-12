<?php

namespace App\Livewire\Web;

use App\Models\User;
use App\Models\BookCopy;
use App\Models\BorrowRequest;
use App\Models\BookUserInteraction;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

new #[Layout('layouts.web')] class extends Component
{
    use Toast;

    public User $user;
    public string $activeTab = 'shelf'; // shelf, reading, activity
    public string $readingSubTab = 'reading'; // reading, completed, want_to_read

    public function mount(int $id): void
    {
        $this->user = User::with(['media'])->findOrFail($id);
    }

    public function title(): string
    {
        return $this->user->name . "'s Bookshelf";
    }

    #[Computed]
    public function physicalShelf(): Collection
    {
        // Books the user is currently sharing (must be available or borrowed, not lost, and borrowable)
        return BookCopy::with(['book.author'])
            ->where('owner_id', $this->user->id)
            ->where('is_borrowable', true)
            ->latest('updated_at')
            ->get();
    }

    #[Computed]
    public function readingTracker(): Collection
    {
        return BookUserInteraction::with(['book.author'])
            ->where('user_id', $this->user->id)
            ->where('reading_status', $this->readingSubTab)
            ->latest('updated_at')
            ->get();
    }

    #[Computed]
    public function giftedToHubs(): Collection
    {
        return BookCopy::with(['book.author', 'libraryHub'])
            ->where('added_by', $this->user->id)
            ->whereNotNull('library_hub_id')
            ->latest('updated_at')
            ->get();
    }

    #[Computed]
    public function borrowedBooks(): Collection
    {
        return BorrowRequest::with(['bookCopy.book.author', 'bookCopy.owner', 'bookCopy.libraryHub'])
            ->where('borrower_id', $this->user->id)
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
        return [
            'books_shared' => BookCopy::where('owner_id', $this->user->id)->where('is_borrowable', true)->count(),
            'books_gifted' => BookCopy::where('added_by', $this->user->id)->whereNotNull('library_hub_id')->count(),
            'books_read'   => BookUserInteraction::where('user_id', $this->user->id)->where('reading_status', 'completed')->count(),
            'books_borrowed' => BorrowRequest::where('borrower_id', $this->user->id)->count(),
        ];
    }

    // Requests logic if the viewer is authenticated
    public function requestBorrow(int $copyId): void
    {
        if (!auth()->check()) {
            $this->warning(__('Please login to borrow books.'));
            return;
        }

        if (auth()->id() === $this->user->id) {
            $this->warning(__('You cannot borrow your own book.'));
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

        $this->success(__('Borrow request sent successfully!'));
    }
};