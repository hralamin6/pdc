<div>
    <x-header title="Bank Accounts" subtitle="Manage cash, bKash, Nagad, and bank accounts" separator>
        <x-slot:actions>
            <x-button icon="o-plus" label="New Account" class="bg-gradient-to-r from-primary to-secondary text-white border-none shadow-lg shadow-primary/30 hover:scale-105 transition-transform" wire:click="openModal()" />
        </x-slot:actions>
    </x-header>

    {{-- Account Cards with Live Balances --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        @forelse($accounts as $acc)
            <div class="bg-base-100 rounded-2xl border border-base-content/5 p-6 shadow-sm hover:shadow-lg transition-all group" wire:key="acc-{{ $acc->id }}">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl flex items-center justify-center {{ $acc->type_color }}">
                            <x-icon :name="$acc->type_icon" class="w-5 h-5" />
                        </div>
                        <div>
                            <h3 class="font-bold text-base-content">{{ $acc->name }}</h3>
                            <p class="text-xs font-semibold uppercase tracking-wider text-base-content/40">{{ ucfirst($acc->type) }}</p>
                        </div>
                    </div>
                    @if(!$acc->is_active)
                        <span class="badge badge-ghost badge-sm">Inactive</span>
                    @endif
                </div>

                {{-- Live Balance --}}
                <div class="bg-base-200/50 rounded-xl p-4 mb-4">
                    <p class="text-xs font-bold uppercase tracking-widest text-base-content/40 mb-1">Current Balance</p>
                    @php $balance = $acc->balance; @endphp
                    <p class="text-2xl font-black {{ $balance >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600' }}">
                        ৳{{ number_format(abs($balance), 2) }}
                        @if($balance < 0) <span class="text-sm">(deficit)</span> @endif
                    </p>
                </div>

                <div class="text-xs text-base-content/50 space-y-1 mb-4">
                    @if($acc->account_number)
                        <p>Account: <span class="font-mono">{{ $acc->account_number }}</span></p>
                    @endif
                    @if($acc->bank_name)
                        <p>Bank: {{ $acc->bank_name }}{{ $acc->branch ? ' — ' . $acc->branch : '' }}</p>
                    @endif
                    @if($acc->holder_name)
                        <p>Holder: {{ $acc->holder_name }}</p>
                    @endif
                    <p>{{ $acc->expenses_count }} expenses recorded</p>
                </div>

                <div class="flex gap-2 pt-3 border-t border-base-content/5">
                    <x-button icon="o-pencil" label="Edit" class="btn-ghost btn-sm flex-1" wire:click="openModal({{ $acc->id }})" />
                    <x-button icon="o-trash" class="btn-ghost btn-sm text-error" wire:click="delete({{ $acc->id }})" wire:confirm="Delete this account?" tooltip="Delete" />
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-16 bg-base-200/50 rounded-2xl border border-dashed border-base-content/10">
                <x-icon name="o-credit-card" class="w-12 h-12 text-base-content/20 mx-auto mb-4" />
                <h3 class="text-lg font-bold text-base-content/70 mb-1">No accounts yet</h3>
                <p class="text-base-content/50 text-sm mb-4">Add your first account to start tracking balances.</p>
                <x-button icon="o-plus" label="Add Account" class="btn-primary" wire:click="openModal()" />
            </div>
        @endforelse
    </div>

    {{-- Modal --}}
    <x-modal wire:model="modal" title="{{ $editingId ? 'Edit Account' : 'New Account' }}" class="backdrop-blur-sm">
        <div class="space-y-4">
            <x-input wire:model="name" label="Account Name *" placeholder="e.g. Main bKash Account" class="input-bordered rounded-xl" />

            <div>
                <label class="label"><span class="label-text font-semibold">Account Type *</span></label>
                <div class="grid grid-cols-3 gap-2">
                    @foreach(['cash' => ['icon' => 'o-banknotes', 'label' => 'Cash'], 'bkash' => ['icon' => 'o-device-phone-mobile', 'label' => 'bKash'], 'nagad' => ['icon' => 'o-device-phone-mobile', 'label' => 'Nagad'], 'bank' => ['icon' => 'o-building-library', 'label' => 'Bank'], 'other' => ['icon' => 'o-credit-card', 'label' => 'Other']] as $val => $opt)
                        <button type="button" wire:click="$set('type', '{{ $val }}')"
                            class="flex flex-col items-center gap-1 p-3 rounded-xl border-2 transition-all {{ $type === $val ? 'border-primary bg-primary/10 text-primary' : 'border-base-content/10 hover:border-base-content/30' }}">
                            <x-icon :name="$opt['icon']" class="w-5 h-5" />
                            <span class="text-xs font-bold">{{ $opt['label'] }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <x-input wire:model="account_number" label="Account Number" placeholder="01XXXXXXXXX" class="input-bordered rounded-xl" />
                <x-input wire:model="holder_name" label="Holder Name" class="input-bordered rounded-xl" />
            </div>

            @if($type === 'bank')
                <div class="grid grid-cols-2 gap-4">
                    <x-input wire:model="bank_name" label="Bank Name" placeholder="Dutch-Bangla Bank" class="input-bordered rounded-xl" />
                    <x-input wire:model="branch" label="Branch" class="input-bordered rounded-xl" />
                </div>
            @endif

            <x-textarea wire:model="notes" label="Notes (optional)" rows="2" class="textarea-bordered rounded-xl" />

            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" wire:model="is_active" class="checkbox checkbox-primary" />
                <span class="label-text font-semibold">Active</span>
            </label>
        </div>

        <x-slot:actions>
            <x-button label="Cancel" wire:click="$set('modal', false)" class="btn-ghost" />
            <x-button label="{{ $editingId ? 'Update' : 'Create' }}" icon="o-check" wire:click="save" class="bg-gradient-to-r from-primary to-secondary text-white border-none font-bold" />
        </x-slot:actions>
    </x-modal>
</div>
