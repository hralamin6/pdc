<?php

use App\Models\DonationCampaign;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;
use Illuminate\Support\Str;

new #[Title('Campaigns Management')] #[Layout('layouts.app')] class extends Component
{
    use Toast, WithPagination, WithFileUploads;

    public bool $campaignModal = false;
    
    // Campaign Form Fields
    public ?int $campaignId = null;
    public string $title = '';
    public ?string $description = null;
    public ?float $goal_amount = null;
    public string $status = 'active';
    public ?string $starts_at = null;
    public ?string $ends_at = null;
    public $cover_image; // uploaded file
    public ?string $existingCoverUrl = null;

    public function mount(): void
    {
        if (!auth()->user() || !auth()->user()->can('donations.campaigns.manage')) {
            abort(403);
        }
        
        $this->autoCloseCampaigns();
    }

    public function autoCloseCampaigns(): void
    {
        $campaigns = DonationCampaign::where('status', 'active')->get();
        foreach ($campaigns as $campaign) {
            $goalMet = $campaign->goal_amount && ($campaign->collected_amount >= $campaign->goal_amount);
            $datePassed = $campaign->ends_at && ($campaign->ends_at->isPast());
            if ($goalMet || $datePassed) {
                $campaign->update(['status' => 'completed']);
            }
        }
    }

    public function with(): array
    {
        return [
            'campaigns' => DonationCampaign::with(['creator'])
                ->orderBy('created_at', 'desc')
                ->paginate(12)
        ];
    }

    public function createCampaign(): void
    {
        $this->reset(['campaignId', 'title', 'description', 'goal_amount', 'status', 'starts_at', 'ends_at', 'cover_image', 'existingCoverUrl']);
        $this->status = 'active';
        $this->starts_at = now()->format('Y-m-d\TH:i');
        $this->campaignModal = true;
    }

    public function editCampaign(int $id): void
    {
        $campaign = DonationCampaign::findOrFail($id);
        $this->campaignId = $campaign->id;
        $this->title = $campaign->title;
        $this->description = $campaign->description;
        $this->goal_amount = $campaign->goal_amount ? (float) $campaign->goal_amount : null;
        $this->status = $campaign->status;
        $this->starts_at = $campaign->starts_at ? $campaign->starts_at->format('Y-m-d\TH:i') : null;
        $this->ends_at = $campaign->ends_at ? $campaign->ends_at->format('Y-m-d\TH:i') : null;
        $this->existingCoverUrl = $campaign->cover_url;
        $this->cover_image = null;
        
        $this->campaignModal = true;
    }

    public function saveCampaign(): void
    {
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'goal_amount' => 'nullable|numeric|min:1',
            'status' => 'required|in:active,completed,cancelled',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'cover_image' => 'nullable|image|max:2048', // max 2MB
        ];

        $this->validate($rules);

        $data = [
            'title' => $this->title,
            'slug' => $this->campaignId ? DonationCampaign::findOrFail($this->campaignId)->slug : Str::slug($this->title) . '-' . rand(1000, 9999),
            'description' => $this->description,
            'goal_amount' => $this->goal_amount,
            'status' => $this->status,
            'starts_at' => $this->starts_at ? new \DateTime($this->starts_at) : null,
            'ends_at' => $this->ends_at ? new \DateTime($this->ends_at) : null,
        ];

        if (!$this->campaignId) {
            $data['created_by'] = auth()->id();
            $campaign = DonationCampaign::create($data);
            $this->success(__('Campaign created successfully!'));
        } else {
            $campaign = DonationCampaign::findOrFail($this->campaignId);
            $campaign->update($data);
            $this->success(__('Campaign updated successfully!'));
        }

        if ($this->cover_image) {
            $campaign->clearMediaCollection('cover');
            $campaign->addMedia($this->cover_image->getRealPath())
                ->usingFileName($this->cover_image->getClientOriginalName())
                ->toMediaCollection('cover');
        }

        $this->campaignModal = false;
        $this->reset(['campaignId', 'title', 'description', 'goal_amount', 'status', 'starts_at', 'ends_at', 'cover_image', 'existingCoverUrl']);
        $this->autoCloseCampaigns();
    }
};
