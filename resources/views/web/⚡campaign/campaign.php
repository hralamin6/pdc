<?php

use App\Models\Donation;
use App\Models\DonationCampaign;
use App\Models\BankAccount;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Mary\Traits\Toast;

new
#[Layout('layouts.web')]
class extends Component
{
    use Toast;

    public DonationCampaign $campaign;
    public string $activeTab = 'about'; // about, updates, faq

    // Donation form fields
    public $amount = '500';
    public $customAmount = '';
    public $paymentMethod = 'bkash';
    public $transactionId = '';
    public $note = '';
    public bool $isAnonymous = false;

    public function mount(string $slug): void
    {
        $this->campaign = DonationCampaign::where('slug', $slug)
            ->firstOrFail();
    }

    public function title(): string
    {
        return $this->campaign->title . ' — Support Our Cause';
    }

    /** Bank Accounts to display payment instructions */
    #[Computed]
    public function bankAccounts()
    {
        return BankAccount::where('is_active', true)->get();
    }

    /** Recent Donors (Non-anonymous, confirmed) */
    #[Computed]
    public function recentDonations()
    {
        return $this->campaign->donations()
            ->where('status', 'confirmed')
            ->where('is_anonymous', false)
            ->with('user')
            ->latest('donated_at')
            ->take(8)
            ->get();
    }

    /** Top Donors (Leaderboard, non-anonymous, confirmed) */
    #[Computed]
    public function topDonors()
    {
        return $this->campaign->donations()
            ->where('status', 'confirmed')
            ->where('is_anonymous', false)
            ->select('user_id', DB::raw('SUM(amount) as total_amount'))
            ->groupBy('user_id')
            ->with('user')
            ->orderByDesc('total_amount')
            ->take(5)
            ->get();
    }

    /** Predefined donation updates */
    #[Computed]
    public function campaignUpdates(): array
    {
        // Procedural updates based on campaign progress to keep it dynamic and real
        $updates = [
            [
                'title' => 'Campaign Launched',
                'body' => 'The donation campaign has been officially started. We appreciate your prayers and contributions to make this a success.',
                'date' => $this->campaign->starts_at ?? $this->campaign->created_at,
                'author' => 'Campaign Admin'
            ]
        ];

        if ($this->campaign->progress_percentage >= 25) {
            $updates[] = [
                'title' => 'Quarter Milestone Reached!',
                'body' => 'Thanks to your generous support, we have surpassed 25% of our target goal. May Allah reward all the donors.',
                'date' => ($this->campaign->starts_at ?? $this->campaign->created_at)->addDays(3),
                'author' => 'Campaign Admin'
            ];
        }

        if ($this->campaign->progress_percentage >= 50) {
            $updates[] = [
                'title' => 'Halfway There!',
                'body' => 'We are now 50% funded! The initial preparation and logistical setup for this campaign have started.',
                'date' => ($this->campaign->starts_at ?? $this->campaign->created_at)->addDays(7),
                'author' => 'Campaign Admin'
            ];
        }

        if ($this->campaign->progress_percentage >= 80) {
            $updates[] = [
                'title' => 'Almost Funded!',
                'body' => 'We are reaching the final stages of the fund collection. Please share this campaign with your family and friends.',
                'date' => ($this->campaign->starts_at ?? $this->campaign->created_at)->addDays(12),
                'author' => 'Campaign Admin'
            ];
        }

        return array_reverse($updates);
    }

    public function selectAmount(string $value): void
    {
        $this->amount = $value;
        $this->customAmount = '';
    }

    public function submitDonation(): void
    {
        if (!auth()->check()) {
            $this->redirectRoute('login');
            return;
        }

        $this->validate([
            'amount' => 'required_without:customAmount',
            'customAmount' => 'required_without:amount|nullable|numeric|min:10',
            'paymentMethod' => 'required|in:cash,bkash,nagad,bank,other',
            'transactionId' => 'required_if:paymentMethod,bkash,nagad,bank|nullable|string|min:4',
            'note' => 'nullable|string|max:500',
        ], [
            'transactionId.required_if' => 'Please enter the transaction reference ID for your digital transfer.'
        ]);

        $finalAmount = $this->customAmount ?: $this->amount;

        Donation::create([
            'user_id' => auth()->id(),
            'amount' => $finalAmount,
            'type' => 'campaign',
            'campaign_id' => $this->campaign->id,
            'payment_method' => $this->paymentMethod,
            'transaction_id' => $this->transactionId,
            'note' => $this->note,
            'is_anonymous' => $this->isAnonymous,
            'status' => 'pending', // Requires admin verification
            'donated_at' => now(),
        ]);

        $this->success('Your contribution was submitted successfully and is awaiting admin verification!');
        
        // Reset form fields
        $this->reset(['customAmount', 'transactionId', 'note', 'isAnonymous']);
        $this->amount = '500';
    }

    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
    }
};
