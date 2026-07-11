<div class="min-h-screen bg-slate-50 dark:bg-slate-950 pb-16">

    {{-- Hero Header --}}
    <div class="bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 text-white py-16 relative overflow-hidden">
        <div class="absolute top-0 right-1/4 w-72 h-72 bg-emerald-500/10 rounded-full blur-[120px]"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="flex items-center gap-2 text-rose-400 font-bold text-xs uppercase tracking-widest mb-3">
                <a href="{{ route('web.campaigns') }}" wire:navigate class="hover:underline">{{ __('Campaigns') }}</a>
                <span>/</span>
                <span>{{ __('Details') }}</span>
            </div>
            <h1 class="text-3xl md:text-5xl font-black mb-4 tracking-tight leading-tight">{{ $campaign->title }}</h1>
            <p class="text-white/60 text-sm md:text-base max-w-2xl">
                {{ __('Started by') }} <span class="font-bold text-white">{{ $campaign->creator?->name ?? __('Community Admin') }}</span>
                @if($campaign->starts_at)
                    · {{ __('Active since') }} {{ $campaign->starts_at->format('M d, Y') }}
                @endif
            </p>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            {{-- LEFT COLUMN: Details & Info --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Campaign Visual & Progress Card --}}
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
                    <div class="h-64 sm:h-96 w-full relative bg-slate-800">
                        @if($campaign->cover_url)
                            <img src="{{ $campaign->cover_url }}" class="w-full h-full object-cover" alt="{{ $campaign->title }}">
                        @else
                            <div class="w-full h-full bg-gradient-to-tr from-rose-500/20 to-indigo-500/20 flex items-center justify-center">
                                <x-icon name="o-heart" class="w-24 h-24 text-rose-500/30" />
                            </div>
                        @endif
                        <span class="absolute top-4 right-4 bg-emerald-500 text-white font-black text-xs px-3.5 py-1.5 rounded-full shadow-lg">
                            {{ $campaign->status }}
                        </span>
                    </div>

                    {{-- Progress Bar Info --}}
                    <div class="p-6 sm:p-8 space-y-4">
                        <div class="flex justify-between items-end">
                            <div>
                                <span class="text-3xl font-black text-slate-900 dark:text-white">
                                    ৳{{ number_format($campaign->collected_amount) }}
                                </span>
                                <span class="text-sm font-bold text-slate-400">
                                    {{ __('raised of') }} ৳{{ number_format($campaign->goal_amount) }}
                                </span>
                            </div>
                            <span class="text-lg font-black text-emerald-600 dark:text-emerald-400">
                                {{ $campaign->progress_percentage }}%
                            </span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-3 overflow-hidden">
                            <div class="bg-gradient-to-r from-emerald-400 to-teal-500 h-full rounded-full transition-all duration-1000" style="width: {{ $campaign->progress_percentage }}%"></div>
                        </div>
                    </div>
                </div>

                {{-- Description, Updates & FAQ Tabs --}}
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 sm:p-8 shadow-sm">
                    <div class="flex gap-2 border-b border-slate-100 dark:border-slate-800 pb-4 mb-6">
                        @foreach([
                            'about'   => ['icon' => 'o-information-circle', 'label' => 'About Campaign'],
                            'updates' => ['icon' => 'o-megaphone',          'label' => 'Updates'],
                            'faq'     => ['icon' => 'o-question-mark-circle','label' => 'FAQ'],
                        ] as $tabKey => $tabInfo)
                        <button wire:click="switchTab('{{ $tabKey }}')"
                                class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-black transition-all
                                {{ $activeTab === $tabKey
                                    ? 'bg-slate-900 dark:bg-slate-800 text-white shadow'
                                    : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/50' }}">
                            <x-icon name="{{ $tabInfo['icon'] }}" class="w-4 h-4" />
                            {{ __($tabInfo['label']) }}
                        </button>
                        @endforeach
                    </div>

                    {{-- TAB CONTENT: About --}}
                    <div class="{{ $activeTab !== 'about' ? 'hidden' : '' }} space-y-4">
                        <div class="prose dark:prose-invert max-w-none text-slate-600 dark:text-slate-300 leading-relaxed text-sm sm:text-base whitespace-pre-line">
                            {{ $campaign->description }}
                        </div>
                    </div>

                    {{-- TAB CONTENT: Updates --}}
                    <div class="{{ $activeTab !== 'updates' ? 'hidden' : '' }} space-y-6">
                        @if($this->isAdmin())
                            <div class="bg-slate-50 dark:bg-slate-800/40 rounded-2xl p-5 border border-slate-200/50 dark:border-slate-800 space-y-4">
                                <h4 class="font-black text-slate-800 dark:text-slate-200 flex items-center gap-2">
                                    <x-icon name="o-pencil" class="w-5 h-5 text-primary" /> Post a Campaign Update
                                </h4>
                                <div class="space-y-3">
                                    <x-input label="Update Title" wire:model.defer="newUpdateTitle" placeholder="e.g. Phase 1 Finished" required />
                                    <x-textarea label="Content" wire:model.defer="newUpdateContent" placeholder="Describe the progress..." rows="3" required />
                                    <x-button label="Publish Update" class="btn-primary btn-sm" wire:click="addUpdate" spinner="addUpdate" />
                                </div>
                            </div>
                        @endif

                        {{-- Real database updates --}}
                        @foreach($this->dbUpdates as $update)
                            <div class="relative pl-6 border-l-2 border-primary pb-6 last:pb-0">
                                <div class="absolute -left-1.5 top-1.5 w-3.5 h-3.5 rounded-full bg-primary border-4 border-white dark:border-slate-900 shadow"></div>
                                <div class="bg-slate-50 dark:bg-slate-800/40 rounded-2xl p-5 border border-slate-200/50 dark:border-slate-800">
                                    <div class="flex justify-between items-start mb-2">
                                        <h4 class="font-black text-slate-800 dark:text-slate-200">{{ $update->title }}</h4>
                                        <div class="flex items-center gap-2">
                                            <span class="text-[10px] text-slate-400 font-bold uppercase">{{ $update->created_at->diffForHumans() }}</span>
                                            @if($this->isAdmin())
                                                <button wire:click="deleteUpdate({{ $update->id }})" confirm="Are you sure you want to delete this update?" class="text-rose-500 hover:text-rose-700 transition-colors">
                                                    <x-icon name="o-trash" class="w-3.5 h-3.5" />
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                    <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed whitespace-pre-line">{{ $update->content }}</p>
                                    <div class="mt-3 flex items-center gap-1.5 text-xs text-slate-400">
                                        <x-icon name="o-user" class="w-3.5 h-3.5" />
                                        <span>{{ $update->user->name ?? 'Admin' }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        @if(count($this->dbUpdates) === 0)
                            <p class="text-slate-400 text-sm italic">{{ __('No updates have been posted yet.') }}</p>
                        @endif
                    </div>

                    {{-- TAB CONTENT: FAQ --}}
                    <div class="{{ $activeTab !== 'faq' ? 'hidden' : '' }} space-y-4">
                        {{-- Ask a question form --}}
                        @auth
                            <div class="bg-slate-50 dark:bg-slate-800/40 rounded-2xl p-5 border border-slate-200/50 dark:border-slate-800 space-y-3">
                                <h4 class="font-black text-slate-800 dark:text-slate-200 flex items-center gap-2">
                                    <x-icon name="o-question-mark-circle" class="w-5 h-5 text-primary" /> Ask a Question
                                </h4>
                                <div class="space-y-3">
                                    <x-textarea wire:model.defer="newQuestion" placeholder="Type your question about this campaign here..." rows="2" required />
                                    <x-button label="Submit Question" class="btn-primary btn-sm" wire:click="askQuestion" spinner="askQuestion" />
                                </div>
                            </div>
                        @else
                            <div class="bg-slate-50 dark:bg-slate-800/40 rounded-2xl p-4 text-center border border-slate-200/50 dark:border-slate-800">
                                <p class="text-sm text-slate-500 dark:text-slate-400">Please <a href="{{ route('login') }}" class="text-primary font-bold hover:underline">log in</a> to ask a question.</p>
                            </div>
                        @endauth

                        {{-- Database user-submitted FAQs --}}
                        @if(count($this->faqs) > 0)
                            <h4 class="text-xs font-black uppercase tracking-wider text-slate-400 mt-6 mb-2">{{ __('Community Q&A') }}</h4>
                            <div class="space-y-3">
                                @foreach($this->faqs as $faq)
                                    <div class="bg-slate-50 dark:bg-slate-800/40 rounded-2xl p-5 border border-slate-200/50 dark:border-slate-800 space-y-3">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <span class="text-xs text-slate-400 font-bold block mb-1">Asked by {{ $faq->user->name ?? 'Member' }}</span>
                                                <h5 class="font-bold text-slate-800 dark:text-slate-200 text-sm sm:text-base">Q: {{ $faq->question }}</h5>
                                            </div>
                                            @if($this->isAdmin())
                                                <button wire:click="deleteFaq({{ $faq->id }})" confirm="Are you sure you want to delete this FAQ?" class="text-rose-500 hover:text-rose-700 transition-colors">
                                                    <x-icon name="o-trash" class="w-4 h-4" />
                                                </button>
                                            @endif
                                        </div>

                                        @if($faq->answer)
                                            <div class="bg-white dark:bg-slate-900 rounded-xl p-3.5 border border-slate-100 dark:border-slate-800 text-xs sm:text-sm text-slate-600 dark:text-slate-300">
                                                <span class="font-black text-primary block mb-1">Answer from {{ $faq->answeredBy->name ?? 'Admin' }}:</span>
                                                <p class="leading-relaxed">{{ $faq->answer }}</p>
                                                <span class="text-[10px] text-slate-400 mt-2 block">{{ $faq->answered_at->diffForHumans() }}</span>
                                            </div>
                                        @else
                                            @if($this->isAdmin())
                                                <div class="space-y-2 mt-2">
                                                    <x-textarea wire:model.defer="faqAnswers.{{ $faq->id }}" placeholder="Type the answer here..." rows="2" />
                                                    <x-button label="Post Answer" class="btn-success btn-xs" wire:click="answerQuestion({{ $faq->id }})" spinner="answerQuestion({{ $faq->id }})" />
                                                </div>
                                            @else
                                                <div class="text-xs text-slate-400 italic">
                                                    <x-icon name="o-clock" class="w-3.5 h-3.5 inline mr-1" /> Awaiting response from administration...
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if(count($this->faqs) === 0)
                            <p class="text-slate-400 text-sm italic">{{ __('No FAQs have been submitted yet.') }}</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- RIGHT COLUMN: Donation panel & Leaderboard --}}
            <div class="space-y-6">

                {{-- Direct inline donation panel --}}
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 sm:p-8 shadow-sm">
                    <h3 class="font-black text-slate-900 dark:text-white text-lg mb-4 flex items-center gap-2">
                        <x-icon name="o-heart" class="w-5 h-5 text-rose-500" />
                        {{ __('Support This Cause') }}
                    </h3>

                    @auth
                        <form wire:submit.prevent="submitDonation" class="space-y-4">
                            
                            {{-- Predefined amounts --}}
                            <div>
                                <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 block">{{ __('Select Amount') }}</label>
                                <div class="grid grid-cols-4 gap-2">
                                    @foreach(['100', '500', '1000', '5000'] as $val)
                                    <button type="button" wire:click="selectAmount('{{ $val }}')"
                                            class="py-2.5 rounded-xl border text-xs font-black transition-all
                                            {{ $amount === $val && !$customAmount
                                                ? 'bg-rose-500 border-rose-600 text-white shadow shadow-rose-500/20'
                                                : 'bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100' }}">
                                        ৳{{ $val }}
                                    </button>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Custom Amount --}}
                            <div>
                                <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1 block">{{ __('Or Custom Amount') }}</label>
                                <input type="number" wire:model.live="customAmount" placeholder="Enter amount (৳)"
                                       class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm font-bold text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500">
                                @error('customAmount') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            {{-- Payment Method Selector --}}
                            <div>
                                <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 block">{{ __('Payment Method') }}</label>
                                <div class="grid grid-cols-3 gap-2">
                                    @foreach(['bkash' => 'bKash', 'nagad' => 'Nagad', 'bank' => 'Bank'] as $key => $label)
                                    <button type="button" wire:click="$set('paymentMethod', '{{ $key }}')"
                                            class="py-2 rounded-xl border text-[11px] font-black transition-all
                                            {{ $paymentMethod === $key
                                                ? 'bg-slate-900 dark:bg-slate-700 border-slate-950 text-white'
                                                : 'bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400' }}">
                                        {{ $label }}
                                    </button>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Payment Instructions depending on selected method --}}
                            <div class="bg-slate-50 dark:bg-slate-800/50 rounded-2xl p-4 border border-slate-200/50 dark:border-slate-800 text-xs text-slate-600 dark:text-slate-300 space-y-2">
                                <span class="font-black text-[10px] uppercase tracking-wider text-rose-500 block">{{ __('Payment Instructions') }}</span>
                                @if($paymentMethod === 'bkash')
                                    <p>{{ __('Please send money (Send Money or Cash In) to our official bKash personal account') }}:</p>
                                    <p class="font-mono font-black text-sm text-slate-950 dark:text-white bg-slate-100 dark:bg-slate-800 p-2 rounded-lg text-center tracking-wide">
                                        {{ setting('payment.bkash_no', '01712345678') }}
                                    </p>
                                @elseif($paymentMethod === 'nagad')
                                    <p>{{ __('Please send money to our official Nagad personal account') }}:</p>
                                    <p class="font-mono font-black text-sm text-slate-950 dark:text-white bg-slate-100 dark:bg-slate-800 p-2 rounded-lg text-center tracking-wide">
                                        {{ setting('payment.nagad_no', '01812345678') }}
                                    </p>
                                @else
                                    <p>{{ __('Please transfer funds to the following Bank Account details') }}:</p>
                                    <div class="font-mono bg-slate-100 dark:bg-slate-800 p-3 rounded-lg space-y-1">
                                        <div><span class="text-slate-400">{{ __('Bank') }}:</span> <span class="text-slate-800 dark:text-slate-200 font-bold">{{ setting('payment.bank_name', 'Dutch Bangla Bank PLC') }}</span></div>
                                        <div><span class="text-slate-400">{{ __('Account') }}:</span> <span class="text-slate-800 dark:text-slate-200 font-bold">{{ setting('payment.bank_account_no', '123-456-7890123') }}</span></div>
                                        <div><span class="text-slate-400">{{ __('Branch') }}:</span> <span class="text-slate-800 dark:text-slate-200 font-bold">{{ setting('payment.bank_branch', 'PSTU Branch') }}</span></div>
                                        <div><span class="text-slate-400">{{ __('Holder') }}:</span> <span class="text-slate-800 dark:text-slate-200 font-bold">{{ setting('payment.bank_holder', 'PSTU Dawah Community') }}</span></div>
                                    </div>
                                @endif
                            </div>

                            {{-- Transaction ID --}}
                            <div>
                                <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1 block">{{ __('Transaction ID / Reference') }}</label>
                                <input type="text" wire:model="transactionId" placeholder="TrxID (e.g. AM89KJ09)"
                                       class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm font-mono font-bold text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500">
                                @error('transactionId') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            {{-- Notes --}}
                            <div>
                                <label class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1 block">{{ __('Note (Optional)') }}</label>
                                <textarea wire:model="note" rows="2" placeholder="Write a short message..."
                                          class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-2.5 text-sm font-bold text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500"></textarea>
                                @error('note') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            {{-- Anonymous Toggle --}}
                            <div class="flex items-center gap-2 py-1">
                                <input type="checkbox" id="anon-toggle" wire:model="isAnonymous" class="checkbox checkbox-rose checkbox-sm rounded-lg" />
                                <label for="anon-toggle" class="text-xs font-bold text-slate-600 dark:text-slate-400 cursor-pointer select-none">
                                    {{ __('Donate anonymously') }}
                                </label>
                            </div>

                            <button type="submit"
                                    class="w-full btn bg-gradient-to-r from-rose-500 to-pink-600 hover:from-rose-600 hover:to-pink-700 border-none text-white font-black rounded-xl py-3 shadow-lg shadow-rose-500/20 hover:shadow-rose-500/30 transition-all">
                                {{ __('Submit Donation') }}
                            </button>

                        </form>
                    @else
                        <div class="text-center py-6 space-y-4">
                            <p class="text-sm text-slate-500">{{ __('Please sign in to make a contribution.') }}</p>
                            <a href="{{ route('login') }}" class="btn btn-primary btn-block rounded-xl font-bold shadow-lg shadow-primary/20">
                                {{ __('Sign In to Donate') }}
                            </a>
                        </div>
                    @endauth
                </div>

                {{-- Leaderboard & Recent Donors Card --}}
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 sm:p-8 shadow-sm space-y-6">
                    
                    {{-- Top Donors Leaderboard --}}
                    <div>
                        <h4 class="font-black text-slate-900 dark:text-white text-sm uppercase tracking-wider mb-4 flex items-center gap-1.5">
                            <x-icon name="o-trophy" class="w-4 h-4 text-amber-500" />
                            {{ __('Top Contributors') }}
                        </h4>
                        @if($this->topDonors->isEmpty())
                            <p class="text-slate-400 text-xs italic">{{ __('No contributions yet.') }}</p>
                        @else
                            <div class="space-y-3">
                                @foreach($this->topDonors as $index => $record)
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <span class="w-5 text-xs font-black text-slate-400">#{{ $index + 1 }}</span>
                                        <div class="w-8 h-8 rounded-lg overflow-hidden bg-slate-100">
                                            <img src="{{ $record->user?->avatar_url }}" class="w-full h-full object-cover">
                                        </div>
                                        <span class="text-xs font-black text-slate-700 dark:text-slate-300">
                                            {{ $record->user?->name }}
                                        </span>
                                    </div>
                                    <span class="text-xs font-black text-emerald-600 dark:text-emerald-400">
                                        ৳{{ number_format($record->total_amount) }}
                                    </span>
                                </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Recent Donors feed --}}
                    <div class="border-t border-slate-100 dark:border-slate-800 pt-6">
                        <h4 class="font-black text-slate-900 dark:text-white text-sm uppercase tracking-wider mb-4 flex items-center gap-1.5">
                            <x-icon name="o-users" class="w-4 h-4 text-rose-500" />
                            {{ __('Recent Donations') }}
                        </h4>
                        @if($this->recentDonations->isEmpty())
                            <p class="text-slate-400 text-xs italic">{{ __('No confirmed public donations yet.') }}</p>
                        @else
                            <div class="space-y-3.5">
                                @foreach($this->recentDonations as $donation)
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-7 h-7 rounded-lg overflow-hidden bg-slate-100">
                                            <img src="{{ $donation->user?->avatar_url }}" class="w-full h-full object-cover">
                                        </div>
                                        <div>
                                            <div class="text-xs font-bold text-slate-700 dark:text-slate-300 leading-none">
                                                {{ $donation->user?->name }}
                                            </div>
                                            <span class="text-[9px] text-slate-400">{{ \Carbon\Carbon::parse($donation->donated_at)->diffForHumans() }}</span>
                                        </div>
                                    </div>
                                    <span class="text-xs font-black text-rose-600 dark:text-rose-400">
                                        +৳{{ number_format($donation->amount) }}
                                    </span>
                                </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                </div>

            </div>

        </div>
    </div>
</div>
