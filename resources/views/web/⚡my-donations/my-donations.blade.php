<div class="min-h-screen bg-slate-50 dark:bg-slate-950 pb-12">
    {{-- Philanthropic Header --}}
    <div class="bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 text-white py-16 relative overflow-hidden">
        <div class="absolute top-0 right-1/4 w-72 h-72 bg-emerald-500/10 rounded-full blur-[120px]"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="flex flex-col md:flex-row items-center justify-between gap-6 text-white">
                <div>
                    <h1 class="text-3xl md:text-5xl font-black mb-3 tracking-tight flex items-center gap-3">
                        <x-icon name="o-heart" class="w-8 h-8 text-rose-400" />
                        {{ __('My Contributions') }}
                    </h1>
                    <p class="text-white/60 max-w-xl text-sm leading-relaxed">
                        {{ __('"Those who spend their wealth in charity day and night, secretly and openly—their reward is with their Lord, and there will be no fear for them, nor will they grieve." (2:274)') }}
                    </p>
                </div>
                
                <div class="shrink-0 flex items-center gap-3 bg-white/5 backdrop-blur-md px-5 py-3 rounded-2xl border border-white/10 shadow-lg">
                    <div class="text-center px-4 border-r border-white/10">
                        <span class="block text-2xl font-black text-rose-400">{{ number_format($this->stats['total']) }}</span>
                        <span class="text-[10px] font-bold uppercase tracking-widest text-white/50">{{ __('Lifetime BDT') }}</span>
                    </div>
                    <div class="text-center px-4">
                        <span class="block text-2xl font-black text-emerald-400">{{ number_format($this->stats['ytd']) }}</span>
                        <span class="text-[10px] font-bold uppercase tracking-widest text-white/50">{{ __('This Year') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8 relative z-20 space-y-8">
        
        {{-- Main Dashboard Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            {{-- Left Column: Active Pledges --}}
            <div class="lg:col-span-1 space-y-6">
                
                {{-- Add Pledge Card --}}
                <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-xl shadow-teal-900/5 border border-slate-200 dark:border-slate-800 text-center relative overflow-hidden group">
                    <div class="absolute inset-0 bg-gradient-to-br from-teal-500/5 to-emerald-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
                    <div class="relative z-10">
                        <x-icon name="o-arrow-path-rounded-square" class="w-12 h-12 text-teal-500 mx-auto mb-3" />
                        <h3 class="font-black text-slate-800 dark:text-white text-lg mb-1">{{ __('Become a Sustainer') }}</h3>
                        <p class="text-xs text-slate-500 mb-5 px-4">
                            {{ __('Set up a recurring pledge to consistently support the community initiatives.') }}
                        </p>
                        <button wire:click="openPledgeModal" class="btn bg-gradient-to-r from-teal-500 to-emerald-500 text-white border-none shadow-md hover:shadow-lg hover:scale-[1.02] transition-all rounded-xl w-full font-bold relative z-20">
                            {{ __('Create New Pledge') }}
                        </button>
                    </div>
                </div>

                {{-- Active Pledges List --}}
                @if($this->activePledges->isNotEmpty())
                    <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-sm border border-slate-200 dark:border-slate-800">
                        <h3 class="font-black text-slate-800 dark:text-white mb-4 flex items-center gap-2">
                            <x-icon name="o-star" class="w-5 h-5 text-amber-500" />
                            {{ __('Active Pledges') }}
                        </h3>
                        <div class="space-y-4">
                            @foreach($this->activePledges as $pledge)
                                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-800 relative group overflow-hidden">
                                    <div class="absolute top-0 left-0 w-1 h-full bg-teal-500"></div>
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">
                                                {{ ucfirst($pledge->frequency) }} {{ __('Commitment') }}
                                            </span>
                                            <div class="text-xl font-black text-teal-600 dark:text-teal-400">
                                                ৳{{ number_format($pledge->amount) }}
                                            </div>
                                            <p class="text-[10px] text-slate-500 mt-1">
                                                {{ __('Started:') }} {{ $pledge->starts_at->format('M j, Y') }}
                                            </p>
                                        </div>
                                        <div>
                                            <button wire:click="cancelPledge({{ $pledge->id }})" wire:confirm="{{ __('Are you sure you want to cancel this recurring pledge?') }}" class="btn btn-circle btn-sm btn-ghost text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 tooltip tooltip-left" data-tip="{{ __('Cancel Pledge') }}">
                                                <x-icon name="o-x-mark" class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Right Column: History & Trends --}}
            <div class="lg:col-span-2 space-y-6">
                
                {{-- Chart / Trend Mini-Card --}}
                <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-sm border border-slate-200 dark:border-slate-800">
                    <h3 class="font-black text-slate-800 dark:text-white mb-6 flex items-center gap-2">
                        <x-icon name="o-chart-bar" class="w-5 h-5 text-indigo-500" />
                        {{ __('Giving Trend (Last 6 Months)') }}
                    </h3>
                    
                    @php $maxAmount = max($this->chartData['data']) ?: 1; @endphp
                    <div class="flex items-end justify-between h-32 gap-2 px-2">
                        @foreach($this->chartData['data'] as $index => $amount)
                            <div class="flex flex-col items-center gap-2 flex-1 group">
                                <div class="w-full relative flex items-end justify-center h-full rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors pt-4">
                                    {{-- Tooltip style amount --}}
                                    <div class="absolute -top-6 bg-slate-800 text-white text-[10px] font-bold px-2 py-1 rounded shadow-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-10">
                                        ৳{{ number_format($amount) }}
                                    </div>
                                    {{-- Bar --}}
                                    <div class="w-full max-w-[40px] bg-gradient-to-t from-teal-600 to-emerald-400 rounded-t-lg transition-all duration-700 ease-out shadow-sm"
                                         style="height: {{ max(($amount / $maxAmount) * 100, 5) }}%;">
                                    </div>
                                </div>
                                <span class="text-xs font-bold text-slate-400 uppercase">{{ $this->chartData['labels'][$index] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Donation History Table --}}
                <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
                    <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <h3 class="font-black text-slate-800 dark:text-white flex items-center gap-2">
                            <x-icon name="o-clock" class="w-5 h-5 text-blue-500" />
                            {{ __('Recent Donations') }}
                        </h3>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-slate-500 uppercase bg-slate-50 dark:bg-slate-800/50">
                                <tr>
                                    <th class="px-6 py-4 font-bold">{{ __('Date') }}</th>
                                    <th class="px-6 py-4 font-bold">{{ __('Purpose') }}</th>
                                    <th class="px-6 py-4 font-bold">{{ __('Amount') }}</th>
                                    <th class="px-6 py-4 font-bold">{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                @forelse($this->donationHistory as $donation)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="font-bold text-slate-700 dark:text-slate-300">
                                                {{ ($donation->donated_at ?? $donation->created_at)->format('M j, Y') }}
                                            </div>
                                            <div class="text-[10px] text-slate-400">
                                                {{ ($donation->donated_at ?? $donation->created_at)->format('h:i A') }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="font-semibold text-slate-800 dark:text-slate-200">
                                                @if($donation->type === 'campaign' && $donation->campaign)
                                                    {{ $donation->campaign->title }}
                                                @elseif($donation->type === 'halaqah' && $donation->halaqah)
                                                    {{ $donation->halaqah->name }}
                                                @else
                                                    {{ __('General Donation') }}
                                                @endif
                                            </div>
                                            @if($donation->note)
                                                <div class="text-xs text-slate-500 italic mt-0.5 max-w-[200px] truncate">
                                                    "{{ $donation->note }}"
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap font-black text-teal-600 dark:text-teal-400">
                                            ৳{{ number_format($donation->amount) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($donation->status === 'confirmed')
                                                <span class="badge badge-success badge-sm font-bold">{{ __('Confirmed') }}</span>
                                            @elseif($donation->status === 'pending_payment')
                                                <div class="flex items-center gap-2">
                                                    <span class="badge badge-error badge-sm font-bold animate-pulse">{{ __('Due') }}</span>
                                                    <x-button label="{{ __('Pay Now') }}" wire:click="openFulfillModal({{ $donation->id }})" class="btn-xs btn-outline border-error text-error hover:bg-error hover:text-white" />
                                                </div>
                                            @elseif($donation->status === 'pending')
                                                <span class="badge badge-warning badge-sm font-bold tooltip" data-tip="{{ __('Waiting for treasurer approval') }}">{{ __('Processing') }}</span>
                                            @else
                                                <span class="badge badge-ghost badge-sm font-bold">{{ ucfirst($donation->status) }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                                            <x-icon name="o-banknotes" class="w-12 h-12 mx-auto mb-3 opacity-20" />
                                            <p>{{ __('No donations found in your history.') }}</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($this->donationHistory->hasPages())
                        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                            {{ $this->donationHistory->links() }}
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>

    {{-- Create Pledge Modal --}}
    <x-modal wire:model="pledgeModal" title="Become a Sustainer" class="backdrop-blur-sm">
        <div class="space-y-6 pt-4">
            <div class="p-4 rounded-2xl bg-teal-50 dark:bg-teal-900/20 text-teal-800 dark:text-teal-300 text-xs leading-relaxed border border-teal-100 dark:border-teal-800/50">
                <x-icon name="o-information-circle" class="w-5 h-5 inline mr-1 text-teal-600 dark:text-teal-400" />
                {{ __('Your pledge is a sincere commitment. Based on your selected frequency, we will generate an intent/reminder for you to fulfill your donation manually (e.g. via bank transfer or cash to the treasurer).') }}
            </div>

            {{-- Predefined amounts --}}
            <div>
                <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 block">{{ __('Select Amount') }}</label>
                <div class="grid grid-cols-5 gap-2 mb-3">
                    @foreach(['200', '500', '1000', '2000', '5000'] as $val)
                    <button type="button" wire:click="$set('pledgeAmount', '{{ $val }}')"
                            class="py-2 rounded-xl border text-[11px] font-black transition-all text-center
                            {{ $pledgeAmount == $val
                                ? 'bg-rose-500 border-rose-600 text-white shadow shadow-rose-500/20'
                                : 'bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
                        ৳{{ $val }}
                    </button>
                    @endforeach
                </div>
                
                <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1 block">{{ __('Or Custom Commitment Amount (BDT)') }}</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 font-bold text-sm">৳</span>
                    <input type="number" wire:model="pledgeAmount" placeholder="{{ __('Enter amount') }}"
                           class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl pl-8 pr-4 py-2.5 text-sm font-bold text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500">
                </div>
                @error('pledgeAmount') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            {{-- Frequency Selection Buttons --}}
            <div>
                <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 block">{{ __('Pledge Frequency') }}</label>
                <div class="grid grid-cols-3 gap-2">
                    @foreach(['weekly' => __('Weekly'), 'monthly' => __('Monthly'), 'yearly' => __('Yearly')] as $key => $label)
                    <button type="button" wire:click="$set('pledgeFrequency', '{{ $key }}')"
                            class="py-2.5 rounded-xl border text-xs font-black transition-all text-center
                            {{ $pledgeFrequency === $key
                                ? 'bg-slate-900 dark:bg-slate-700 border-slate-950 text-white'
                                : 'bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
                        {{ $label }}
                    </button>
                    @endforeach
                </div>
                @error('pledgeFrequency') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
            </div>
        </div>
        
        <x-slot:actions>
            <x-button label="{{ __('Cancel') }}" wire:click="$set('pledgeModal', false)" class="btn-ghost rounded-xl" />
            <x-button label="{{ __('Confirm Pledge') }}" wire:click="savePledge" icon="o-check" class="bg-gradient-to-r from-teal-500 to-emerald-500 text-white border-none shadow-md rounded-xl" spinner="savePledge" />
        </x-slot:actions>
    </x-modal>

    {{-- Fulfill Payment Modal --}}
    <x-modal wire:model="fulfillModal" title="Fulfill Donation" class="backdrop-blur-sm">
        <div class="space-y-4 pt-2">
            <p class="text-sm text-slate-500 mb-2">{{ __('Please select your payment method first.') }}</p>
            
            <x-select wire:model.live="paymentMethod" label="{{ __('Payment Method Used') }}" :options="[
                ['id' => 'cash', 'name' => 'Cash (Handed to Treasurer)'],
                ['id' => 'bkash', 'name' => 'bKash'],
                ['id' => 'nagad', 'name' => 'Nagad'],
                ['id' => 'bank', 'name' => 'Bank Transfer'],
                ['id' => 'other', 'name' => 'Other']
            ]" placeholder="Select a method..." required />

            {{-- Organization Accounts Box (Filtered by Payment Method) --}}
            @if($paymentMethod && $this->filteredBankAccounts->isNotEmpty())
                <div class="mt-6 mb-2">
                    <p class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">{{ __('Available :type Accounts:', ['type' => ucfirst($paymentMethod)]) }}</p>
                    <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl p-3 border border-slate-200 dark:border-slate-700/50 max-h-48 overflow-y-auto space-y-3">
                        @foreach($this->filteredBankAccounts as $account)
                            <div class="flex items-start gap-3">
                                <div class="p-2 rounded-lg shrink-0 {{ $account->type_color }}">
                                    <x-icon :name="$account->type_icon" class="w-5 h-5" />
                                </div>
                                <div>
                                    <h4 class="font-bold text-sm text-slate-800 dark:text-slate-200 leading-none mb-1">{{ $account->name }}</h4>
                                    @if($account->account_number)
                                        <p class="text-xs text-slate-600 dark:text-slate-400 font-mono font-semibold">{{ __('A/C:') }} {{ $account->account_number }}</p>
                                    @endif
                                    @if($account->bank_name || $account->branch)
                                        <p class="text-[10px] text-slate-500">{{ $account->bank_name }} {{ $account->branch ? ' - ' . $account->branch : '' }}</p>
                                    @endif
                                    @if($account->holder_name)
                                        <p class="text-[10px] text-slate-500">{{ __('Name:') }} {{ $account->holder_name }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                
                <x-select wire:model="bankAccountId" label="{{ __('Destination Account') }}" :options="$this->filteredBankAccounts" option-value="id" option-label="name" placeholder="Select where you sent the money..." required />
            @elseif($paymentMethod)
                <div class="p-3 rounded-xl bg-warning/20 text-warning-content text-sm mt-4 mb-4">
                    <x-icon name="o-exclamation-triangle" class="w-5 h-5 inline mr-1" />
                    {{ __('No :type accounts are currently configured in the system. Please hand cash directly to the treasurer.', ['type' => ucfirst($paymentMethod)]) }}
                </div>
            @endif

            @if(in_array($paymentMethod, ['bkash', 'nagad', 'bank']))
                <x-input wire:model="transactionId" label="{{ __('Transaction ID') }}" placeholder="e.g. 9ABCXYZ23" hint="{{ __('Required for verification') }}" required />
            @endif
            
            <x-textarea wire:model="paymentNote" label="{{ __('Note (Optional)') }}" placeholder="Any message to the treasurer?" rows="2" />
        </div>
        
        <x-slot:actions>
            <x-button label="{{ __('Cancel') }}" wire:click="$set('fulfillModal', false)" class="btn-ghost rounded-xl" />
            <x-button label="{{ __('Submit Payment Info') }}" wire:click="submitFulfillment" icon="o-paper-airplane" class="btn-primary rounded-xl" spinner="submitFulfillment" />
        </x-slot:actions>
    </x-modal>
</div>