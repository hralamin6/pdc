<div>
    <!-- Header -->
    <x-header title="{{ __('Donation Pledges') }}" subtitle="{{ __('Manage recurring donation commitments') }}" separator>
        <x-slot:actions>
            <x-button label="{{ __('Campaigns') }}" icon="o-megaphone" link="{{ route('app.donations.campaigns') }}" class="btn-ghost" />
            <x-button label="{{ __('Verify Payments') }}" icon="o-check-badge" link="{{ route('app.donations.verify') }}" class="btn-primary" />
        </x-slot:actions>
    </x-header>

    <!-- Analytics Dashboard (Option 1.A) -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <x-stat title="{{ __('Active Pledges') }}" value="{{ $this->stats['active_count'] }}" icon="o-arrow-path" class="bg-base-200/50 shadow-sm border border-base-300 rounded-2xl" />
        
        <x-stat title="{{ __('Est. MRR') }}" value="৳{{ number_format($this->stats['mrr'], 0) }}" description="{{ __('Monthly Recurring Revenue') }}" icon="o-banknotes" class="bg-base-200/50 shadow-sm border border-base-300 rounded-2xl text-primary" />
        
        <x-stat title="{{ __('Overdue Pledges') }}" value="{{ $this->stats['overdue_count'] }}" icon="o-exclamation-triangle" class="bg-base-200/50 shadow-sm border border-base-300 rounded-2xl {{ $this->stats['overdue_count'] > 0 ? 'text-error' : 'text-success' }}" />
        
        <x-stat title="{{ __('Collection Rate') }}" value="{{ $this->stats['collection_rate'] }}%" description="{{ __('Last 30 Days') }}" icon="o-chart-pie" class="bg-base-200/50 shadow-sm border border-base-300 rounded-2xl" />
    </div>

    <!-- Tabs -->
    <x-tabs wire:model.live="activeTab" class="mb-6">
        <x-tab name="all" label="{{ __('All Pledges') }}" icon="o-list-bullet" />
        <x-tab name="overdue" label="{{ __('Overdue & Reminders') }}" icon="o-bell-alert">
            <x-slot:badge>
                @if($this->stats['overdue_count'] > 0)
                    <x-badge value="{{ $this->stats['overdue_count'] }}" class="badge-error badge-sm" />
                @endif
            </x-slot:badge>
        </x-tab>
    </x-tabs>

    <!-- Filters -->
    <div class="flex flex-col md:flex-row gap-4 mb-6">
        <x-input placeholder="{{ __('Search donor name or email...') }}" wire:model.live.debounce.300ms="search" icon="o-magnifying-glass" class="w-full md:w-1/3" />
        
        @if($activeTab === 'all')
        <x-select 
            :options="[['id' => '', 'name' => __('All Statuses')], ['id' => '1', 'name' => __('Active')], ['id' => '0', 'name' => __('Paused')]]" 
            wire:model.live="statusFilter" 
            class="w-full md:w-48" 
        />
        @endif
        
        <x-select 
            :options="[['id' => '', 'name' => __('All Frequencies')], ['id' => 'weekly', 'name' => __('Weekly')], ['id' => 'monthly', 'name' => __('Monthly')], ['id' => 'yearly', 'name' => __('Yearly')]]" 
            wire:model.live="frequencyFilter" 
            class="w-full md:w-48" 
        />
    </div>

    <!-- List -->
    <div class="bg-base-100 rounded-2xl shadow-sm border border-base-200 overflow-hidden">
        <table class="table w-full">
            <thead class="bg-base-200/50">
                <tr>
                    <th>{{ __('Donor') }}</th>
                    <th>{{ __('Commitment') }}</th>
                    <th>{{ __('Status / Dates') }}</th>
                    @if($activeTab === 'overdue')
                        <th>{{ __('Overdue By') }}</th>
                    @endif
                    <th class="text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pledges as $pledge)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <x-avatar :image="$pledge->user?->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode($pledge->user?->name ?? 'User')" class="!w-10 !h-10" />
                                <div>
                                    <div class="font-bold">{{ $pledge->user?->name ?? __('Unknown User') }}</div>
                                    <div class="text-xs text-base-content/70">{{ $pledge->user?->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="font-bold text-primary">{{ __('BDT') }} {{ number_format($pledge->amount, 2) }}</div>
                            <div class="text-xs uppercase tracking-widest text-base-content/70">{{ __($pledge->frequency) }}</div>
                        </td>
                        <td>
                            <div class="flex flex-col gap-1 items-start">
                                @if(!$pledge->is_active)
                                    <x-badge value="{{ __('Paused') }}" class="badge-neutral badge-sm" />
                                @elseif($pledge->next_due_at && $pledge->next_due_at->isPast())
                                    <x-badge value="{{ __('Overdue') }}" class="badge-error badge-sm" />
                                @else
                                    <x-badge value="{{ __('Active') }}" class="badge-success badge-sm" />
                                @endif
                                
                                <div class="text-xs text-base-content/70">
                                    {{ __('Next: :date', ['date' => $pledge->next_due_at ? $pledge->next_due_at->format('d M, Y') : __('Not Set')]) }}
                                </div>
                            </div>
                        </td>
                        @if($activeTab === 'overdue')
                        <td>
                            @php
                                $daysLate = $pledge->next_due_at ? $pledge->next_due_at->diffInDays(now()) : 0;
                            @endphp
                            <div class="text-error font-semibold flex items-center gap-1">
                                <x-icon name="o-clock" class="w-4 h-4" />
                                {{ trans_choice(':count day late|:count days late', $daysLate, ['count' => $daysLate]) }}
                            </div>
                        </td>
                        @endif
                        <td class="text-right">
                            <div class="flex justify-end gap-2">
                                @if($activeTab === 'overdue')
                                    <x-button icon="o-paper-airplane" wire:click="sendReminder({{ $pledge->id }})" class="btn-sm btn-outline btn-error" tooltip="{{ __('Send Reminder') }}" spinner="sendReminder({{ $pledge->id }})" />
                                @endif
                                
                                @if($pledge->is_active)
                                    <x-button icon="o-pause" wire:click="togglePledgeStatus({{ $pledge->id }})" class="btn-sm btn-ghost text-warning" tooltip="{{ __('Pause Pledge') }}" spinner="togglePledgeStatus({{ $pledge->id }})" />
                                @else
                                    <x-button icon="o-play" wire:click="togglePledgeStatus({{ $pledge->id }})" class="btn-sm btn-ghost text-success" tooltip="{{ __('Resume Pledge') }}" spinner="togglePledgeStatus({{ $pledge->id }})" />
                                @endif
                                
                                <x-button icon="o-arrow-right" link="{{ route('app.donations.pledge-details', $pledge) }}" class="btn-sm btn-ghost" tooltip="{{ __('View Details') }}" />
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $activeTab === 'overdue' ? '5' : '4' }}">
                            <div class="py-12 text-center text-base-content/50">
                                <x-icon name="o-inbox" class="w-12 h-12 mx-auto mb-3 opacity-20" />
                                <p>{{ __('No pledges found matching your criteria.') }}</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $pledges->links() }}
    </div>
</div>
