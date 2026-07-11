<div class="min-h-screen bg-slate-50 dark:bg-slate-950">

    {{-- HERO --}}
    <div class="relative h-72 overflow-hidden bg-slate-900">
        @if($book->cover_url)
            <img src="{{ $book->cover_url }}" class="w-full h-full object-cover blur-xl scale-110 opacity-30">
        @endif
        <div class="absolute inset-0 bg-gradient-to-t from-slate-900 to-transparent"></div>
        <div class="absolute top-5 left-5">
            <a href="{{ route('web.library') }}" wire:navigate class="inline-flex items-center gap-2 px-4 py-2 bg-black/30 backdrop-blur-md text-white text-sm font-bold rounded-xl border border-white/20 hover:bg-black/50 transition-all">
                <x-icon name="o-arrow-left" class="w-4 h-4" /> {{ __('Library') }}
            </a>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 -mt-32 relative z-10 pb-16">

        {{-- BOOK HEADER --}}
        <div class="flex flex-col md:flex-row gap-8 mb-10">
            {{-- Cover --}}
            <div class="shrink-0">
                <div class="w-44 h-64 md:w-52 md:h-72 rounded-2xl shadow-2xl overflow-hidden bg-slate-800 border-4 border-white dark:border-slate-900">
                    @if($book->cover_url)
                        <img src="{{ $book->cover_url }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-slate-700 to-slate-900">
                            <x-icon name="o-book-open" class="w-16 h-16 text-slate-400" />
                        </div>
                    @endif
                </div>
            </div>

            {{-- Info --}}
            <div class="flex-1 pt-36 md:pt-16">
                <div class="flex flex-wrap gap-2 mb-3">
                    @if($book->category)
                        <span class="text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full bg-cyan-100 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-400 border border-cyan-200 dark:border-cyan-800">{{ $book->category->name }}</span>
                    @endif
                    @if($book->type === 'ebook')
                        <span class="text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full bg-fuchsia-100 text-fuchsia-700 dark:bg-fuchsia-900/30 dark:text-fuchsia-400 border border-fuchsia-200 dark:border-fuchsia-800">eBook</span>
                    @elseif($book->type === 'physical')
                        <span class="text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 border border-amber-200 dark:border-amber-800">Physical</span>
                    @elseif($book->type === 'both')
                        <span class="text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-400 border border-violet-200 dark:border-violet-800">Digital + Physical</span>
                    @endif
                </div>

                <h1 class="text-3xl md:text-4xl font-black text-slate-900 dark:text-white tracking-tight mb-2">{{ $book->title }}</h1>
                @if($book->author)
                    <p class="text-lg font-bold text-slate-500 dark:text-slate-400 mb-3">{{ $book->author->name }}</p>
                @endif

                {{-- Star Rating --}}
                <div class="flex items-center gap-3 mb-5">
                    <div class="flex gap-0.5">
                        @for($i = 1; $i <= 5; $i++)
                            <button wire:click="setRating({{ $i }})" class="text-2xl transition-transform hover:scale-125 {{ $i <= ($rating ?? round($this->avgRating ?? 0)) ? 'text-amber-400' : 'text-slate-300 dark:text-slate-600' }}">★</button>
                        @endfor
                    </div>
                    @if($this->avgRating)
                        <span class="font-black text-slate-700 dark:text-slate-300">{{ $this->avgRating }}</span>
                        <span class="text-sm text-slate-500">({{ $this->reviews->count() }} {{ __('reviews') }})</span>
                    @endif
                </div>

                {{-- Reading Shelf --}}
                <div class="flex flex-wrap gap-2 mb-5">
                    @foreach(['want_to_read' => '📖 Want to Read', 'reading' => '⚡ Reading', 'completed' => '✅ Finished'] as $status => $label)
                    <button wire:click="setStatus('{{ $status }}')"
                        class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest transition-all border-2
                            {{ $reading_status === $status
                                ? 'bg-slate-900 dark:bg-white text-white dark:text-slate-900 border-slate-900 dark:border-white shadow-lg'
                                : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:border-slate-400 dark:hover:border-slate-500' }}">
                        {{ $label }}
                    </button>
                    @endforeach
                </div>

                {{-- Action Buttons --}}
                <div class="flex flex-wrap gap-3">
                    @if($book->pdf_url)
                        <button wire:click="toggleReader"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-sm shadow-lg transition-all hover:-translate-y-0.5
                                {{ $showReader ? 'bg-slate-200 dark:bg-slate-700 text-slate-900 dark:text-white' : 'bg-gradient-to-r from-cyan-600 to-blue-600 text-white' }}">
                            <x-icon name="{{ $showReader ? 'o-eye-slash' : 'o-eye' }}" class="w-4 h-4" />
                            {{ $showReader ? __('Hide Reader') : __('Read Online') }}
                        </button>
                        <a href="{{ $book->pdf_url }}" target="_blank"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-sm bg-emerald-600 hover:bg-emerald-700 text-white shadow-lg transition-all hover:-translate-y-0.5"
                            @guest onclick="window.location='{{ route('login') }}'; return false;" @endguest>
                            <x-icon name="o-arrow-down-tray" class="w-4 h-4" /> {{ __('Download PDF') }}
                        </a>
                    @endif
                    @if($book->external_link)
                        <a href="{{ $book->external_link }}" target="_blank"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-sm bg-indigo-600 hover:bg-indigo-700 text-white shadow-lg transition-all hover:-translate-y-0.5">
                            <x-icon name="o-arrow-top-right-on-square" class="w-4 h-4" /> {{ __('External Link') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- PDF READER --}}
        @if($showReader && $book->pdf_url)
        <div class="mb-8 bg-white dark:bg-slate-900 rounded-[2rem] border border-slate-200 dark:border-slate-800 overflow-hidden shadow-xl">
            <div class="flex items-center justify-between px-5 py-3 border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50">
                <span class="font-black text-slate-900 dark:text-white flex items-center gap-2"><x-icon name="o-document-text" class="w-4 h-4 text-cyan-500" /> {{ __('PDF Reader') }}</span>
                <button wire:click="toggleReader" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                    <x-icon name="o-x-mark" class="w-5 h-5" />
                </button>
            </div>
            <iframe src="{{ $book->pdf_url }}#toolbar=1" class="w-full" style="height: 80vh;" title="{{ $book->title }}"></iframe>
        </div>
        @endif

        {{-- MAIN GRID --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- LEFT SIDEBAR --}}
            <div class="space-y-5">
                {{-- Book Details --}}
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5">
                    <h3 class="font-black text-sm uppercase tracking-widest text-slate-900 dark:text-white mb-4">{{ __('Details') }}</h3>
                    <div class="space-y-3 text-sm">
                        @if($book->author)
                        <div class="flex justify-between"><span class="text-slate-500 font-bold">{{ __('Author') }}</span><span class="font-black text-slate-800 dark:text-slate-200">{{ $book->author->name }}</span></div>
                        @endif
                        @if($book->publication)
                        <div class="flex justify-between"><span class="text-slate-500 font-bold">{{ __('Publisher') }}</span><span class="font-black text-slate-800 dark:text-slate-200">{{ $book->publication->name }}</span></div>
                        @endif
                        @if($book->publication_year)
                        <div class="flex justify-between"><span class="text-slate-500 font-bold">{{ __('Year') }}</span><span class="font-black text-slate-800 dark:text-slate-200">{{ $book->publication_year }}</span></div>
                        @endif
                        @if($book->pages_count)
                        <div class="flex justify-between"><span class="text-slate-500 font-bold">{{ __('Pages') }}</span><span class="font-black text-slate-800 dark:text-slate-200">{{ $book->pages_count }}</span></div>
                        @endif
                        @if($book->isbn)
                        <div class="flex justify-between"><span class="text-slate-500 font-bold">{{ __('ISBN') }}</span><span class="font-black text-slate-800 dark:text-slate-200 text-xs">{{ $book->isbn }}</span></div>
                        @endif
                    </div>
                </div>

                {{-- Reading Progress (auth only) --}}
                @auth
                @if($reading_status === 'reading' && $book->pages_count)
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5">
                    <h3 class="font-black text-sm uppercase tracking-widest text-slate-900 dark:text-white mb-3">{{ __('Reading Progress') }}</h3>
                    @php $pct = $book->pages_count > 0 ? min(100, round(($pages_read / $book->pages_count) * 100)) : 0; @endphp
                    <div class="flex justify-between text-xs font-bold text-slate-500 mb-2">
                        <span>{{ $pages_read }} / {{ $book->pages_count }} {{ __('pages') }}</span>
                        <span>{{ $pct }}%</span>
                    </div>
                    <div class="h-2.5 bg-slate-100 dark:bg-slate-800 rounded-full mb-3">
                        <div class="h-full bg-gradient-to-r from-cyan-500 to-blue-600 rounded-full transition-all duration-500" style="width: {{ $pct }}%"></div>
                    </div>
                    <div class="flex gap-2">
                        <input type="number" wire:model.blur="pages_read" min="0" max="{{ $book->pages_count }}"
                            class="flex-1 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm font-bold focus:ring-2 focus:ring-cyan-500">
                        <button wire:click="saveInteraction" class="px-3 py-1.5 bg-cyan-600 text-white rounded-xl text-xs font-bold hover:bg-cyan-700 transition-colors">{{ __('Save') }}</button>
                    </div>
                </div>
                @endif

                {{-- Your Review --}}
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5">
                    <h3 class="font-black text-sm uppercase tracking-widest text-slate-900 dark:text-white mb-3">{{ __('Your Review') }}</h3>
                    <textarea wire:model.blur="review" wire:change="saveInteraction"
                        placeholder="{{ __('Share your thoughts...') }}"
                        rows="4"
                        class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-sm font-medium focus:ring-2 focus:ring-cyan-500 resize-none"></textarea>
                </div>
                @endauth
                @guest
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 text-center">
                    <x-icon name="o-lock-closed" class="w-8 h-8 mx-auto text-slate-400 mb-2" />
                    <p class="text-sm font-bold text-slate-600 dark:text-slate-400 mb-3">{{ __('Login to track your reading and leave reviews') }}</p>
                    <a href="{{ route('login') }}" class="btn btn-sm btn-primary rounded-xl font-bold">{{ __('Login') }}</a>
                </div>
                @endguest
            </div>

            {{-- RIGHT MAIN --}}
            <div class="lg:col-span-2">

                {{-- Tabs --}}
                <div class="flex gap-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-1.5 mb-5">
                    @foreach(['about' => ['icon' => 'o-information-circle', 'label' => 'About'], 'copies' => ['icon' => 'o-building-library', 'label' => 'Borrow'], 'reviews' => ['icon' => 'o-chat-bubble-bottom-center-text', 'label' => 'Reviews']] as $tab => $info)
                    <button wire:click="$set('activeTab','{{ $tab }}')"
                        class="flex-1 flex items-center justify-center gap-2 py-2.5 rounded-xl font-bold text-sm transition-all duration-200
                            {{ $activeTab === $tab ? 'bg-slate-900 dark:bg-slate-700 text-white shadow-sm' : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                        <x-icon name="{{ $info['icon'] }}" class="w-4 h-4" />
                        <span class="hidden sm:inline">{{ __($info['label']) }}</span>
                    </button>
                    @endforeach
                </div>

                {{-- About Tab --}}
                @if($activeTab === 'about')
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                    <h3 class="font-black text-slate-900 dark:text-white mb-4 text-lg">{{ __('Description') }}</h3>
                    <div class="prose dark:prose-invert prose-slate max-w-none text-sm leading-relaxed">
                        {!! nl2br(e($book->description)) !!}
                    </div>
                </div>
                @endif

                {{-- Copies / Borrow Tab --}}
                @if($activeTab === 'copies')
                <div class="space-y-4">
                    @if($book->copies->isEmpty())
                        <div class="text-center py-16 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800">
                            <x-icon name="o-building-library" class="w-12 h-12 mx-auto text-slate-300 dark:text-slate-600 mb-3" />
                            <h3 class="font-black text-slate-700 dark:text-slate-300">{{ __('No borrowable copies available') }}</h3>
                            <p class="text-slate-500 text-sm mt-1">{{ __('Check back later or ask a community member.') }}</p>
                        </div>
                    @else
                        @foreach($book->copies as $copy)
                        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 flex items-center gap-5">
                            <div class="w-12 h-12 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center shrink-0">
                                <x-icon name="o-book-open" class="w-6 h-6 text-amber-600 dark:text-amber-400" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="font-black text-slate-900 dark:text-white">{{ $copy->owner?->name ?? __('Community') }}</div>
                                @if($copy->libraryHub)
                                    <div class="text-sm text-slate-500 flex items-center gap-1.5 mt-0.5">
                                        <x-icon name="o-map-pin" class="w-3.5 h-3.5" /> {{ $copy->libraryHub->name }}
                                    </div>
                                @endif
                                <div class="flex gap-2 mt-1.5">
                                    <span class="text-[10px] font-black uppercase tracking-widest px-2 py-0.5 rounded-md {{ $copy->condition === 'good' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' }}">
                                        {{ ucfirst($copy->condition ?? 'Good') }}
                                    </span>
                                    <span class="text-[10px] font-black uppercase tracking-widest px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                                        {{ ucfirst($copy->status) }}
                                    </span>
                                </div>
                            </div>
                            @if($copy->is_borrowable && $copy->status === 'available' && !in_array($copy->id, $this->activeRequestsCopyIds))
                                @auth
                                <button wire:click="openBorrowModal({{ $copy->id }})"
                                    class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold text-sm rounded-xl shadow transition-all hover:-translate-y-0.5 shrink-0">
                                    {{ __('Request') }}
                                </button>
                                @else
                                <a href="{{ route('login') }}" class="px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold text-sm rounded-xl shrink-0">
                                    {{ __('Login to Borrow') }}
                                </a>
                                @endauth
                            @elseif(in_array($copy->id, $this->activeRequestsCopyIds))
                                <span class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-500 font-bold text-sm rounded-xl shrink-0">{{ __('Requested') }}</span>
                            @else
                                <span class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-500 font-bold text-sm rounded-xl shrink-0">{{ __('Unavailable') }}</span>
                            @endif
                        </div>
                        @endforeach
                    @endif
                </div>
                @endif

                {{-- Reviews Tab --}}
                @if($activeTab === 'reviews')
                <div class="space-y-4">
                    @if($this->reviews->isEmpty())
                        <div class="text-center py-16 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800">
                            <x-icon name="o-chat-bubble-bottom-center-text" class="w-12 h-12 mx-auto text-slate-300 dark:text-slate-600 mb-3" />
                            <h3 class="font-black text-slate-700 dark:text-slate-300">{{ __('No reviews yet') }}</h3>
                            <p class="text-slate-500 text-sm mt-1">{{ __('Be the first to share your thoughts!') }}</p>
                        </div>
                    @else
                        @foreach($this->reviews as $review)
                        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5">
                            <div class="flex items-center gap-3 mb-3">
                                <img src="{{ $review->user->avatar_url }}" class="w-10 h-10 rounded-full object-cover border border-slate-200 dark:border-slate-700">
                                <div>
                                    <div class="font-black text-slate-900 dark:text-white text-sm">{{ $review->user->name }}</div>
                                    <div class="flex gap-0.5 mt-0.5">
                                        @for($i = 1; $i <= 5; $i++)
                                            <span class="text-xs {{ $i <= ($review->rating ?? 0) ? 'text-amber-400' : 'text-slate-300 dark:text-slate-600' }}">★</span>
                                        @endfor
                                    </div>
                                </div>
                                <span class="ml-auto text-xs text-slate-400">{{ $review->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed">{{ $review->review }}</p>
                        </div>
                        @endforeach
                    @endif
                </div>
                @endif
            </div>
        </div>

        {{-- RELATED BOOKS --}}
        @if($this->relatedBooks->isNotEmpty())
        <div class="mt-12">
            <h2 class="text-xl font-black text-slate-900 dark:text-white mb-5">{{ __('Related Books') }}</h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                @foreach($this->relatedBooks as $related)
                <a href="{{ route('web.book', $related->slug) }}" wire:navigate class="group">
                    <div class="aspect-[2/3] rounded-xl overflow-hidden bg-slate-200 dark:bg-slate-800 shadow hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                        @if($related->cover_url)
                            <img src="{{ $related->cover_url }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        @else
                            <div class="w-full h-full flex items-center justify-center"><x-icon name="o-book-open" class="w-8 h-8 text-slate-400" /></div>
                        @endif
                    </div>
                    <div class="mt-2 px-1">
                        <h4 class="text-sm font-black text-slate-900 dark:text-white line-clamp-2 group-hover:text-cyan-600 dark:group-hover:text-cyan-400 transition-colors">{{ $related->title }}</h4>
                        <p class="text-xs text-slate-500 mt-0.5">{{ $related->author?->name }}</p>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- BORROW MODAL --}}
    @if($borrowModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" wire:click.self="$set('borrowModal', false)">
        <div class="bg-white dark:bg-slate-900 rounded-[2rem] shadow-2xl border border-slate-200 dark:border-slate-800 max-w-md w-full p-6">
            <h3 class="text-xl font-black text-slate-900 dark:text-white mb-1">{{ __('Request to Borrow') }}</h3>
            <p class="text-slate-500 text-sm mb-6">{{ __('Submit your borrowing request. The owner will be notified.') }}</p>

            <div class="mb-5">
                <label class="block text-xs font-black uppercase tracking-widest text-slate-700 dark:text-slate-300 mb-2">{{ __('How many days?') }}</label>
                <input type="number" wire:model="requested_days" min="1" max="30"
                    class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                <p class="text-xs text-slate-400 mt-1">{{ __('Maximum 30 days per request') }}</p>
            </div>

            <div class="flex gap-3">
                <button wire:click="$set('borrowModal', false)" class="flex-1 py-3 rounded-xl border-2 border-slate-200 dark:border-slate-700 font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                    {{ __('Cancel') }}
                </button>
                <button wire:click="submitBorrowRequest" wire:loading.attr="disabled"
                    class="flex-1 py-3 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold shadow-lg transition-all">
                    <span wire:loading.remove wire:target="submitBorrowRequest">{{ __('Send Request') }}</span>
                    <span wire:loading wire:target="submitBorrowRequest">{{ __('Sending...') }}</span>
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
