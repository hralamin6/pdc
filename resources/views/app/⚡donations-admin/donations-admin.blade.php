<div>
    <x-header :title="__('Donations Admin')" separator>
        <x-slot:actions>
            <x-button icon="o-arrow-down-tray" class="btn-outline" :label="__('Export CSV')" />
            <x-button icon="o-plus" class="btn-primary" :label="__('New Campaign')" wire:click="openCampaignModal()" />
        </x-slot:actions>
    </x-header>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <x-stat :title="__('Total Collected')" value="৳{{ number_format($stats['total_confirmed'], 2) }}" icon="o-banknotes" class="bg-base-200/50" />
        <x-stat :title="__('This Month')" value="৳{{ number_format($stats['this_month'], 2) }}" icon="o-calendar" class="bg-base-200/50" />
        <x-stat :title="__('Pending Review')" value="{{ $stats['pending_count'] }}" icon="o-clock" class="bg-warning/20 text-warning" />
    </div>

    <div class="flex gap-4 mb-4">
        <x-select :options="[['id'=>'', 'name'=>__('All Status')], ['id'=>'pending', 'name'=>__('Pending')], ['id'=>'confirmed', 'name'=>__('Confirmed')], ['id'=>'rejected', 'name'=>__('Rejected')]]" wire:model.live="statusFilter" class="max-w-xs" />
        <x-select :options="[['id'=>'', 'name'=>__('All Types')], ['id'=>'general', 'name'=>__('General')], ['id'=>'halaqah', 'name'=>__('Halaqah')], ['id'=>'recurring', 'name'=>__('Recurring')], ['id'=>'campaign', 'name'=>__('Campaign')]]" wire:model.live="typeFilter" class="max-w-xs" />
    </div>

    <div class="bg-base-200/50 rounded-xl p-4 overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Member') }}</th>
                    <th>{{ __('Type') }}</th>
                    <th>{{ __('Method') }}</th>
                    <th>{{ __('Amount') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($donations as $donation)
                    <tr>
                        <td>{{ $donation->donated_at ? $donation->donated_at->format('M d, Y') : $donation->created_at->format('M d, Y') }}</td>
                        <td>
                            @if($donation->is_anonymous)
                                <span class="opacity-50 italic">{{ __('Anonymous') }}</span>
                                <div class="text-xs opacity-50">({{ $donation->user?->name ?? __('Guest') }})</div>
                            @else
                                <div class="font-bold">{{ $donation->user?->name ?? __('Guest') }}</div>
                            @endif
                        </td>
                        <td class="capitalize">
                            {{ $donation->type }}
                            @if($donation->type === 'campaign' && $donation->campaign)
                                <div class="text-xs opacity-70">{{ $donation->campaign->title }}</div>
                            @endif
                        </td>
                        <td>
                            <div class="capitalize">{{ $donation->payment_method }}</div>
                            @if($donation->transaction_id)
                                <div class="text-[10px] font-mono opacity-70 mt-1" title="{{ __('Transaction ID') }}">{{ __('TxID:') }} {{ $donation->transaction_id }}</div>
                            @endif
                        </td>
                        <td class="font-bold">৳{{ number_format($donation->amount, 2) }}</td>
                        <td>
                            @if($donation->status === 'confirmed')
                                <x-badge :value="__('Confirmed')" class="badge-success badge-sm" />
                            @elseif($donation->status === 'pending')
                                <x-badge :value="__('Pending Verification')" class="badge-warning badge-sm" />
                            @elseif($donation->status === 'pending_payment')
                                <x-badge :value="__('Awaiting User Payment')" class="badge-error badge-sm" />
                            @else
                                <x-badge :value="__('Rejected')" class="badge-error badge-sm" />
                            @endif
                        </td>
                        <td>
                            @if($donation->status === 'pending')
                                <div class="join">
                                    <button wire:click="confirmDonation({{ $donation->id }})" class="btn btn-sm btn-success join-item">{{ __('Confirm') }}</button>
                                    <button wire:click="rejectDonation({{ $donation->id }})" class="btn btn-sm btn-error join-item">{{ __('Reject') }}</button>
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center opacity-50 py-4">{{ __('No donations found matching criteria.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">
            {{ $donations->links() }}
        </div>
    </div>

    <!-- Campaign CRUD Modal -->
    <x-modal wire:model="campaignModal" title="{{ $campaignId ? __('Edit Campaign') : __('Create Campaign') }}" separator>
        <div class="space-y-4">
            <x-input :label="__('Campaign Title')" wire:model="title" :placeholder="__('e.g. Ramadan Iftar Fund')" required />

            <x-textarea :label="__('Description')" wire:model="description" :placeholder="__('Explain the purpose of this campaign...')" rows="4" required />

            <x-input :label="__('Goal Amount (৳)')" wire:model="goalAmount" :placeholder="__('Leave empty for no specific goal')" type="number" />

            <x-file :label="__('Cover Image (Optional)')" wire:model="coverImage" accept="image/*" />
        </div>

        <x-slot:actions>
            <x-button :label="__('Cancel')" wire:click="$set('campaignModal', false)" />
            <x-button :label="__('Save Campaign')" class="btn-primary" wire:click="saveCampaign" spinner />
        </x-slot:actions>
    </x-modal>
</div>
