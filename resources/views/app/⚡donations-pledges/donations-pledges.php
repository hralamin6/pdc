<?php

use App\Models\DonationPledge;
use App\Models\Donation;
use App\Notifications\DonationPledgeReminderNotification;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

new #[Title('Pledges Management')] #[Layout('layouts.app')] class extends Component
{
    use Toast, WithPagination;

    public string $activeTab = 'all'; // 'all' or 'overdue'
    public string $search = '';
    public string $statusFilter = ''; // '1' for active, '0' for paused
    public string $frequencyFilter = ''; // 'weekly', 'monthly', 'yearly'

    public function mount(): void
    {
        if (!auth()->user() || !auth()->user()->can('donations.pledges.manage')) {
            abort(403);
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedFrequencyFilter(): void
    {
        $this->resetPage();
    }
    
    public function updatedActiveTab(): void
    {
        $this->resetPage();
    }

    // Stats Counters
    #[Computed]
    public function stats(): array
    {
        $activePledgesQuery = DonationPledge::where('is_active', true);
        $activeCount = $activePledgesQuery->count();

        // Calculate Monthly Recurring Revenue (MRR)
        $mrr = 0;
        $activePledges = $activePledgesQuery->get();
        foreach ($activePledges as $pledge) {
            $amount = (float) $pledge->amount;
            if ($pledge->frequency === 'weekly') {
                $mrr += $amount * 4.33;
            } elseif ($pledge->frequency === 'monthly') {
                $mrr += $amount;
            } elseif ($pledge->frequency === 'yearly') {
                $mrr += $amount / 12;
            }
        }

        $overdueCount = DonationPledge::where('is_active', true)
            ->where('next_due_at', '<', now())
            ->count();

        // Collection Rate in last 30 days
        $lastMonth = now()->subDays(30);
        $totalRecurringGenerated = Donation::where('type', 'recurring')
            ->where('created_at', '>=', $lastMonth)
            ->count();
        $confirmedRecurring = Donation::where('type', 'recurring')
            ->where('status', 'confirmed')
            ->where('created_at', '>=', $lastMonth)
            ->count();
        
        $collectionRate = $totalRecurringGenerated > 0 
            ? round(($confirmedRecurring / $totalRecurringGenerated) * 100) 
            : 100;

        return [
            'active_count' => $activeCount,
            'mrr' => $mrr,
            'overdue_count' => $overdueCount,
            'collection_rate' => $collectionRate,
        ];
    }

    public function with(): array
    {
        $query = DonationPledge::with('user')->orderBy('created_at', 'desc');

        if ($this->activeTab === 'overdue') {
            $query->where('is_active', true)
                  ->where('next_due_at', '<', now());
        }

        if ($this->search) {
            $query->whereHas('user', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->statusFilter !== '') {
            $query->where('is_active', $this->statusFilter === '1');
        }

        if ($this->frequencyFilter) {
            $query->where('frequency', $this->frequencyFilter);
        }

        return [
            'pledges' => $query->paginate(15),
        ];
    }

    public function togglePledgeStatus(int $id): void
    {
        $pledge = DonationPledge::findOrFail($id);
        $pledge->update(['is_active' => !$pledge->is_active]);
        
        $statusStr = $pledge->is_active ? __('activated') : __('paused');
        $this->success(__("Pledge has been successfully :status.", ['status' => $statusStr]));
    }

    public function sendReminder(int $id): void
    {
        $pledge = DonationPledge::with('user')->findOrFail($id);
        
        if ($pledge->user) {
            try {
                $pledge->user->notify(new DonationPledgeReminderNotification($pledge));
                $this->success(__('Reminder notification sent successfully.'));
            } catch (\Exception $e) {
                logger()->error('Failed sending pledge reminder notification: ' . $e->getMessage());
                $this->error(__('Failed to send reminder.'));
            }
        }
    }
};
