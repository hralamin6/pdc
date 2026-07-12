<div>
    <x-header :title="__('Expenses Management')" :subtitle="__('Track and manage community expenditures')" separator>
        <x-slot:actions>
            <x-button icon="o-plus" :label="__('Record Expense')" class="bg-gradient-to-r from-primary to-secondary text-white border-none shadow-lg shadow-primary/30 hover:scale-105 transition-transform" wire:click="openModal()" />
        </x-slot:actions>
    </x-header>

    {{-- Filters & Stats --}}
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-8">
        {{-- Filters Column --}}
        <div class="lg:col-span-3 grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-input wire:model.live.debounce.300ms="search" :placeholder="__('Search by title...')" icon="o-magnifying-glass" class="input-bordered rounded-xl" clearable />
            <select wire:model.live="category_filter" class="select select-bordered w-full rounded-xl">
                <option value="">{{ __('All Categories') }}</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
            <x-input type="month" wire:model.live="month_filter" class="input-bordered rounded-xl" />
        </div>

        {{-- Total Summary --}}
        <div class="bg-gradient-to-br from-rose-500 to-rose-600 rounded-2xl p-5 text-white shadow-lg shadow-rose-500/30 flex flex-col justify-center">
            <p class="text-xs font-bold uppercase tracking-widest text-rose-100 mb-1">{{ __('Total Found') }}</p>
            <p class="text-3xl font-black">৳{{ number_format($totalAmount, 2) }}</p>
        </div>
    </div>

    {{-- Chart & List Grid --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        
        {{-- List Column --}}
        <div class="xl:col-span-2 space-y-4">
            <div class="bg-base-100 rounded-2xl border border-base-content/5 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="table table-sm">
                        <thead>
                            <tr class="text-base-content/50 text-xs uppercase tracking-wider">
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Details') }}</th>
                                <th>{{ __('Method') }}</th>
                                <th>{{ __('Amount') }}</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($expenses as $ex)
                                <tr class="hover:bg-base-200/50 transition-colors" wire:key="expense-{{ $ex->id }}">
                                    <td class="text-sm font-medium whitespace-nowrap">
                                        {{ $ex->expense_date->format('d M Y') }}
                                    </td>
                                    <td>
                                        <div class="flex items-center gap-3">
                                            @if($ex->category)
                                                <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0" style="background-color: {{ $ex->category->color }}22; color: {{ $ex->category->color }}">
                                                    <x-icon :name="$ex->category->icon" class="w-4 h-4" />
                                                </div>
                                            @else
                                                <div class="w-8 h-8 rounded-lg bg-base-200 flex items-center justify-center shrink-0">
                                                    <x-icon name="o-tag" class="w-4 h-4 text-base-content/50" />
                                                </div>
                                            @endif
                                            <div>
                                                <p class="font-bold text-sm text-base-content">{{ $ex->title }}</p>
                                                <p class="text-xs text-base-content/50">
                                                    {{ $ex->category->name ?? __('Uncategorized') }}
                                                    @if($ex->linkable)
                                                        &bull; {{ __('Linked:') }} {{ class_basename($ex->linkable_type) }}
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-ghost badge-sm capitalize">{{ str_replace('_', ' ', $ex->payment_method) }}</span>
                                        @if($ex->bankAccount)
                                            <p class="text-[10px] text-base-content/50 mt-1">{{ $ex->bankAccount->name }}</p>
                                        @endif
                                    </td>
                                    <td class="font-black text-rose-600">৳{{ number_format($ex->amount, 2) }}</td>
                                    <td>
                                        <div class="flex gap-1">
                                            @if($ex->receipt_url)
                                                <a href="{{ $ex->receipt_url }}" target="_blank" class="btn btn-ghost btn-xs text-primary" title="{{ __('View Receipt') }}">
                                                    <x-icon name="o-paper-clip" class="w-4 h-4" />
                                                </a>
                                            @endif
                                            <x-button icon="o-pencil" class="btn-ghost btn-xs" wire:click="openModal({{ $ex->id }})" :tooltip="__('Edit')" />
                                            <x-button icon="o-trash" class="btn-ghost btn-xs text-error" wire:click="delete({{ $ex->id }})" wire:confirm="{{ __('Delete this expense?') }}" :tooltip="__('Delete')" />
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-12 text-base-content/40">
                                        <x-icon name="o-receipt-percent" class="w-10 h-10 mx-auto mb-3 opacity-30" />
                                        <p class="font-medium">{{ __('No expenses found.') }}</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($expenses->hasPages())
                    <div class="p-4 border-t border-base-content/5">{{ $expenses->links() }}</div>
                @endif
            </div>
        </div>

        {{-- Chart Column --}}
        <div class="xl:col-span-1 space-y-6">
            @if(count($chartData) > 0)
                <div class="bg-base-100 rounded-2xl border border-base-content/5 p-6 shadow-sm">
                    <h3 class="font-bold text-lg mb-6">{{ __('Category Breakdown') }}</h3>
                    
                    {{-- Alpine Component for Chart.js Pie Chart --}}
                    <div class="relative w-full aspect-square max-h-[300px] mx-auto" 
                         x-data="{ 
                            init() {
                                const ctx = this.$refs.canvas.getContext('2d');
                                const data = {{ json_encode($chartData) }};
                                new Chart(ctx, {
                                    type: 'doughnut',
                                    data: {
                                        labels: data.map(d => d.name),
                                        datasets: [{
                                            data: data.map(d => d.total),
                                            backgroundColor: data.map(d => d.color),
                                            borderWidth: 0,
                                            hoverOffset: 4
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        cutout: '70%',
                                        plugins: {
                                            legend: { display: false },
                                            tooltip: {
                                                callbacks: {
                                                    label: function(context) {
                                                        return context.label + ': ৳' + context.raw.toLocaleString();
                                                    }
                                                }
                                            }
                                        }
                                    }
                                });
                            }
                         }">
                        <canvas x-ref="canvas"></canvas>
                        
                        {{-- Center Text overlay for Doughnut --}}
                        <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                            <span class="text-xs text-base-content/50 uppercase font-bold tracking-widest">{{ __('Total') }}</span>
                            <span class="text-xl font-black text-base-content">৳{{ number_format(collect($chartData)->sum('total')) }}</span>
                        </div>
                    </div>

                    {{-- Chart Legend --}}
                    <div class="mt-6 space-y-3">
                        @foreach($chartData as $cd)
                            <div class="flex items-center justify-between text-sm">
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded-full" style="background-color: {{ $cd['color'] }}"></div>
                                    <span class="text-base-content/80 font-medium">{{ $cd['name'] }}</span>
                                </div>
                                <span class="font-bold">৳{{ number_format($cd['total']) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="bg-base-100 rounded-2xl border border-base-content/5 p-6 shadow-sm text-center">
                    <x-icon name="o-chart-pie" class="w-12 h-12 text-base-content/20 mx-auto mb-3" />
                    <p class="text-base-content/50 text-sm">{{ __('No category data for selected month.') }}</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Expense Modal --}}
    <x-modal wire:model="expenseModal" title="{{ $editingId ? __('Edit Expense') : __('Record Expense') }}" class="backdrop-blur-sm" box-class="w-full max-w-3xl">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-input wire:model="title" :label="__('Title/Description *')" :placeholder="__('e.g. Monthly Iftar Food')" class="input-bordered rounded-xl md:col-span-2" />

            <div class="space-y-4">
                <div>
                    <label class="label"><span class="label-text font-semibold">{{ __('Category *') }}</span></label>
                    <select wire:model="expense_category_id" class="select select-bordered w-full rounded-xl">
                        <option value="0">{{ __('Select category...') }}</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <x-input wire:model="amount" :label="__('Amount (৳) *')" type="number" step="0.01" min="1" placeholder="0.00" class="input-bordered rounded-xl" />

                <x-input wire:model="expense_date" :label="__('Date *')" type="date" class="input-bordered rounded-xl" />
            </div>

            <div class="space-y-4">
                <div>
                    <label class="label"><span class="label-text font-semibold">{{ __('Payment Method *') }}</span></label>
                    <select wire:model.live="payment_method" class="select select-bordered w-full rounded-xl">
                        <option value="cash">{{ __('Cash') }}</option>
                        <option value="bkash">bKash</option>
                        <option value="nagad">Nagad</option>
                        <option value="bank_transfer">{{ __('Bank Transfer') }}</option>
                        <option value="other">{{ __('Other') }}</option>
                    </select>
                </div>

                @if(in_array($payment_method, ['bkash', 'nagad', 'bank_transfer']))
                    <div>
                        <label class="label"><span class="label-text font-semibold">{{ __('Source Bank Account') }}</span></label>
                        <select wire:model="bank_account_id" class="select select-bordered w-full rounded-xl">
                            <option value="0">{{ __('Not specified / External') }}</option>
                            @foreach($bankAccounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->name }} ({{ ucfirst($acc->type) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <x-input wire:model="transaction_id" :label="__('Transaction ID / Ref')" placeholder="TxID" class="input-bordered rounded-xl" />
                @endif
            </div>

            {{-- Linking --}}
            <div class="md:col-span-2 bg-base-200/50 rounded-xl p-4 border border-base-content/5">
                <p class="font-bold text-sm mb-3 text-base-content/70"><x-icon name="o-link" class="w-4 h-4 inline" /> {{ __('Link to Project (Optional)') }}</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <select wire:model.live="linkable_type" class="select select-sm select-bordered w-full rounded-lg">
                            <option value="">{{ __('No link') }}</option>
                            <option value="App\Models\DonationCampaign">{{ __('Campaign') }}</option>
                            <option value="App\Models\Halaqah">{{ __('Halaqah Session') }}</option>
                        </select>
                    </div>
                    <div>
                        @if($linkable_type === 'App\Models\DonationCampaign')
                            <select wire:model="linkable_id" class="select select-sm select-bordered w-full rounded-lg">
                                <option value="">{{ __('Select Campaign...') }}</option>
                                @foreach($campaigns as $camp)
                                    <option value="{{ $camp->id }}">{{ $camp->title }}</option>
                                @endforeach
                            </select>
                        @elseif($linkable_type === 'App\Models\Halaqah')
                            <select wire:model="linkable_id" class="select select-sm select-bordered w-full rounded-lg">
                                <option value="">{{ __('Select Halaqah...') }}</option>
                                @foreach($halaqahs as $hal)
                                    <option value="{{ $hal->id }}">{{ $hal->title }} ({{ $hal->scheduled_at->format('d M') }})</option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                </div>
            </div>

            <div class="md:col-span-2">
                <x-textarea wire:model="notes" :label="__('Additional Notes')" :placeholder="__('Any extra details...')" rows="2" class="textarea-bordered rounded-xl" />
            </div>

            <div class="md:col-span-2">
                <x-file wire:model="receipt" :label="__('Receipt Image (optional)')" accept="image/*" class="file-input-bordered w-full" :hint="__('Max 5MB. Clear image of receipt/memo.')" />
                @if($receipt)
                    <div class="mt-2 text-emerald-600 text-sm font-semibold">{{ __('New image selected for upload.') }}</div>
                @endif
            </div>
        </div>

        <x-slot:actions>
            <x-button label="{{ __('Cancel') }}" wire:click="$set('expenseModal', false)" class="btn-ghost" />
            <x-button label="{{ $editingId ? __('Update') : __('Record Expense') }}" icon="{{ $editingId ? 'o-check' : 'o-receipt-percent' }}" wire:click="save" class="bg-gradient-to-r from-primary to-secondary text-white border-none font-bold" spinner="save" />
        </x-slot:actions>
    </x-modal>
    
    {{-- Ensure Chart.js is loaded --}}
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @endpush
</div>
