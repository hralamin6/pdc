<?php

use App\Models\Donation;
use App\Models\DonationCampaign;
use App\Models\Halaqah;
use App\Models\User;
use App\Notifications\DonationStatusNotification;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;
use Symfony\Component\HttpFoundation\StreamedResponse;

new #[Title('Transaction Ledger')] #[Layout('layouts.app')] class extends Component
{
    use Toast, WithPagination;

    public string $activeTab = 'ledger'; // 'ledger', 'manual_entry'

    // Filtering State
    public string $search = '';
    public string $typeFilter = ''; // 'general', 'campaign', 'halaqah', 'recurring'
    public string $statusFilter = ''; // 'pending', 'confirmed', 'rejected', 'pending_payment'
    public string $methodFilter = ''; // 'cash', 'bkash', 'bank', etc.
    public string $dateFilter = ''; // 'today', 'this_week', 'this_month', 'all'

    // Manual Entry State
    public ?int $selectedUserId = null;
    public string $amount = '';
    public string $currency = 'BDT';
    public string $type = 'general';
    public ?int $campaignId = null;
    public ?int $halaqahId = null;
    public string $paymentMethod = 'cash';
    public ?int $bank_account_id = null;
    public string $transactionId = '';
    public string $note = '';
    public bool $isAnonymous = false;
    public bool $sendReceipt = true;
    public string $donatedAt = '';

    // Search Users for Manual Entry
    public array $usersForEntry = [];

    public function mount(): void
    {
        if (!auth()->user() || !auth()->user()->can('donations.transactions.manage')) {
            abort(403);
        }
        
        $this->donatedAt = now()->format('Y-m-d\TH:i');
        $this->searchUsers('');
    }

    public function searchUsers(string $value = ''): void
    {
        $this->usersForEntry = User::where('name', 'like', "%{$value}%")
            ->orWhere('email', 'like', "%{$value}%")
            ->limit(20)
            ->get()
            ->toArray();
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'typeFilter', 'statusFilter', 'methodFilter', 'dateFilter', 'activeTab'])) {
            $this->resetPage();
        }
    }


    #[Computed]
    public function activeCampaigns(): \Illuminate\Database\Eloquent\Collection
    {
        return DonationCampaign::where('status', 'active')->get();
    }

    #[Computed]
    public function recentHalaqahs(): \Illuminate\Database\Eloquent\Collection
    {
        return Halaqah::orderBy('start_time', 'desc')->limit(10)->get();
    }

    private function buildQuery(): Builder
    {
        $query = Donation::with(['user', 'campaign', 'halaqah'])->orderBy('created_at', 'desc');

        if ($this->search) {
            $query->where(function($q) {
                $q->where('transaction_id', 'like', "%{$this->search}%")
                  ->orWhere('note', 'like', "%{$this->search}%")
                  ->orWhereHas('user', function($u) {
                      $u->where('name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%");
                  });
            });
        }

        if ($this->typeFilter) {
            $query->where('type', $this->typeFilter);
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->methodFilter) {
            $query->where('payment_method', $this->methodFilter);
        }

        if ($this->dateFilter) {
            $now = now();
            if ($this->dateFilter === 'today') {
                $query->whereDate('created_at', $now->toDateString());
            } elseif ($this->dateFilter === 'this_week') {
                $query->whereBetween('created_at', [$now->startOfWeek()->toDateTimeString(), now()->endOfWeek()->toDateTimeString()]);
            } elseif ($this->dateFilter === 'this_month') {
                $query->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year);
            }
        }

        return $query;
    }

    #[Computed]
    public function totalCollected(): float
    {
        $query = clone $this->buildQuery();
        // Only sum confirmed donations for the total
        return (float) $query->where('status', 'confirmed')->sum('amount');
    }

    public function with(): array
    {
        if ($this->activeTab === 'manual_entry') {
            return [
                'bankAccounts' => \App\Models\BankAccount::where('is_active', true)->get(),
            ];
        }

        return [
            'transactions' => $this->buildQuery()->paginate(20),
        ];
    }

    public function recordTransaction(): void
    {
        $this->validate([
            'amount' => 'required|numeric|min:1',
            'type' => 'required|in:general,campaign,halaqah',
            'paymentMethod' => 'required|string',
            'donatedAt' => 'required|date',
        ]);

        if ($this->type === 'campaign' && !$this->campaignId) {
            $this->error(__('Please select a campaign.'));
            return;
        }

        if ($this->type === 'halaqah' && !$this->halaqahId) {
            $this->error(__('Please select a halaqah.'));
            return;
        }

        $donation = Donation::create([
            'user_id' => $this->selectedUserId ?: null,
            'campaign_id' => $this->type === 'campaign' ? $this->campaignId : null,
            'halaqah_id' => $this->type === 'halaqah' ? $this->halaqahId : null,
            'type' => $this->type,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'note' => $this->note,
            'payment_method' => $this->paymentMethod,
            'bank_account_id' => $this->bank_account_id ?: null,
            'transaction_id' => $this->transactionId ?: null,
            'status' => 'confirmed', // Manual entries are instantly confirmed
            'is_anonymous' => $this->isAnonymous,
            'donated_at' => \Carbon\Carbon::parse($this->donatedAt),
            'collected_by' => auth()->id(),
        ]);

        if ($this->sendReceipt && $donation->user_id) {
            $user = User::find($donation->user_id);
            if ($user) {
                try {
                    $user->notify(new DonationStatusNotification($donation, 'confirmed'));
                    $this->success(__('Transaction recorded and receipt sent.'));
                } catch (\Exception $e) {
                    logger()->error('Failed to send donation receipt: ' . $e->getMessage());
                    $this->warning(__('Transaction recorded, but failed to send receipt.'));
                }
            }
        } else {
            $this->success(__('Transaction recorded successfully.'));
        }

        $this->resetManualEntry();
        $this->activeTab = 'ledger';
    }

    private function resetManualEntry(): void
    {
        $this->selectedUserId = null;
        $this->amount = '';
        $this->type = 'general';
        $this->campaignId = null;
        $this->halaqahId = null;
        $this->paymentMethod = 'cash';
        $this->bank_account_id = null;
        $this->transactionId = '';
        $this->note = '';
        $this->isAnonymous = false;
        $this->donatedAt = now()->format('Y-m-d\TH:i');
    }

    public function voidTransaction(int $id): void
    {
        $donation = Donation::findOrFail($id);
        
        if ($donation->status !== 'confirmed') {
            $this->error(__('Only confirmed transactions can be voided.'));
            return;
        }
        
        $donation->update([
            'status' => 'rejected', // Acts as void/refund
            'note' => $donation->note ? $donation->note . ' [VOIDED BY ADMIN]' : '[VOIDED BY ADMIN]'
        ]);
        
        $this->success(__('Transaction has been voided securely.'));
    }

    public function exportCsv(): StreamedResponse
    {
        $transactions = $this->buildQuery()->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="transactions_export_' . now()->format('Y_m_d_His') . '.csv"',
        ];

        $callback = function() use ($transactions) {
            $file = fopen('php://output', 'w');
            
            // CSV Header
            fputcsv($file, [
                'ID', 'Date', 'Donor', 'Type', 'Amount', 'Currency', 
                'Status', 'Method', 'Transaction ID', 'Note', 'Target ID'
            ]);

            foreach ($transactions as $tx) {
                $targetId = '';
                if ($tx->type === 'campaign') $targetId = 'Camp-'.$tx->campaign_id;
                if ($tx->type === 'halaqah') $targetId = 'Hal-'.$tx->halaqah_id;
                
                fputcsv($file, [
                    $tx->id,
                    $tx->created_at->format('Y-m-d H:i:s'),
                    $tx->is_anonymous ? 'Anonymous' : ($tx->user ? $tx->user->name : 'Guest/Offline'),
                    $tx->type,
                    $tx->amount,
                    $tx->currency,
                    $tx->status,
                    $tx->payment_method,
                    $tx->transaction_id ?? '',
                    $tx->note ?? '',
                    $targetId
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
};
