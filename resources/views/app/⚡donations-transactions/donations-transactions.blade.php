<div>
    <!-- Header -->
    <x-header title="{{ __('Transaction Ledger') }}" subtitle="{{ __('Master record of all donation activities and offline entries') }}" separator>
        <x-slot:actions>
            @if($activeTab === 'ledger')
                <x-button label="{{ __('Export CSV') }}" icon="o-arrow-down-tray" wire:click="exportCsv" class="btn-outline" spinner="exportCsv" />
            @endif
        </x-slot:actions>
    </x-header>

    <x-tabs wire:model.live="activeTab" class="mb-6">
        <x-tab name="ledger" label="{{ __('Accountant Ledger') }}" icon="o-table-cells" />
        <x-tab name="manual_entry" label="{{ __('Record Offline Transaction') }}" icon="o-plus-circle" />
    </x-tabs>

    @if($activeTab === 'ledger')
        <!-- Summary Row -->
        <div class="mb-6 bg-gradient-to-br from-primary/10 to-primary/5 border border-primary/20 rounded-2xl p-6 shadow-sm flex items-center justify-between">
            <div>
                <h3 class="text-sm uppercase tracking-widest font-bold text-primary/70 mb-1">{{ __('Filtered Total Collected') }}</h3>
                <div class="text-4xl font-extrabold text-primary">{{ __('BDT') }} {{ number_format($this->totalCollected, 2) }}</div>
            </div>
            <div class="hidden md:block">
                <x-icon name="o-banknotes" class="w-16 h-16 text-primary/20" />
            </div>
        </div>

        <!-- Filters Grid -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
            <x-input placeholder="{{ __('Search ID, Note, Name...') }}" wire:model.live.debounce.300ms="search" icon="o-magnifying-glass" class="w-full md:col-span-2" />
            
            <x-select 
                :options="[['id' => '', 'name' => __('All Types')], ['id' => 'general', 'name' => __('General')], ['id' => 'campaign', 'name' => __('Campaign')], ['id' => 'halaqah', 'name' => __('Halaqah')], ['id' => 'recurring', 'name' => __('Recurring')]]" 
                wire:model.live="typeFilter" 
                class="w-full" 
            />
            
            <x-select 
                :options="[['id' => '', 'name' => __('All Statuses')], ['id' => 'confirmed', 'name' => __('Confirmed')], ['id' => 'pending', 'name' => __('Pending')], ['id' => 'rejected', 'name' => __('Rejected/Voided')], ['id' => 'pending_payment', 'name' => __('Unpaid')]]" 
                wire:model.live="statusFilter" 
                class="w-full" 
            />

            <x-select 
                :options="[['id' => '', 'name' => __('Any Time')], ['id' => 'today', 'name' => __('Today')], ['id' => 'this_week', 'name' => __('This Week')], ['id' => 'this_month', 'name' => __('This Month')]]" 
                wire:model.live="dateFilter" 
                class="w-full" 
            />
        </div>

        <!-- Ledger Table -->
        <div class="bg-base-100 rounded-2xl shadow-sm border border-base-200 overflow-hidden">
            <table class="table w-full">
                <thead class="bg-base-200/50">
                    <tr>
                        <th>{{ __('Date & ID') }}</th>
                        <th>{{ __('Donor') }}</th>
                        <th>{{ __('Category') }}</th>
                        <th>{{ __('Amount / Method') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $tx)
                        <tr>
                            <td>
                                <div class="font-medium whitespace-nowrap">{{ (clone $tx->created_at)->setTimezone('Asia/Dhaka')->format('d M, Y h:i A') }}</div>
                                <div class="text-xs font-mono text-base-content/50 mt-1" title="{{ __('Transaction ID') }}">{{ $tx->transaction_id ?? 'TXN-'.$tx->id }}</div>
                            </td>
                            <td>
                                <div class="flex items-center gap-3">
                                    @if($tx->is_anonymous || !$tx->user)
                                        <div class="avatar placeholder">
                                            <div class="bg-neutral text-neutral-content rounded-full w-10">
                                                <span class="text-xs">{{ __('AN') }}</span>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="font-bold">{{ __('Anonymous / Offline') }}</div>
                                            <div class="text-xs text-base-content/50">{{ __('No linked account') }}</div>
                                        </div>
                                    @else
                                        <x-avatar :image="$tx->user->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode($tx->user->name)" class="!w-10 !h-10" />
                                        <div>
                                            <div class="font-bold">{{ $tx->user->name }}</div>
                                            <div class="text-xs text-base-content/70">{{ $tx->user->email }}</div>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <x-badge value="{{ ucfirst($tx->type) }}" class="badge-neutral badge-sm uppercase" />
                                @if($tx->type === 'campaign' && $tx->campaign)
                                    <div class="text-xs text-base-content/70 mt-1 truncate max-w-[150px]" title="{{ $tx->campaign->title }}">{{ $tx->campaign->title }}</div>
                                @elseif($tx->type === 'halaqah' && $tx->halaqah)
                                    <div class="text-xs text-base-content/70 mt-1 truncate max-w-[150px]" title="{{ $tx->halaqah->title }}">{{ $tx->halaqah->title }}</div>
                                @endif
                            </td>
                            <td>
                                <div class="font-bold text-primary">{{ __('BDT') }} {{ number_format($tx->amount, 2) }}</div>
                                <div class="text-xs uppercase tracking-widest text-base-content/70 mt-1">{{ __($tx->payment_method) }}</div>
                            </td>
                            <td>
                                @if($tx->status === 'confirmed')
                                    <x-badge value="{{ __('Confirmed') }}" class="badge-success badge-sm" />
                                @elseif($tx->status === 'pending')
                                    <x-badge value="{{ __('Pending Review') }}" class="badge-warning badge-sm" />
                                @elseif($tx->status === 'pending_payment')
                                    <x-badge value="{{ __('Unpaid / Due') }}" class="badge-error badge-sm" />
                                @else
                                    <x-badge value="{{ __('Voided') }}" class="badge-neutral badge-sm" />
                                @endif
                            </td>
                            <td class="text-right">
                                @if($tx->status === 'confirmed')
                                    <x-button icon="o-archive-box-x-mark" wire:click="voidTransaction({{ $tx->id }})" class="btn-sm btn-ghost text-error" tooltip="{{ __('Void Transaction') }}" wire:confirm="{{ __('Are you sure? This will mark the transaction as voided/rejected.') }}" spinner="voidTransaction({{ $tx->id }})" />
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="py-12 text-center text-base-content/50">
                                    <x-icon name="o-document-text" class="w-12 h-12 mx-auto mb-3 opacity-20" />
                                    <p>{{ __('No transactions found matching your criteria.') }}</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $transactions->links() }}
        </div>
    @endif

    @if($activeTab === 'manual_entry')
        <div class="max-w-4xl mx-auto">
            <x-form wire:submit="recordTransaction">
                <div class="bg-base-100 rounded-2xl shadow-sm border border-base-200 p-6 md:p-8 space-y-8">
                    
                    <div>
                        <h2 class="text-lg font-bold mb-4 flex items-center gap-2"><x-icon name="o-user" class="w-5 h-5 text-primary" /> {{ __('Donor Information') }}</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <x-choices 
                                label="{{ __('Registered User (Optional)') }}" 
                                wire:model="selectedUserId" 
                                :options="$usersForEntry" 
                                option-value="id" 
                                option-label="name" 
                                search-function="searchUsers" 
                                debounce="300ms"
                                placeholder="{{ __('Search user by name or email...') }}" 
                                hint="{{ __('Leave blank if anonymous or guest') }}"
                                single
                            />
                            
                            <div class="flex items-center h-full pt-6">
                                <x-toggle label="{{ __('Mark as Anonymous') }}" wire:model="isAnonymous" hint="{{ __('Hide name from public lists') }}" />
                            </div>
                        </div>
                    </div>

                    <hr class="border-base-300" />

                    <div>
                        <h2 class="text-lg font-bold mb-4 flex items-center gap-2"><x-icon name="o-banknotes" class="w-5 h-5 text-primary" /> {{ __('Transaction Details') }}</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                            <x-input label="{{ __('Amount') }}" wire:model="amount" type="number" step="0.01" prefix="৳" required />
                            <x-input label="{{ __('Date & Time') }}" wire:model="donatedAt" type="datetime-local" required />
                            <x-select label="{{ __('Payment Method') }}" wire:model.live="paymentMethod" :options="[['id' => 'cash', 'name' => 'Cash'], ['id' => 'bkash', 'name' => 'bKash'], ['id' => 'nagad', 'name' => 'Nagad'], ['id' => 'bank', 'name' => 'Bank Transfer'], ['id' => 'other', 'name' => 'Other']]" required />
                        </div>

                        @if(in_array($paymentMethod, ['bkash', 'nagad', 'bank']))
                            <div class="mb-6">
                                <x-select label="{{ __('Target Bank Account') }}" wire:model="bank_account_id" :options="$bankAccounts" option-value="id" option-label="name" placeholder="{{ __('Select Bank Account (Optional)') }}" />
                            </div>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <x-input label="{{ __('Transaction ID / Reference') }}" wire:model="transactionId" placeholder="{{ __('Optional') }}" />
                            
                            <x-select 
                                label="{{ __('Donation Type / Category') }}" 
                                wire:model.live="type" 
                                :options="[['id' => 'general', 'name' => __('General Fund')], ['id' => 'campaign', 'name' => __('Specific Campaign')], ['id' => 'halaqah', 'name' => __('Halaqah Collection')]]" 
                                required 
                            />
                        </div>

                        @if($type === 'campaign')
                            <div class="mb-6">
                                <x-select label="{{ __('Select Campaign') }}" wire:model="campaignId" :options="$this->activeCampaigns" option-value="id" option-label="title" placeholder="{{ __('Choose a campaign') }}" required />
                            </div>
                        @endif

                        @if($type === 'halaqah')
                            <div class="mb-6">
                                <x-select label="{{ __('Select Halaqah') }}" wire:model="halaqahId" :options="$this->recentHalaqahs" option-value="id" option-label="title" placeholder="{{ __('Choose a halaqah') }}" required />
                            </div>
                        @endif

                        <x-textarea label="{{ __('Admin Notes') }}" wire:model="note" placeholder="{{ __('Internal notes regarding this manual entry...') }}" rows="2" />
                    </div>

                    <hr class="border-base-300" />

                    <div class="flex items-center justify-between">
                        <x-toggle label="{{ __('Send Email/Notification Receipt') }}" wire:model="sendReceipt" class="toggle-primary" hint="{{ __('Only applies if a Registered User is selected') }}" />
                        
                        <x-button label="{{ __('Record Transaction') }}" type="submit" icon="o-check-circle" class="btn-primary" spinner="recordTransaction" />
                    </div>
                </div>
            </x-form>
        </div>
    @endif
</div>
