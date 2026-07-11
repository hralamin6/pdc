<div class="min-h-screen bg-slate-50 dark:bg-slate-950">

    {{-- ══════════════════════════ HERO ══════════════════════════ --}}
    <section class="relative bg-slate-900 pt-20 pb-14 overflow-hidden">
        <div class="absolute inset-0 opacity-10" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E'); background-size:30px"></div>
        <div class="absolute -top-20 -left-20 w-96 h-96 bg-cyan-500/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-10 -right-20 w-80 h-80 bg-fuchsia-500/10 rounded-full blur-3xl"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-white/10 rounded-full text-xs font-black uppercase tracking-widest text-cyan-400 mb-5 border border-white/10">
                <x-icon name="o-book-open" class="w-4 h-4" /> {{ __('PSTU Dawah Library') }}
            </div>
            <h1 class="text-4xl md:text-6xl font-black text-white tracking-tight mb-5">
                {{ __('Islamic Knowledge') }}<br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-fuchsia-400">{{ __('at Your Fingertips') }}</span>
            </h1>

            {{-- Stats --}}
            <div class="flex flex-wrap justify-center gap-6 mb-8 text-sm">
                <div class="flex items-center gap-2 text-slate-300">
                    <x-icon name="o-book-open" class="w-4 h-4 text-cyan-400" />
                    <span class="font-bold text-white">{{ $this->stats['total'] }}</span> {{ __('Books') }}
                </div>
                <div class="flex items-center gap-2 text-slate-300">
                    <x-icon name="o-device-phone-mobile" class="w-4 h-4 text-fuchsia-400" />
                    <span class="font-bold text-white">{{ $this->stats['ebooks'] }}</span> {{ __('eBooks') }}
                </div>
                <div class="flex items-center gap-2 text-slate-300">
                    <x-icon name="o-building-library" class="w-4 h-4 text-amber-400" />
                    <span class="font-bold text-white">{{ $this->stats['physical'] }}</span> {{ __('Physical') }}
                </div>
            </div>

            {{-- Search --}}
            <div class="max-w-xl mx-auto relative">
                <input type="text" wire:model.live.debounce.300ms="search"
                    placeholder="{{ __('Search books, authors...') }}"
                    class="w-full pl-14 pr-5 py-4 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 text-white placeholder:text-slate-400 font-medium focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:bg-white/15 transition-all text-lg">
                <x-icon name="o-magnifying-glass" class="absolute left-5 top-4.5 w-5 h-5 text-slate-400" />
                @if($search)
                <button wire:click="$set('search','')" class="absolute right-5 top-4.5 text-slate-400 hover:text-white transition-colors">
                    <x-icon name="o-x-mark" class="w-5 h-5" />
                </button>
                @endif
            </div>
        </div>
    </section>

    {{-- ══════════════════════════ FILTER BAR ══════════════════════════ --}}
    <section class="sticky top-0 z-40 bg-white/80 dark:bg-slate-950/80 backdrop-blur-xl border-b border-slate-200 dark:border-slate-800 py-4 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-wrap gap-3 items-center justify-between">

                {{-- Category Pills --}}
                <div class="flex flex-wrap gap-2 items-center">
                    <button wire:click="$set('categoryId', null)"
                        class="px-3 py-1.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all {{ is_null($categoryId) ? 'bg-slate-900 dark:bg-white text-white dark:text-slate-900 shadow' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                        {{ __('All') }}
                    </button>
                    @foreach($this->categories as $cat)
                    <button wire:click="$set('categoryId', {{ $cat->id }})"
                        class="px-3 py-1.5 rounded-xl text-xs font-black uppercase tracking-widest transition-all {{ $categoryId === $cat->id ? 'bg-cyan-600 text-white shadow' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700' }}">
                        {{ $cat->name }} <span class="opacity-60">({{ $cat->books_count }})</span>
                    </button>
                    @endforeach
                </div>

                {{-- Right Controls --}}
                <div class="flex items-center gap-2 shrink-0">
                    {{-- Type Filter --}}
                    <select wire:model.live="typeFilter" class="text-xs font-bold px-3 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 border-0 text-slate-700 dark:text-slate-300 focus:ring-2 focus:ring-cyan-500">
                        <option value="">{{ __('All Types') }}</option>
                        <option value="ebook">{{ __('eBook only') }}</option>
                        <option value="physical">{{ __('Physical only') }}</option>
                        <option value="both">{{ __('Both') }}</option>
                    </select>

                    {{-- Sort --}}
                    <select wire:model.live="sort" class="text-xs font-bold px-3 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 border-0 text-slate-700 dark:text-slate-300 focus:ring-2 focus:ring-cyan-500">
                        <option value="latest">{{ __('Newest') }}</option>
                        <option value="rating">{{ __('Top Rated') }}</option>
                        <option value="title">{{ __('A → Z') }}</option>
                    </select>

                    {{-- View Toggle --}}
                    <div class="bg-slate-100 dark:bg-slate-800 p-1 rounded-xl flex gap-1">
                        <button wire:click="$set('viewMode','grid')" class="p-1.5 rounded-lg {{ $viewMode === 'grid' ? 'bg-white dark:bg-slate-700 shadow text-cyan-600' : 'text-slate-400' }}">
                            <x-icon name="o-squares-2x2" class="w-4 h-4" />
                        </button>
                        <button wire:click="$set('viewMode','list')" class="p-1.5 rounded-lg {{ $viewMode === 'list' ? 'bg-white dark:bg-slate-700 shadow text-cyan-600' : 'text-slate-400' }}">
                            <x-icon name="o-list-bullet" class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ══════════════════════════ BOOK GRID ══════════════════════════ --}}
    <section class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div wire:loading.class="opacity-50 pointer-events-none" class="transition-opacity duration-200">
                @if($this->books->isEmpty())
                    <div class="text-center py-24 rounded-[3rem] border-2 border-dashed border-slate-300 dark:border-slate-800">
                        <x-icon name="o-book-open" class="w-16 h-16 mx-auto text-slate-300 dark:text-slate-700 mb-4" />
                        <h3 class="text-2xl font-black text-slate-800 dark:text-white mb-2">{{ __('No books found') }}</h3>
                        <p class="text-slate-500 mb-6">{{ __('Try adjusting your search or filters.') }}</p>
                        <button wire:click="resetFilters" class="btn btn-primary rounded-xl">
                            <x-icon name="o-arrow-path" class="w-4 h-4 mr-2" /> {{ __('Reset Filters') }}
                        </button>
                    </div>
                @elseif($viewMode === 'grid')
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-6">
                        @foreach($this->books as $book)
                        <a href="{{ route('web.book', $book->slug) }}" wire:navigate class="group block">
                            {{-- Cover --}}
                            <div class="aspect-[2/3] rounded-2xl overflow-hidden bg-slate-200 dark:bg-slate-800 relative shadow-md group-hover:shadow-2xl group-hover:-translate-y-2 transition-all duration-300">
                                @if($book->cover_url)
                                    <img src="{{ $book->cover_url }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt="{{ $book->title }}">
                                @else
                                    <div class="absolute inset-0 flex flex-col items-center justify-center bg-gradient-to-br from-slate-200 to-slate-300 dark:from-slate-700 dark:to-slate-800">
                                        <x-icon name="o-book-open" class="w-12 h-12 text-slate-400 mb-2" />
                                        <span class="text-[10px] font-bold uppercase tracking-widest text-slate-500 text-center px-3 line-clamp-3">{{ $book->title }}</span>
                                    </div>
                                @endif

                                {{-- Type Badge --}}
                                <div class="absolute top-2 left-2">
                                    @if($book->type === 'ebook')
                                        <span class="text-[9px] font-black uppercase tracking-widest bg-cyan-600 text-white px-2 py-0.5 rounded-md shadow">eBook</span>
                                    @elseif($book->type === 'both')
                                        <span class="text-[9px] font-black uppercase tracking-widest bg-fuchsia-600 text-white px-2 py-0.5 rounded-md shadow">Digital+Physical</span>
                                    @else
                                        <span class="text-[9px] font-black uppercase tracking-widest bg-amber-600 text-white px-2 py-0.5 rounded-md shadow">Physical</span>
                                    @endif
                                </div>

                                @if($book->created_at->diffInDays() < 14)
                                <div class="absolute top-2 right-2">
                                    <span class="text-[9px] font-black uppercase tracking-widest bg-emerald-500 text-white px-2 py-0.5 rounded-md shadow">New</span>
                                </div>
                                @endif

                                {{-- Hover Overlay --}}
                                <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/50 to-transparent translate-y-full group-hover:translate-y-0 transition-transform duration-300 flex flex-col justify-end p-3">
                                    @if($book->category)
                                    <span class="text-[10px] font-black uppercase tracking-widest text-cyan-400 mb-1">{{ $book->category->name }}</span>
                                    @endif
                                    <p class="text-white text-xs line-clamp-3 opacity-90">{{ $book->description }}</p>
                                </div>
                            </div>

                            {{-- Info --}}
                            <div class="mt-3 px-1">
                                <h3 class="font-black text-slate-900 dark:text-white text-sm line-clamp-2 group-hover:text-cyan-600 dark:group-hover:text-cyan-400 transition-colors leading-snug">{{ $book->title }}</h3>
                                <p class="text-xs font-bold text-slate-500 dark:text-slate-400 mt-1 truncate">{{ $book->author?->name ?? __('Unknown Author') }}</p>
                                @if($book->interactions_avg_rating)
                                <div class="flex items-center gap-1 mt-1.5">
                                    @for($i = 1; $i <= 5; $i++)
                                        <span class="text-xs {{ $i <= round($book->interactions_avg_rating) ? 'text-amber-400' : 'text-slate-300 dark:text-slate-600' }}">★</span>
                                    @endfor
                                    <span class="text-[10px] text-slate-500 ml-1">{{ number_format($book->interactions_avg_rating, 1) }}</span>
                                </div>
                                @endif
                            </div>
                        </a>
                        @endforeach
                    </div>
                @else
                    {{-- LIST VIEW --}}
                    <div class="bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-200 dark:border-slate-800 divide-y divide-slate-100 dark:divide-slate-800 overflow-hidden shadow-sm">
                        @foreach($this->books as $book)
                        <a href="{{ route('web.book', $book->slug) }}" wire:navigate class="flex items-center gap-5 p-4 hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors group">
                            <div class="w-14 h-20 rounded-xl overflow-hidden bg-slate-200 dark:bg-slate-800 shrink-0 shadow">
                                @if($book->cover_url)
                                    <img src="{{ $book->cover_url }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                @else
                                    <div class="w-full h-full flex items-center justify-center"><x-icon name="o-book-open" class="w-6 h-6 text-slate-400" /></div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start gap-2 mb-1">
                                    <h3 class="font-black text-slate-900 dark:text-white line-clamp-1 group-hover:text-cyan-600 transition-colors">{{ $book->title }}</h3>
                                    @if($book->type === 'ebook') <span class="shrink-0 text-[9px] font-black uppercase bg-cyan-100 dark:bg-cyan-900/30 text-cyan-600 dark:text-cyan-400 px-2 py-0.5 rounded-md">eBook</span> @endif
                                </div>
                                <p class="text-sm text-slate-500 dark:text-slate-400 font-bold">{{ $book->author?->name ?? __('Unknown') }} @if($book->category) · {{ $book->category->name }} @endif</p>
                                @if($book->description)
                                <p class="text-xs text-slate-400 line-clamp-1 mt-1">{{ $book->description }}</p>
                                @endif
                            </div>
                            <div class="shrink-0 text-right hidden sm:block">
                                @if($book->interactions_avg_rating)
                                <div class="flex items-center gap-1 justify-end mb-1">
                                    <span class="text-amber-400 text-sm">★</span>
                                    <span class="text-sm font-black text-slate-700 dark:text-slate-300">{{ number_format($book->interactions_avg_rating, 1) }}</span>
                                </div>
                                @endif
                                <span class="text-xs text-slate-400">{{ $book->created_at->format('M Y') }}</span>
                            </div>
                        </a>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="mt-10">{{ $this->books->links() }}</div>
        </div>
    </section>
</div>
