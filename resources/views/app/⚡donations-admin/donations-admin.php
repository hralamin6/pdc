<?php

use App\Models\Donation;
use App\Models\DonationCampaign;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

new #[Title('Donations Admin')] #[Layout('layouts.app')] class extends Component
{
    use WithPagination, WithFileUploads;

    public $statusFilter = '';
    public $typeFilter = '';

    // Campaign CRUD State
    public bool $campaignModal = false;
    public $campaignId = null;
    public $title = '';
    public $description = '';
    public $goalAmount = '';
    public $coverImage = null;

    public function mount()
    {
        // Require admin or accountant role
        if (!auth()->user()->hasRole(['super-admin', 'admin', 'accountant'])) {
            abort(403, 'Unauthorized access.');
        }
    }

    public function confirmDonation($id)
    {
        $donation = Donation::findOrFail($id);
        $donation->update(['status' => 'confirmed']);
        
        $this->js("toast('Donation confirmed', { type: 'success' })");
    }

    public function rejectDonation($id)
    {
        $donation = Donation::findOrFail($id);
        $donation->update(['status' => 'rejected']);
        
        $this->js("toast('Donation rejected', { type: 'error' })");
    }

    public function openCampaignModal($id = null)
    {
        $this->reset(['campaignId', 'title', 'description', 'goalAmount', 'coverImage']);
        
        if ($id) {
            $campaign = DonationCampaign::findOrFail($id);
            $this->campaignId = $campaign->id;
            $this->title = $campaign->title;
            $this->description = $campaign->description;
            $this->goalAmount = $campaign->goal_amount;
        }
        
        $this->campaignModal = true;
    }

    public function saveCampaign()
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'goalAmount' => 'nullable|numeric|min:0',
            'coverImage' => 'nullable|image|max:2048',
        ]);

        $campaign = DonationCampaign::updateOrCreate(
            ['id' => $this->campaignId],
            [
                'title' => $this->title,
                'slug' => \Illuminate\Support\Str::slug($this->title) . '-' . time(),
                'description' => $this->description,
                'goal_amount' => $this->goalAmount ?: null,
                'created_by' => auth()->id(),
            ]
        );

        if ($this->coverImage) {
            $campaign->addMedia($this->coverImage->getRealPath())
                ->toMediaCollection('cover');
        }

        $this->js("toast('Campaign saved successfully!', { type: 'success' })");
        $this->campaignModal = false;
    }

    public function with(): array
    {
        $query = Donation::with(['user', 'campaign', 'halaqah'])->latest('donated_at');

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->typeFilter) {
            $query->where('type', $this->typeFilter);
        }

        return [
            'donations' => $query->paginate(20),
            'campaigns' => DonationCampaign::latest()->get(),
            'stats' => [
                'total_confirmed' => Donation::where('status', 'confirmed')->sum('amount'),
                'pending_count' => Donation::where('status', 'pending')->count(),
                'this_month' => Donation::where('status', 'confirmed')
                    ->whereMonth('donated_at', now()->month)
                    ->whereYear('donated_at', now()->year)
                    ->sum('amount')
            ]
        ];
    }
};
