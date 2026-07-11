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

    // Form fields for new updates (Admins only)
    public string $newUpdateTitle = '';
    public string $newUpdateContent = '';

    // Form fields for FAQ (Members can ask)
    public string $newQuestion = '';

    // Answers array for FAQ (Admins can answer)
    public array $faqAnswers = [];

    public function mount(string $slug): void
    {
        $this->campaign = DonationCampaign::where('slug', $slug)
            ->firstOrFail();
    }

    public function title(): string
    {
        return $this->campaign->title . ' — Support Our Cause';
    }

    public function isAdmin(): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['super-admin', 'admin']);
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

    /** Real DB updates */
    #[Computed]
    public function dbUpdates()
    {
        return $this->campaign->updates()
            ->with('user')
            ->latest()
            ->get();
    }

    /** Real DB FAQs */
    #[Computed]
    public function faqs()
    {
        return $this->campaign->faqs()
            ->with(['user', 'answeredBy'])
            ->get(); // Display all questions (answered and unanswered)
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

    public function addUpdate(): void
    {
        if (!$this->isAdmin()) {
            $this->error('Unauthorized action.');
            return;
        }

        $this->validate([
            'newUpdateTitle' => 'required|string|min:3|max:255',
            'newUpdateContent' => 'required|string|min:10',
        ]);

        \App\Models\DonationCampaignUpdate::create([
            'campaign_id' => $this->campaign->id,
            'user_id' => auth()->id(),
            'title' => $this->newUpdateTitle,
            'content' => $this->newUpdateContent,
        ]);

        $this->reset(['newUpdateTitle', 'newUpdateContent']);
        $this->success('Campaign update posted successfully.');
    }

    public function deleteUpdate(int $id): void
    {
        if (!$this->isAdmin()) {
            $this->error('Unauthorized action.');
            return;
        }

        \App\Models\DonationCampaignUpdate::where('campaign_id', $this->campaign->id)->findOrFail($id)->delete();
        $this->success('Campaign update deleted.');
    }

    public function askQuestion(): void
    {
        if (!auth()->check()) {
            $this->redirectRoute('login');
            return;
        }

        $this->validate([
            'newQuestion' => 'required|string|min:5|max:500',
        ]);

        \App\Models\DonationCampaignFaq::create([
            'campaign_id' => $this->campaign->id,
            'user_id' => auth()->id(),
            'question' => $this->newQuestion,
        ]);

        $this->reset(['newQuestion']);
        $this->success('Your question has been submitted and is awaiting an answer.');
    }

    public function answerQuestion(int $faqId): void
    {
        if (!$this->isAdmin()) {
            $this->error('Unauthorized action.');
            return;
        }

        $answer = $this->faqAnswers[$faqId] ?? '';
        if (empty(trim($answer))) {
            $this->error('Please type an answer first.');
            return;
        }

        $faq = \App\Models\DonationCampaignFaq::where('campaign_id', $this->campaign->id)->findOrFail($faqId);
        $faq->update([
            'answer' => $answer,
            'answered_by' => auth()->id(),
            'answered_at' => now(),
        ]);

        unset($this->faqAnswers[$faqId]);
        $this->success('FAQ question answered successfully.');
    }

    public function deleteFaq(int $id): void
    {
        if (!$this->isAdmin()) {
            $this->error('Unauthorized action.');
            return;
        }

        \App\Models\DonationCampaignFaq::where('campaign_id', $this->campaign->id)->findOrFail($id)->delete();
        $this->success('FAQ deleted.');
    }

    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
    }
};
