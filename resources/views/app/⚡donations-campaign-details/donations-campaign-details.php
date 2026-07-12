<?php

use App\Models\DonationCampaign;
use App\Models\DonationCampaignUpdate;
use App\Models\DonationCampaignFaq;
use App\Models\Donation;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\DB;

new #[Layout('layouts.app')] class extends Component
{
    use Toast, WithPagination;

    public DonationCampaign $campaign;
    public string $activeTab = 'donations';

    // State for Posting Update
    public bool $updateModal = false;
    public string $updateTitle = '';
    public string $updateContent = '';

    // State for Answering/Creating FAQ
    public bool $faqModal = false;
    public ?int $faqId = null;
    public string $faqQuestion = '';
    public string $faqAnswer = '';

    public function mount(DonationCampaign $campaign): void
    {
        if (!auth()->user() || !auth()->user()->can('donations.campaigns.manage')) {
            abort(403);
        }

        $this->campaign = $campaign;
        $this->autoCloseCampaign();
    }

    public function autoCloseCampaign(): void
    {
        $goalMet = $this->campaign->goal_amount && ($this->campaign->collected_amount >= $this->campaign->goal_amount);
        $datePassed = $this->campaign->ends_at && ($this->campaign->ends_at->isPast());
        if ($this->campaign->status === 'active' && ($goalMet || $datePassed)) {
            $this->campaign->update(['status' => 'completed']);
            $this->success(__('Campaign auto-completed as target has been reached!'));
        }
    }

    public function getTitle(): string
    {
        return __('Campaign Details: :title', ['title' => $this->campaign->title]);
    }

    public function with(): array
    {
        // 1. Paginated donations list
        $donations = Donation::with('user')
            ->where('campaign_id', $this->campaign->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'donationsPage');

        // 2. Paginated FAQs
        $faqs = DonationCampaignFaq::with(['user', 'answeredBy'])
            ->where('campaign_id', $this->campaign->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'faqsPage');

        // 3. Paginated Updates
        $updates = DonationCampaignUpdate::with('user')
            ->where('campaign_id', $this->campaign->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'updatesPage');

        // 4. Analytics: Last 6 Months giving trend for this campaign
        $trends = $this->getGivingTrend();

        // 5. Analytics: Payment method breakdown
        $paymentMethodsBreakdown = Donation::where('campaign_id', $this->campaign->id)
            ->where('status', 'confirmed')
            ->select('payment_method', DB::raw('SUM(amount) as total'))
            ->groupBy('payment_method')
            ->get();

        return [
            'donations' => $donations,
            'faqs' => $faqs,
            'updates' => $updates,
            'trends' => $trends,
            'paymentBreakdown' => $paymentMethodsBreakdown,
        ];
    }

    protected function getGivingTrend(): array
    {
        $data = [];
        $labels = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $labels[] = $month->format('M Y');

            $amount = Donation::where('campaign_id', $this->campaign->id)
                ->where('status', 'confirmed')
                ->whereYear('donated_at', $month->year)
                ->whereMonth('donated_at', $month->month)
                ->sum('amount');

            $data[] = (float) $amount;
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    // ----------------------------------------
    // Campaign Update Actions
    // ----------------------------------------
    public function openUpdateModal(): void
    {
        $this->reset(['updateTitle', 'updateContent']);
        $this->updateModal = true;
    }

    public function saveUpdate(): void
    {
        $this->validate([
            'updateTitle' => 'required|string|max:255',
            'updateContent' => 'required|string',
        ]);

        DonationCampaignUpdate::create([
            'campaign_id' => $this->campaign->id,
            'user_id' => auth()->id(),
            'title' => $this->updateTitle,
            'content' => $this->updateContent,
        ]);

        $this->updateModal = false;
        $this->success(__('Campaign update posted successfully!'));
    }

    public function deleteUpdate(int $id): void
    {
        $update = DonationCampaignUpdate::where('campaign_id', $this->campaign->id)->findOrFail($id);
        $update->delete();
        $this->success(__('Campaign update deleted.'));
    }

    // ----------------------------------------
    // Campaign FAQ Actions
    // ----------------------------------------
    public function openFaqModal(?int $id = null): void
    {
        $this->reset(['faqId', 'faqQuestion', 'faqAnswer']);
        
        if ($id) {
            $faq = DonationCampaignFaq::where('campaign_id', $this->campaign->id)->findOrFail($id);
            $this->faqId = $faq->id;
            $this->faqQuestion = $faq->question;
            $this->faqAnswer = $faq->answer ?? '';
        }
        
        $this->faqModal = true;
    }

    public function saveFaq(): void
    {
        $this->validate([
            'faqQuestion' => 'required|string',
            'faqAnswer' => 'required|string',
        ]);

        if ($this->faqId) {
            $faq = DonationCampaignFaq::where('campaign_id', $this->campaign->id)->findOrFail($this->faqId);
            $faq->update([
                'question' => $this->faqQuestion,
                'answer' => $this->faqAnswer,
                'answered_by' => auth()->id(),
                'answered_at' => now(),
            ]);
            $this->success(__('FAQ answered successfully!'));
        } else {
            DonationCampaignFaq::create([
                'campaign_id' => $this->campaign->id,
                'user_id' => auth()->id(), // Admin asking & answering
                'question' => $this->faqQuestion,
                'answer' => $this->faqAnswer,
                'answered_by' => auth()->id(),
                'answered_at' => now(),
            ]);
            $this->success(__('FAQ created successfully!'));
        }

        $this->faqModal = false;
    }

    public function deleteFaq(int $id): void
    {
        $faq = DonationCampaignFaq::where('campaign_id', $this->campaign->id)->findOrFail($id);
        $faq->delete();
        $this->success(__('FAQ item deleted.'));
    }
};
