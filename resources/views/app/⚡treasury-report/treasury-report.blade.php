<div>
    <x-header title="Monthly Treasury Reports" subtitle="Generate, manage, and publish financial transparency reports" separator>
        <x-slot:actions>
            <x-button icon="o-document-plus" label="Generate Report" class="bg-gradient-to-r from-primary to-secondary text-white border-none shadow-lg shadow-primary/30 hover:scale-105 transition-transform" wire:click="openModal()" />
        </x-slot:actions>
    </x-header>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @forelse($reports as $r)
            <div class="bg-base-100 rounded-2xl border border-base-content/5 shadow-sm overflow-hidden flex flex-col group" wire:key="report-{{ $r->id }}">
                <div class="bg-gradient-to-br from-slate-900 to-indigo-950 px-5 py-4 text-white relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                    <div class="flex items-center justify-between relative z-10">
                        <div>
                            <h3 class="font-black text-xl">{{ $r->month_name }}</h3>
                            <p class="text-xs text-white/50 font-semibold mt-0.5">Report #{{ str_pad($r->id, 4, '0', STR_PAD_LEFT) }}</p>
                        </div>
                        @if($r->is_published)
                            <div class="w-8 h-8 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center ring-1 ring-emerald-500/50" title="Published to Members">
                                <x-icon name="o-eye" class="w-4 h-4" />
                            </div>
                        @else
                            <div class="w-8 h-8 rounded-full bg-white/10 text-white/50 flex items-center justify-center ring-1 ring-white/20" title="Draft (Hidden)">
                                <x-icon name="o-eye-slash" class="w-4 h-4" />
                            </div>
                        @endif
                    </div>
                </div>

                <div class="p-5 flex-grow space-y-4">
                    <div class="space-y-2">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-base-content/60">Opening</span>
                            <span class="font-mono font-semibold">৳{{ number_format($r->opening_balance) }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-base-content/60">Income</span>
                            <span class="font-mono font-bold text-emerald-600">+৳{{ number_format($r->total_income) }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-base-content/60">Expenses & Fees</span>
                            <span class="font-mono font-bold text-rose-600">-৳{{ number_format($r->total_expense + $r->total_transfer_fees) }}</span>
                        </div>
                    </div>
                    
                    <div class="pt-3 border-t border-base-content/10">
                        <div class="flex justify-between items-end">
                            <span class="text-xs font-bold uppercase tracking-wider text-base-content/40">Closing</span>
                            <span class="text-xl font-black {{ $r->closing_balance >= 0 ? 'text-primary' : 'text-rose-600' }}">
                                ৳{{ number_format($r->closing_balance) }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="p-3 bg-base-200/50 border-t border-base-content/5 flex items-center gap-1">
                    <x-button class="btn-ghost btn-sm flex-1" :icon="$r->is_published ? 'o-eye-slash' : 'o-eye'" :class="$r->is_published ? 'text-warning' : 'text-success'" wire:click="togglePublish({{ $r->id }})" tooltip="{{ $r->is_published ? 'Unpublish' : 'Publish' }}" />
                    <x-button icon="o-information-circle" class="btn-ghost btn-sm text-info" wire:click="viewDetails({{ $r->id }})" tooltip="View Breakdown" />
                    <x-button icon="o-pencil" class="btn-ghost btn-sm" wire:click="openModal({{ $r->id }})" tooltip="Edit" />
                    <x-button icon="o-printer" class="btn-ghost btn-sm" onclick="window.print()" tooltip="Print" />
                    <x-button icon="o-trash" class="btn-ghost btn-sm text-error" wire:click="delete({{ $r->id }})" wire:confirm="Delete this report?" tooltip="Delete" />
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-20 bg-base-200/50 rounded-2xl border border-dashed border-base-content/10">
                <x-icon name="o-document-chart-bar" class="w-12 h-12 text-base-content/20 mx-auto mb-4" />
                <h3 class="text-lg font-bold text-base-content/70 mb-1">No reports generated yet</h3>
                <p class="text-base-content/50 text-sm mb-4">Generate your first monthly treasury report.</p>
                <x-button icon="o-document-plus" label="Generate Report" class="btn-primary" wire:click="openModal()" />
            </div>
        @endforelse
    </div>
    
    @if($reports->hasPages())
        <div class="mt-8">{{ $reports->links() }}</div>
    @endif

    {{-- Generate/Edit Modal --}}
    <x-modal wire:model="modal" title="{{ $editingId ? 'Edit Report' : 'Generate Monthly Report' }}" class="backdrop-blur-sm" box-class="w-full max-w-2xl">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
            <x-input wire:model.live="year" label="Year *" type="number" min="2020" class="input-bordered rounded-xl" />
            <div>
                <label class="label"><span class="label-text font-semibold">Month *</span></label>
                <select wire:model.live="month" class="select select-bordered w-full rounded-xl">
                    @for($i=1; $i<=12; $i++)
                        <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}">{{ date('F', mktime(0, 0, 0, $i, 10)) }}</option>
                    @endfor
                </select>
            </div>
        </div>

        <div class="bg-base-200/50 rounded-2xl p-5 border border-base-content/10 mb-6 relative overflow-hidden">
            @if(!$editingId)
                <div class="absolute top-2 right-2 text-[10px] uppercase font-bold tracking-widest text-primary bg-primary/10 px-2 py-0.5 rounded-full">Auto-Calculated</div>
            @endif
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <p class="text-xs font-semibold text-base-content/50 uppercase">Total Income</p>
                    <p class="text-xl font-black text-emerald-600">৳{{ number_format($calc_income, 2) }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-base-content/50 uppercase">Total Expenses</p>
                    <p class="text-xl font-black text-rose-600">৳{{ number_format($calc_expense + $calc_fees, 2) }}</p>
                    @if($calc_fees > 0)
                        <p class="text-[10px] text-rose-600/70">(incl. ৳{{ $calc_fees }} fees)</p>
                    @endif
                </div>
            </div>
            <div class="pt-4 border-t border-base-content/10 grid grid-cols-1 sm:grid-cols-2 gap-4 items-end">
                <x-input wire:model.live="opening_balance" label="Opening Balance (৳) *" type="number" step="0.01" class="input-bordered rounded-xl bg-base-100" />
                <div class="pb-2">
                    <p class="text-xs font-semibold text-base-content/50 uppercase mb-1">Projected Closing Balance</p>
                    <p class="text-2xl font-black {{ $this->closingBalance >= 0 ? 'text-primary' : 'text-rose-600' }}">৳{{ number_format($this->closingBalance, 2) }}</p>
                </div>
            </div>
        </div>

        <x-textarea wire:model="notes" label="Treasurer Notes / Summary" placeholder="Optional comments for the community..." rows="3" class="textarea-bordered rounded-xl" />

        <x-slot:actions>
            <x-button label="Cancel" wire:click="$set('modal', false)" class="btn-ghost" />
            <x-button label="{{ $editingId ? 'Save Changes' : 'Generate & Save' }}" icon="o-check" wire:click="save" class="bg-gradient-to-r from-primary to-secondary text-white border-none font-bold" spinner="save" />
        </x-slot:actions>
    </x-modal>

    {{-- Details Breakdown Modal --}}
    <x-modal wire:model="detailsModal" title="Report Breakdown - {{ $viewingReport?->month_name }}" class="backdrop-blur-sm" box-class="w-full max-w-3xl">
        @if($viewingReport)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Income Breakdown --}}
                <div class="bg-emerald-50 dark:bg-emerald-900/10 rounded-2xl p-5 border border-emerald-100 dark:border-emerald-900/30">
                    <h4 class="font-bold text-emerald-800 dark:text-emerald-400 mb-4 flex items-center gap-2">
                        <x-icon name="o-arrow-down-tray" class="w-5 h-5" /> Income Sources
                    </h4>
                    <div class="space-y-3">
                        @forelse($incomeBreakdown as $inc)
                            <div class="flex justify-between items-center text-sm border-b border-emerald-200 dark:border-emerald-800/50 pb-2 last:border-0">
                                <span class="font-medium">{{ $inc['method'] }}</span>
                                <span class="font-bold text-emerald-600 dark:text-emerald-400">৳{{ number_format($inc['total']) }}</span>
                            </div>
                        @empty
                            <p class="text-sm text-emerald-600/50">No specific income data found.</p>
                        @endforelse
                    </div>
                    <div class="mt-4 pt-3 border-t border-emerald-200 dark:border-emerald-800 flex justify-between font-black text-lg text-emerald-700 dark:text-emerald-300">
                        <span>Total Income</span>
                        <span>৳{{ number_format($viewingReport->total_income) }}</span>
                    </div>
                </div>

                {{-- Expense Breakdown --}}
                <div class="bg-rose-50 dark:bg-rose-900/10 rounded-2xl p-5 border border-rose-100 dark:border-rose-900/30">
                    <h4 class="font-bold text-rose-800 dark:text-rose-400 mb-4 flex items-center gap-2">
                        <x-icon name="o-receipt-percent" class="w-5 h-5" /> Expenditures
                    </h4>
                    <div class="space-y-3">
                        @forelse($expenseBreakdown as $ex)
                            <div class="flex justify-between items-center text-sm border-b border-rose-200 dark:border-rose-800/50 pb-2 last:border-0">
                                <div class="flex items-center gap-2">
                                    <div class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $ex['color'] }}"></div>
                                    <span class="font-medium">{{ $ex['name'] }}</span>
                                </div>
                                <span class="font-bold text-rose-600 dark:text-rose-400">৳{{ number_format($ex['total']) }}</span>
                            </div>
                        @empty
                            <p class="text-sm text-rose-600/50">No specific expense data found.</p>
                        @endforelse
                        @if($viewingReport->total_transfer_fees > 0)
                            <div class="flex justify-between items-center text-sm border-b border-rose-200 dark:border-rose-800/50 pb-2 last:border-0">
                                <div class="flex items-center gap-2">
                                    <div class="w-2.5 h-2.5 rounded-full bg-amber-500"></div>
                                    <span class="font-medium">Transfer Fees</span>
                                </div>
                                <span class="font-bold text-rose-600 dark:text-rose-400">৳{{ number_format($viewingReport->total_transfer_fees) }}</span>
                            </div>
                        @endif
                    </div>
                    <div class="mt-4 pt-3 border-t border-rose-200 dark:border-rose-800 flex justify-between font-black text-lg text-rose-700 dark:text-rose-300">
                        <span>Total Outflow</span>
                        <span>৳{{ number_format($viewingReport->total_expense + $viewingReport->total_transfer_fees) }}</span>
                    </div>
                </div>
            </div>
            
            <div class="mt-6 flex justify-between items-center p-4 bg-base-200/50 rounded-xl">
                <div>
                    <span class="text-xs uppercase font-bold text-base-content/50">Opening Balance</span>
                    <p class="text-lg font-black">৳{{ number_format($viewingReport->opening_balance) }}</p>
                </div>
                <div class="text-right">
                    <span class="text-xs uppercase font-bold text-base-content/50">Closing Balance</span>
                    <p class="text-lg font-black {{ $viewingReport->closing_balance >= 0 ? 'text-primary' : 'text-rose-600' }}">৳{{ number_format($viewingReport->closing_balance) }}</p>
                </div>
            </div>
        @endif

        <x-slot:actions>
            <x-button label="Close" wire:click="$set('detailsModal', false)" class="btn-primary" />
        </x-slot:actions>
    </x-modal>

    {{-- Print Styles (Minimalist) --}}
    <style>
        @media print {
            body * { visibility: hidden; }
            .group, .group * { visibility: visible; }
            .group { position: absolute; left: 0; top: 0; width: 100%; box-shadow: none; border: none; }
            .btn-ghost { display: none; }
            .drawer-side { display: none; }
        }
    </style>
</div>
