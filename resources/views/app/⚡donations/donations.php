<?php

use App\Models\Donation;
use App\Models\DonationCampaign;
use App\Models\DonationPledge;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('My Donations')] #[Layout('layouts.app')] class extends Component
{
    use WithPagination;

    // Form state
    public bool $donationModal = false;
    public bool $pledgeModal = false;
    public $pledgeId = null; // For editing pledges
    
    public $amount = '';
    public $customAmount = '';
    public $campaign_id = '';
    public $type = 'campaign';
    public $paymentMethod = 'cash';
    public $transactionId = '';
    public $note = '';
    public $isAnonymous = false;
    public $frequency = 'monthly';

    public bool $payModal = false;
    public $pendingDonationId = null;

    public function saveDonation()
    {
        $this->validate([
            'amount' => 'required_without:customAmount',
            'customAmount' => 'required_without:amount|numeric|min:10',
            'campaign_id' => 'required|exists:donation_campaigns,id',
            'paymentMethod' => 'required|in:cash,bkash,nagad,bank,other',
            'transactionId' => 'required_if:paymentMethod,bkash,nagad,bank',
        ], [
            'transactionId.required_if' => 'Please provide the Transaction ID for digital payments.'
        ]);

        $finalAmount = $this->customAmount ?: $this->amount;

        Donation::create([
            'user_id' => auth()->id(),
            'amount' => $finalAmount,
            'type' => 'campaign',
            'campaign_id' => $this->campaign_id,
            'payment_method' => $this->paymentMethod,
            'transaction_id' => $this->transactionId,
            'note' => $this->note,
            'is_anonymous' => $this->isAnonymous,
            'status' => 'pending', // Requires accountant approval
            'donated_at' => now(),
        ]);

        $this->js("toast('Donation recorded and pending approval!', { type: 'success' })");
        $this->donationModal = false;
        $this->reset(['amount', 'customAmount', 'campaign_id', 'transactionId', 'note', 'isAnonymous']);
    }

    public function savePledge()
    {
        $this->validate([
            'amount' => 'required_without:customAmount',
            'customAmount' => 'required_without:amount|numeric|min:10',
            'frequency' => 'required|in:weekly,monthly,yearly',
        ]);

        $finalAmount = $this->customAmount ?: $this->amount;

        DonationPledge::updateOrCreate(
            ['id' => $this->pledgeId],
            [
                'user_id' => auth()->id(),
                'amount' => $finalAmount,
                'frequency' => $this->frequency,
                'starts_at' => $this->pledgeId ? DonationPledge::find($this->pledgeId)->starts_at : now(),
                'next_due_at' => $this->pledgeId ? DonationPledge::find($this->pledgeId)->next_due_at : now(),
                'is_active' => true,
            ]
        );

        $this->js("toast('Pledge saved successfully!', { type: 'success' })");
        $this->pledgeModal = false;
        $this->reset(['amount', 'customAmount', 'pledgeId']);
    }

    public function editPledge($id)
    {
        $pledge = DonationPledge::where('user_id', auth()->id())->findOrFail($id);
        $this->pledgeId = $pledge->id;
        
        // Try to match standard amount, else use custom
        if (in_array($pledge->amount, [50, 100, 200, 500])) {
            $this->amount = $pledge->amount;
            $this->customAmount = '';
        } else {
            $this->amount = '';
            $this->customAmount = $pledge->amount;
        }
        
        $this->frequency = $pledge->frequency;
        $this->pledgeModal = true;
    }

    public function cancelPledge($id)
    {
        $pledge = DonationPledge::where('user_id', auth()->id())->findOrFail($id);
        $pledge->update(['is_active' => false]);
        $this->js("toast('Pledge cancelled.', { type: 'info' })");
    }

    public function deletePledge($id)
    {
        DonationPledge::where('user_id', auth()->id())->findOrFail($id)->delete();
        $this->js("toast('Pledge deleted.', { type: 'error' })");
    }

    public function openPledgeModal()
    {
        $this->reset(['amount', 'customAmount', 'frequency', 'pledgeId']);
        $this->pledgeModal = true;
    }

    public function openPayModal($id)
    {
        $this->pendingDonationId = $id;
        $this->paymentMethod = 'cash';
        $this->transactionId = '';
        $this->payModal = true;
    }

    public function payPendingDonation()
    {
        $this->validate([
            'paymentMethod' => 'required|in:cash,bkash,nagad,bank,other',
            'transactionId' => 'required_if:paymentMethod,bkash,nagad,bank',
        ]);

        $donation = Donation::where('user_id', auth()->id())
            ->where('status', 'pending_payment')
            ->findOrFail($this->pendingDonationId);

        $donation->update([
            'payment_method' => $this->paymentMethod,
            'transaction_id' => $this->transactionId,
            'status' => 'pending', // Move to pending for accountant approval
            'donated_at' => now(),
        ]);

        $this->js("toast('Payment submitted for verification!', { type: 'success' })");
        $this->payModal = false;
        $this->pendingDonationId = null;
    }
    public function getDonorRankProperty()
    {
        $total = Donation::where('user_id', auth()->id())->where('status', 'confirmed')->sum('amount');
        if ($total >= 50000) return ['name' => 'Platinum Pillar', 'color' => 'bg-gradient-to-r from-slate-200 to-slate-400 text-slate-800 shadow-lg shadow-slate-300/20', 'icon' => 'o-sparkles'];
        if ($total >= 20000) return ['name' => 'Gold Guardian', 'color' => 'bg-gradient-to-r from-yellow-300 to-yellow-500 text-yellow-900 shadow-lg shadow-yellow-500/20', 'icon' => 'o-trophy'];
        if ($total >= 5000) return ['name' => 'Silver Supporter', 'color' => 'bg-gradient-to-r from-gray-300 to-gray-400 text-gray-800 shadow-lg shadow-gray-400/20', 'icon' => 'o-shield-check'];
        if ($total > 0) return ['name' => 'Bronze Donor', 'color' => 'bg-gradient-to-r from-amber-600 to-amber-700 text-white shadow-lg shadow-amber-700/20', 'icon' => 'o-heart'];
        return ['name' => 'Rising Star', 'color' => 'bg-base-200 text-base-content/70', 'icon' => 'o-star'];
    }

    public function getChartDataProperty()
    {
        $months = collect(range(1, 12))->map(fn($m) => now()->startOfYear()->addMonths($m - 1)->format('M'));
        
        $donations = Donation::where('user_id', auth()->id())
            ->where('status', 'confirmed')
            ->whereYear('donated_at', now()->year)
            ->selectRaw('SUM(amount) as total, MONTH(donated_at) as month')
            ->groupBy('month')
            ->pluck('total', 'month');

        $data = [];
        foreach (range(1, 12) as $m) {
            $data[] = $donations->get($m, 0);
        }

        return [
            'type' => 'line',
            'data' => [
                'labels' => $months,
                'datasets' => [
                    [
                        'label' => 'Donations (' . now()->year . ')',
                        'data' => $data,
                        'borderColor' => '#10b981', // Tailwind Emerald 500
                        'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                        'borderWidth' => 3,
                        'tension' => 0.4,
                        'fill' => true,
                        'pointBackgroundColor' => '#10b981',
                    ]
                ]
            ],
            'options' => [
                'responsive' => true,
                'maintainAspectRatio' => false,
                'plugins' => [
                    'legend' => ['display' => false]
                ],
                'scales' => [
                    'y' => [
                        'beginAtZero' => true,
                        'grid' => ['color' => 'rgba(160, 160, 160, 0.1)']
                    ],
                    'x' => [
                        'grid' => ['display' => false]
                    ]
                ]
            ]
        ];
    }

    public function downloadReceipt($id)
    {
        $donation = Donation::where('user_id', auth()->id())->findOrFail($id);
        $this->js("toast('Receipt generation feature is coming soon!', { type: 'info' })");
    }

    public function with(): array
    {
        $user = auth()->user();

        // Get personal donations history
        $donations = Donation::with('campaign')->where('user_id', $user->id)
            ->latest('donated_at')
            ->paginate(10);

        // Get active pledges
        $pledges = DonationPledge::where('user_id', $user->id)->get();

        // Get active campaigns
        $campaigns = DonationCampaign::where('status', 'active')
            ->latest()
            ->get();

        $totalDonated = Donation::where('user_id', $user->id)->where('status', 'confirmed')->sum('amount');

        return [
            'user' => $user,
            'donations' => $donations,
            'pledges' => $pledges,
            'campaigns' => $campaigns,
            'totalDonated' => $totalDonated,
        ];
    }
};
