<div class="p-6 space-y-6">
    {{-- Header & Stats --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <div>
            <h1 class="text-2xl font-black text-slate-800 dark:text-white flex items-center gap-2">
                <x-icon name="o-shield-check" class="w-7 h-7 text-indigo-500" />
                {{ __('Payment Verification Inbox') }}
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                {{ __('Reconcile and approve pending user mobile banking & bank transfers. Review transaction references before verifying.') }}
            </p>
        </div>

        <div class="flex gap-4 shrink-0">
            <div class="bg-indigo-50 dark:bg-indigo-950/25 px-5 py-3 rounded-2xl border border-indigo-100 dark:border-indigo-900/40 text-center min-w-[130px]">
                <span class="block text-2xl font-black text-indigo-600 dark:text-indigo-400">{{ $stats['total_pending_count'] }}</span>
                <span class="text-[9px] font-bold uppercase tracking-wider text-indigo-500 dark:text-indigo-400/80">{{ __('Pending Payments') }}</span>
            </div>
            <div class="bg-emerald-50 dark:bg-emerald-950/25 px-5 py-3 rounded-2xl border border-emerald-100 dark:border-emerald-900/40 text-center min-w-[150px]">
                <span class="block text-2xl font-black text-emerald-600 dark:text-emerald-400">৳{{ number_format($stats['total_pending_amount']) }}</span>
                <span class="text-[9px] font-bold uppercase tracking-wider text-emerald-500 dark:text-emerald-400/80">{{ __('Unverified Value') }}</span>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="flex flex-col md:flex-row gap-4 bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
        <div class="flex-1">
            <x-input icon="o-magnifying-glass" wire:model.live.debounce.300ms="search" placeholder="{{ __('Search by donor name or email...') }}" class="rounded-xl" />
        </div>
        <div class="w-full md:w-64">
            <x-select icon="o-funnel" wire:model.live="methodFilter" :options="[
                ['id' => '', 'name' => __('All Payment Methods')],
                ['id' => 'bkash', 'name' => 'bKash'],
                ['id' => 'nagad', 'name' => 'Nagad'],
                ['id' => 'bank', 'name' => 'Bank Transfer'],
                ['id' => 'cash', 'name' => 'Cash / Offline'],
                ['id' => 'other', 'name' => 'Other / Custom']
            ]" class="rounded-xl" />
        </div>
    </div>

    {{-- Pending Table --}}
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-50 dark:bg-slate-800/40 text-slate-500 uppercase font-bold">
                    <tr>
                        <th class="px-6 py-4">{{ __('Donor') }}</th>
                        <th class="px-6 py-4">{{ __('Type & Destination') }}</th>
                        <th class="px-6 py-4">{{ __('Payment Method') }}</th>
                        <th class="px-6 py-4">{{ __('Transaction ID') }}</th>
                        <th class="px-6 py-4">{{ __('Amount') }}</th>
                        <th class="px-6 py-4">{{ __('Submitted') }}</th>
                        <th class="px-6 py-4 text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                    @forelse($pendingDonations as $donation)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                            {{-- Donor Profile --}}
                            <td class="px-6 py-4 font-bold text-slate-800 dark:text-slate-200">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center font-bold text-indigo-500 uppercase">
                                        {{ substr($donation->user?->name ?? 'G', 0, 2) }}
                                    </div>
                                    <div>
                                        <span class="block text-slate-800 dark:text-slate-100 font-bold">{{ $donation->user?->name ?? __('Guest') }}</span>
                                        <span class="block text-[10px] text-slate-400 font-medium">{{ $donation->user?->email }}</span>
                                    </div>
                                </div>
                            </td>

                            {{-- Type & Destination --}}
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-400">
                                @if($donation->type === 'campaign')
                                    <span class="badge badge-indigo badge-xs text-white uppercase font-bold">{{ __('Campaign') }}</span>
                                    <span class="block font-bold text-slate-700 dark:text-slate-300 mt-0.5 line-clamp-1">{{ $donation->campaign?->title }}</span>
                                @elseif($donation->type === 'recurring')
                                    <span class="badge badge-success badge-xs text-white uppercase font-bold">{{ __('Pledge / Recurring') }}</span>
                                    <span class="block font-bold text-slate-700 dark:text-slate-300 mt-0.5">{{ __('Weekly/Monthly Commit') }}</span>
                                @else
                                    <span class="badge badge-neutral badge-xs uppercase font-bold">{{ __('General Fund') }}</span>
                                @endif
                            </td>

                            {{-- Payment Method --}}
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider
                                    @if($donation->payment_method === 'bkash') bg-pink-100 text-pink-700 dark:bg-pink-950/30 dark:text-pink-400
                                    @elseif($donation->payment_method === 'nagad') bg-orange-100 text-orange-700 dark:bg-orange-950/30 dark:text-orange-400
                                    @elseif($donation->payment_method === 'bank') bg-blue-100 text-blue-700 dark:bg-blue-950/30 dark:text-blue-400
                                    @else bg-slate-100 text-slate-700 dark:bg-slate-850 dark:text-slate-400 @endif">
                                    {{ $donation->payment_method }}
                                </span>
                            </td>

                            {{-- Transaction ID --}}
                            <td class="px-6 py-4">
                                @if($donation->transaction_id)
                                    <span class="font-mono text-indigo-600 dark:text-indigo-400 font-bold bg-slate-50 dark:bg-slate-800/40 px-2 py-0.5 rounded border border-slate-100 dark:border-slate-800">{{ $donation->transaction_id }}</span>
                                @else
                                    <span class="text-slate-400 italic">{{ __('None') }}</span>
                                @endif
                            </td>

                            {{-- Amount --}}
                            <td class="px-6 py-4 font-black text-slate-800 dark:text-white text-sm">
                                ৳{{ number_format($donation->amount) }}
                            </td>

                            {{-- Submitted --}}
                            <td class="px-6 py-4 text-slate-500">
                                {{ $donation->created_at->diffForHumans() }}
                            </td>

                            {{-- Actions --}}
                            <td class="px-6 py-4 text-right">
                                <x-button label="{{ __('Verify') }}" icon="o-magnifying-glass" wire:click="selectDonation({{ $donation->id }})" class="btn-sm bg-indigo-50 dark:bg-indigo-950/30 text-indigo-600 dark:text-indigo-400 border-none rounded-xl font-bold hover:bg-indigo-100" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center text-slate-400">
                                <x-icon name="o-shield-check" class="w-16 h-16 mx-auto mb-3 opacity-30 text-emerald-500" />
                                <h3 class="font-black text-slate-800 dark:text-white text-lg mb-1">{{ __('Inbox is Empty') }}</h3>
                                <p class="text-xs text-slate-500 max-w-sm mx-auto">{{ __('Great! All pending user payment transactions have been verified and reconciled.') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($pendingDonations->hasPages())
            <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                {{ $pendingDonations->links() }}
            </div>
        @endif
    </div>

    {{-- Slide-over Verification Drawer (Option 1.A) --}}
    <x-drawer wire:model="verifyDrawer" title="{{ __('Review & Verify Payment') }}" right class="w-11/12 md:w-[500px]" separator>
        @if($this->selectedDonation)
            @php $donation = $this->selectedDonation; @endphp
            <div class="space-y-6">
                {{-- Donor Card --}}
                <div class="bg-slate-50 dark:bg-slate-800/40 p-4 rounded-2xl border border-slate-100 dark:border-slate-800">
                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">{{ __('Submitted By') }}</span>
                    <div class="flex items-center gap-3 mt-2">
                        <div class="w-10 h-10 rounded-full bg-indigo-500/10 text-indigo-500 flex items-center justify-center font-bold text-sm uppercase shrink-0">
                            {{ substr($donation->user?->name ?? 'G', 0, 2) }}
                        </div>
                        <div>
                            <h4 class="font-black text-slate-800 dark:text-white text-sm leading-snug">{{ $donation->user?->name ?? __('Guest') }}</h4>
                            <span class="block text-xs text-slate-400 font-medium">{{ $donation->user?->email }}</span>
                        </div>
                    </div>
                </div>

                {{-- Amount and Target --}}
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-slate-50 dark:bg-slate-800/40 p-4 rounded-2xl border border-slate-100 dark:border-slate-800 text-center">
                        <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">{{ __('Payment Value') }}</span>
                        <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400">৳{{ number_format($donation->amount) }}</span>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-800/40 p-4 rounded-2xl border border-slate-100 dark:border-slate-800 text-center">
                        <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">{{ __('Destination Fund') }}</span>
                        @if($donation->type === 'campaign')
                            <span class="text-xs font-black text-indigo-600 dark:text-indigo-400 block truncate" title="{{ $donation->campaign?->title }}">
                                {{ $donation->campaign?->title }}
                            </span>
                        @elseif($donation->type === 'recurring')
                            <span class="text-xs font-black text-emerald-600 dark:text-emerald-400 block">
                                {{ __('Pledge Commitment') }}
                            </span>
                        @else
                            <span class="text-xs font-black text-slate-700 dark:text-slate-300 block">
                                {{ __('General Fund') }}
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Payment Verification Details --}}
                <div class="space-y-4 pt-2">
                    <h3 class="text-xs font-black uppercase text-slate-400 tracking-wider">{{ __('Transaction Proof') }}</h3>
                    
                    {{-- Method / TXID --}}
                    <div class="flex justify-between items-center py-3 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-xs text-slate-500 font-bold">{{ __('Payment Method') }}</span>
                        <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-wider bg-indigo-50 text-indigo-600 dark:bg-indigo-950/20 dark:text-indigo-400">
                            {{ $donation->payment_method }}
                        </span>
                    </div>

                    <div class="flex justify-between items-center py-3 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-xs text-slate-500 font-bold">{{ __('Transaction ID') }}</span>
                        <span class="font-mono text-xs font-bold text-slate-800 dark:text-white select-all bg-slate-50 dark:bg-slate-850 px-2 py-0.5 rounded">{{ $donation->transaction_id ?: __('N/A') }}</span>
                    </div>

                    {{-- Target Account info --}}
                    <div class="flex justify-between items-start py-3 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-xs text-slate-500 font-bold shrink-0">{{ __('Sent To (Account)') }}</span>
                        <div class="text-right">
                            @if($donation->bankAccount)
                                <span class="text-xs font-bold text-slate-800 dark:text-white block">{{ $donation->bankAccount->holder_name }}</span>
                                <span class="text-[10px] text-slate-400 block font-medium">{{ $donation->bankAccount->bank_name }} · {{ $donation->bankAccount->account_number }}</span>
                            @else
                                <span class="text-xs font-bold text-slate-400 italic block">{{ __('No account mapped') }}</span>
                            @endif
                        </div>
                    </div>

                    {{-- User's Note / Comment --}}
                    @if($donation->note)
                        <div class="bg-slate-50 dark:bg-slate-850 p-4 rounded-2xl border border-slate-100 dark:border-slate-800/80">
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block mb-1">{{ __('Message from Donor') }}</span>
                            <p class="text-xs text-slate-600 dark:text-slate-400 italic">
                                "{{ $donation->note }}"
                            </p>
                        </div>
                    @endif
                </div>

                {{-- Action buttons --}}
                <div class="flex gap-3 pt-6 border-t border-slate-100 dark:border-slate-800">
                    <x-button label="{{ __('Reject Payment') }}" icon="o-x-circle" wire:click="openRejectionModal" class="btn-error btn-ghost text-rose-500 rounded-xl flex-1 font-bold" />
                    <x-button label="{{ __('Approve & Verify') }}" icon="o-check" wire:click="approveDonation" class="bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl flex-1 font-bold shadow-sm" spinner="approveDonation" />
                </div>
            </div>
        @endif
    </x-drawer>

    {{-- Rejection Reason Modal (Option 2.A) --}}
    <x-modal wire:model="rejectionModal" title="{{ __('Reject Payment') }}" class="backdrop-blur-sm">
        <div class="space-y-4 pt-2">
            <p class="text-xs text-slate-500 leading-relaxed">
                {{ __('Provide a detailed reason why this donation verification failed. This explanation will be sent to the donor via notifications.') }}
            </p>
            <x-textarea label="{{ __('Explanation Note') }}" wire:model="rejectionNote" placeholder="{{ __('e.g. The Transaction ID does not match any record. Please check again.') }}" rows="4" required />
        </div>
        <x-slot:actions>
            <x-button label="{{ __('Cancel') }}" wire:click="$set('rejectionModal', false)" class="btn-ghost rounded-xl" />
            <x-button label="{{ __('Confirm Rejection') }}" wire:click="rejectDonation" icon="o-x-mark" class="bg-rose-600 text-white border-none rounded-xl" spinner="rejectDonation" />
        </x-slot:actions>
    </x-modal>
</div>
