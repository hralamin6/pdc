<div class="bg-slate-50/40 dark:bg-slate-950/40 min-h-screen py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Minimalist Typography Header --}}
        <div class="border-b border-slate-200 dark:border-slate-800/80 pb-8 mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary/10 text-primary text-xs font-black uppercase tracking-widest mb-3">
                    <x-icon name="o-book-open" class="w-3.5 h-3.5" />
                    {{ __('Library Hub') }}
                </span>
                <h1 class="text-3xl sm:text-5xl font-black text-slate-900 dark:text-white tracking-tight mb-3">
                    {{ __('My Books') }}
                </h1>
                <p class="text-sm sm:text-base text-slate-500 dark:text-slate-400 max-w-xl leading-relaxed">
                    {{ __('Manage physical copies you own, log your personal reading progress, and track your lend/borrow requests.') }}
                </p>
            </div>

            <div class="flex items-center gap-3">
                @if($activeTab === 'copies')
                    <button wire:click="openAddModal" class="btn btn-primary btn-sm rounded-xl px-5 font-bold shadow-lg shadow-primary/25 hover:scale-105 transition-transform">
                        <x-icon name="o-plus" class="w-4 h-4" /> {{ __('Add Book to Shelf') }}
                    </button>
                @elseif($activeTab === 'reading')
                    <button wire:click="openAddReadingModal" class="btn btn-primary btn-sm rounded-xl px-5 font-bold shadow-lg shadow-primary/25 hover:scale-105 transition-transform">
                        <x-icon name="o-plus" class="w-4 h-4" /> {{ __('Track a Book') }}
                    </button>
                @endif
            </div>
        </div>

        {{-- Tab Pills --}}
        <div class="flex flex-wrap items-center gap-2 mb-8 bg-slate-100/60 dark:bg-slate-900/60 p-1.5 rounded-2xl max-w-md border border-slate-250/20 dark:border-slate-800/20">
            <button wire:click="switchTab('copies')" class="flex-1 py-2.5 px-4 rounded-xl text-xs font-bold transition-all text-center {{ $activeTab === 'copies' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-sm' : 'text-slate-500 hover:text-slate-950 dark:hover:text-white' }}">
                {{ __('Physical Shelf') }}
            </button>
            <button wire:click="switchTab('reading')" class="flex-1 py-2.5 px-4 rounded-xl text-xs font-bold transition-all text-center {{ $activeTab === 'reading' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-sm' : 'text-slate-500 hover:text-slate-950 dark:hover:text-white' }}">
                {{ __('Reading Tracker') }}
            </button>
            <button wire:click="switchTab('requests')" class="flex-1 py-2.5 px-4 rounded-xl text-xs font-bold transition-all text-center {{ $activeTab === 'requests' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-sm' : 'text-slate-500 hover:text-slate-950 dark:hover:text-white' }}">
                {{ __('Lend & Borrow') }}
            </button>
        </div>

        {{-- ==================== TAB: PHYSICAL SHELF ==================== --}}
        @if($activeTab === 'copies')
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @forelse($this->myCopies as $copy)
                    <div wire:key="copy-{{ $copy->id }}" class="group bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/50 dark:border-slate-800/80 overflow-hidden flex flex-col transition-all duration-300 hover:shadow-xl hover:shadow-slate-200/30 dark:hover:shadow-none hover:border-slate-300 dark:hover:border-slate-700">
                        {{-- Book Cover Preview --}}
                        <div class="h-56 bg-slate-100 dark:bg-slate-950 relative overflow-hidden flex items-center justify-center">
                            @if($copy->book->cover_url)
                                <img src="{{ $copy->book->cover_url }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                            @else
                                <div class="w-full h-full flex flex-col items-center justify-center bg-gradient-to-br from-indigo-50/50 to-slate-100 dark:from-slate-800 dark:to-slate-900 text-slate-300 dark:text-slate-700">
                                    <x-icon name="o-book-open" class="w-12 h-12 mb-2" />
                                    <span class="text-xs uppercase font-semibold text-slate-400">{{ __('No Cover Available') }}</span>
                                </div>
                            @endif

                            {{-- Badges --}}
                            <div class="absolute top-3 right-3 flex flex-col gap-1">
                                @if($copy->status === 'available')
                                    <span class="px-2.5 py-0.5 text-[10px] font-black uppercase bg-emerald-500 text-white rounded-lg shadow-sm">{{ __('Available') }}</span>
                                @elseif($copy->status === 'borrowed')
                                    <span class="px-2.5 py-0.5 text-[10px] font-black uppercase bg-amber-500 text-white rounded-lg shadow-sm">{{ __('Borrowed') }}</span>
                                @endif
                            </div>
                        </div>

                        {{-- Card Body --}}
                        <div class="p-5 flex-grow flex flex-col justify-between">
                            <div>
                                <h3 class="font-black text-slate-800 dark:text-slate-100 leading-snug line-clamp-2 mb-1 group-hover:text-primary transition-colors">{{ $copy->book->title }}</h3>
                                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-4">{{ $copy->book->author?->name ?? __('Unknown Author') }}</p>

                                <div class="flex items-center justify-between text-xs font-semibold text-slate-600 dark:text-slate-400 mb-4 bg-slate-50 dark:bg-slate-950 p-2.5 rounded-xl border border-slate-200/50 dark:border-slate-800/80">
                                    <span>{{ __('Condition:') }} <span class="text-slate-800 dark:text-slate-200 font-bold">{{ $copy->condition }}</span></span>
                                    <span>
                                        @if($copy->is_borrowable)
                                            <span class="text-emerald-600 dark:text-emerald-400 flex items-center gap-1"><x-icon name="o-check-circle" class="w-3.5 h-3.5" /> {{ __('Lendable') }}</span>
                                        @else
                                            <span class="text-slate-400 flex items-center gap-1"><x-icon name="o-lock-closed" class="w-3.5 h-3.5" /> {{ __('Private') }}</span>
                                        @endif
                                    </span>
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="pt-3 border-t border-slate-100 dark:border-slate-850 flex justify-between items-center">
                                <button wire:click="toggleBorrowable({{ $copy->id }})" class="btn btn-ghost btn-xs text-xs font-bold rounded-lg px-2.5 py-1 {{ $copy->is_borrowable ? 'text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/20' : 'text-emerald-500 hover:bg-emerald-50 dark:hover:bg-emerald-950/20' }}">
                                    {{ $copy->is_borrowable ? __('Make Private') : __('Make Lendable') }}
                                </button>

                                <button wire:click="deleteCopy({{ $copy->id }})" wire:confirm="{{ __('Remove this copy from your shelf? This action cannot be undone.') }}" class="btn btn-ghost btn-circle btn-xs text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/20">
                                    <x-icon name="o-trash" class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-20 bg-white dark:bg-slate-900 rounded-3xl border border-dashed border-slate-200 dark:border-slate-800 p-8 shadow-sm">
                        <x-icon name="o-bookmark-square" class="w-16 h-16 text-slate-300 dark:text-slate-700 mx-auto mb-4" />
                        <h3 class="text-xl font-black text-slate-800 dark:text-slate-200 mb-2">{{ __('Your shelf is empty') }}</h3>
                        <p class="text-sm text-slate-450 dark:text-slate-500 max-w-sm mx-auto mb-6">{{ __('List physical books you own to let others in the community borrow them, or catalog them on your private shelf.') }}</p>
                        <button wire:click="openAddModal" class="btn btn-primary btn-sm rounded-xl px-5 font-bold shadow-lg shadow-primary/25">
                            <x-icon name="o-plus" class="w-4 h-4" /> {{ __('Add Your First Book') }}
                        </button>
                    </div>
                @endforelse
            </div>
        @endif

        {{-- ==================== TAB: READING SHELF ==================== --}}
        @if($activeTab === 'reading')
            {{-- Reading Sub Tabs --}}
            <div class="flex items-center gap-1.5 mb-6 overflow-x-auto pb-2 scrollbar-none">
                <button wire:click="switchReadingTab('reading')" class="px-4 py-1.5 text-xs font-black rounded-xl border transition {{ $readingSubTab === 'reading' ? 'bg-primary border-primary text-white shadow-sm shadow-primary/20' : 'bg-white dark:bg-slate-900 border-slate-200/60 dark:border-slate-800/80 text-slate-600 dark:text-slate-400 hover:border-slate-350' }}">
                    📖 {{ __('Currently Reading') }}
                </button>
                <button wire:click="switchReadingTab('want_to_read')" class="px-4 py-1.5 text-xs font-black rounded-xl border transition {{ $readingSubTab === 'want_to_read' ? 'bg-primary border-primary text-white shadow-sm shadow-primary/20' : 'bg-white dark:bg-slate-900 border-slate-200/60 dark:border-slate-800/80 text-slate-600 dark:text-slate-400 hover:border-slate-350' }}">
                    📌 {{ __('Wishlist (Want to Read)') }}
                </button>
                <button wire:click="switchReadingTab('completed')" class="px-4 py-1.5 text-xs font-black rounded-xl border transition {{ $readingSubTab === 'completed' ? 'bg-primary border-primary text-white shadow-sm shadow-primary/20' : 'bg-white dark:bg-slate-900 border-slate-200/60 dark:border-slate-800/80 text-slate-600 dark:text-slate-400 hover:border-slate-350' }}">
                    ✅ {{ __('Completed Books') }}
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($this->myInteractions as $interaction)
                    <div wire:key="interaction-{{ $interaction->id }}" class="group bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/50 dark:border-slate-800/80 p-5 flex gap-4 transition-all duration-300 hover:shadow-xl hover:shadow-slate-200/30 dark:hover:shadow-none hover:border-slate-300 dark:hover:border-slate-700">
                        {{-- Cover Image --}}
                        <div class="w-24 h-32 shrink-0 rounded-2xl overflow-hidden bg-slate-100 dark:bg-slate-950 border border-slate-200/30 dark:border-slate-850">
                            @if($interaction->book->cover_url)
                                <img src="{{ $interaction->book->cover_url }}" class="w-full h-full object-cover" />
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-indigo-50/50 to-slate-100 dark:from-slate-800 dark:to-slate-900">
                                    <x-icon name="o-book-open" class="w-8 h-8 text-slate-300 dark:text-slate-700" />
                                </div>
                            @endif
                        </div>

                        {{-- Details --}}
                        <div class="flex-grow flex flex-col justify-between">
                            <div>
                                <h3 class="font-black text-slate-800 dark:text-slate-100 line-clamp-1 leading-snug group-hover:text-primary transition-colors">{{ $interaction->book->title }}</h3>
                                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-2">{{ $interaction->book->author?->name ?? __('Unknown Author') }}</p>

                                @if($interaction->reading_status === 'reading')
                                    <div class="mt-2.5">
                                        <div class="flex items-center justify-between text-[11px] font-bold text-slate-500 mb-1">
                                            <span>{{ __('Progress') }}</span>
                                            <span>{{ $interaction->pages_read }} / {{ $interaction->book->pages_count ?? '?' }} {{ __('pages') }}</span>
                                        </div>
                                        <div class="w-full h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                                            @php
                                                $percent = $interaction->book->pages_count ? min(100, round(($interaction->pages_read / $interaction->book->pages_count) * 100)) : 0;
                                            @endphp
                                            <div class="h-full bg-primary transition-all duration-300" style="width: {{ $percent }}%"></div>
                                        </div>
                                    </div>
                                @elseif($interaction->reading_status === 'completed')
                                    <div class="flex items-center gap-1 mt-2.5">
                                        @if($interaction->rating)
                                            <div class="flex gap-0.5">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <x-icon name="o-star" class="w-3.5 h-3.5 {{ $i <= $interaction->rating ? 'text-amber-500 fill-amber-500' : 'text-slate-300 dark:text-slate-700' }}" />
                                                @endfor
                                            </div>
                                        @else
                                            <span class="text-[10px] font-bold text-slate-400 uppercase">{{ __('Not Rated Yet') }}</span>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            {{-- Actions --}}
                            <div class="flex items-center justify-between mt-3 pt-2.5 border-t border-slate-100 dark:border-slate-850">
                                <button wire:click="openProgressModal({{ $interaction->id }})" class="btn btn-ghost btn-xs text-xs font-bold text-primary hover:bg-primary/5 rounded-lg px-2">
                                    <x-icon name="o-pencil" class="w-3 h-3" /> {{ __('Update Log') }}
                                </button>

                                <button wire:click="removeInteraction({{ $interaction->id }})" wire:confirm="{{ __('Remove this book from your reading shelf?') }}" class="btn btn-ghost btn-circle btn-xs text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/20">
                                    <x-icon name="o-x-mark" class="w-3.5 h-3.5" />
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-20 bg-white dark:bg-slate-900 rounded-3xl border border-dashed border-slate-200 dark:border-slate-800 p-8 shadow-sm">
                        <x-icon name="o-bookmark" class="w-16 h-16 text-slate-300 dark:text-slate-700 mx-auto mb-4" />
                        <h3 class="text-xl font-black text-slate-800 dark:text-slate-200 mb-2">{{ __('No books in this list') }}</h3>
                        <p class="text-sm text-slate-450 dark:text-slate-500 max-w-sm mx-auto mb-6">{{ __('Search our catalog and add books to keep track of your reading wishlist and log pages completed.') }}</p>
                        <button wire:click="openAddReadingModal" class="btn btn-primary btn-sm rounded-xl px-5 font-bold shadow-lg shadow-primary/25">
                            <x-icon name="o-plus" class="w-4 h-4" /> {{ __('Track a Book') }}
                        </button>
                    </div>
                @endforelse
            </div>
        @endif

        {{-- ==================== TAB: REQUESTS (LEND & BORROW) ==================== --}}
        @if($activeTab === 'requests')
            {{-- Requests Sub Tabs --}}
            <div class="flex items-center gap-1.5 mb-6">
                <button wire:click="switchRequestsTab('incoming')" class="px-4 py-1.5 text-xs font-black rounded-xl border transition {{ $requestsSubTab === 'incoming' ? 'bg-primary border-primary text-white shadow-sm' : 'bg-white dark:bg-slate-900 border-slate-200/60 dark:border-slate-800/80 text-slate-600 dark:text-slate-400 hover:border-slate-350' }}">
                    📥 {{ __('Incoming Requests (Lend Others)') }}
                </button>
                <button wire:click="switchRequestsTab('outgoing')" class="px-4 py-1.5 text-xs font-black rounded-xl border transition {{ $requestsSubTab === 'outgoing' ? 'bg-primary border-primary text-white shadow-sm' : 'bg-white dark:bg-slate-900 border-slate-200/60 dark:border-slate-800/80 text-slate-600 dark:text-slate-400 hover:border-slate-350' }}">
                    📤 {{ __('Outgoing Requests (Borrow Books)') }}
                </button>
            </div>

            @if($requestsSubTab === 'incoming')
                {{-- Incoming Requests (Lending console) --}}
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/50 dark:border-slate-800/80 overflow-hidden shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="bg-slate-50 dark:bg-slate-950 border-b border-slate-200 dark:border-slate-800/80 text-slate-500 text-xs font-black uppercase tracking-wider">
                                    <th class="py-4 px-6">{{ __('Book Copy Details') }}</th>
                                    <th class="py-4 px-6">{{ __('Requester') }}</th>
                                    <th class="py-4 px-6">{{ __('Days Requested') }}</th>
                                    <th class="py-4 px-6">{{ __('Status') }}</th>
                                    <th class="py-4 px-6 text-right">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-850">
                                @forelse($this->incomingRequests as $req)
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-950/20 transition-colors" wire:key="incoming-{{ $req->id }}">
                                        <td class="py-4 px-6">
                                            <div class="flex items-center gap-3">
                                                <div class="w-9 h-12 bg-slate-150 dark:bg-slate-800 rounded-lg overflow-hidden shrink-0 border border-slate-200/40 dark:border-slate-800">
                                                    @if($req->bookCopy->book->cover_url)
                                                        <img src="{{ $req->bookCopy->book->cover_url }}" class="w-full h-full object-cover" />
                                                    @else
                                                        <div class="w-full h-full flex items-center justify-center bg-slate-200 dark:bg-slate-850"><x-icon name="o-book-open" class="w-4 h-4 text-slate-400" /></div>
                                                    @endif
                                                </div>
                                                <div>
                                                    <span class="font-bold text-slate-800 dark:text-slate-200 block max-w-[220px] truncate">{{ $req->bookCopy->book->title }}</span>
                                                    <span class="text-xs text-slate-500">{{ __('Condition:') }} <span class="font-semibold">{{ $req->bookCopy->condition }}</span></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-4 px-6">
                                            <div class="flex items-center gap-2">
                                                <div class="w-7 h-7 rounded-xl bg-slate-100 dark:bg-slate-850 ring-2 ring-primary/10 overflow-hidden flex items-center justify-center text-[10px] font-black text-primary">
                                                    @if($req->borrower->avatar_url)
                                                        <img src="{{ $req->borrower->avatar_url }}" class="w-full h-full object-cover" />
                                                    @else
                                                        {{ substr($req->borrower->name ?? 'U', 0, 1) }}
                                                    @endif
                                                </div>
                                                <span class="font-semibold text-slate-700 dark:text-slate-350">{{ $req->borrower->name }}</span>
                                            </div>
                                        </td>
                                        <td class="py-4 px-6 font-semibold text-slate-600 dark:text-slate-400">{{ $req->requested_days }} {{ __('days') }}</td>
                                        <td class="py-4 px-6">
                                            @if($req->status === 'pending')
                                                <span class="px-2 py-0.5 rounded-lg text-xs font-black uppercase bg-blue-50 text-blue-600 dark:bg-blue-950/20 dark:text-blue-400">{{ __('Pending') }}</span>
                                            @elseif($req->status === 'accepted')
                                                <span class="px-2 py-0.5 rounded-lg text-xs font-black uppercase bg-amber-50 text-amber-600 dark:bg-amber-950/20 dark:text-amber-400">{{ __('Accepted') }}</span>
                                            @elseif($req->status === 'rejected')
                                                <span class="px-2 py-0.5 rounded-lg text-xs font-black uppercase bg-rose-50 text-rose-600 dark:bg-rose-950/20 dark:text-rose-400">{{ __('Rejected') }}</span>
                                            @elseif($req->status === 'given')
                                                <span class="px-2 py-0.5 rounded-lg text-xs font-black uppercase bg-violet-50 text-violet-600 dark:bg-violet-950/20 dark:text-violet-400">{{ __('Handed Over') }}</span>
                                            @elseif($req->status === 'active')
                                                <span class="px-2 py-0.5 rounded-lg text-xs font-black uppercase bg-emerald-50 text-emerald-600 dark:bg-emerald-950/20 dark:text-emerald-400">{{ __('On Loan') }}</span>
                                            @elseif($req->status === 'returned')
                                                <span class="px-2 py-0.5 rounded-lg text-xs font-black uppercase bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">{{ __('Returned') }}</span>
                                            @endif
                                        </td>
                                        <td class="py-4 px-6 text-right">
                                            <div class="flex items-center justify-end gap-1.5">
                                                @if($req->status === 'pending')
                                                    <button wire:click="acceptRequest({{ $req->id }})" class="btn btn-emerald btn-xs rounded-lg font-bold">{{ __('Accept') }}</button>
                                                    <button wire:click="rejectRequest({{ $req->id }})" class="btn btn-ghost btn-xs text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/20 rounded-lg font-bold">{{ __('Reject') }}</button>
                                                @elseif($req->status === 'accepted')
                                                    <button wire:click="markGiven({{ $req->id }})" class="btn btn-primary btn-xs rounded-lg font-bold">{{ __('Hand Over') }}</button>
                                                @elseif($req->status === 'active' || $req->status === 'given')
                                                    @if($req->status === 'active')
                                                        <button wire:click="sendReminder({{ $req->id }})" class="btn btn-warning btn-xs rounded-lg font-bold">{{ __('Remind') }}</button>
                                                    @endif
                                                    <button wire:click="confirmReturned({{ $req->id }})" wire:confirm="{{ __('Confirm that you have physically received this book back?') }}" class="btn btn-emerald btn-xs rounded-lg font-bold">{{ __('Mark Returned') }}</button>
                                                @else
                                                    <span class="text-xs text-slate-400 font-bold">-</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-16 text-slate-450 dark:text-slate-500 font-bold">{{ __('No incoming lend requests found.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            @if($requestsSubTab === 'outgoing')
                {{-- Outgoing Requests (Borrow console) --}}
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/50 dark:border-slate-800/80 overflow-hidden shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="bg-slate-50 dark:bg-slate-950 border-b border-slate-200 dark:border-slate-800/80 text-slate-500 text-xs font-black uppercase tracking-wider">
                                    <th class="py-4 px-6">{{ __('Book Details') }}</th>
                                    <th class="py-4 px-6">{{ __('Owner') }}</th>
                                    <th class="py-4 px-6">{{ __('Due Date / Days') }}</th>
                                    <th class="py-4 px-6">{{ __('Status') }}</th>
                                    <th class="py-4 px-6 text-right">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-850">
                                @forelse($this->outgoingRequests as $req)
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-950/20 transition-colors" wire:key="outgoing-{{ $req->id }}">
                                        <td class="py-4 px-6">
                                            <div class="flex items-center gap-3">
                                                <div class="w-9 h-12 bg-slate-150 dark:bg-slate-800 rounded-lg overflow-hidden shrink-0 border border-slate-200/40 dark:border-slate-800">
                                                    @if($req->bookCopy->book->cover_url)
                                                        <img src="{{ $req->bookCopy->book->cover_url }}" class="w-full h-full object-cover" />
                                                    @else
                                                        <div class="w-full h-full flex items-center justify-center bg-slate-200 dark:bg-slate-850"><x-icon name="o-book-open" class="w-4 h-4 text-slate-400" /></div>
                                                    @endif
                                                </div>
                                                <span class="font-bold text-slate-800 dark:text-slate-200 block max-w-[220px] truncate">{{ $req->bookCopy->book->title }}</span>
                                            </div>
                                        </td>
                                        <td class="py-4 px-6">
                                            <div class="flex items-center gap-2">
                                                <div class="w-7 h-7 rounded-xl bg-slate-100 dark:bg-slate-850 ring-2 ring-primary/10 overflow-hidden flex items-center justify-center text-[10px] font-black text-primary">
                                                    @if($req->bookCopy->owner)
                                                        @if($req->bookCopy->owner->avatar_url)
                                                            <img src="{{ $req->bookCopy->owner->avatar_url }}" class="w-full h-full object-cover" />
                                                        @else
                                                            {{ substr($req->bookCopy->owner->name, 0, 1) }}
                                                        @endif
                                                    @elseif($req->bookCopy->libraryHub)
                                                        <x-icon name="o-building-library" class="w-4 h-4" />
                                                    @endif
                                                </div>
                                                <span class="font-semibold text-slate-700 dark:text-slate-350">
                                                    {{ $req->bookCopy->owner->name ?? $req->bookCopy->libraryHub?->name ?? __('Unknown') }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="py-4 px-6 font-semibold text-slate-650 dark:text-slate-400">
                                            @if($req->status === 'active' && $req->due_date)
                                                <span class="text-emerald-600 dark:text-emerald-400">{{ \Carbon\Carbon::parse($req->due_date)->format('M d, Y') }}</span>
                                            @else
                                                {{ $req->requested_days }} {{ __('days') }}
                                            @endif
                                        </td>
                                        <td class="py-4 px-6">
                                            @if($req->status === 'pending')
                                                <span class="px-2 py-0.5 rounded-lg text-xs font-black uppercase bg-blue-50 text-blue-600 dark:bg-blue-950/20 dark:text-blue-400">{{ __('Waiting for Owner') }}</span>
                                            @elseif($req->status === 'accepted')
                                                <span class="px-2 py-0.5 rounded-lg text-xs font-black uppercase bg-amber-50 text-amber-600 dark:bg-amber-950/20 dark:text-amber-400">{{ __('Ready to Collect') }}</span>
                                            @elseif($req->status === 'rejected')
                                                <span class="px-2 py-0.5 rounded-lg text-xs font-black uppercase bg-rose-50 text-rose-600 dark:bg-rose-950/20 dark:text-rose-400">{{ __('Rejected') }}</span>
                                            @elseif($req->status === 'given')
                                                <span class="px-2 py-0.5 rounded-lg text-xs font-black uppercase bg-violet-50 text-violet-600 dark:bg-violet-950/20 dark:text-violet-400">{{ __('Owner Handed Over') }}</span>
                                            @elseif($req->status === 'active')
                                                <span class="px-2 py-0.5 rounded-lg text-xs font-black uppercase bg-emerald-50 text-emerald-600 dark:bg-emerald-950/20 dark:text-emerald-400">{{ __('Reading Now') }}</span>
                                            @elseif($req->status === 'returned')
                                                <span class="px-2 py-0.5 rounded-lg text-xs font-black uppercase bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">{{ __('Returned') }}</span>
                                            @endif
                                        </td>
                                        <td class="py-4 px-6 text-right">
                                            <div class="flex items-center justify-end gap-1.5">
                                                @if($req->status === 'given')
                                                    <button wire:click="confirmReceived({{ $req->id }})" class="btn btn-emerald btn-xs rounded-lg font-bold">{{ __('Confirm Received') }}</button>
                                                @elseif($req->status === 'pending' || $req->status === 'accepted')
                                                    <button wire:click="cancelRequest({{ $req->id }})" wire:confirm="{{ __('Cancel your borrow request?') }}" class="btn btn-ghost btn-xs text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/20 rounded-lg font-bold">{{ __('Cancel') }}</button>
                                                @else
                                                    <span class="text-xs text-slate-400 font-bold">-</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-16 text-slate-450 dark:text-slate-500 font-bold">{{ __('No borrow requests sent.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        @endif

    </div>

    {{-- ==================== MODAL: ADD PHYSICAL BOOK ==================== --}}
    <x-modal wire:model="addModal" title="{{ __('Add Book to Your Physical Shelf') }}" class="backdrop-blur-sm" box-class="rounded-3xl border border-slate-200/60 dark:border-slate-800/80 bg-white dark:bg-slate-900 shadow-2xl p-6">
        <div class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">{{ __('Search Global Catalog') }}</label>
                <div class="relative">
                    <x-icon name="o-magnifying-glass" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-450" />
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="{{ __('Type book title...') }}"
                        class="w-full pl-10 pr-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl focus:ring-2 focus:ring-primary/45 focus:border-primary/45 focus:outline-none transition text-slate-700 dark:text-slate-350" />
                </div>
            </div>

            {{-- Catalog Results list --}}
            <div class="max-h-60 overflow-y-auto rounded-2xl border border-slate-200/50 dark:border-slate-800/80 bg-slate-50/50 dark:bg-slate-950/30 p-2 space-y-1">
                @forelse($this->availableBooks as $b)
                    <button type="button" wire:click="selectBook({{ $b->id }})" class="w-full text-left p-2.5 rounded-xl flex items-center gap-3 transition-colors {{ $selectedBookId === $b->id ? 'bg-primary text-white shadow-sm' : 'hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300' }}">
                        @if($b->cover_url)
                            <img src="{{ $b->cover_url }}" class="w-8 h-12 object-cover rounded-lg shadow-sm" />
                        @else
                            <div class="w-8 h-12 bg-white dark:bg-slate-900 flex items-center justify-center rounded-lg shadow-sm"><x-icon name="o-book-open" class="w-4 h-4 opacity-50" /></div>
                        @endif
                        <div>
                            <div class="font-bold text-sm leading-tight">{{ $b->title }}</div>
                            <div class="text-xs opacity-70">{{ $b->author?->name ?? __('Unknown Author') }}</div>
                        </div>
                    </button>
                @empty
                    <div class="p-4 text-center text-xs text-slate-400 font-bold uppercase tracking-wider">
                        @if($search)
                            {{ __('No match found in our global catalog.') }}
                        @else
                            {{ __('Type to search approved books...') }}
                        @endif
                    </div>
                @endforelse
            </div>

            @if(!$isCustom)
                <div class="text-center mt-2">
                    <button type="button" wire:click="toggleCustom" class="text-xs font-black text-primary hover:underline uppercase tracking-wider">
                        {{ __("Can't find your book? Create a new one") }}
                    </button>
                </div>
            @endif

            {{-- Custom Book Form --}}
            @if($isCustom)
                <div class="bg-slate-50/50 dark:bg-slate-950/40 p-5 rounded-2xl border border-slate-200/50 dark:border-slate-800/80 space-y-4 mt-4">
                    <h3 class="font-black text-slate-800 dark:text-slate-200 text-xs uppercase tracking-wider border-b border-slate-100 dark:border-slate-900 pb-2">{{ __('Create Custom Catalog Entry') }}</h3>
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-450 dark:text-slate-400 mb-1.5">{{ __('Book Title *') }}</label>
                        <input type="text" wire:model="customTitle" class="w-full px-3 py-2 text-sm bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl focus:ring-2 focus:ring-primary/45 focus:border-primary/45 focus:outline-none transition text-slate-700 dark:text-slate-350" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-450 dark:text-slate-400 mb-1.5">{{ __('Author Name') }}</label>
                        <input type="text" wire:model="customAuthor" class="w-full px-3 py-2 text-sm bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl focus:ring-2 focus:ring-primary/45 focus:border-primary/45 focus:outline-none transition text-slate-700 dark:text-slate-350" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-450 dark:text-slate-400 mb-1.5">{{ __('Category') }}</label>
                            <select wire:model="customCategoryId" class="select select-sm select-bordered w-full rounded-xl text-xs bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300">
                                <option value="">{{ __('Select Category') }}</option>
                                @foreach($this->categories as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-450 dark:text-slate-400 mb-1.5">{{ __('Total Pages') }}</label>
                            <input type="number" wire:model="customPagesCount" class="w-full px-3 py-2 text-sm bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl focus:ring-2 focus:ring-primary/45 focus:border-primary/45 focus:outline-none transition text-slate-700 dark:text-slate-350" />
                        </div>
                    </div>

                    {{-- Cover Upload --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-455 dark:text-slate-400 mb-1.5">{{ __('Book Cover Image') }}</label>
                        <x-file wire:model="customCoverFile" accept="image/*" class="w-full" crop-after-change>
                            <div x-data="{ hasImage: false }" class="flex flex-col items-center justify-center p-4 border border-dashed border-slate-200 dark:border-slate-800 rounded-2xl hover:bg-slate-100 dark:hover:bg-slate-850 transition cursor-pointer w-full">
                                <img src="{{ $this->getCoverPreviewUrl() ?? 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7' }}" 
                                     class="w-16 h-24 object-cover rounded-lg shadow-sm"
                                     @load="hasImage = !$event.target.src.includes('data:image/gif')"
                                     x-show="hasImage" />
                                
                                <div x-show="!hasImage" class="flex flex-col items-center justify-center">
                                    <x-icon name="o-arrow-up-tray" class="w-6 h-6 text-slate-400 mb-1.5" />
                                    <span class="text-xs text-slate-450 font-semibold">{{ __('Click to upload cover image') }}</span>
                                </div>
                            </div>
                        </x-file>
                    </div>
                </div>
            @endif

            {{-- Condition & Lending status details --}}
            @if($selectedBookId || $isCustom)
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4 bg-slate-50/50 dark:bg-slate-950/20 p-4 rounded-2xl border border-slate-200/50 dark:border-slate-800/80">
                    <div>
                        <label class="block text-xs font-bold text-slate-450 dark:text-slate-400 mb-1.5">{{ __('Physical Condition *') }}</label>
                        <select wire:model="condition" class="select select-sm select-bordered w-full rounded-xl text-xs bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300">
                            <option value="New">{{ __('New') }}</option>
                            <option value="Good">{{ __('Good') }}</option>
                            <option value="Fair">{{ __('Fair') }}</option>
                            <option value="Worn">{{ __('Worn') }}</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-450 dark:text-slate-400 mb-1.5">{{ __('Gift to a Community Hub?') }}</label>
                        <select wire:model="giftToHubId" class="select select-sm select-bordered w-full rounded-xl text-xs bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300">
                            <option value="">{{ __('No, keep on my shelf') }}</option>
                            @foreach($this->activeHubs as $hub)
                                <option value="{{ $hub->id }}">{{ __('Gift to: ') . $hub->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    @if(!$giftToHubId)
                        <div class="sm:col-span-2 mt-2">
                            <label class="block text-xs font-bold text-slate-450 dark:text-slate-400 mb-1.5">{{ __('Lending Status *') }}</label>
                            <select wire:model="is_borrowable" class="select select-sm select-bordered w-full rounded-xl text-xs bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300">
                                <option value="1">{{ __('Available to Lend (Others can borrow from you)') }}</option>
                                <option value="0">{{ __('Keep Private (Only you can see it)') }}</option>
                            </select>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <x-slot:actions>
            <button type="button" wire:click="$set('addModal', false)" class="btn btn-ghost btn-sm rounded-xl font-bold">{{ __('Cancel') }}</button>
            <button type="button" wire:click="addCopy" class="btn btn-primary btn-sm rounded-xl font-bold shadow-lg shadow-primary/25" spinner="addCopy" @disabled(!$selectedBookId && !$isCustom)>
                {{ __('Add to Shelf') }}
            </button>
        </x-slot:actions>
    </x-modal>

    {{-- ==================== MODAL: ADD READING TRACKING ==================== --}}
    <x-modal wire:model="addReadingModal" title="{{ __('Track a Book on your Reading Shelf') }}" class="backdrop-blur-sm" box-class="rounded-3xl border border-slate-200/60 dark:border-slate-800/80 bg-white dark:bg-slate-900 shadow-2xl p-6">
        <div class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">{{ __('Search Global Catalog') }}</label>
                <div class="relative">
                    <x-icon name="o-magnifying-glass" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-450" />
                    <input type="text" wire:model.live.debounce.300ms="searchCatalog" placeholder="{{ __('Type book title...') }}"
                        class="w-full pl-10 pr-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl focus:ring-2 focus:ring-primary/45 focus:border-primary/45 focus:outline-none transition text-slate-700 dark:text-slate-350" />
                </div>
            </div>

            {{-- Catalog List Results --}}
            <div class="max-h-60 overflow-y-auto rounded-2xl border border-slate-200/50 dark:border-slate-800/80 bg-slate-50/50 dark:bg-slate-950/30 p-2 space-y-1">
                @forelse($this->availableBooks as $b)
                    <div class="w-full p-2 rounded-xl flex items-center justify-between gap-3 hover:bg-slate-100 dark:hover:bg-slate-850 text-slate-700 dark:text-slate-300">
                        <div class="flex items-center gap-3">
                            @if($b->cover_url)
                                <img src="{{ $b->cover_url }}" class="w-8 h-12 object-cover rounded-lg shadow-sm" />
                            @else
                                <div class="w-8 h-12 bg-white dark:bg-slate-900 flex items-center justify-center rounded-lg shadow-sm"><x-icon name="o-book-open" class="w-4 h-4 opacity-50" /></div>
                            @endif
                            <div>
                                <div class="font-bold text-sm leading-tight">{{ $b->title }}</div>
                                <div class="text-xs opacity-70">{{ $b->author?->name ?? __('Unknown Author') }}</div>
                            </div>
                        </div>

                        <div class="flex gap-1.5 shrink-0">
                            <button type="button" wire:click="addReadingStatus({{ $b->id }}, 'reading')" class="btn btn-ghost btn-xs font-black uppercase text-primary hover:bg-primary/5 rounded-lg px-2" tooltip="{{ __('Currently Reading') }}">📖 {{ __('Read') }}</button>
                            <button type="button" wire:click="addReadingStatus({{ $b->id }}, 'want_to_read')" class="btn btn-ghost btn-xs font-black uppercase text-amber-500 hover:bg-amber-500/5 rounded-lg px-2" tooltip="{{ __('Wishlist') }}">📌 {{ __('Wish') }}</button>
                            <button type="button" wire:click="addReadingStatus({{ $b->id }}, 'completed')" class="btn btn-ghost btn-xs font-black uppercase text-emerald-500 hover:bg-emerald-500/5 rounded-lg px-2" tooltip="{{ __('Completed') }}">✅ {{ __('Done') }}</button>
                        </div>
                    </div>
                @empty
                    <div class="p-4 text-center text-xs text-slate-400 font-bold uppercase tracking-wider">
                        @if($searchCatalog)
                            {{ __('No match found in catalog.') }}
                        @else
                            {{ __('Type to search approved books...') }}
                        @endif
                    </div>
                @endforelse
            </div>
        </div>

        <x-slot:actions>
            <button type="button" wire:click="$set('addReadingModal', false)" class="btn btn-ghost btn-sm rounded-xl font-bold">{{ __('Close') }}</button>
        </x-slot:actions>
    </x-modal>

    {{-- ==================== MODAL: UPDATE READING PROGRESS / REVIEW ==================== --}}
    <x-modal wire:model="progressModal" title="{{ __('Update Reading Progress') }}" class="backdrop-blur-sm" box-class="rounded-3xl border border-slate-200/60 dark:border-slate-800/80 bg-white dark:bg-slate-900 shadow-2xl p-6">
        <div class="space-y-4">
            @if($selectedInteractionId)
                @php
                    $activeInteraction = BookUserInteraction::with('book')->find($selectedInteractionId);
                @endphp
                @if($activeInteraction)
                    <div class="bg-slate-50/50 dark:bg-slate-950/20 p-4 rounded-2xl border border-slate-200/50 dark:border-slate-800/80 flex gap-3">
                        <div class="w-12 h-16 bg-white dark:bg-slate-900 rounded-lg overflow-hidden shrink-0 border border-slate-200/40 dark:border-slate-800">
                            @if($activeInteraction->book->cover_url)
                                <img src="{{ $activeInteraction->book->cover_url }}" class="w-full h-full object-cover" />
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-slate-200 dark:bg-slate-850"><x-icon name="o-book-open" class="w-5 h-5 text-slate-450" /></div>
                            @endif
                        </div>
                        <div>
                            <span class="font-bold text-slate-800 dark:text-slate-100 block leading-snug">{{ $activeInteraction->book->title }}</span>
                            <span class="text-xs text-slate-500">{{ __('Total pages:') }} <span class="font-semibold">{{ $activeInteraction->book->pages_count ?? __('Unspecified') }}</span></span>
                        </div>
                    </div>
                @endif
            @endif

            {{-- Progress Pages Read --}}
            @if($readingSubTab === 'reading')
                <div>
                    <label class="block text-xs font-bold text-slate-450 dark:text-slate-400 mb-1.5">{{ __('Pages Read') }}</label>
                    <input type="number" wire:model="progressPagesRead" class="w-full px-3 py-2 text-sm bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl focus:ring-2 focus:ring-primary/45 focus:border-primary/45 focus:outline-none transition text-slate-700 dark:text-slate-350" />
                </div>
            @endif

            {{-- Rating stars --}}
            <div>
                <label class="block text-xs font-bold text-slate-450 dark:text-slate-400 mb-2">{{ __('Your Rating') }}</label>
                <div class="flex items-center gap-2">
                    @for($i = 1; $i <= 5; $i++)
                        <button type="button" wire:click="$set('progressRating', {{ $i }})" class="focus:outline-none transition-transform active:scale-125">
                            <x-icon name="o-star" class="w-8 h-8 {{ $progressRating >= $i ? 'text-amber-500 fill-amber-500' : 'text-slate-300 dark:text-slate-750 hover:text-amber-400' }}" />
                        </button>
                    @endfor
                    @if($progressRating)
                        <button type="button" wire:click="$set('progressRating', null)" class="text-xs text-slate-400 hover:text-rose-500 font-bold ml-2">{{ __('Clear') }}</button>
                    @endif
                </div>
            </div>

            {{-- Review --}}
            <div>
                <label class="block text-xs font-bold text-slate-450 dark:text-slate-400 mb-1.5">{{ __('Your Review / Personal Note') }}</label>
                <textarea wire:model="progressReview" rows="4" placeholder="{{ __('Write down your thoughts, takeaways, or short review...') }}"
                    class="w-full px-3 py-2 text-sm bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl focus:ring-2 focus:ring-primary/45 focus:border-primary/45 focus:outline-none transition text-slate-700 dark:text-slate-350 resize-none"></textarea>
            </div>
        </div>

        <x-slot:actions>
            <button type="button" wire:click="$set('progressModal', false)" class="btn btn-ghost btn-sm rounded-xl font-bold">{{ __('Cancel') }}</button>
            <button type="button" wire:click="saveProgress" class="btn btn-primary btn-sm rounded-xl font-bold shadow-lg shadow-primary/25">
                {{ __('Save Changes') }}
            </button>
        </x-slot:actions>
    </x-modal>

</div>