<div>
    <div class="bg-slate-50 dark:bg-slate-950 border-b border-slate-200 dark:border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="flex flex-col md:flex-row items-center md:items-end gap-6">
                <div class="w-32 h-32 md:w-40 md:h-40 rounded-3xl overflow-hidden border-4 border-white dark:border-slate-900 shadow-xl shrink-0 bg-cyan-100 dark:bg-cyan-900/30 text-cyan-500 relative z-10 flex items-center justify-center">
                    <x-icon name="o-building-library" class="w-20 h-20" />
                </div>
                
                <div class="flex-1 text-center md:text-left pt-2 pb-4">
                    <h1 class="text-3xl font-black text-slate-900 dark:text-white">{{ $hub->name }}</h1>
                    <p class="text-slate-500 mt-1 flex items-center justify-center md:justify-start gap-2">
                        <x-icon name="o-map-pin" class="w-4 h-4" /> {{ $hub->location ?? __('Location TBA') }}
                    </p>
                    <p class="text-slate-400 mt-1 text-xs flex items-center justify-center md:justify-start gap-2">
                        <x-icon name="o-user" class="w-3 h-3" /> {{ __('Manager:') }} {{ $hub->manager?->name ?? __('None') }}
                    </p>
                    
                    <div class="flex flex-wrap items-center justify-center md:justify-start gap-4 mt-6">
                        <div class="text-center md:text-left">
                            <span class="block text-2xl font-black text-cyan-600 dark:text-cyan-400 leading-none">{{ $this->stats['total_books'] }}</span>
                            <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">{{ __('Total Inventory') }}</span>
                        </div>
                        <div class="w-px h-8 bg-slate-200 dark:bg-slate-800 hidden md:block"></div>
                        <div class="text-center md:text-left">
                            <span class="block text-2xl font-black text-emerald-600 dark:text-emerald-400 leading-none">{{ $this->stats['available_books'] }}</span>
                            <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">{{ __('Available Now') }}</span>
                        </div>
                        <div class="w-px h-8 bg-slate-200 dark:bg-slate-800 hidden md:block"></div>
                        <div class="text-center md:text-left">
                            <span class="block text-2xl font-black text-purple-600 dark:text-purple-400 leading-none">{{ $this->stats['total_borrowed'] }}</span>
                            <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">{{ __('Times Borrowed') }}</span>
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
        {{-- Navigation Header --}}
        <div class="flex items-center gap-2 mb-8 border-b border-slate-200 dark:border-slate-800">
            <div class="px-6 py-3 font-bold text-sm border-b-2 border-cyan-500 text-cyan-600 dark:text-cyan-400">
                <x-icon name="o-archive-box" class="w-4 h-4 inline mr-1" /> {{ __('Hub Inventory') }}
            </div>
        </div>

        {{-- INVENTORY GRID --}}
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-6">
            @forelse($this->inventory as $copy)
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
                        <p class="text-xs text-slate-500 truncate mb-2">{{ $copy->book->author?->name }}</p>
                        
                        @if($copy->addedBy)
                            <p class="text-[10px] text-slate-400 truncate mb-3">
                                {{ __('Gifted by:') }} {{ $copy->addedBy->name }}
                            </p>
                        @endif
                        
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
                    <p>{{ __('This hub currently has no books in its inventory.') }}</p>
                </div>
            @endforelse
        </div>
    </div>
</div>