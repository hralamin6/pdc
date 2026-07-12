<?php

use App\Models\Donation;
use App\Notifications\DonationStatusNotification;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

new #[Title('Verify Payments')] #[Layout('layouts.app')] class extends Component
{
    use Toast, WithPagination;

    // Search/Filters
    public string $search = '';
    public string $methodFilter = '';

    // Slide-over Drawer State (Option 1.A)
    public bool $verifyDrawer = false;
    public ?int $selectedDonationId = null;

    // Rejection Modal State (Option 2.A)
    public bool $rejectionModal = false;
    public string $rejectionNote = '';

    public function mount(): void
    {
        if (!auth()->user() || !auth()->user()->can('donations.verify')) {
            abort(403);
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedMethodFilter(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function selectedDonation(): ?Donation
    {
        if (!$this->selectedDonationId) {
            return null;
        }
        return Donation::with(['user', 'bankAccount', 'campaign', 'halaqah'])->find($this->selectedDonationId);
    }

    public function selectDonation(int $id): void
    {
        $this->selectedDonationId = $id;
        $this->verifyDrawer = true;
    }

    public function approveDonation(): void
    {
        $donation = $this->selectedDonation;
        if (!$donation || $donation->status !== 'pending') {
            $this->error(__('Invalid or already processed donation.'));
            return;
        }

        $donation->update([
            'status' => 'confirmed',
            'donated_at' => $donation->donated_at ?? now(),
            'collected_by' => auth()->id(),
        ]);

        // Send notification (Option 3.B)
        if ($donation->user) {
            try {
                $donation->user->notify(new DonationStatusNotification($donation, 'confirmed'));
            } catch (\Exception $e) {
                dd($e->getMessage(), $e->getTraceAsString());
                // Log and continue if mail server isn't set up
                logger()->error('Failed sending donation confirmation notification: ' . $e->getMessage());
            }
        }

        $this->verifyDrawer = false;
        $this->selectedDonationId = null;
        $this->success(__('Payment verified and donation confirmed!'));
    }

    public function openRejectionModal(): void
    {
        $this->rejectionNote = '';
        $this->rejectionModal = true;
    }

    public function rejectDonation(): void
    {
        $this->validate([
            'rejectionNote' => 'required|string|min:5|max:500',
        ]);

        $donation = $this->selectedDonation;
        if (!$donation || $donation->status !== 'pending') {
            $this->error(__('Invalid or already processed donation.'));
            return;
        }

        $donation->update([
            'status' => 'rejected',
            'note' => $this->rejectionNote,
            'collected_by' => auth()->id(),
        ]);

        // Send notification (Option 3.B)
        if ($donation->user) {
            try {
                $donation->user->notify(new DonationStatusNotification($donation, 'rejected', $this->rejectionNote));
            } catch (\Exception $e) {
                // Log and continue
                logger()->error('Failed sending donation rejection notification: ' . $e->getMessage());
            }
        }

        $this->rejectionModal = false;
        $this->verifyDrawer = false;
        $this->selectedDonationId = null;
        $this->success(__('Payment rejected. Rejection reason has been logged.'));
    }

    public function with(): array
    {
        $query = Donation::with(['user', 'bankAccount', 'campaign'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc');

        if ($this->search) {
            $query->whereHas('user', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->methodFilter) {
            $query->where('payment_method', $this->methodFilter);
        }

        return [
            'pendingDonations' => $query->paginate(15),
            'stats' => [
                'total_pending_count' => Donation::where('status', 'pending')->count(),
                'total_pending_amount' => Donation::where('status', 'pending')->sum('amount'),
            ]
        ];
    }
};
