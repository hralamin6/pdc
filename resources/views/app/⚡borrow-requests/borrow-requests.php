<?php

use App\Models\BookCopy;
use App\Models\BorrowRequest;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

new #[Title('Borrow Requests')] #[Layout('layouts.app')] class extends Component
{
    use Toast;

    public string $activeTab = 'incoming'; // incoming (others want my books), outgoing (I want others' books)

    #[\Livewire\Attributes\Computed]
    public function incomingRequests()
    {
        return BorrowRequest::with(['borrower', 'bookCopy.book'])
            ->whereHas('bookCopy', function ($q) {
                $q->where('owner_id', auth()->id());
            })
            ->latest('updated_at')
            ->get();
    }

    #[\Livewire\Attributes\Computed]
    public function outgoingRequests()
    {
        return BorrowRequest::with(['bookCopy.owner', 'bookCopy.book'])
            ->where('borrower_id', auth()->id())
            ->latest('updated_at')
            ->get();
    }

    public function switchTab($tab)
    {
        $this->activeTab = $tab;
    }

    // --- OWNER ACTIONS ---

    public function acceptRequest($id)
    {
        $req = BorrowRequest::whereHas('bookCopy', fn($q) => $q->where('owner_id', auth()->id()))->findOrFail($id);
        if ($req->status !== 'pending') return;

        // Auto-reject other pending requests for this same copy
        BorrowRequest::where('book_copy_id', $req->book_copy_id)
            ->where('id', '!=', $req->id)
            ->where('status', 'pending')
            ->update(['status' => 'rejected']);

        $req->update(['status' => 'accepted']);
        $this->success('Request accepted. Please physically hand over the book.');
        unset($this->incomingRequests);
    }

    public function rejectRequest($id)
    {
        $req = BorrowRequest::whereHas('bookCopy', fn($q) => $q->where('owner_id', auth()->id()))->findOrFail($id);
        if ($req->status !== 'pending') return;

        $req->update(['status' => 'rejected']);
        $this->info('Request rejected.');
        unset($this->incomingRequests);
    }

    public function markGiven($id)
    {
        $req = BorrowRequest::whereHas('bookCopy', fn($q) => $q->where('owner_id', auth()->id()))->findOrFail($id);
        if ($req->status !== 'accepted') return;

        $req->update(['status' => 'given']);
        $this->success('Marked as given. Waiting for borrower to confirm receipt.');
        unset($this->incomingRequests);
    }
    
    public function confirmReturned($id)
    {
        $req = BorrowRequest::whereHas('bookCopy', fn($q) => $q->where('owner_id', auth()->id()))->findOrFail($id);
        if ($req->status !== 'active') return; // Technically it could be overdue too

        $req->update([
            'status' => 'returned',
            'returned_at' => now(),
        ]);
        
        $req->bookCopy->update(['status' => 'available']);

        $this->success('Book successfully returned to your shelf!');
        unset($this->incomingRequests);
    }

    // --- BORROWER ACTIONS ---

    public function confirmReceived($id)
    {
        $req = BorrowRequest::where('borrower_id', auth()->id())->findOrFail($id);
        if ($req->status !== 'given') return;

        $req->update([
            'status' => 'active',
            'due_date' => now()->addDays($req->requested_days),
        ]);
        
        $req->bookCopy->update(['status' => 'borrowed']);

        $this->success('Book received! Due date has been set.');
        unset($this->outgoingRequests);
    }

    public function cancelRequest($id)
    {
        $req = BorrowRequest::where('borrower_id', auth()->id())->findOrFail($id);
        if (in_array($req->status, ['pending', 'accepted'])) {
            $req->delete();
            $this->warning('Request cancelled.');
            unset($this->outgoingRequests);
        }
    }
};
