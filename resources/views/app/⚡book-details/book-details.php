<?php

use App\Models\Book;
use App\Models\BookUserInteraction;
use App\Models\BorrowRequest;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

new #[Layout('layouts.app')] class extends Component
{
    use Toast;

    public Book $book;
    
    // Tracking & Review state
    public string $reading_status = '';
    public ?int $rating = null;
    public string $review = '';
    public int $pages_read = 0;
    
    // Borrow Request State
    public bool $borrowModal = false;
    public ?int $selectedCopyId = null;
    public int $requested_days = 7;
    public int $max_borrows = 2; // Fixed limit

    public function mount(string $slug)
    {
        $this->book = Book::with([
            'author', 'publication', 'category', 'interactions.user',
            'copies' => function ($q) {
                $q->where(function($query) {
                    $query->where('status', 'available')->where('is_borrowable', true);
                    if (auth()->check()) {
                        $query->orWhere('owner_id', auth()->id());
                    }
                })->with(['owner', 'libraryHub']);
            }
        ])
            ->where('slug', $slug)
            ->where('status', 'approved')
            ->firstOrFail();

        $interaction = BookUserInteraction::where('user_id', auth()->id())
            ->where('book_id', $this->book->id)
            ->first();

        if ($interaction) {
            $this->reading_status = $interaction->reading_status ?? '';
            $this->rating = $interaction->rating;
            $this->review = $interaction->review ?? '';
            $this->pages_read = $interaction->pages_read;
        }
    }

    public function getTitleAttribute()
    {
        return $this->book->title;
    }

    public function setStatus(string $status)
    {
        $this->reading_status = $status;
        if ($status === 'completed' && $this->book->pages_count) {
            $this->pages_read = $this->book->pages_count;
        }
        $this->saveInteraction();
    }

    public function setRating(int $rating)
    {
        $this->rating = $rating;
        $this->saveInteraction();
    }

    public function saveInteraction()
    {
        $this->validate([
            'rating' => 'nullable|integer|min:1|max:5',
            'review' => 'nullable|string|max:1000',
            'pages_read' => 'integer|min:0',
        ]);

        BookUserInteraction::updateOrCreate(
            ['user_id' => auth()->id(), 'book_id' => $this->book->id],
            [
                'reading_status' => $this->reading_status ?: null,
                'rating' => $this->rating,
                'review' => $this->review ?: null,
                'pages_read' => $this->pages_read,
            ]
        );

        $this->success(__('Saved.'), position: 'bottom-right');
    }

    public function openBorrowModal($copyId)
    {
        // Check global borrow limit
        $activeBorrows = BorrowRequest::where('borrower_id', auth()->id())
            ->whereIn('status', ['pending', 'accepted', 'given', 'active'])
            ->count();
            
        if ($activeBorrows >= $this->max_borrows) {
            $this->error(__('You have reached the maximum limit of :count active borrows/requests.', ['count' => $this->max_borrows]));
            return;
        }

        // Check if user already has an active/pending request for THIS book (prevents spamming multiple owners)
        $targetCopy = App\Models\BookCopy::find($copyId);
        
        $existingRequestForBook = BorrowRequest::where('borrower_id', auth()->id())
            ->whereIn('status', ['pending', 'accepted', 'given', 'active'])
            ->whereHas('bookCopy', function ($q) use ($targetCopy) {
                $q->where('book_id', $targetCopy->book_id);
            })
            ->exists();
            
        if ($existingRequestForBook) {
            $this->error(__('You already have an active or pending request for a copy of this book.'));
            return;
        }

        // Check if user already owns this book
        $userOwnsCopy = \App\Models\BookCopy::where('book_id', $this->book->id)->where('owner_id', auth()->id())->exists();
        if ($userOwnsCopy) {
            $this->error(__('You already own a physical copy of this book.'));
            return;
        }

        $this->selectedCopyId = $copyId;
        $this->requested_days = 7;
        $this->borrowModal = true;
    }

    public function submitBorrowRequest()
    {
        $this->validate([
            'selectedCopyId' => 'required|exists:book_copies,id',
            'requested_days' => 'required|integer|min:1|max:30',
        ]);

        BorrowRequest::create([
            'borrower_id' => auth()->id(),
            'book_copy_id' => $this->selectedCopyId,
            'requested_days' => $this->requested_days,
            'status' => 'pending',
        ]);

        $copy = \App\Models\BookCopy::with(['owner', 'book'])->find($this->selectedCopyId);
        if ($copy && $copy->owner && $copy->owner->id !== auth()->id()) {
            $copy->owner->notify(new \App\Notifications\BookNotification(
                'request',
                auth()->user()->name,
                $copy->book->title,
                route('web.my-books'),
                $this->requested_days
            ));
        }

        $this->success(__('Borrow request sent! The owner will be notified.'));
        $this->borrowModal = false;
    }

    public function with(): array
    {
        // Calculate average rating
        $avgRating = $this->book->interactions()->avg('rating');
        $reviewsCount = $this->book->interactions()->whereNotNull('review')->count();
        $reviews = $this->book->interactions()->with('user')->whereNotNull('review')->latest()->get();

        // Get user's active requests for copies of this book
        $activeRequestsCopyIds = [];
        $userOwnsCopy = false;
        
        if (auth()->check()) {
            $activeRequestsCopyIds = BorrowRequest::where('borrower_id', auth()->id())
                ->whereIn('status', ['pending', 'accepted', 'given', 'active'])
                ->pluck('book_copy_id')
                ->toArray();
                
            $userOwnsCopy = \App\Models\BookCopy::where('book_id', $this->book->id)
                ->where('owner_id', auth()->id())
                ->exists();
        }

        return [
            'avgRating' => $avgRating ? number_format($avgRating, 1) : null,
            'reviewsCount' => $reviewsCount,
            'reviews' => $reviews,
            'activeRequestsCopyIds' => $activeRequestsCopyIds,
            'userOwnsCopy' => $userOwnsCopy,
        ];
    }
};
