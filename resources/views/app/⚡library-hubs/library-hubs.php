<?php

namespace App\Livewire;

use App\Models\Book;
use App\Models\BookCopy;
use App\Models\BorrowRequest;
use App\Models\LibraryHub;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

new #[Title('Community Library Hubs')] #[Layout('layouts.app')] class extends Component
{
    use Toast;

    public string $activeTab = 'hubs'; // hubs, requests

    // Hub CRUD
    public bool $hubModal = false;
    public ?int $editingHubId = null;
    public string $hubName = '';
    public string $hubLocation = '';
    public ?int $hubManagerId = null;
    public bool $hubIsActive = true;

    // Requests Tab
    public string $requestsSubTab = 'incoming'; // incoming, history

    // Add Books to Hub
    public bool $addBooksModal = false;
    public ?int $addBooksHubId = null;
    public string $bookSearch = '';
    public array $selectedBookIds = [];
    public string $singleCondition = 'Good';

    public function mount()
    {
        $this->authorize('library.hubs.manage');
    }

    // --- Data Sources ---

    #[Computed]
    public function hubs(): Collection
    {
        $query = LibraryHub::with(['manager', 'bookCopies.book'])->withCount('bookCopies');
        
        // If not admin, only show hubs they manage
        if (!auth()->user()->can('library.hubs.create')) {
            $query->where('manager_id', auth()->id());
        }

        return $query->get();
    }

    #[Computed]
    public function potentialManagers(): Collection
    {
        return User::permission('library.hubs.manage')->orderBy('name')->get();
    }

    #[Computed]
    public function catalogBooks(): Collection
    {
        if (strlen($this->bookSearch) < 2) {
            return collect();
        }

        return Book::with(['author'])
            ->where('status', 'approved')
            ->where(function ($q) {
                $q->where('title', 'like', "%{$this->bookSearch}%")
                    ->orWhereHas('author', fn ($a) => $a->where('name', 'like', "%{$this->bookSearch}%"));
            })
            ->limit(20)
            ->get();
    }

    #[Computed]
    public function borrowRequests(): Collection
    {
        $hubIds = $this->hubs->pluck('id')->toArray();

        return BorrowRequest::with(['borrower', 'bookCopy.book', 'bookCopy.libraryHub'])
            ->whereHas('bookCopy', function ($q) use ($hubIds) {
                $q->whereIn('library_hub_id', $hubIds);
            })
            ->when($this->requestsSubTab === 'incoming', function ($q) {
                $q->whereIn('status', ['pending', 'accepted', 'given', 'active']);
            })
            ->when($this->requestsSubTab === 'history', function ($q) {
                $q->whereIn('status', ['returned', 'rejected', 'lost']);
            })
            ->latest('updated_at')
            ->get();
    }

    // --- UI Actions ---
    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    // --- Add Books to Hub ---
    public function openAddBooksModal(int $hubId): void
    {
        $this->addBooksHubId = $hubId;
        $this->bookSearch = '';
        $this->selectedBookIds = [];
        $this->singleCondition = 'Good';
        $this->addBooksModal = true;
    }

    public function toggleBookSelection(int $bookId): void
    {
        if (in_array($bookId, $this->selectedBookIds)) {
            $this->selectedBookIds = array_values(array_diff($this->selectedBookIds, [$bookId]));
        } else {
            $this->selectedBookIds[] = $bookId;
        }
    }

    public function saveBooksToHub(): void
    {
        $this->validate([
            'selectedBookIds' => 'required|array|min:1',
            'singleCondition' => 'required|string',
        ]);

        $hub = LibraryHub::findOrFail($this->addBooksHubId);
        
        // Ensure permission
        if (!auth()->user()->can('library.hubs.create') && $hub->manager_id !== auth()->id()) {
            abort(403);
        }

        foreach ($this->selectedBookIds as $bookId) {
            $book = Book::find($bookId);
            if ($book && $book->type === 'ebook') {
                $book->update(['type' => 'both']);
            }

            BookCopy::create([
                'book_id' => $bookId,
                'library_hub_id' => $hub->id,
                'added_by' => auth()->id(),
                'status' => 'available',
                'is_borrowable' => true,
                'condition' => count($this->selectedBookIds) === 1 ? $this->singleCondition : 'Good',
            ]);
        }

        $this->success(__(':count book(s) added to :name inventory.', ['count' => count($this->selectedBookIds), 'name' => $hub->name]));
        $this->addBooksModal = false;
        unset($this->hubs);
    }

    // --- Hub CRUD ---

    public function openHubModal(?int $id = null): void
    {
        $this->resetValidation();
        if ($id) {
            $hub = LibraryHub::findOrFail($id);
            $this->editingHubId = $hub->id;
            $this->hubName = $hub->name;
            $this->hubLocation = $hub->location ?? '';
            $this->hubManagerId = $hub->manager_id;
            $this->hubIsActive = $hub->is_active;
        } else {
            $this->editingHubId = null;
            $this->hubName = '';
            $this->hubLocation = '';
            $this->hubManagerId = auth()->id();
            $this->hubIsActive = true;
        }
        $this->hubModal = true;
    }

    public function saveHub(): void
    {
        $this->authorize('library.hubs.create');

        $this->validate([
            'hubName' => 'required|string|max:255',
            'hubLocation' => 'nullable|string|max:255',
            'hubManagerId' => 'nullable|exists:users,id',
        ]);

        LibraryHub::updateOrCreate(
            ['id' => $this->editingHubId],
            [
                'name' => $this->hubName,
                'location' => $this->hubLocation,
                'manager_id' => $this->hubManagerId,
                'is_active' => $this->hubIsActive,
            ]
        );

        $this->success(__('Hub saved successfully.'));
        $this->hubModal = false;
        unset($this->hubs);
    }

    public function toggleHubStatus(int $id): void
    {
        $this->authorize('library.hubs.create');
        $hub = LibraryHub::findOrFail($id);
        $hub->update(['is_active' => !$hub->is_active]);
        $this->success(__('Hub status updated.'));
        unset($this->hubs);
    }

    // --- Borrow Request Management ---

    public function acceptRequest(int $id): void
    {
        $req = $this->getAuthorizedRequest($id);
        if ($req->status !== 'pending') return;

        // Reject other pending requests for same copy
        BorrowRequest::where('book_copy_id', $req->book_copy_id)
            ->where('id', '!=', $req->id)
            ->where('status', 'pending')
            ->update(['status' => 'rejected']);

        $req->update(['status' => 'accepted']);
        $this->notifyBorrower($req, 'accepted');
        
        $this->success(__('Request accepted. Please arrange for pickup at the Hub.'));
        unset($this->borrowRequests);
    }

    public function rejectRequest(int $id): void
    {
        $req = $this->getAuthorizedRequest($id);
        if ($req->status !== 'pending') return;

        $req->update(['status' => 'rejected']);
        $this->info(__('Request rejected.'));
        unset($this->borrowRequests);
    }

    public function markGiven(int $id): void
    {
        $req = $this->getAuthorizedRequest($id);
        if ($req->status !== 'accepted') return;

        $req->update(['status' => 'given']);
        $this->notifyBorrower($req, 'given');

        $this->success(__('Marked as given to borrower.'));
        unset($this->borrowRequests);
    }

    public function confirmReturned(int $id): void
    {
        $req = $this->getAuthorizedRequest($id);
        if (!in_array($req->status, ['active', 'given'])) return;

        $req->update([
            'status' => 'returned',
            'returned_at' => now(),
        ]);
        $req->bookCopy->update(['status' => 'available']);

        $this->success(__('Book returned to the Hub inventory!'));
        unset($this->borrowRequests);
    }

    public function sendReminder(int $id): void
    {
        $req = $this->getAuthorizedRequest($id);
        if ($req->status !== 'active') return;

        $this->notifyBorrower($req, 'reminder');
        $this->success(__('Return reminder sent to the borrower.'));
    }

    private function getAuthorizedRequest(int $id)
    {
        $hubIds = $this->hubs->pluck('id')->toArray();
        return BorrowRequest::with(['borrower', 'bookCopy.book'])
            ->whereHas('bookCopy', fn ($q) => $q->whereIn('library_hub_id', $hubIds))
            ->findOrFail($id);
    }

    private function notifyBorrower($req, string $action)
    {
        if ($req->borrower) {
            $req->borrower->notify(new \App\Notifications\BookNotification(
                $action,
                $req->bookCopy->libraryHub->name ?? 'Library Hub',
                $req->bookCopy->book->title,
                route('web.my-books')
            ));
        }
    }
};
