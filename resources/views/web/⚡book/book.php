<?php

use App\Models\Book;
use App\Models\BookCopy;
use App\Models\BookUserInteraction;
use App\Models\BorrowRequest;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

new
#[Layout('layouts.web')]
class extends Component
{
    use Toast;

    public Book $book;
    public string $activeTab = 'about'; // about, copies, reviews

    // Reading shelf state
    public string $reading_status = '';
    public ?int $rating = null;
    public string $review = '';
    public int $pages_read = 0;

    // PDF reader state
    public bool $showReader = false;

    // Borrow modal state
    public bool $borrowModal = false;
    public ?int $selectedCopyId = null;
    public int $requested_days = 7;
    public int $max_borrows = 2;

    public function mount(string $slug): void
    {
        $this->book = Book::with([
            'author', 'publication', 'category', 'uploader',
            'interactions.user',
            'copies' => function ($q) {
                $q->where(function ($query) {
                    $query->where('status', 'available')->where('is_borrowable', true);
                    if (auth()->check()) {
                        $query->orWhere('owner_id', auth()->id());
                    }
                })->with(['owner', 'libraryHub']);
            },
        ])
            ->where('slug', $slug)
            ->where('status', 'approved')
            ->firstOrFail();

        if (auth()->check()) {
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
    }

    public function getTitle(): string
    {
        return $this->book->title.' — Library';
    }

    public function setStatus(string $status): void
    {
        if (! auth()->check()) {
            $this->redirectRoute('login');
            return;
        }
        $this->reading_status = $status;
        if ($status === 'completed' && $this->book->pages_count) {
            $this->pages_read = $this->book->pages_count;
        }
        $this->saveInteraction();
    }

    public function setRating(int $rating): void
    {
        if (! auth()->check()) {
            $this->redirectRoute('login');
            return;
        }
        $this->rating = $rating;
        $this->saveInteraction();
    }

    public function saveInteraction(): void
    {
        $this->validate([
            'rating'     => 'nullable|integer|min:1|max:5',
            'review'     => 'nullable|string|max:1000',
            'pages_read' => 'integer|min:0',
        ]);

        BookUserInteraction::updateOrCreate(
            ['user_id' => auth()->id(), 'book_id' => $this->book->id],
            [
                'reading_status' => $this->reading_status ?: null,
                'rating'         => $this->rating,
                'review'         => $this->review ?: null,
                'pages_read'     => $this->pages_read,
            ]
        );

        $this->success('Saved!', position: 'bottom-right');
        unset($this->reviews); // clear computed cache
    }

    public function openBorrowModal(int $copyId): void
    {
        if (! auth()->check()) {
            $this->redirectRoute('login');
            return;
        }

        $activeBorrows = BorrowRequest::where('borrower_id', auth()->id())
            ->whereIn('status', ['pending', 'accepted', 'given', 'active'])
            ->count();

        if ($activeBorrows >= $this->max_borrows) {
            $this->error("You have reached the maximum limit of {$this->max_borrows} active borrows.");
            return;
        }

        $targetCopy = BookCopy::find($copyId);
        $existingRequest = BorrowRequest::where('borrower_id', auth()->id())
            ->whereIn('status', ['pending', 'accepted', 'given', 'active'])
            ->whereHas('bookCopy', fn ($q) => $q->where('book_id', $targetCopy->book_id))
            ->exists();

        if ($existingRequest) {
            $this->error('You already have an active request for a copy of this book.');
            return;
        }

        if (BookCopy::where('book_id', $this->book->id)->where('owner_id', auth()->id())->exists()) {
            $this->error('You already own a physical copy of this book.');
            return;
        }

        $this->selectedCopyId = $copyId;
        $this->requested_days = 7;
        $this->borrowModal = true;
    }

    public function submitBorrowRequest(): void
    {
        $this->validate([
            'selectedCopyId' => 'required|exists:book_copies,id',
            'requested_days' => 'required|integer|min:1|max:30',
        ]);

        BorrowRequest::create([
            'borrower_id'    => auth()->id(),
            'book_copy_id'   => $this->selectedCopyId,
            'requested_days' => $this->requested_days,
            'status'         => 'pending',
        ]);

        $copy = BookCopy::with(['owner', 'book'])->find($this->selectedCopyId);
        if ($copy && $copy->owner && $copy->owner->id !== auth()->id()) {
            $copy->owner->notify(new \App\Notifications\BookNotification(
                'request',
                auth()->user()->name,
                $copy->book->title,
                route('web.my-books'),
                $this->requested_days
            ));
        }

        $this->success('Borrow request sent! The book owner will be notified.');
        $this->borrowModal = false;
    }

    public function toggleReader(): void
    {
        if (! auth()->check()) {
            $this->redirectRoute('login');
            return;
        }
        $this->showReader = ! $this->showReader;
    }

    #[Computed]
    public function avgRating(): ?string
    {
        $avg = $this->book->interactions()->avg('rating');

        return $avg ? number_format($avg, 1) : null;
    }

    #[Computed]
    public function reviews()
    {
        return $this->book->interactions()
            ->with('user')
            ->whereNotNull('review')
            ->latest()
            ->get();
    }

    #[Computed]
    public function activeRequestsCopyIds(): array
    {
        if (! auth()->check()) {
            return [];
        }

        return BorrowRequest::where('borrower_id', auth()->id())
            ->whereIn('status', ['pending', 'accepted', 'given', 'active'])
            ->pluck('book_copy_id')
            ->toArray();
    }

    #[Computed]
    public function relatedBooks()
    {
        return Book::where('status', 'approved')
            ->where('book_category_id', $this->book->book_category_id)
            ->where('id', '!=', $this->book->id)
            ->with('author')
            ->latest()
            ->take(4)
            ->get();
    }
};
