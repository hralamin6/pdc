<?php

namespace App\Livewire\Web;

use App\Models\Donation;
use App\Models\DonationPledge;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

new #[Layout('layouts.web')] class extends Component
{
    use WithPagination, Toast;

    public bool $pledgeModal = false;

    // Pledge Form
    #[Validate('required|numeric|min:10')]
    public $pledgeAmount = null;

    #[Validate('required|in:weekly,monthly,yearly')]
    public string $pledgeFrequency = 'monthly';

    public bool $isAnonymous = false;

    public function mount(): void
    {
        if (!auth()->check()) {
            $this->redirectRoute('login');
        }
    }

    public function title(): string
    {
        return __('My Donations');
    }

    #[Computed]
    public function stats(): array
    {
        $userId = auth()->id();
        
        $totalDonated = Donation::where('user_id', $userId)
            ->where('status', 'confirmed')
            ->sum('amount');
            
        $ytdDonated = Donation::where('user_id', $userId)
            ->where('status', 'confirmed')
            ->whereYear('donated_at', now()->year)
            ->sum('amount');
            
        $activePledgesCount = DonationPledge::where('user_id', $userId)
            ->where('is_active', true)
            ->count();

        return [
            'total' => $totalDonated,
            'ytd' => $ytdDonated,
            'active_pledges' => $activePledgesCount,
        ];
    }

    #[Computed]
    public function activePledges()
    {
        return DonationPledge::where('user_id', auth()->id())
            ->where('is_active', true)
            ->latest()
            ->get();
    }

    #[Computed]
    public function donationHistory()
    {
        return Donation::where('user_id', auth()->id())
            ->with(['campaign', 'halaqah'])
            ->latest('donated_at')
            ->paginate(10);
    }

    #[Computed]
    public function activeBankAccounts()
    {
        return \App\Models\BankAccount::where('is_active', true)->get();
    }

    #[Computed]
    public function filteredBankAccounts()
    {
        if (!$this->paymentMethod) return collect();
        return $this->activeBankAccounts->where('type', $this->paymentMethod);
    }

    // Chart data for last 6 months
    #[Computed]
    public function chartData(): array
    {
        $months = [];
        $amounts = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = $date->format('M');
            
            $sum = Donation::where('user_id', auth()->id())
                ->where('status', 'confirmed')
                ->whereYear('donated_at', $date->year)
                ->whereMonth('donated_at', $date->month)
                ->sum('amount');
                
            $amounts[] = (float) $sum;
        }

        return [
            'labels' => $months,
            'data' => $amounts,
        ];
    }

    public function openPledgeModal(): void
    {
        $this->pledgeAmount = null;
        $this->pledgeFrequency = 'monthly';
        $this->pledgeModal = true;
    }

    public function savePledge(): void
    {
        $this->validate([
            'pledgeAmount' => 'required|numeric|min:10',
            'pledgeFrequency' => 'required|in:weekly,monthly,yearly',
        ]);

        $existing = DonationPledge::where('user_id', auth()->id())
            ->where('frequency', $this->pledgeFrequency)
            ->where('is_active', true)
            ->exists();

        if ($existing) {
            $this->addError('pledgeFrequency', __('You already have an active :frequency pledge.', ['frequency' => $this->pledgeFrequency]));
            return;
        }

        $pledge = DonationPledge::create([
            'user_id' => auth()->id(),
            'amount' => $this->pledgeAmount,
            'frequency' => $this->pledgeFrequency,
            'starts_at' => now(),
            'is_active' => true,
        ]);
        
        // Immediately create the first invoice
        Donation::create([
            'user_id' => $pledge->user_id,
            'type' => 'recurring',
            'amount' => $pledge->amount,
            'currency' => $pledge->currency ?? 'BDT',
            'status' => 'pending_payment',
            'note' => 'Initial pledge payment',
        ]);

        $this->pledgeModal = false;
        $this->reset(['pledgeAmount', 'pledgeFrequency']);
        
        $this->success(__('Your recurring donation pledge has been created and your first invoice is ready. May Allah reward you!'));
    }

    public function cancelPledge(int $pledgeId): void
    {
        $pledge = DonationPledge::where('user_id', auth()->id())->findOrFail($pledgeId);
        $pledge->update([
            'is_active' => false,
            'ends_at' => now(),
        ]);

        $this->success(__('Your pledge has been cancelled.'));
    }

    // Fulfill Payment Logic
    public bool $fulfillModal = false;
    public ?int $fulfillingDonationId = null;
    
    #[Validate('required|in:cash,bkash,nagad,bank,other')]
    public string $paymentMethod = '';
    
    #[Validate('required')]
    public ?int $bankAccountId = null;
    
    public ?string $transactionId = null;
    public ?string $paymentNote = null;

    public function openFulfillModal(int $donationId): void
    {
        $donation = Donation::where('user_id', auth()->id())
            ->where('status', 'pending_payment')
            ->findOrFail($donationId);

        $this->fulfillingDonationId = $donation->id;
        $this->paymentMethod = '';
        $this->bankAccountId = null;
        $this->transactionId = null;
        $this->paymentNote = null;
        $this->fulfillModal = true;
    }

    public function submitFulfillment(): void
    {
        $this->validateOnly('paymentMethod');
        
        if ($this->filteredBankAccounts->isNotEmpty()) {
            $this->validateOnly('bankAccountId');
        } else {
            $this->bankAccountId = null;
        }

        if (in_array($this->paymentMethod, ['bkash', 'nagad', 'bank']) && empty($this->transactionId)) {
            $this->addError('transactionId', __('Transaction ID is required for verification.'));
            return;
        }

        $donation = Donation::where('user_id', auth()->id())
            ->where('status', 'pending_payment')
            ->findOrFail($this->fulfillingDonationId);

        $donation->update([
            'payment_method' => $this->paymentMethod,
            'bank_account_id' => $this->bankAccountId,
            'transaction_id' => $this->transactionId,
            'note' => $this->paymentNote,
            'status' => 'pending', // Now pending treasurer approval
            'donated_at' => now(),
        ]);

        // If it was a recurring donation, update the pledge's last_donated_at
        if ($donation->type === 'recurring') {
            DonationPledge::where('user_id', auth()->id())
                ->where('amount', $donation->amount)
                ->where('currency', $donation->currency)
                ->where('is_active', true)
                ->update(['last_donated_at' => now()]);
        }

        $this->fulfillModal = false;
        $this->success(__('Your payment has been recorded and is pending confirmation by the treasurer. JazakAllah Khair!'));
    }
};