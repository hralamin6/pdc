<div>
    <x-header title="Financial Summary" subtitle="Community transparency reports" separator />

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($reports as $r)
            <div class="bg-base-100 rounded-2xl border border-base-content/5 shadow-sm overflow-hidden flex flex-col hover:shadow-md transition-shadow" wire:key="report-{{ $r->id }}">
                <div class="bg-gradient-to-br from-primary to-secondary px-6 py-5 text-white relative overflow-hidden">
                    <div class="absolute -top-4 -right-4 w-24 h-24 bg-white/10 rounded-full"></div>
                    <div class="relative z-10 flex items-center justify-between">
                        <div>
                            <h3 class="font-black text-2xl">{{ $r->month_name }}</h3>
                            <p class="text-xs text-white/70 font-semibold mt-1">Community Treasury Report</p>
                        </div>
                        <x-icon name="o-document-text" class="w-8 h-8 text-white/30" />
                    </div>
                </div>

                <div class="p-6 flex-grow space-y-5">
                    <div class="grid grid-cols-2 gap-4 text-center">
                        <div class="bg-emerald-50 dark:bg-emerald-900/10 rounded-xl p-3 border border-emerald-100 dark:border-emerald-900/30">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-emerald-600 dark:text-emerald-500 mb-1">Income</p>
                            <p class="text-lg font-black text-emerald-700 dark:text-emerald-400">৳{{ number_format($r->total_income) }}</p>
                        </div>
                        <div class="bg-rose-50 dark:bg-rose-900/10 rounded-xl p-3 border border-rose-100 dark:border-rose-900/30">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-rose-600 dark:text-rose-500 mb-1">Expenses</p>
                            <p class="text-lg font-black text-rose-700 dark:text-rose-400">৳{{ number_format($r->total_expense + $r->total_transfer_fees) }}</p>
                        </div>
                    </div>
                    
                    @if($r->notes)
                        <div class="text-sm text-base-content/70 italic border-l-2 border-primary/30 pl-3">
                            "{{ $r->notes }}"
                        </div>
                    @endif
                    
                    <div class="pt-4 border-t border-base-content/10 flex justify-between items-end">
                        <span class="text-xs font-bold uppercase tracking-wider text-base-content/40">Closing Balance</span>
                        <span class="text-2xl font-black {{ $r->closing_balance >= 0 ? 'text-primary' : 'text-rose-600' }}">
                            ৳{{ number_format($r->closing_balance) }}
                        </span>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-20 bg-base-200/50 rounded-2xl border border-dashed border-base-content/10">
                <x-icon name="o-shield-check" class="w-12 h-12 text-base-content/20 mx-auto mb-4" />
                <h3 class="text-lg font-bold text-base-content/70 mb-1">No reports published yet</h3>
                <p class="text-base-content/50 text-sm">Transparency reports will appear here once published by the treasurer.</p>
            </div>
        @endforelse
    </div>
    
    @if($reports->hasPages())
        <div class="mt-8">{{ $reports->links() }}</div>
    @endif
</div>
