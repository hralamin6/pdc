<?php

use App\Models\Donation;
use App\Models\DonationCampaign;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Campaigns | PSTU Dawah Community')] #[Layout('layouts.web')] class extends Component
{
    public function with(): array
    {
        return [
            'activeCampaigns' => DonationCampaign::where('status', 'active')->latest()->get(),
            'completedCampaigns' => DonationCampaign::where('status', 'completed')->latest()->take(6)->get(),
            'totalRaised' => Donation::where('status', 'confirmed')->sum('amount'),
            'totalDonors' => Donation::where('status', 'confirmed')->distinct('user_id')->count('user_id'),
        ];
    }
};
