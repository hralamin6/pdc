<div>
    <x-header title="Fund Transfers" subtitle="Move funds between accounts" separator>
        <x-slot:actions>
            <x-button icon="o-plus" label="New Transfer" class="bg-gradient-to-r from-primary to-secondary text-white border-none shadow-lg shadow-primary/30 hover:scale-105 transition-transform" wire:click="openModal()" />
        </x-slot:actions>
    </x-header>

    {{-- Stats Row --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        <div class="bg-base-100 rounded-2xl border border-base-content/5 p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-widest text-base-content/40 mb-1">Total Transferred</p>
            <p class="text-2xl font-black text-base-content">৳{{ number_format($totalTransferred, 2) }}</p>
        </div>
        <div class="bg-base-100 rounded-2xl border border-base-content/5 p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-widest text-base-content/40 mb-1">Total Fees Paid</p>
            <p class="text-2xl font-black text-rose-600">৳{{ number_format($totalFees, 2) }}</p>
        </div>
        <div class="bg-base-100 rounded-2xl border border-base-content/5 p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-widest text-base-content/40 mb-1">Net Transferred</p>
            <p class="text-2xl font-black text-emerald-600">৳{{ number_format($totalTransferred - $totalFees, 2) }}</p>
        </div>
    </div>

    {{-- Transfer History Table --}}
    <div class="bg-base-100 rounded-2xl border border-base-content/5 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-base-content/5">
            <h3 class="font-bold text-lg flex items-center gap-2">
                <x-icon name="o-arrows-right-left" class="w-5 h-5 text-primary" /> Transfer History
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="table table-sm">
                <thead>
                    <tr class="text-base-content/50 text-xs uppercase tracking-wider">
                        <th>Date</th>
                        <th>From → To</th>
                        <th>Amount</th>
                        <th>Fee</th>
                        <th>Net Received</th>
                        <th>Reference</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transfers as $t)
                        <tr class="hover:bg-base-200/50 transition-colors" wire:key="transfer-{{ $t->id }}">
                            <td class="text-sm font-medium">{{ $t->transfer_date->format('d M Y') }}</td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <span class="badge badge-ghost badge-sm font-semibold">{{ $t->fromAccount->name }}</span>
                                    <x-icon name="o-arrow-right" class="w-3 h-3 text-base-content/40" />
                                    <span class="badge badge-ghost badge-sm font-semibold">{{ $t->toAccount->name }}</span>
                                </div>
                            </td>
                            <td class="font-bold">৳{{ number_format($t->amount, 2) }}</td>
                            <td class="text-rose-600 font-semibold">{{ $t->fee > 0 ? '৳' . number_format($t->fee, 2) : '—' }}</td>
                            <td class="text-emerald-600 font-bold">৳{{ number_format($t->net_received, 2) }}</td>
                            <td class="text-base-content/50 text-xs">{{ $t->reference_id ?? '—' }}</td>
                            <td><span class="badge badge-sm {{ $t->status_color }}">{{ ucfirst($t->status) }}</span></td>
                            <td>
                                <div class="flex gap-1">
                                    <x-button icon="o-pencil" class="btn-ghost btn-xs" wire:click="openModal({{ $t->id }})" tooltip="Edit" />
                                    <x-button icon="o-trash" class="btn-ghost btn-xs text-error" wire:click="delete({{ $t->id }})" wire:confirm="Delete this transfer? The auto-generated fee expense will also be removed." tooltip="Delete" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-12 text-base-content/40">
                                <x-icon name="o-arrows-right-left" class="w-10 h-10 mx-auto mb-3 opacity-30" />
                                <p class="font-medium">No transfers recorded yet.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transfers->hasPages())
            <div class="p-4 border-t border-base-content/5">{{ $transfers->links() }}</div>
        @endif
    </div>

    {{-- Transfer Modal --}}
    <x-modal wire:model="transferModal" title="{{ $editingId ? 'Edit Transfer' : 'New Fund Transfer' }}" class="backdrop-blur-sm">
        <div class="space-y-4">

            {{-- From / To Accounts --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="label"><span class="label-text font-semibold">From Account *</span></label>
                    <select wire:model.live="from_account_id" class="select select-bordered w-full rounded-xl">
                        <option value="0">Select source account</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->name }} ({{ ucfirst($acc->type) }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label"><span class="label-text font-semibold">To Account *</span></label>
                    <select wire:model.live="to_account_id" class="select select-bordered w-full rounded-xl">
                        <option value="0">Select destination account</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}" {{ $acc->id == $from_account_id ? 'disabled' : '' }}>{{ $acc->name }} ({{ ucfirst($acc->type) }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Amount & Fee --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-input wire:model.live="amount" label="Amount to Send (৳) *" type="number" step="0.01" min="1" placeholder="0.00" class="input-bordered rounded-xl" />
                <x-input wire:model.live="fee" label="Transfer Fee (৳)" type="number" step="0.01" min="0" placeholder="0.00" class="input-bordered rounded-xl" hint="Leave 0 if no fee" />
            </div>

            {{-- Net Received (computed) --}}
            @if((float)$amount > 0)
                <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-xl p-4 border border-emerald-200 dark:border-emerald-800">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-bold text-emerald-700 dark:text-emerald-400">Net Amount Received</span>
                        <span class="text-xl font-black text-emerald-600 dark:text-emerald-400">৳{{ number_format($this->netReceived, 2) }}</span>
                    </div>
                    @if((float)$fee > 0)
                        <p class="text-xs text-emerald-600/70 dark:text-emerald-400/70 mt-1">৳{{ $amount }} − ৳{{ $fee }} fee = ৳{{ number_format($this->netReceived, 2) }} received</p>
                        <p class="text-xs text-amber-600 dark:text-amber-400 mt-1 font-semibold">⚡ A fee expense of ৳{{ $fee }} will be auto-logged.</p>
                    @endif
                </div>
            @endif

            {{-- Date & Reference --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-input wire:model="transfer_date" label="Transfer Date *" type="date" class="input-bordered rounded-xl" />
                <x-input wire:model="reference_id" label="Reference / TxID" placeholder="bKash/Nagad transaction ID" class="input-bordered rounded-xl" />
            </div>

            {{-- Status --}}
            <div>
                <label class="label"><span class="label-text font-semibold">Status *</span></label>
                <select wire:model="status" class="select select-bordered w-full rounded-xl">
                    <option value="completed">Completed</option>
                    <option value="pending">Pending</option>
                    <option value="failed">Failed</option>
                </select>
            </div>

            <x-textarea wire:model="notes" label="Notes" placeholder="Optional notes..." rows="2" class="textarea-bordered rounded-xl" />
        </div>

        <x-slot:actions>
            <x-button label="Cancel" wire:click="$set('transferModal', false)" class="btn-ghost" />
            <x-button label="{{ $editingId ? 'Update' : 'Record Transfer' }}" icon="{{ $editingId ? 'o-check' : 'o-arrows-right-left' }}" wire:click="save" class="bg-gradient-to-r from-primary to-secondary text-white border-none font-bold" />
        </x-slot:actions>
    </x-modal>
</div>
