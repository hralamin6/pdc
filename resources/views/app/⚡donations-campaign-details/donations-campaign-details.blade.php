<div class="p-6 space-y-6">
    {{-- Breadcrumb & Back --}}
    <div class="flex items-center justify-between">
        <a href="{{ route('app.donations.campaigns') }}" wire:navigate class="flex items-center gap-2 text-xs font-black text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 transition-colors uppercase tracking-wider">
            <x-icon name="o-arrow-left" class="w-4 h-4" />
            {{ __('Back to Campaigns') }}
        </a>
        
        @if($campaign->status === 'active')
            <span class="badge badge-success font-black text-xs px-2.5 py-1 text-white shadow-sm">{{ __('Active') }}</span>
        @elseif($campaign->status === 'completed')
            <span class="badge badge-neutral font-black text-xs px-2.5 py-1 text-slate-800 dark:text-slate-200 shadow-sm">{{ __('Completed') }}</span>
        @else
            <span class="badge badge-error font-black text-xs px-2.5 py-1 text-white shadow-sm">{{ __('Cancelled') }}</span>
        @endif
    </div>

    {{-- Title Banner --}}
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-black text-slate-800 dark:text-white leading-tight">
                    {{ $campaign->title }}
                </h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                    {{ __('Created by') }} <span class="font-bold">{{ $campaign->creator?->name ?? __('System') }}</span> 
                    · {{ __('Active since') }} {{ $campaign->starts_at ? $campaign->starts_at->format('M j, Y') : $campaign->created_at->format('M j, Y') }}
                    @if($campaign->ends_at)
                        · {{ __('Ends on') }} {{ $campaign->ends_at->format('M j, Y') }}
                    @endif
                </p>
            </div>
            
            <div class="shrink-0 flex items-center gap-3 bg-slate-50 dark:bg-slate-800/50 px-5 py-3 rounded-2xl border border-slate-100 dark:border-slate-800">
                <div class="text-center px-4 border-r border-slate-200 dark:border-slate-700">
                    <span class="block text-xl font-black text-emerald-600 dark:text-emerald-400">৳{{ number_format($campaign->collected_amount) }}</span>
                    <span class="text-[9px] font-bold uppercase tracking-widest text-slate-400">{{ __('Raised') }}</span>
                </div>
                <div class="text-center px-4">
                    <span class="block text-xl font-black text-slate-700 dark:text-slate-300">৳{{ number_format($campaign->goal_amount ?? 0) }}</span>
                    <span class="text-[9px] font-bold uppercase tracking-widest text-slate-400">{{ __('Goal') }}</span>
                </div>
            </div>
        </div>

        {{-- Progress Bar --}}
        <div class="mt-6 space-y-2">
            <div class="flex justify-between items-center text-xs font-bold text-slate-500">
                <span>{{ __('Fundraising Progress') }}</span>
                <span>{{ $campaign->progress_percentage }}%</span>
            </div>
            <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-3 overflow-hidden">
                <div class="bg-gradient-to-r from-emerald-400 to-indigo-500 h-full rounded-full transition-all duration-1000" style="width: {{ $campaign->progress_percentage }}%"></div>
            </div>
        </div>
    </div>

    {{-- Main Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- Left: Analytics & Details (1 Column) --}}
        <div class="lg:col-span-1 space-y-6">
            
            {{-- Giving Trend Chart (Option 4.B) --}}
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm">
                <h3 class="font-black text-slate-800 dark:text-white mb-6 flex items-center gap-2 text-sm uppercase tracking-wider">
                    <x-icon name="o-presentation-chart-line" class="w-4 h-4 text-indigo-500" />
                    {{ __('Giving Velocity (Last 6 Months)') }}
                </h3>
                
                @php $maxAmount = max($trends['data']) ?: 1; @endphp
                <div class="flex items-end justify-between h-36 gap-2 px-1">
                    @foreach($trends['data'] as $index => $amount)
                        <div class="flex flex-col items-center gap-2 flex-1 group">
                            <div class="w-full relative flex items-end justify-center h-full rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors pt-4">
                                {{-- Tooltip --}}
                                <div class="absolute -top-6 bg-slate-800 text-white text-[9px] font-bold px-2 py-0.5 rounded shadow-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-10">
                                    ৳{{ number_format($amount) }}
                                </div>
                                {{-- Bar --}}
                                <div class="w-full max-w-[24px] bg-gradient-to-t from-indigo-600 to-indigo-400 rounded-t-md transition-all duration-500 shadow-sm"
                                     style="height: {{ max(($amount / $maxAmount) * 100, 4) }}%;">
                                </div>
                            </div>
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-tight">{{ explode(' ', $trends['labels'][$index])[0] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Payment Methods Breakdown (Option 4.B) --}}
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm">
                <h3 class="font-black text-slate-800 dark:text-white mb-4 flex items-center gap-2 text-sm uppercase tracking-wider">
                    <x-icon name="o-chart-pie" class="w-4 h-4 text-emerald-500" />
                    {{ __('Payment Sources') }}
                </h3>
                
                <div class="space-y-3">
                    @php 
                        $totalConfirmed = $paymentBreakdown->sum('total') ?: 1;
                        $methods = ['cash' => 'Cash', 'bkash' => 'bKash', 'nagad' => 'Nagad', 'bank' => 'Bank', 'other' => 'Other'];
                    @endphp
                    
                    @foreach($methods as $key => $label)
                        @php 
                            $val = $paymentBreakdown->firstWhere('payment_method', $key)?->total ?? 0;
                            $pct = round(($val / $totalConfirmed) * 100);
                        @endphp
                        <div class="space-y-1">
                            <div class="flex justify-between text-xs font-semibold">
                                <span class="text-slate-600 dark:text-slate-400 flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full 
                                        @if($key === 'bkash') bg-pink-500 
                                        @elseif($key === 'nagad') bg-orange-500 
                                        @elseif($key === 'bank') bg-blue-500 
                                        @elseif($key === 'cash') bg-emerald-500 
                                        @else bg-slate-400 @endif"></span>
                                    {{ $label }}
                                </span>
                                <span class="font-bold text-slate-800 dark:text-slate-200">৳{{ number_format($val) }} ({{ $pct }}%)</span>
                            </div>
                            <div class="w-full bg-slate-50 dark:bg-slate-800 rounded-full h-1.5 overflow-hidden">
                                <div class="h-full rounded-full 
                                    @if($key === 'bkash') bg-pink-500 
                                    @elseif($key === 'nagad') bg-orange-500 
                                    @elseif($key === 'bank') bg-blue-500 
                                    @elseif($key === 'cash') bg-emerald-500 
                                    @else bg-slate-400 @endif" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            
            {{-- Campaign Description --}}
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm">
                <h3 class="font-black text-slate-800 dark:text-white mb-2 text-sm uppercase tracking-wider">{{ __('About Campaign') }}</h3>
                <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed whitespace-pre-line">
                    {{ $campaign->description ?: __('No details provided.') }}
                </p>
            </div>
        </div>

        {{-- Right: Content Tabs (2 Columns) --}}
        <div class="lg:col-span-2 space-y-6">
            
            {{-- Tabs --}}
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="flex border-b border-slate-100 dark:border-slate-800/80 p-4 gap-2">
                    @foreach([
                        'donations' => ['label' => __('Donations Log'), 'icon' => 'o-list-bullet'],
                        'updates'   => ['label' => __('Updates'),       'icon' => 'o-megaphone'],
                        'faqs'      => ['label' => __('Q&A / FAQ'),    'icon' => 'o-question-mark-circle']
                    ] as $key => $tab)
                        <button wire:click="$set('activeTab', '{{ $key }}')" 
                                class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-black transition-all
                                {{ $activeTab === $key 
                                    ? 'bg-indigo-600 text-white shadow-sm' 
                                    : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/30' }}">
                            <x-icon name="{{ $tab['icon'] }}" class="w-4 h-4" />
                            {{ $tab['label'] }}
                        </button>
                    @endforeach
                </div>

                {{-- TAB: Donations --}}
                <div class="p-6 {{ $activeTab !== 'donations' ? 'hidden' : '' }}">
                    <div class="overflow-x-auto -mx-6">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-slate-50 dark:bg-slate-800/40 text-slate-500 uppercase font-bold">
                                <tr>
                                    <th class="px-6 py-3.5">{{ __('Donor') }}</th>
                                    <th class="px-6 py-3.5">{{ __('Amount') }}</th>
                                    <th class="px-6 py-3.5">{{ __('Payment Method') }}</th>
                                    <th class="px-6 py-3.5">{{ __('Status') }}</th>
                                    <th class="px-6 py-3.5">{{ __('Date') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                                @forelse($donations as $donation)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                                        <td class="px-6 py-4 font-bold text-slate-800 dark:text-slate-200">
                                            @if($donation->is_anonymous)
                                                <span class="text-slate-400 italic">{{ __('Anonymous') }}</span>
                                                <span class="text-[10px] text-slate-400 font-normal block">({{ $donation->user?->name }})</span>
                                            @else
                                                {{ $donation->user?->name ?? __('Guest Donor') }}
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 font-black text-slate-800 dark:text-white">
                                            ৳{{ number_format($donation->amount) }}
                                        </td>
                                        <td class="px-6 py-4 text-slate-600 dark:text-slate-400">
                                            <span class="font-bold uppercase">{{ $donation->payment_method }}</span>
                                            @if($donation->transaction_id)
                                                <span class="block text-[10px] font-mono text-indigo-500">{{ $donation->transaction_id }}</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($donation->status === 'confirmed')
                                                <span class="badge badge-success badge-sm font-bold text-white">{{ __('Confirmed') }}</span>
                                            @elseif($donation->status === 'pending')
                                                <span class="badge badge-warning badge-sm font-bold text-slate-800">{{ __('Pending Review') }}</span>
                                            @elseif($donation->status === 'pending_payment')
                                                <span class="badge badge-error badge-sm font-bold text-white">{{ __('Due') }}</span>
                                            @else
                                                <span class="badge badge-ghost badge-sm font-bold">{{ ucfirst($donation->status) }}</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-slate-500">
                                            {{ ($donation->donated_at ?? $donation->created_at)->format('M d, Y h:i A') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                            <x-icon name="o-banknotes" class="w-10 h-10 mx-auto mb-2 opacity-35" />
                                            {{ __('No donations recorded for this campaign yet.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($donations->hasPages())
                        <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                            {{ $donations->links(restoreURI: true) }}
                        </div>
                    @endif
                </div>

                {{-- TAB: Updates --}}
                <div class="p-6 {{ $activeTab !== 'updates' ? 'hidden' : '' }} space-y-6">
                    <div class="flex items-center justify-between">
                        <h4 class="font-bold text-slate-800 dark:text-white text-sm">{{ __('Campaign Updates Log') }}</h4>
                        <x-button label="{{ __('Post Update') }}" icon="o-plus" wire:click="openUpdateModal" class="btn-xs bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-bold" />
                    </div>

                    <div class="space-y-4">
                        @forelse($updates as $update)
                            <div class="bg-slate-50 dark:bg-slate-800/35 border border-slate-150 dark:border-slate-800/80 p-5 rounded-2xl flex items-start gap-4">
                                <div class="w-9 h-9 rounded-full bg-indigo-500/10 text-indigo-500 flex items-center justify-center shrink-0">
                                    <x-icon name="o-megaphone" class="w-5 h-5" />
                                </div>
                                <div class="flex-1 space-y-1">
                                    <div class="flex items-start justify-between">
                                        <h5 class="font-black text-slate-800 dark:text-white text-sm">{{ $update->title }}</h5>
                                        <div class="flex items-center gap-2">
                                            <span class="text-[10px] text-slate-400 font-bold uppercase">{{ $update->created_at->format('M j, Y') }}</span>
                                            <button wire:click="deleteUpdate({{ $update->id }})" wire:confirm="{{ __('Are you sure you want to delete this update?') }}" class="text-rose-500 hover:text-rose-700 p-0.5 rounded transition-colors">
                                                <x-icon name="o-trash" class="w-3.5 h-3.5" />
                                            </button>
                                        </div>
                                    </div>
                                    <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed whitespace-pre-line">
                                        {{ $update->content }}
                                    </p>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12 text-slate-400">
                                <x-icon name="o-megaphone" class="w-10 h-10 mx-auto mb-2 opacity-35" />
                                {{ __('No updates have been posted for this campaign.') }}
                            </div>
                        @endforelse
                    </div>

                    @if($updates->hasPages())
                        <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                            {{ $updates->links(restoreURI: true) }}
                        </div>
                    @endif
                </div>

                {{-- TAB: FAQs --}}
                <div class="p-6 {{ $activeTab !== 'faqs' ? 'hidden' : '' }} space-y-6">
                    <div class="flex items-center justify-between">
                        <h4 class="font-bold text-slate-800 dark:text-white text-sm">{{ __('Campaign FAQs') }}</h4>
                        <x-button label="{{ __('Add FAQ') }}" icon="o-plus" wire:click="openFaqModal" class="btn-xs bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-bold" />
                    </div>

                    <div class="space-y-4">
                        @forelse($faqs as $faq)
                            <div class="bg-slate-50 dark:bg-slate-800/35 border border-slate-150 dark:border-slate-800/80 p-5 rounded-2xl space-y-3">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <span class="badge badge-neutral badge-xs font-bold uppercase mb-1">{{ __('Question') }}</span>
                                        <p class="font-bold text-slate-800 dark:text-white text-xs leading-relaxed">
                                            {{ $faq->question }}
                                        </p>
                                        <span class="text-[9px] text-slate-400 block mt-1">{{ __('Asked by') }}: {{ $faq->user?->name ?? __('Anonymous') }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <x-button icon="o-pencil" wire:click="openFaqModal({{ $faq->id }})" class="btn-circle btn-xs btn-ghost text-indigo-500 hover:bg-indigo-50 dark:hover:bg-indigo-950/40" />
                                        <x-button icon="o-trash" wire:click="deleteFaq({{ $faq->id }})" wire:confirm="{{ __('Delete this FAQ item?') }}" class="btn-circle btn-xs btn-ghost text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40" />
                                    </div>
                                </div>

                                <div class="border-t border-slate-200/50 dark:border-slate-700/60 pt-3">
                                    <span class="badge badge-success badge-xs font-bold text-white uppercase mb-1">{{ __('Answer') }}</span>
                                    @if($faq->answer)
                                        <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed whitespace-pre-line">
                                            {{ $faq->answer }}
                                        </p>
                                        <span class="text-[9px] text-slate-400 block mt-1">{{ __('Answered by') }}: {{ $faq->answeredBy?->name ?? __('System') }}</span>
                                    @else
                                        <p class="text-xs text-rose-400 italic">
                                            {{ __('Awaiting treasurer / admin response.') }}
                                        </p>
                                        <div class="mt-2">
                                            <x-button label="{{ __('Answer Question') }}" icon="o-chat-bubble-left-right" wire:click="openFaqModal({{ $faq->id }})" class="btn-xs bg-emerald-600 text-white rounded-lg" />
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12 text-slate-400">
                                <x-icon name="o-question-mark-circle" class="w-10 h-10 mx-auto mb-2 opacity-35" />
                                {{ __('No FAQ items configured or asked for this campaign.') }}
                            </div>
                        @endforelse
                    </div>

                    @if($faqs->hasPages())
                        <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                            {{ $faqs->links(restoreURI: true) }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Post Update Modal --}}
    <x-modal wire:model="updateModal" title="{{ __('Post Campaign Update') }}" class="backdrop-blur-sm">
        <div class="space-y-4 pt-2">
            <x-input label="{{ __('Update Title') }}" wire:model="updateTitle" placeholder="{{ __('e.g. Phase 1 Goal Reached!') }}" required />
            <x-textarea label="{{ __('Content Details') }}" wire:model="updateContent" placeholder="{{ __('Provide detailed progress description for backers...') }}" rows="5" required />
        </div>
        <x-slot:actions>
            <x-button label="{{ __('Cancel') }}" wire:click="$set('updateModal', false)" class="btn-ghost rounded-xl" />
            <x-button label="{{ __('Publish Update') }}" wire:click="saveUpdate" icon="o-paper-airplane" class="bg-indigo-600 text-white border-none rounded-xl" spinner="saveUpdate" />
        </x-slot:actions>
    </x-modal>

    {{-- Answer/Create FAQ Modal --}}
    <x-modal wire:model="faqModal" title="{{ $faqId ? __('Manage/Answer FAQ Question') : __('Add Predefined FAQ Q&A') }}" class="backdrop-blur-sm">
        <div class="space-y-4 pt-2">
            <x-textarea label="{{ __('Question') }}" wire:model="faqQuestion" placeholder="{{ __('e.g. Will there be an audit report published?') }}" rows="2" required />
            <x-textarea label="{{ __('Answer') }}" wire:model="faqAnswer" placeholder="{{ __('Provide details to help clarify...') }}" rows="4" required />
        </div>
        <x-slot:actions>
            <x-button label="{{ __('Cancel') }}" wire:click="$set('faqModal', false)" class="btn-ghost rounded-xl" />
            <x-button label="{{ __('Save FAQ') }}" wire:click="saveFaq" icon="o-check" class="bg-indigo-600 text-white border-none rounded-xl" spinner="saveFaq" />
        </x-slot:actions>
    </x-modal>
</div>
