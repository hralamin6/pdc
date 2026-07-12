<?php

use App\Models\DonationPledge;
use App\Models\Donation;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

new #[Title('Pledge Details')] #[Layout('layouts.app')] class extends Component
{
    use Toast, WithPagination;

    public DonationPledge $pledge;

    // Edit State
    public bool $editModal = false;
    public string $amount = '';
    public string $frequency = '';
    public bool $isActive = true;

    public function mount(DonationPledge $pledge): void
    {
        if (!auth()->user() || !auth()->user()->can('donations.pledges.manage')) {
            abort(403);
        }

        $this->pledge = $pledge;
    }

    public function getTitle(): string
    {
        return __('Pledge Details: :name', ['name' => $this->pledge->user->name ?? __('Unknown')]);
    }

    public function openEditModal(): void
    {
        $this->amount = (string) $this->pledge->amount;
        $this->frequency = $this->pledge->frequency;
        $this->isActive = $this->pledge->is_active;
        $this->editModal = true;
    }

    public function savePledge(): void
    {
        $this->validate([
            'amount' => 'required|numeric|min:10',
            'frequency' => 'required|in:weekly,monthly,yearly',
            'isActive' => 'required|boolean',
        ]);

        // If frequency is changed, recalculate next_due_at to avoid double charging
        $nextDue = $this->pledge->next_due_at;
        if ($this->frequency !== $this->pledge->frequency) {
            $nextDue = now();
            if ($this->frequency === 'weekly') $nextDue->addWeek();
            if ($this->frequency === 'monthly') $nextDue->addMonth();
            if ($this->frequency === 'yearly') $nextDue->addYear();
        }

        $this->pledge->update([
            'amount' => $this->amount,
            'frequency' => $this->frequency,
            'is_active' => $this->isActive,
            'next_due_at' => $nextDue,
        ]);

        $this->editModal = false;
        $this->success(__('Pledge updated successfully.'));
    }

    public function with(): array
    {
        // Fetch all recurring payments generated for this user
        $payments = Donation::with('user')
            ->where('user_id', $this->pledge->user_id)
            ->where('type', 'recurring')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return [
            'payments' => $payments,
        ];
    }
};
