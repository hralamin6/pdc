<?php

use App\Models\Author;
use App\Models\Book;
use App\Models\BookCategory;
use App\Models\BookCopy;
use App\Models\BookUserInteraction;
use App\Models\BorrowRequest;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

new #[Title('My Bookshelf')] #[Layout('layouts.web')] class extends Component
{
    use Toast, WithFileUploads;

    // Tab state
    public string $activeTab = 'copies'; // copies, reading, requests
    public string $readingSubTab = 'reading'; // reading, want_to_read, completed
    public string $requestsSubTab = 'incoming'; // incoming, outgoing

    // Modals
    public bool $addModal = false;
    public bool $progressModal = false;
    public bool $addReadingModal = false;

    // Reading progress modal properties
    public ?int $selectedInteractionId = null;
    public ?int $progressPagesRead = 0;
    public ?int $progressRating = null;
    public string $progressReview = '';

    // Search and Catalog
    public string $search = '';
    public string $searchCatalog = '';

    // Lending Copy State
    public ?int $selectedBookId = null;
    public string $condition = 'Good';
    public bool $is_borrowable = true;
    public ?int $giftToHubId = null;

    // Custom Book Creation
    public bool $isCustom = false;
    public string $customTitle = '';
    public string $customAuthor = '';
    public ?int $customCategoryId = null;
    public ?int $customPagesCount = null;
    public $customCoverFile = null;

    public function getCoverPreviewUrl(): ?string
    {
        if (!$this->customCoverFile) {
            return null;
        }
        try {
            return $this->customCoverFile->temporaryUrl();
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function mount(): void
    {
        $this->authorize('library.view');
    }

    #[Computed]
    public function activeHubs(): Collection
    {
        return \App\Models\LibraryHub::where('is_active', true)->orderBy('name')->get();
    }

    #[Computed]
    public function myCopies(): Collection
    {
        return BookCopy::with(['book.category', 'book.author', 'libraryHub'])
            ->where('owner_id', auth()->id())
            ->latest()
            ->get();
    }

    #[Computed]
    public function myInteractions(): Collection
    {
        return BookUserInteraction::with(['book.category', 'book.author'])
            ->where('user_id', auth()->id())
            ->when($this->readingSubTab, function ($q) {
                $q->where('reading_status', $this->readingSubTab);
            })
            ->latest('updated_at')
            ->get();
    }

    #[Computed]
    public function availableBooks(): Collection
    {
        $searchTerm = $this->activeTab === 'copies' ? $this->search : $this->searchCatalog;

        return Book::with(['author', 'category'])
            ->where('status', 'approved')
            ->when($searchTerm, fn ($q) => $q->where('title', 'like', "%{$searchTerm}%"))
            ->limit(10)
            ->get();
    }

    #[Computed]
    public function categories(): Collection
    {
        return BookCategory::orderBy('name')->get();
    }

    #[Computed]
    public function incomingRequests(): Collection
    {
        return BorrowRequest::with(['borrower', 'bookCopy.book'])
            ->whereHas('bookCopy', function ($q) {
                $q->where('owner_id', auth()->id());
            })
            ->latest('updated_at')
            ->get();
    }

    #[Computed]
    public function outgoingRequests(): Collection
    {
        return BorrowRequest::with(['bookCopy.owner', 'bookCopy.libraryHub', 'bookCopy.book'])
            ->where('borrower_id', auth()->id())
            ->latest('updated_at')
            ->get();
    }

    // --- Tab Switcher ---
    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function switchReadingTab(string $tab): void
    {
        $this->readingSubTab = $tab;
        unset($this->myInteractions);
    }

    public function switchRequestsTab(string $tab): void
    {
        $this->requestsSubTab = $tab;
        unset($this->incomingRequests);
        unset($this->outgoingRequests);
    }

    // --- Physical Shelf Actions ---
    public function openAddModal(): void
    {
        $this->search = '';
        $this->selectedBookId = null;
        $this->condition = 'Good';
        $this->is_borrowable = true;
        $this->giftToHubId = null;
        $this->isCustom = false;
        $this->customTitle = '';
        $this->customAuthor = '';
        $this->customCategoryId = null;
        $this->customPagesCount = null;
        $this->customCoverFile = null;
        $this->addModal = true;
    }

    public function selectBook(int $id): void
    {
        $this->selectedBookId = $id;
        $this->isCustom = false;
    }

    public function toggleCustom(): void
    {
        $this->isCustom = true;
        $this->selectedBookId = null;
        $this->customTitle = $this->search;
    }

    public function addCopy(): void
    {
        if ($this->isCustom) {
            $this->validate([
                'customTitle' => 'required|string|max:255',
                'customAuthor' => 'nullable|string|max:255',
                'customCategoryId' => 'nullable|exists:book_categories,id',
                'customPagesCount' => 'nullable|integer|min:1',
                'customCoverFile' => 'nullable|image|max:2048',
                'condition' => 'required|string',
                'is_borrowable' => 'boolean',
            ]);

            $authorId = null;
            if ($this->customAuthor) {
                $author = Author::firstOrCreate(['name' => $this->customAuthor]);
                $authorId = $author->id;
            }

            $book = Book::create([
                'title' => $this->customTitle,
                'slug' => Str::slug($this->customTitle.'-'.uniqid()),
                'author_id' => $authorId,
                'book_category_id' => $this->customCategoryId,
                'pages_count' => $this->customPagesCount,
                'type' => 'physical',
                'status' => 'approved',
                'uploaded_by' => auth()->id(),
            ]);

            if ($this->customCoverFile) {
                $extension = $this->customCoverFile->getClientOriginalExtension() ?: 'jpg';
                $book->addMedia($this->customCoverFile)
                    ->usingFileName('cover_' . now()->timestamp . '_' . uniqid() . '.' . $extension)
                    ->toMediaCollection('cover');
            }

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

        if ($this->giftToHubId) {
            BookCopy::create([
                'book_id' => $bookId,
                'library_hub_id' => $this->giftToHubId,
                'added_by' => auth()->id(),
                'status' => 'available',
                'is_borrowable' => true,
                'condition' => $this->condition,
            ]);
            $this->success('Thank you! Book successfully gifted to the hub.');
        } else {
            BookCopy::create([
                'book_id' => $bookId,
                'owner_id' => auth()->id(),
                'added_by' => auth()->id(),
                'status' => 'available',
                'is_borrowable' => $this->is_borrowable,
                'condition' => $this->condition,
            ]);
            $this->success('Book successfully added to your shelf!');
        }

        $this->addModal = false;
        unset($this->myCopies);
    }

    public function toggleBorrowable(int $id): void
    {
        $copy = BookCopy::where('owner_id', auth()->id())->findOrFail($id);
        $copy->update(['is_borrowable' => !$copy->is_borrowable]);
        unset($this->myCopies);
        $this->success('Lending status updated.');
    }

    public function deleteCopy(int $id): void
    {
        $copy = BookCopy::where('owner_id', auth()->id())->findOrFail($id);

        if ($copy->status === 'borrowed') {
            $this->error('Cannot remove a book that is currently borrowed out.');

            return;
        }

        $copy->delete();
        $this->warning('Removed from your shelf.');
        unset($this->myCopies);
    }

    // --- Personal Reading Shelf Actions ---
    public function openAddReadingModal(): void
    {
        $this->searchCatalog = '';
        $this->addReadingModal = true;
    }

    public function addReadingStatus(int $bookId, string $status): void
    {
        $book = Book::findOrFail($bookId);

        $interaction = BookUserInteraction::updateOrCreate(
            ['user_id' => auth()->id(), 'book_id' => $bookId],
            [
                'reading_status' => $status,
                'pages_read' => $status === 'completed' && $book->pages_count ? $book->pages_count : 0,
            ]
        );

        $this->success('Book added to your reading shelf.');
        $this->addReadingModal = false;
        unset($this->myInteractions);
    }

    public function openProgressModal(int $interactionId): void
    {
        $interaction = BookUserInteraction::where('user_id', auth()->id())->findOrFail($interactionId);
        $this->selectedInteractionId = $interactionId;
        $this->progressPagesRead = $interaction->pages_read ?? 0;
        $this->progressRating = $interaction->rating;
        $this->progressReview = $interaction->review ?? '';
        $this->progressModal = true;
    }

    public function saveProgress(): void
    {
        $interaction = BookUserInteraction::with('book')->where('user_id', auth()->id())->findOrFail($this->selectedInteractionId);

        $this->validate([
            'progressPagesRead' => 'integer|min:0|nullable',
            'progressRating' => 'integer|min:1|max:5|nullable',
            'progressReview' => 'string|max:1000|nullable',
        ]);

        $maxPages = $interaction->book->pages_count ?? 999999;
        if ($this->progressPagesRead > $maxPages) {
            $this->progressPagesRead = $maxPages;
        }

        $isCompleted = ($this->readingSubTab === 'completed') || ($maxPages > 0 && $this->progressPagesRead === $maxPages);

        $interaction->update([
            'pages_read' => $this->progressPagesRead,
            'rating' => $this->progressRating,
            'review' => $this->progressReview ?: null,
            'reading_status' => $isCompleted ? 'completed' : $interaction->reading_status,
        ]);

        $this->success('Reading progress updated.');
        $this->progressModal = false;
        unset($this->myInteractions);
    }

    public function removeInteraction(int $interactionId): void
    {
        $interaction = BookUserInteraction::where('user_id', auth()->id())->findOrFail($interactionId);
        $interaction->delete();
        $this->info('Removed from reading shelf.');
        unset($this->myInteractions);
    }

    // --- Lend / Borrow Requests Actions ---

    public function acceptRequest(int $id): void
    {
        $req = BorrowRequest::with(['borrower', 'bookCopy.book'])->whereHas('bookCopy', fn ($q) => $q->where('owner_id', auth()->id()))->findOrFail($id);
        if ($req->status !== 'pending') {
            return;
        }

        // Auto-reject other pending requests for this same copy
        BorrowRequest::where('book_copy_id', $req->book_copy_id)
            ->where('id', '!=', $req->id)
            ->where('status', 'pending')
            ->update(['status' => 'rejected']);

        $req->update(['status' => 'accepted']);

        if ($req->borrower) {
            $req->borrower->notify(new \App\Notifications\BookNotification(
                'accepted',
                auth()->user()->name,
                $req->bookCopy->book->title,
                route('web.my-books')
            ));
        }

        $this->success('Request accepted! Please arrange to hand over the physical book.');
        unset($this->incomingRequests);
    }

    // Owner rejects incoming request
    public function rejectRequest(int $id): void
    {
        $req = BorrowRequest::whereHas('bookCopy', fn ($q) => $q->where('owner_id', auth()->id()))->findOrFail($id);
        if ($req->status !== 'pending') {
            return;
        }

        $req->update(['status' => 'rejected']);
        $this->info('Request rejected.');
        unset($this->incomingRequests);
    }

    // Owner marks copy as handed over/given
    public function markGiven(int $id): void
    {
        $req = BorrowRequest::with(['borrower', 'bookCopy.book'])->whereHas('bookCopy', fn ($q) => $q->where('owner_id', auth()->id()))->findOrFail($id);
        if ($req->status !== 'accepted') {
            return;
        }

        $req->update(['status' => 'given']);

        if ($req->borrower) {
            $req->borrower->notify(new \App\Notifications\BookNotification(
                'given',
                auth()->user()->name,
                $req->bookCopy->book->title,
                route('web.my-books')
            ));
        }

        $this->success('Marked as given. Waiting for borrower to confirm receipt.');
        unset($this->incomingRequests);
    }

    // Owner confirms copy returned
    public function confirmReturned(int $id): void
    {
        $req = BorrowRequest::with('bookCopy')->whereHas('bookCopy', fn ($q) => $q->where('owner_id', auth()->id()))->findOrFail($id);
        if (! in_array($req->status, ['active', 'given'])) {
            return;
        }

        $req->update([
            'status' => 'returned',
            'returned_at' => now(),
        ]);

        $req->bookCopy->update(['status' => 'available']);

        $this->success('Book returned successfully to your shelf!');
        unset($this->incomingRequests);
    }

    // Owner sends return reminder to borrower
    public function sendReminder(int $id): void
    {
        $req = BorrowRequest::with(['borrower', 'bookCopy.owner', 'bookCopy.book'])->whereHas('bookCopy', fn ($q) => $q->where('owner_id', auth()->id()))->findOrFail($id);
        if ($req->status !== 'active') {
            return;
        }

        if ($req->borrower) {
            $req->borrower->notify(new \App\Notifications\BookNotification(
                'reminder',
                auth()->user()->name,
                $req->bookCopy->book->title,
                route('web.my-books')
            ));
        }

        $this->success('Return reminder sent to the borrower.');
    }

    public function confirmReceived(int $id): void
    {
        $req = BorrowRequest::with(['bookCopy.owner', 'bookCopy.book'])->where('borrower_id', auth()->id())->findOrFail($id);
        if ($req->status !== 'given') {
            return;
        }

        $req->update([
            'status' => 'active',
            'due_date' => now()->addDays($req->requested_days),
        ]);

        $req->bookCopy->update(['status' => 'borrowed']);

        if ($req->bookCopy && $req->bookCopy->owner) {
            $req->bookCopy->owner->notify(new \App\Notifications\BookNotification(
                'received',
                auth()->user()->name,
                $req->bookCopy->book->title,
                route('web.my-books')
            ));
        }

        $this->success('Book received! Due date set.');
        unset($this->outgoingRequests);
    }

    // Borrower cancels their request
    public function cancelRequest(int $id): void
    {
        $req = BorrowRequest::where('borrower_id', auth()->id())->findOrFail($id);
        if (in_array($req->status, ['pending', 'accepted'])) {
            $req->delete();
            $this->warning('Request cancelled.');
            unset($this->outgoingRequests);
        }
    }
};
