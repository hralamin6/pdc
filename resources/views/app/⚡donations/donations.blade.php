<div>
    <x-header title="My Impact" subtitle="Your journey of generosity" separator>
        <x-slot:actions>
            <x-button icon="o-arrow-path" class="btn-outline border-primary/20 text-primary hover:bg-primary/10" label="New Pledge" wire:click="openPledgeModal" />
            <x-button icon="o-heart" class="bg-gradient-to-r from-primary to-secondary text-white border-none shadow-lg shadow-primary/30 hover:scale-105 transition-transform" label="Donate Now" wire:click="$set('donationModal', true)" />
        </x-slot:actions>
    </x-header>

    {{-- Hero Glassmorphism Banner --}}
    <div class="relative rounded-3xl overflow-hidden shadow-2xl mb-10 min-h-[220px] flex items-center">
        <!-- Background Gradients -->
        <div class="absolute inset-0 bg-gradient-to-br from-primary/90 via-primary to-secondary"></div>
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-white/20 blur-3xl rounded-full"></div>
        <div class="absolute -bottom-24 -left-24 w-72 h-72 bg-black/20 blur-3xl rounded-full"></div>

        <div class="relative z-10 p-8 md:p-12 w-full flex flex-col md:flex-row items-center justify-between gap-8">
            <div class="text-white text-center md:text-left">
                <h2 class="text-xl md:text-2xl font-bold opacity-90 mb-1">Total Lifetime Impact</h2>
                <div class="text-5xl md:text-6xl font-black tracking-tight mb-4">৳{{ number_format($totalDonated) }}</div>
                <div class="inline-flex items-center gap-2 {{ $this->donorRank['color'] }} px-4 py-1.5 rounded-full text-sm font-bold tracking-wide uppercase">
                    <x-icon :name="$this->donorRank['icon']" class="w-4 h-4" />
                    {{ $this->donorRank['name'] }}
                </div>
            </div>
            
            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-6 border border-white/20 w-full md:w-64">
                <div class="text-white/70 text-sm font-semibold uppercase tracking-wider mb-1">Total Contributions</div>
                <div class="text-3xl text-white font-bold">{{ $donations->where('status', 'confirmed')->count() }}</div>
            </div>
        </div>
    </div>

    {{-- Chart & Stats Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">
        <div class="lg:col-span-2 bg-base-100 rounded-3xl p-6 shadow-sm border border-base-200">
            <h3 class="font-bold text-lg mb-4 flex items-center gap-2">
                <x-icon name="o-chart-bar" class="w-5 h-5 text-primary" /> Activity this Year
            </h3>
            <div class="h-64">
                <x-chart wire:model="chartData" />
            </div>
        </div>
        
        <div class="bg-base-100 rounded-3xl p-6 shadow-sm border border-base-200 flex flex-col justify-between">
            <div>
                <h3 class="font-bold text-lg mb-4">Quick Stats</h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center pb-4 border-b border-base-content/5">
                        <span class="text-base-content/70">Active Pledges</span>
                        <span class="font-bold text-xl">{{ $pledges->count() }}</span>
                    </div>
                    <div class="flex justify-between items-center pb-4 border-b border-base-content/5">
                        <span class="text-base-content/70">Campaigns Supported</span>
                        <span class="font-bold text-xl">{{ $donations->whereNotNull('campaign_id')->unique('campaign_id')->count() }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-base-content/70">Joined</span>
                        <span class="font-bold">{{ $user->created_at->format('M Y') }}</span>
                    </div>
                </div>
            </div>
            <x-button label="Download Annual Tax Summary" icon="o-document-arrow-down" class="btn-outline btn-block mt-6" onclick="toast('Tax summary generation coming soon!', {type: 'info'})" />
        </div>
    </div>

    <!-- Active Campaigns -->
    @if($campaigns->count() > 0)
        <h2 class="text-2xl font-black mb-6 tracking-tight">Support a Cause</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
            @foreach($campaigns as $campaign)
                <div class="group bg-base-100 rounded-3xl overflow-hidden shadow-sm hover:shadow-2xl border border-base-200 transition-all duration-300 flex flex-col relative">
                    <!-- Image -->
                    <div class="h-48 bg-base-300 relative overflow-hidden">
                        @if($campaign->cover_url)
                            <img src="{{ $campaign->cover_url }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" alt="{{ $campaign->title }}" />
                        @else
                            <div class="w-full h-full bg-gradient-to-tr from-primary/20 to-secondary/20 flex items-center justify-center">
                                <x-icon name="o-heart" class="w-16 h-16 text-primary/30" />
                            </div>
                        @endif
                        <div class="absolute top-4 right-4 bg-white/90 backdrop-blur-sm text-primary font-bold px-3 py-1 rounded-full text-xs shadow-lg">
                            {{ $campaign->progress_percentage }}% Funded
                        </div>
                    </div>
                    
                    <!-- Content -->
                    <div class="p-6 flex-1 flex flex-col">
                        <h3 class="font-bold text-lg leading-tight mb-2 group-hover:text-primary transition-colors">{{ $campaign->title }}</h3>
                        <p class="text-sm opacity-70 mb-6 flex-1">{{ Str::limit($campaign->description, 90) }}</p>
                        
                        <div class="space-y-2 mb-4">
                            <div class="flex justify-between text-sm font-semibold">
                                <span class="text-primary">৳{{ number_format($campaign->collected_amount) }}</span>
                                <span class="opacity-50">of ৳{{ number_format($campaign->goal_amount) }}</span>
                            </div>
                            <div class="w-full bg-base-200 rounded-full h-2 overflow-hidden">
                                <div class="bg-gradient-to-r from-primary to-secondary h-full rounded-full" style="width: {{ $campaign->progress_percentage }}%"></div>
                            </div>
                        </div>
                        
                        <x-button label="Donate" icon="o-heart" class="btn-primary btn-block shadow-lg shadow-primary/20" wire:click="$set('donationModal', true); $set('campaign_id', {{ $campaign->id }})" />
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- My Pledges Section --}}
    @if($pledges->isNotEmpty())
        <div class="mb-8">
            <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
                <x-icon name="o-arrow-path" class="w-6 h-6 text-primary" /> My Pledges
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($pledges as $pledge)
                    <x-card class="bg-base-100 shadow-sm border border-base-200">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="font-bold text-lg">৳{{ number_format($pledge->amount, 2) }} <span class="text-sm font-normal text-base-content/70 capitalize">/ {{ $pledge->frequency }}</span></h3>
                                <p class="text-xs text-base-content/50">Started: {{ $pledge->starts_at->format('M j, Y') }}</p>
                            </div>
                            @if($pledge->is_active)
                                <x-badge value="Active" class="badge-success badge-sm" />
                            @else
                                <x-badge value="Cancelled" class="badge-error badge-sm" />
                            @endif
                        </div>
                        
                        @if($pledge->is_active)
                            <div class="bg-primary/5 rounded-xl p-3 mb-4 flex items-center gap-3">
                                <x-icon name="o-calendar-days" class="w-5 h-5 text-primary shrink-0" />
                                <div>
                                    <p class="text-xs font-bold text-primary/70 uppercase tracking-wider">Next Due</p>
                                    <p class="text-sm font-semibold">{{ $pledge->next_due_at ? \Carbon\Carbon::parse($pledge->next_due_at)->format('l, M j, Y') : 'Processing...' }}</p>
                                </div>
                            </div>
                        @endif

                        <div class="flex gap-2 justify-end">
                            @if($pledge->is_active)
                                <x-button icon="o-pencil" class="btn-ghost btn-sm" wire:click="editPledge({{ $pledge->id }})" tooltip="Edit" />
                                <x-button icon="o-pause" class="btn-ghost btn-sm text-warning" wire:click="cancelPledge({{ $pledge->id }})" tooltip="Cancel Pledge" confirm="Are you sure you want to stop this pledge?" />
                            @else
                                <x-button icon="o-trash" class="btn-ghost btn-sm text-error" wire:click="deletePledge({{ $pledge->id }})" tooltip="Delete" confirm="Are you sure you want to permanently delete this record?" />
                            @endif
                        </div>
                    </x-card>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Donation History -->
    <h2 class="text-xl font-bold mb-4">Donation History</h2>
    <div class="bg-base-200/50 rounded-xl p-4 overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($donations as $donation)
                    <tr>
                        <td>{{ $donation->donated_at ? $donation->donated_at->format('M d, Y') : $donation->created_at->format('M d, Y') }}</td>
                        <td class="capitalize">{{ $donation->type }}</td>
                        <td class="font-bold">৳{{ number_format($donation->amount, 2) }}</td>
                        <td class="capitalize">{{ $donation->payment_method }}</td>
                    <td class="flex items-center gap-2">
                        @if($donation->status === 'confirmed')
                            <x-badge value="Confirmed" class="badge-success badge-sm" />
                        @elseif($donation->status === 'pending')
                            <x-badge value="Pending Verification" class="badge-warning badge-sm" />
                        @elseif($donation->status === 'pending_payment')
                            <x-badge value="Payment Due" class="badge-error badge-sm" />
                        @else
                            <x-badge value="Rejected" class="badge-error badge-sm" />
                        @endif
                    </td>
                    <td>
                        @if($donation->status === 'confirmed')
                            <x-button icon="o-document-arrow-down" class="btn-ghost btn-xs text-primary" wire:click="downloadReceipt({{ $donation->id }})" tooltip="Download Receipt" />
                        @elseif($donation->status === 'pending_payment')
                            <x-button label="Pay Now" class="btn-primary btn-xs" wire:click="openPayModal({{ $donation->id }})" />
                        @endif
                    </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center opacity-50 py-4">No donations yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">
            {{ $donations->links() }}
        </div>
    </div>

    <!-- New Donation Modal -->
    <x-modal wire:model="donationModal" title="Make a Donation" subtitle="Support our active campaigns" separator>
        <div class="space-y-4">
            
            <x-select label="Select Campaign" wire:model="campaign_id" :options="$campaigns" option-value="id" option-label="title" placeholder="-- Select Campaign --" />
            
            <div class="form-control">
                <label class="label"><span class="label-text font-bold">Amount (৳)</span></label>
                <div class="grid grid-cols-4 gap-2 mb-2">
                    <x-radio wire:model="amount" :options="[['id'=>'50', 'name'=>'৳50'], ['id'=>'100', 'name'=>'৳100'], ['id'=>'200', 'name'=>'৳200'], ['id'=>'500', 'name'=>'৳500']]" class="btn-outline" />
                </div>
                <x-input wire:model="customAmount" placeholder="Or enter custom amount..." prefix="৳" type="number" />
            </div>

            <x-select label="Payment Method" wire:model.live="paymentMethod" :options="[['id'=>'cash', 'name'=>'Cash (Given to Treasurer)'], ['id'=>'bkash', 'name'=>'bKash'], ['id'=>'nagad', 'name'=>'Nagad'], ['id'=>'bank', 'name'=>'Bank Transfer']]" />
            
            @if(in_array($paymentMethod, ['bkash', 'nagad', 'bank']))
                <div class="bg-base-200 p-3.5 rounded-xl text-xs space-y-1.5 border border-base-content/10">
                    <span class="font-bold text-primary text-[10px] uppercase tracking-wider block">Payment Details</span>
                    @if($paymentMethod === 'bkash')
                        <p>Please send money to our official bKash account: <strong class="font-mono text-sm block mt-1 text-center bg-base-100 py-1 rounded">{{ setting('payment.bkash_no', '01712345678') }}</strong></p>
                    @elseif($paymentMethod === 'nagad')
                        <p>Please send money to our official Nagad account: <strong class="font-mono text-sm block mt-1 text-center bg-base-100 py-1 rounded">{{ setting('payment.nagad_no', '01812345678') }}</strong></p>
                    @else
                        <p>Please transfer funds to the following Bank Account details:</p>
                        <div class="font-mono bg-base-100 p-2.5 rounded-lg space-y-1 mt-1 text-[11px]">
                            <div><span class="opacity-60">Bank:</span> <strong>{{ setting('payment.bank_name', 'Dutch Bangla Bank PLC') }}</strong></div>
                            <div><span class="opacity-60">Account:</span> <strong>{{ setting('payment.bank_account_no', '123-456-7890123') }}</strong></div>
                            <div><span class="opacity-60">Branch:</span> <strong>{{ setting('payment.bank_branch', 'PSTU Branch') }}</strong></div>
                            <div><span class="opacity-60">Holder:</span> <strong>{{ setting('payment.bank_holder', 'PSTU Dawah Community') }}</strong></div>
                        </div>
                    @endif
                </div>
                <x-input label="Transaction ID / Reference" wire:model="transactionId" placeholder="Enter Transaction ID..." required />
            @endif
            
            <x-textarea label="Note (Optional)" wire:model="note" placeholder="Add a note or prayer request..." rows="2" />
            
            <x-checkbox label="Make this donation anonymous" wire:model="isAnonymous" hint="Your name will be hidden from other members" />
            
            <div class="text-sm opacity-70 mt-2 bg-base-200 p-3 rounded-lg flex gap-2">
                <x-icon name="o-information-circle" class="w-5 h-5 flex-shrink-0" />
                <p>Donations require approval by the accountant/treasurer to be confirmed in the system.</p>
            </div>
        </div>

        <x-slot:actions>
            <x-button label="Cancel" wire:click="$set('donationModal', false)" />
            <x-button label="Submit Donation" class="btn-primary" wire:click="saveDonation" spinner />
        </x-slot:actions>
    </x-modal>

    <!-- New/Edit Pledge Modal -->
    <x-modal wire:model="pledgeModal" title="{{ $pledgeId ? 'Edit Pledge' : 'Set up Recurring Pledge' }}" subtitle="Consistency is loved by Allah" separator>
        <div class="space-y-4">
            
            <div class="form-control">
                <label class="label"><span class="label-text font-bold">Amount (৳)</span></label>
                <div class="grid grid-cols-4 gap-2 mb-2">
                    <x-radio wire:model="amount" :options="[['id'=>'50', 'name'=>'৳50'], ['id'=>'100', 'name'=>'৳100'], ['id'=>'200', 'name'=>'৳200'], ['id'=>'500', 'name'=>'৳500']]" class="btn-outline" />
                </div>
                <x-input wire:model="customAmount" placeholder="Or enter custom amount..." prefix="৳" type="number" />
            </div>

            <x-select label="Frequency" wire:model="frequency" :options="[['id'=>'weekly', 'name'=>'Weekly'], ['id'=>'monthly', 'name'=>'Monthly'], ['id'=>'yearly', 'name'=>'Yearly']]" />
            
            <div class="text-sm opacity-70 mt-2 bg-base-200 p-3 rounded-lg flex gap-2">
                <x-icon name="o-information-circle" class="w-5 h-5 flex-shrink-0" />
                <p>The system will automatically generate a pending payment on the due date. You will need to log in to complete the payment via Cash or Mobile Banking.</p>
            </div>
        </div>

        <x-slot:actions>
            <x-button label="Cancel" wire:click="$set('pledgeModal', false)" />
            <x-button label="Confirm Pledge" class="btn-primary" wire:click="savePledge" spinner />
        </x-slot:actions>
    </x-modal>

    <!-- Pay Pending Pledge Modal -->
    <x-modal wire:model="payModal" title="Complete Payment" subtitle="Enter your payment details for this cycle" separator>
        <div class="space-y-4">
            
            <x-select label="Payment Method" wire:model.live="paymentMethod" :options="[['id'=>'cash', 'name'=>'Cash (Given to Treasurer)'], ['id'=>'bkash', 'name'=>'bKash'], ['id'=>'nagad', 'name'=>'Nagad'], ['id'=>'bank', 'name'=>'Bank Transfer']]" />
            
            @if(in_array($paymentMethod, ['bkash', 'nagad', 'bank']))
                <div class="bg-base-200 p-3.5 rounded-xl text-xs space-y-1.5 border border-base-content/10">
                    <span class="font-bold text-primary text-[10px] uppercase tracking-wider block">Payment Details</span>
                    @if($paymentMethod === 'bkash')
                        <p>Please send money to our official bKash account: <strong class="font-mono text-sm block mt-1 text-center bg-base-100 py-1 rounded">{{ setting('payment.bkash_no', '01712345678') }}</strong></p>
                    @elseif($paymentMethod === 'nagad')
                        <p>Please send money to our official Nagad account: <strong class="font-mono text-sm block mt-1 text-center bg-base-100 py-1 rounded">{{ setting('payment.nagad_no', '01812345678') }}</strong></p>
                    @else
                        <p>Please transfer funds to the following Bank Account details:</p>
                        <div class="font-mono bg-base-100 p-2.5 rounded-lg space-y-1 mt-1 text-[11px]">
                            <div><span class="opacity-60">Bank:</span> <strong>{{ setting('payment.bank_name', 'Dutch Bangla Bank PLC') }}</strong></div>
                            <div><span class="opacity-60">Account:</span> <strong>{{ setting('payment.bank_account_no', '123-456-7890123') }}</strong></div>
                            <div><span class="opacity-60">Branch:</span> <strong>{{ setting('payment.bank_branch', 'PSTU Branch') }}</strong></div>
                            <div><span class="opacity-60">Holder:</span> <strong>{{ setting('payment.bank_holder', 'PSTU Dawah Community') }}</strong></div>
                        </div>
                    @endif
                </div>
                <x-input label="Transaction ID / Reference" wire:model="transactionId" placeholder="Enter Transaction ID..." required />
            @endif
            
            <div class="text-sm opacity-70 mt-2 bg-base-200 p-3 rounded-lg flex gap-2">
                <x-icon name="o-information-circle" class="w-5 h-5 flex-shrink-0" />
                <p>Once submitted, the treasurer will verify your payment and confirm it.</p>
            </div>
        </div>

        <x-slot:actions>
            <x-button label="Cancel" wire:click="$set('payModal', false)" />
            <x-button label="Submit Payment" class="btn-primary" wire:click="payPendingDonation" spinner />
        </x-slot:actions>
    </x-modal>
</div>
