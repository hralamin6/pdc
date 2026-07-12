<div class="bg-slate-50/40 dark:bg-slate-950/40 min-h-screen py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Minimalist Typography Header --}}
        <div class="border-b border-slate-200 dark:border-slate-800/80 pb-8 mb-10 text-center md:text-left">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary/10 text-primary text-xs font-black uppercase tracking-widest mb-3">
                <x-icon name="o-book-open" class="w-3.5 h-3.5" />
                {{ __('Knowledge Hub') }}
            </span>
            <h1 class="text-3xl sm:text-5xl font-black text-slate-900 dark:text-white tracking-tight mb-3">
                {{ __('Blog & Insights') }}
            </h1>
            <p class="text-sm sm:text-base text-slate-500 dark:text-slate-400 max-w-xl leading-relaxed">
                {{ __('Discover reflections, guides, and thoughts written by our community writers.') }}
            </p>
        </div>

        {{-- Main Layout: Left = Posts (70%), Right = Sidebar (30%) --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            {{-- Left column: Posts List --}}
            <div class="lg:col-span-8 space-y-8">
                
                {{-- Active Filters indicator --}}
                @if($search || $category || $tag || $sortBy !== 'latest')
                    <div class="flex flex-wrap items-center justify-between gap-3 p-4 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/60 dark:border-slate-800/80 shadow-sm text-sm">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="font-bold text-slate-400 uppercase text-xs">{{ __('Active Filters:') }}</span>
                            @if($search)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-lg text-xs font-semibold">
                                    {{ __('Search:') }} "{{ $search }}"
                                    <button wire:click="$set('search', '')" class="text-slate-400 hover:text-slate-600">
                                        <x-icon name="o-x-mark" class="w-3.5 h-3.5" />
                                    </button>
                                </span>
                            @endif
                            @if($category)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-lg text-xs font-semibold">
                                    {{ __('Category:') }} {{ \App\Models\Category::find($category)?->name }}
                                    <button wire:click="$set('category', null)" class="text-slate-400 hover:text-slate-600">
                                        <x-icon name="o-x-mark" class="w-3.5 h-3.5" />
                                    </button>
                                </span>
                            @endif
                            @if($tag)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-lg text-xs font-semibold">
                                    {{ __('Tag:') }} #{{ $tag }}
                                    <button wire:click="$set('tag', null)" class="text-slate-400 hover:text-slate-600">
                                        <x-icon name="o-x-mark" class="w-3.5 h-3.5" />
                                    </button>
                                </span>
                            @endif
                            @if($sortBy !== 'latest')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-lg text-xs font-semibold">
                                    {{ __('Sorted by:') }} {{ ucfirst($sortBy) }}
                                    <button wire:click="$set('sortBy', 'latest')" class="text-slate-400 hover:text-slate-600">
                                        <x-icon name="o-x-mark" class="w-3.5 h-3.5" />
                                    </button>
                                </span>
                            @endif
                        </div>
                        <button wire:click="resetFilters" class="text-xs font-black text-primary hover:underline uppercase">
                            {{ __('Clear All') }}
                        </button>
                    </div>
                @endif

                {{-- Post Feed --}}
                <div class="space-y-6">
                    @forelse($this->posts as $post)
                        <article wire:key="post-{{ $post->id }}" class="group bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/50 dark:border-slate-800/80 p-5 sm:p-6 transition-all duration-300 hover:shadow-xl hover:shadow-slate-200/30 dark:hover:shadow-none hover:border-slate-300 dark:hover:border-slate-700 flex flex-col sm:flex-row gap-5">
                            
                            {{-- Post Image Preview --}}
                            <div class="relative w-full sm:w-48 h-36 shrink-0 rounded-2xl overflow-hidden bg-slate-100 dark:bg-slate-800 border border-slate-200/30 dark:border-slate-800">
                                @if($post->getFirstMediaUrl('featured_image'))
                                    <img src="{{ $post->getFirstMediaUrl('featured_image') }}" alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-indigo-50 to-slate-100 dark:from-slate-800 dark:to-slate-900">
                                        <x-icon name="o-newspaper" class="w-10 h-10 text-slate-300 dark:text-slate-700" />
                                    </div>
                                @endif
                                
                                @if($post->category)
                                    <span class="absolute top-2.5 left-2.5 px-2.5 py-0.5 text-[10px] font-black uppercase bg-white/90 dark:bg-slate-900/90 backdrop-blur-sm text-slate-700 dark:text-slate-300 rounded-lg shadow-sm border border-slate-200/30 dark:border-slate-800">
                                        {{ $post->category->name }}
                                    </span>
                                @endif
                            </div>

                            {{-- Post Content details --}}
                            <div class="flex-grow flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center gap-2 mb-1.5 text-xs text-slate-400 font-medium">
                                        <span>{{ $post->published_at?->format('M d, Y') }}</span>
                                        <span>•</span>
                                        <span>{{ ceil(str_word_count(strip_tags($post->content)) / 200) }} {{ __('min read') }}</span>
                                    </div>
                                    
                                    <a wire:navigate href="{{ route('web.post', $post->slug) }}" class="block group/link">
                                        <h3 class="font-black text-lg sm:text-xl text-slate-800 dark:text-slate-100 group-hover/link:text-primary transition-colors line-clamp-2">
                                            {{ $post->title }}
                                        </h3>
                                    </a>
                                    
                                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-2 line-clamp-2 leading-relaxed">
                                        {{ $post->excerpt }}
                                    </p>
                                </div>

                                {{-- Footer: Author & Stats --}}
                                <div class="flex items-center justify-between mt-4 pt-3.5 border-t border-slate-100 dark:border-slate-800/80">
                                    <a wire:navigate href="{{ route('web.user', $post->user->username ?? $post->user->id) }}" class="flex items-center gap-2 hover:opacity-85 transition-opacity">
                                        <div class="w-6 h-6 rounded-full overflow-hidden bg-primary/10 flex items-center justify-center text-primary text-[10px] font-bold">
                                            @if($post->user->avatar_url)
                                                <img src="{{ $post->user->avatar_url }}" alt="{{ $post->user->name }}" class="w-full h-full object-cover">
                                            @else
                                                {{ substr($post->user->name ?? 'A', 0, 1) }}
                                            @endif
                                        </div>
                                        <span class="text-xs font-bold text-slate-600 dark:text-slate-300">
                                            {{ Str::limit($post->user->name ?? __('Anonymous'), 16) }}
                                        </span>
                                    </a>

                                    <div class="flex items-center gap-3 text-xs text-slate-400">
                                        <span class="flex items-center gap-1">
                                            <x-icon name="o-eye" class="w-3.5 h-3.5" />
                                            {{ number_format($post->views_count) }}
                                        </span>
                                        @if($post->comments_count > 0)
                                            <span class="flex items-center gap-1">
                                                <x-icon name="o-chat-bubble-left-right" class="w-3.5 h-3.5" />
                                                {{ $post->comments_count }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="text-center py-16 bg-white dark:bg-slate-900 rounded-3xl border border-dashed border-slate-200 dark:border-slate-800">
                            <x-icon name="o-newspaper" class="w-12 h-12 text-slate-300 dark:text-slate-700 mx-auto mb-3" />
                            <h3 class="font-black text-slate-800 dark:text-slate-200">{{ __('No articles found') }}</h3>
                            <p class="text-sm text-slate-400 mt-1 max-w-xs mx-auto">{{ __('Try adjusting your filter keywords or browse another category.') }}</p>
                            @if($search || $category || $tag)
                                <x-button label="{{ __('Clear Filters') }}" class="btn-primary btn-sm rounded-xl mt-4" wire:click="resetFilters" />
                            @endif
                        </div>
                    @endforelse
                </div>

                {{-- Pagination Links --}}
                @if($this->posts->hasPages())
                    <div class="pt-4">
                        {{ $this->posts->links() }}
                    </div>
                @endif
            </div>

            {{-- Right column: Sidebar (30%) --}}
            <div class="lg:col-span-4 space-y-6">
                
                {{-- Search Box --}}
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/50 dark:border-slate-800/80 p-5 shadow-sm">
                    <h4 class="font-black text-slate-800 dark:text-slate-200 text-sm uppercase tracking-wider mb-3">
                        {{ __('Search') }}
                    </h4>
                    <div class="relative">
                        <x-icon name="o-magnifying-glass" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
                        <input type="text" wire:model.live.debounce.500ms="search" placeholder="{{ __('Search keywords...') }}"
                            class="w-full pl-10 pr-4 py-2.5 text-sm bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl focus:ring-2 focus:ring-primary/45 focus:border-primary/45 focus:outline-none transition text-slate-700 dark:text-slate-300" />
                    </div>
                </div>

                {{-- Sort Dropdown --}}
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/50 dark:border-slate-800/80 p-5 shadow-sm">
                    <h4 class="font-black text-slate-800 dark:text-slate-200 text-sm uppercase tracking-wider mb-3">
                        {{ __('Sort Articles') }}
                    </h4>
                    <select wire:model.live="sortBy" class="select select-sm select-bordered w-full rounded-xl text-xs bg-slate-50 dark:bg-slate-950 border-slate-200 dark:border-slate-800">
                        <option value="latest">{{ __('Latest') }}</option>
                        <option value="popular">{{ __('Most Popular') }}</option>
                        <option value="oldest">{{ __('Oldest') }}</option>
                    </select>
                </div>

                {{-- Categories Navigation Widget --}}
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/50 dark:border-slate-800/80 p-5 shadow-sm">
                    <h4 class="font-black text-slate-800 dark:text-slate-200 text-sm uppercase tracking-wider mb-4 pb-2 border-b border-slate-100 dark:border-slate-850">
                        {{ __('Categories') }}
                    </h4>
                    <div class="space-y-1.5">
                        <button wire:click="$set('category', null)" class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-bold transition-all {{ !$category ? 'bg-primary text-white' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50' }}">
                            <span>{{ __('All Topics') }}</span>
                            <span class="badge {{ !$category ? 'badge-ghost text-white' : 'badge-neutral' }} text-[10px]">{{ $this->stats['total'] }}</span>
                        </button>
                        @foreach($this->categories as $cat)
                            <button wire:click="$set('category', '{{ $cat->id }}')" class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-bold transition-all {{ $category == $cat->id ? 'bg-primary text-white' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50' }}">
                                <span>{{ $cat->name }}</span>
                                <span class="badge {{ $category == $cat->id ? 'badge-ghost text-white' : 'badge-neutral' }} text-[10px]">{{ $cat->posts_count }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Popular Keywords/Tag Cloud Widget --}}
                @if(count($this->popularTags) > 0)
                    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/50 dark:border-slate-800/80 p-5 shadow-sm">
                        <h4 class="font-black text-slate-800 dark:text-slate-200 text-sm uppercase tracking-wider mb-4 pb-2 border-b border-slate-100 dark:border-slate-850">
                            {{ __('Tag Cloud') }}
                        </h4>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($this->popularTags as $t)
                                <button wire:click="$set('tag', '{{ $t }}')" class="px-2.5 py-1 text-[11px] font-bold rounded-lg border transition {{ $tag === $t ? 'bg-primary border-primary text-white shadow-sm shadow-primary/20' : 'bg-slate-50 dark:bg-slate-950 border-slate-200/65 dark:border-slate-800/70 text-slate-600 dark:text-slate-400 hover:border-slate-300 dark:hover:border-slate-700' }}">
                                    #{{ $t }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Trending Posts Widget --}}
                @if(count($this->popularPosts) > 0)
                    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/50 dark:border-slate-800/80 p-5 shadow-sm">
                        <h4 class="font-black text-slate-800 dark:text-slate-200 text-sm uppercase tracking-wider mb-4 pb-2 border-b border-slate-100 dark:border-slate-850">
                            {{ __('Trending Articles') }}
                        </h4>
                        <div class="space-y-4">
                            @foreach($this->popularPosts as $popPost)
                                <a wire:navigate href="{{ route('web.post', $popPost->slug) }}" class="flex gap-3 group/trend">
                                    <div class="w-12 h-12 rounded-xl overflow-hidden bg-slate-100 dark:bg-slate-800 shrink-0">
                                        @if($popPost->getFirstMediaUrl('featured_image'))
                                            <img src="{{ $popPost->getFirstMediaUrl('featured_image') }}" alt="{{ $popPost->title }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center bg-slate-200 dark:bg-slate-800">
                                                <x-icon name="o-newspaper" class="w-5 h-5 text-slate-400" />
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-grow min-w-0">
                                        <h5 class="text-xs font-black text-slate-800 dark:text-slate-200 group-hover/trend:text-primary transition-colors line-clamp-2 leading-tight">
                                            {{ $popPost->title }}
                                        </h5>
                                        <div class="flex items-center gap-2 mt-1 text-[10px] text-slate-450">
                                            <span>{{ $popPost->published_at?->format('M d, Y') }}</span>
                                            <span>•</span>
                                            <span class="flex items-center gap-0.5">
                                                <x-icon name="o-eye" class="w-2.5 h-2.5" />
                                                {{ number_format($popPost->views_count) }}
                                            </span>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>
        </div>

    </div>
</div>