<div>
  {{-- Header --}}
  <section class="relative overflow-hidden bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 text-white py-16 lg:py-20">
    <div class="absolute top-0 right-1/3 w-72 h-72 bg-amber-400/20 rounded-full blur-[120px]"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
      <div class="max-w-2xl">
        <p class="text-amber-400 font-bold text-sm uppercase tracking-widest mb-3">Knowledge Hub</p>
        <h1 class="text-3xl md:text-5xl font-black mb-4 tracking-tight">Blog & Articles</h1>
        <p class="text-white/50 text-lg mb-8">Discover reflections, guides, and insights from our community writers.</p>
        <div class="flex gap-6">
          <div>
            <p class="text-2xl font-black">{{ number_format($this->stats['total']) }}</p>
            <p class="text-xs text-white/40 uppercase tracking-wider mt-1">Articles</p>
          </div>
          <div class="border-l border-white/20 pl-6">
            <p class="text-2xl font-black">{{ number_format($this->stats['categories']) }}</p>
            <p class="text-xs text-white/40 uppercase tracking-wider mt-1">Categories</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- Main --}}
  <section class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

      {{-- Search --}}
      <div class="relative mb-8">
        <x-icon name="o-magnifying-glass" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
        <input type="text" wire:model.live.debounce.500ms="search" placeholder="Search articles..."
          class="w-full px-6 py-4 pl-12 bg-base-100 border border-base-content/10 rounded-2xl focus:ring-2 focus:ring-primary/50 focus:border-primary/50 transition shadow-sm text-base-content" />
        @if($search)
          <button wire:click="$set('search', '')" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
            <x-icon name="o-x-mark" class="w-5 h-5" />
          </button>
        @endif
      </div>

      {{-- Category Tabs + Sort --}}
      <div class="flex flex-col md:flex-row gap-4 items-start md:items-center justify-between mb-10">
        <div class="flex flex-wrap gap-2">
          <button wire:click="$set('category', null)"
            class="px-4 py-2 rounded-full text-sm font-semibold transition-all {{ !$category ? 'bg-primary text-white shadow-lg shadow-primary/30' : 'bg-base-200 text-base-content/60 hover:bg-base-300' }}">
            All Topics
          </button>
          @foreach($this->categories->take(8) as $cat)
            <button wire:click="$set('category', '{{ $cat->id }}')"
              class="px-4 py-2 rounded-full text-sm font-semibold transition-all {{ $category == $cat->id ? 'bg-primary text-white shadow-lg shadow-primary/30' : 'bg-base-200 text-base-content/60 hover:bg-base-300' }}">
              {{ $cat->name }}
            </button>
          @endforeach
        </div>
        <select wire:model.live="sortBy" class="select select-sm select-bordered rounded-full text-sm">
          <option value="latest">Latest</option>
          <option value="popular">Most Popular</option>
          <option value="oldest">Oldest</option>
        </select>
      </div>

      {{-- Posts Grid --}}
      <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
        @forelse($this->posts as $post)
          <article wire:key="post-{{ $post->id }}" class="group bg-base-100 rounded-2xl overflow-hidden border border-base-content/5 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col">
            <a wire:navigate href="{{ route('web.post', $post->slug) }}" class="block flex-grow flex flex-col">
              <div class="relative h-48 bg-gradient-to-br from-amber-400/20 to-orange-500/20 overflow-hidden">
                @if($post->getFirstMediaUrl('featured_image'))
                  <img src="{{ $post->getFirstMediaUrl('featured_image') }}" alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" loading="lazy">
                @else
                  <div class="w-full h-full flex items-center justify-center">
                    <x-icon name="o-newspaper" class="w-16 h-16 text-amber-500/30" />
                  </div>
                @endif
                @if($post->category)
                  <div class="absolute top-3 left-3">
                    <span class="px-3 py-1 text-xs font-bold text-white bg-black/40 backdrop-blur-sm rounded-full">{{ $post->category->name }}</span>
                  </div>
                @endif
              </div>
              <div class="p-5 flex-grow flex flex-col">
                <h3 class="font-bold text-lg text-base-content mb-2 line-clamp-2 group-hover:text-primary transition-colors">{{ $post->title }}</h3>
                <p class="text-sm text-base-content/60 mb-4 line-clamp-2 flex-grow">{{ $post->excerpt }}</p>
                <div class="flex items-center justify-between pt-3 border-t border-base-content/5">
                  <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-full bg-gradient-to-br from-primary to-secondary flex items-center justify-center text-white text-xs font-bold shrink-0">
                      {{ substr($post->user->name ?? 'A', 0, 1) }}
                    </div>
                    <div class="text-xs">
                      <p class="font-semibold text-base-content">{{ Str::limit($post->user->name ?? 'Anonymous', 15) }}</p>
                      <p class="text-base-content/50">{{ $post->published_at?->diffForHumans() }}</p>
                    </div>
                  </div>
                  <span class="text-xs text-base-content/40 flex items-center gap-1">
                    <x-icon name="o-eye" class="w-3.5 h-3.5" /> {{ number_format($post->views_count) }}
                  </span>
                </div>
              </div>
            </a>
          </article>
        @empty
          <div class="col-span-full text-center py-20 bg-base-200/50 rounded-2xl border border-dashed border-base-content/10">
            <x-icon name="o-newspaper" class="w-12 h-12 text-base-content/20 mx-auto mb-4" />
            <h3 class="text-lg font-bold text-base-content/70 mb-1">No articles found</h3>
            <p class="text-base-content/50 text-sm mb-4">Try adjusting your filters.</p>
            @if($search || $category)
              <button wire:click="resetFilters" class="btn btn-primary btn-sm rounded-xl font-bold">Clear Filters</button>
            @endif
          </div>
        @endforelse
      </div>

      @if($this->posts->hasPages())
        <div class="flex justify-center">{{ $this->posts->links() }}</div>
      @endif
    </div>
  </section>
</div>