<div>
    <div class="bg-slate-50 dark:bg-slate-950 border-b border-slate-200 dark:border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="flex flex-col md:flex-row items-center md:items-end gap-6">
                <div class="w-32 h-32 md:w-40 md:h-40 rounded-full overflow-hidden border-4 border-white dark:border-slate-900 shadow-xl shrink-0 bg-slate-100 dark:bg-slate-800 relative z-10">
                    @if($user->avatar_url)
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-4xl font-black text-slate-400">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                    @endif
                </div>
                
                <div class="flex-1 text-center md:text-left pt-2 pb-4">
                    <h1 class="text-3xl font-black text-slate-900 dark:text-white">{{ $user->name }}</h1>
                    <p class="text-slate-500 mt-1 flex items-center justify-center md:justify-start gap-2">
                        <x-icon name="o-book-open" class="w-4 h-4" /> {{ __('Community Library Contributor') }}
                    </p>
                    
                    <div class="flex flex-wrap items-center justify-center md:justify-start gap-4 mt-6">
                        <div class="text-center md:text-left">
                            <span class="block text-2xl font-black text-cyan-600 dark:text-cyan-400 leading-none">{{ $this->stats['books_shared'] }}</span>
                            <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">{{ __('Shared') }}</span>
                        </div>
                        <div class="w-px h-8 bg-slate-200 dark:bg-slate-800 hidden md:block"></div>
                        <div class="text-center md:text-left">
                            <span class="block text-2xl font-black text-emerald-600 dark:text-emerald-400 leading-none">{{ $this->stats['books_gifted'] }}</span>
                            <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">{{ __('Gifted to Hubs') }}</span>
                        </div>
                        <div class="w-px h-8 bg-slate-200 dark:bg-slate-800 hidden md:block"></div>
                        <div class="text-center md:text-left">
                            <span class="block text-2xl font-black text-purple-600 dark:text-purple-400 leading-none">{{ $this->stats['books_read'] }}</span>
                            <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">{{ __('Read') }}</span>
                        </div>
                    </div>
                </div>
                
                <div class="shrink-0 flex items-center gap-3">
                    <a href="{{ route('web.library') }}" class="btn btn-outline btn-sm rounded-xl">
                        <x-icon name="o-arrow-left" class="w-4 h-4" /> {{ __('Back to Library') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Navigation Tabs --}}
        <div class="flex items-center gap-2 mb-8 overflow-x-auto pb-2 scrollbar-none border-b border-slate-200 dark:border-slate-800">
            <button wire:click="$set('activeTab', 'shelf')" class="px-6 py-3 font-bold text-sm border-b-2 transition-colors whitespace-nowrap {{ $activeTab === 'shelf' ? 'border-cyan-500 text-cyan-600 dark:text-cyan-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300' }}">
                <x-icon name="o-archive-box" class="w-4 h-4 inline mr-1" /> {{ __('Lending Shelf') }}
            </button>
            <button wire:click="$set('activeTab', 'reading')" class="px-6 py-3 font-bold text-sm border-b-2 transition-colors whitespace-nowrap {{ $activeTab === 'reading' ? 'border-cyan-500 text-cyan-600 dark:text-cyan-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300' }}">
                <x-icon name="o-bookmark" class="w-4 h-4 inline mr-1" /> {{ __('Reading Tracker') }}
            </button>
            <button wire:click="$set('activeTab', 'activity')" class="px-6 py-3 font-bold text-sm border-b-2 transition-colors whitespace-nowrap {{ $activeTab === 'activity' ? 'border-cyan-500 text-cyan-600 dark:text-cyan-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300' }}">
                <x-icon name="o-arrow-path-rounded-square" class="w-4 h-4 inline mr-1" /> {{ __('Lending & Gifting Activity') }}
            </button>
        </div>

        {{-- SHELF TAB --}}
        @if($activeTab === 'shelf')
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-6">
                @forelse($this->physicalShelf as $copy)
                    <div class="group relative bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/50 dark:border-slate-800/80 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col" wire:key="copy-{{ $copy->id }}">
                        <a href="{{ route('web.book', $copy->book->slug) }}" class="block aspect-[2/3] relative overflow-hidden bg-slate-100 dark:bg-slate-800">
                            @if($copy->book->cover_url)
                                <img src="{{ $copy->book->cover_url }}" alt="{{ $copy->book->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                            @else
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <x-icon name="o-book-open" class="w-12 h-12 text-slate-300 dark:text-slate-600" />
                                </div>
                            @endif
                            
                            {{-- Status Badge --}}
                            <div class="absolute top-2 right-2">
                                @if($copy->status === 'available')
                                    <span class="bg-emerald-500 text-white text-[9px] font-black uppercase tracking-wider px-2 py-1 rounded-lg shadow-sm">
                                        {{ __('Available') }}
                                    </span>
                                @else
                                    <span class="bg-amber-500 text-white text-[9px] font-black uppercase tracking-wider px-2 py-1 rounded-lg shadow-sm">
                                        {{ __('On Loan') }}
                                    </span>
                                @endif
                            </div>
                        </a>

                        <div class="p-3 flex-1 flex flex-col">
                            <a href="{{ route('web.book', $copy->book->slug) }}" class="font-bold text-sm text-slate-900 dark:text-white leading-tight mb-1 line-clamp-2 hover:text-cyan-600 transition-colors">
                                {{ $copy->book->title }}
                            </a>
                            <p class="text-xs text-slate-500 truncate mb-3">{{ $copy->book->author?->name }}</p>
                            
                            <div class="mt-auto pt-3 border-t border-slate-100 dark:border-slate-800">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase">{{ __('Condition') }}</span>
                                    <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ $copy->condition ?? 'Good' }}</span>
                                </div>
                                @if(in_array($copy->id, $this->myActiveRequests))
                                    <button disabled class="w-full py-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 font-bold text-xs cursor-not-allowed">
                                        <x-icon name="o-check-circle" class="w-4 h-4 inline" /> {{ __('Requested') }}
                                    </button>
                                @elseif($copy->status === 'available')
                                    <button wire:click="requestBorrow({{ $copy->id }})" class="w-full py-1.5 rounded-lg bg-cyan-50 dark:bg-cyan-900/20 text-cyan-700 dark:text-cyan-400 font-bold text-xs hover:bg-cyan-600 hover:text-white transition-colors" wire:loading.attr="disabled">
                                        <span wire:loading.remove wire:target="requestBorrow({{ $copy->id }})">{{ __('Borrow') }}</span>
                                        <span wire:loading wire:target="requestBorrow({{ $copy->id }})">{{ __('Requesting...') }}</span>
                                    </button>
                                @else
                                    <button disabled class="w-full py-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-400 font-bold text-xs cursor-not-allowed">
                                        {{ __('Currently Borrowed') }}
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-20 text-center text-slate-500">
                        <x-icon name="o-archive-box" class="w-16 h-16 mx-auto mb-4 opacity-20" />
                        <p>{{ __('This user is not currently sharing any physical books.') }}</p>
                    </div>
                @endforelse
            </div>
        @endif

        {{-- READING TRACKER TAB --}}
        @if($activeTab === 'reading')
            <div class="flex items-center gap-2 mb-6">
                <button wire:click="$set('readingSubTab', 'reading')" class="px-4 py-1.5 text-xs font-black rounded-xl border transition {{ $readingSubTab === 'reading' ? 'bg-purple-500 border-purple-500 text-white shadow-sm' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:border-slate-300' }}">
                    📖 {{ __('Currently Reading') }}
                </button>
                <button wire:click="$set('readingSubTab', 'completed')" class="px-4 py-1.5 text-xs font-black rounded-xl border transition {{ $readingSubTab === 'completed' ? 'bg-emerald-500 border-emerald-500 text-white shadow-sm' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:border-slate-300' }}">
                    ✅ {{ __('Completed') }}
                </button>
                <button wire:click="$set('readingSubTab', 'want_to_read')" class="px-4 py-1.5 text-xs font-black rounded-xl border transition {{ $readingSubTab === 'want_to_read' ? 'bg-amber-500 border-amber-500 text-white shadow-sm' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:border-slate-300' }}">
                    📌 {{ __('Wishlist') }}
                </button>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-6">
                @forelse($this->readingTracker as $interaction)
                    <a href="{{ route('web.book', $interaction->book->slug) }}" class="group relative block aspect-[2/3] rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 border border-slate-200/50 dark:border-slate-800/80 bg-slate-100 dark:bg-slate-900" wire:key="interaction-{{ $interaction->id }}">
                        @if($interaction->book->cover_url)
                            <img src="{{ $interaction->book->cover_url }}" alt="{{ $interaction->book->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                        @else
                            <div class="absolute inset-0 flex items-center justify-center">
                                <x-icon name="o-book-open" class="w-12 h-12 text-slate-300 dark:text-slate-600" />
                            </div>
                        @endif
                        
                        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent p-4 translate-y-2 group-hover:translate-y-0 transition-transform">
                            <h3 class="text-white font-bold text-sm leading-tight line-clamp-2">{{ $interaction->book->title }}</h3>
                            <p class="text-white/70 text-xs mt-1 truncate">{{ $interaction->book->author?->name }}</p>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full py-20 text-center text-slate-500">
                        <x-icon name="o-bookmark" class="w-16 h-16 mx-auto mb-4 opacity-20" />
                        <p>{{ __('No books found in this list.') }}</p>
                    </div>
                @endforelse
            </div>
        @endif

        {{-- ACTIVITY TAB --}}
        @if($activeTab === 'activity')
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                {{-- Gifted to Hubs --}}
                <div>
                    <h3 class="text-lg font-black text-slate-800 dark:text-slate-200 mb-4 flex items-center gap-2">
                        <x-icon name="o-building-library" class="w-5 h-5 text-emerald-500" /> {{ __('Gifted to Community Hubs') }}
                    </h3>
                    <div class="space-y-4">
                        @forelse($this->giftedToHubs as $copy)
                            <div class="flex items-center gap-4 bg-white dark:bg-slate-900 border border-slate-200/50 dark:border-slate-800/80 p-3 rounded-2xl shadow-sm" wire:key="gift-{{ $copy->id }}">
                                @if($copy->book->cover_url)
                                    <img src="{{ $copy->book->cover_url }}" class="w-12 h-16 object-cover rounded-xl shadow-sm shrink-0" />
                                @else
                                    <div class="w-12 h-16 bg-slate-100 dark:bg-slate-800 rounded-xl flex items-center justify-center shrink-0">
                                        <x-icon name="o-book-open" class="w-5 h-5 text-slate-300 dark:text-slate-600" />
                                    </div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <a href="{{ route('web.book', $copy->book->slug) }}" class="font-bold text-sm text-slate-900 dark:text-white truncate hover:text-cyan-600">{{ $copy->book->title }}</a>
                                    <p class="text-xs text-slate-500 mb-1">
                                        {{ __('Gifted to:') }} <span class="font-semibold">{{ $copy->libraryHub?->name ?? 'Unknown Hub' }}</span>
                                    </p>
                                    <span class="badge badge-sm badge-success text-[10px] uppercase font-bold">{{ __('Community Asset') }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="p-8 text-center border border-dashed border-slate-200 dark:border-slate-800 rounded-2xl text-slate-500">
                                {{ __('No books gifted to hubs yet.') }}
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Borrowing History --}}
                <div>
                    <h3 class="text-lg font-black text-slate-800 dark:text-slate-200 mb-4 flex items-center gap-2">
                        <x-icon name="o-arrow-path-rounded-square" class="w-5 h-5 text-purple-500" /> {{ __('Borrowing Activity') }}
                    </h3>
                    <div class="space-y-4">
                        @forelse($this->borrowedBooks as $req)
                            <div class="flex items-center gap-4 bg-white dark:bg-slate-900 border border-slate-200/50 dark:border-slate-800/80 p-3 rounded-2xl shadow-sm" wire:key="borrow-{{ $req->id }}">
                                @if($req->bookCopy->book->cover_url)
                                    <img src="{{ $req->bookCopy->book->cover_url }}" class="w-12 h-16 object-cover rounded-xl shadow-sm shrink-0" />
                                @else
                                    <div class="w-12 h-16 bg-slate-100 dark:bg-slate-800 rounded-xl flex items-center justify-center shrink-0">
                                        <x-icon name="o-book-open" class="w-5 h-5 text-slate-300 dark:text-slate-600" />
                                    </div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <a href="{{ route('web.book', $req->bookCopy->book->slug) }}" class="font-bold text-sm text-slate-900 dark:text-white truncate hover:text-cyan-600">{{ $req->bookCopy->book->title }}</a>
                                    <p class="text-xs text-slate-500 mb-1">
                                        {{ __('From:') }} <span class="font-semibold">{{ $req->bookCopy->owner->name ?? $req->bookCopy->libraryHub?->name ?? 'Unknown' }}</span>
                                    </p>
                                    <span class="badge badge-sm text-[10px] uppercase font-bold
                                        {{ in_array($req->status, ['active','given']) ? 'badge-warning' : 'badge-ghost' }}">
                                        {{ $req->status === 'active' ? __('Currently Borrowing') : $req->status }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="p-8 text-center border border-dashed border-slate-200 dark:border-slate-800 rounded-2xl text-slate-500">
                                {{ __('No borrowing history yet.') }}
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>
        @endif
    </div>
</div>