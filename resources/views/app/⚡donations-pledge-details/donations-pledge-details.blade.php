<div>
    <!-- Header -->
    <x-header title="{{ $this->getTitle() }}" subtitle="{{ __('Pledge ID: #:id', ['id' => $pledge->id]) }}" separator>
        <x-slot:actions>
            <x-button label="{{ __('Back to Pledges') }}" icon="o-arrow-left" link="{{ route('app.donations.pledges') }}" class="btn-ghost" />
            <x-button label="{{ __('Edit Pledge') }}" icon="o-pencil" wire:click="openEditModal" class="btn-primary" />
        </x-slot:actions>
    </x-header>

    <!-- Details Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- User Details -->
        <div class="bg-base-100 rounded-2xl shadow-sm border border-base-200 p-6 flex items-center gap-4">
            <x-avatar :image="$pledge->user?->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode($pledge->user?->name ?? 'User')" class="!w-16 !h-16" />
            <div>
                <h3 class="font-bold text-lg">{{ $pledge->user?->name ?? __('Unknown') }}</h3>
                <p class="text-sm text-base-content/70">{{ $pledge->user?->email }}</p>
                <div class="mt-2 text-xs">
                    {{ __('Member since: :date', ['date' => $pledge->user?->created_at?->format('d M, Y') ?? 'N/A']) }}
                </div>
            </div>
        </div>

        <!-- Commitment Details -->
        <div class="bg-base-100 rounded-2xl shadow-sm border border-base-200 p-6">
            <div class="text-sm text-base-content/70 uppercase tracking-wider font-semibold mb-1">{{ __('Commitment') }}</div>
            <div class="text-3xl font-bold text-primary mb-2">{{ __('BDT') }} {{ number_format($pledge->amount, 2) }}</div>
            <div class="flex items-center gap-2 mb-2">
                <x-badge value="{{ __($pledge->frequency) }}" class="badge-neutral badge-sm uppercase" />
                @if(!$pledge->is_active)
                    <x-badge value="{{ __('Paused') }}" class="badge-neutral badge-sm" />
                @elseif($pledge->next_due_at && $pledge->next_due_at->isPast())
                    <x-badge value="{{ __('Overdue') }}" class="badge-error badge-sm" />
                @else
                    <x-badge value="{{ __('Active') }}" class="badge-success badge-sm" />
                @endif
            </div>
        </div>

        <!-- Schedule -->
        <div class="bg-base-100 rounded-2xl shadow-sm border border-base-200 p-6">
            <div class="text-sm text-base-content/70 uppercase tracking-wider font-semibold mb-1">{{ __('Schedule') }}</div>
            <div class="mt-2 space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-base-content/70">{{ __('Started On') }}:</span>
                    <span class="font-medium">{{ $pledge->starts_at ? $pledge->starts_at->format('d M, Y') : __('N/A') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-base-content/70">{{ __('Last Donated') }}:</span>
                    <span class="font-medium">{{ $pledge->last_donated_at ? $pledge->last_donated_at->format('d M, Y') : __('Never') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-base-content/70">{{ __('Next Due') }}:</span>
                    <span class="font-bold {{ $pledge->next_due_at && $pledge->next_due_at->isPast() ? 'text-error' : 'text-success' }}">
                        {{ $pledge->next_due_at ? $pledge->next_due_at->format('d M, Y') : __('Not Set') }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Payments/Invoices History -->
    <h3 class="text-xl font-bold mb-4">{{ __('Payment History (Recurring Invoices)') }}</h3>
    <div class="bg-base-100 rounded-2xl shadow-sm border border-base-200 overflow-hidden">
        <table class="table w-full">
            <thead class="bg-base-200/50">
                <tr>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Amount') }}</th>
                    <th>{{ __('Method') }}</th>
                    <th>{{ __('Transaction ID') }}</th>
                    <th>{{ __('Status') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                    <tr>
                        <td>
                            <div class="font-medium">{{ $payment->created_at->format('d M, Y h:i A') }}</div>
                            <div class="text-xs text-base-content/50">{{ $payment->created_at->diffForHumans() }}</div>
                        </td>
                        <td class="font-bold">
                            {{ $payment->currency }} {{ number_format($payment->amount, 2) }}
                        </td>
                        <td class="uppercase text-sm tracking-wide">
                            {{ __($payment->payment_method) }}
                        </td>
                        <td class="font-mono text-sm text-base-content/70">
                            {{ $payment->transaction_id ?? '-' }}
                        </td>
                        <td>
                            @if($payment->status === 'confirmed')
                                <x-badge value="{{ __('Confirmed') }}" class="badge-success badge-sm" />
                            @elseif($payment->status === 'pending')
                                <x-badge value="{{ __('Pending Review') }}" class="badge-warning badge-sm" />
                            @elseif($payment->status === 'pending_payment')
                                <x-badge value="{{ __('Unpaid / Due') }}" class="badge-error badge-sm" />
                            @else
                                <x-badge value="{{ ucfirst($payment->status) }}" class="badge-neutral badge-sm" />
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="py-12 text-center text-base-content/50">
                                <x-icon name="o-banknotes" class="w-12 h-12 mx-auto mb-3 opacity-20" />
                                <p>{{ __('No recurring payments found for this user.') }}</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $payments->links() }}
    </div>

    <!-- Edit Modal -->
    <x-modal wire:model="editModal" title="{{ __('Edit Pledge Configuration') }}" separator>
        <div class="space-y-4 py-2">
            <x-input label="{{ __('Amount (BDT)') }}" wire:model="amount" type="number" step="0.01" prefix="৳" />
            
            <x-select 
                label="{{ __('Frequency') }}" 
                wire:model="frequency" 
                :options="[['id' => 'weekly', 'name' => __('Weekly')], ['id' => 'monthly', 'name' => __('Monthly')], ['id' => 'yearly', 'name' => __('Yearly')]]" 
            />
            
            <x-toggle label="{{ __('Active Status') }}" wire:model="isActive" hint="{{ __('Turn off to pause automated invoice generation.') }}" />
        </div>
        
        <x-slot:actions>
            <x-button label="{{ __('Cancel') }}" @click="$wire.editModal = false" class="btn-ghost" />
            <x-button label="{{ __('Save Changes') }}" wire:click="savePledge" class="btn-primary" spinner="savePledge" />
        </x-slot:actions>
    </x-modal>
</div>
