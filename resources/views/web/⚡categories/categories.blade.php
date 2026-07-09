<div>
  {{-- Header Section --}}
<section class="relative overflow-hidden py-12 lg:py-16 bg-gradient-to-br from-indigo-500/10 via-purple-500/10 to-pink-500/10">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-8">
      <h1 class="text-4xl md:text-5xl font-extrabold mb-4">
        <span class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 bg-clip-text text-transparent">
          Browse Categories
        </span>
      </h1>
      <p class="text-lg text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">
        Explore all fitness and health topics
      </p>
    </div>

    {{-- Search Bar --}}
    <div class="max-w-2xl mx-auto">
      <div class="relative">
        <input 
          wire:model.live.debounce.300ms="search" 
          type="text" 
          placeholder="Search categories..." 
          class="w-full px-6 py-4 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
        />
        <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
      </div>
    </div>
  </div>
</section>

{{-- Categories Grid --}}
<section class="py-12">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    @if($this->categories->count() > 0)
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($this->categories as $category)
          <a wire:navigate href="{{ route('web.posts') }}?category={{ $category->id }}" class="group relative overflow-hidden bg-white dark:bg-gray-800 rounded-2xl p-8 border border-gray-200 dark:border-gray-700 hover:border-indigo-500 dark:hover:border-indigo-500 transition-all duration-300 hover:shadow-2xl hover:shadow-indigo-500/20 hover:-translate-y-2">
            <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/0 to-purple-500/0 group-hover:from-indigo-500/10 group-hover:to-purple-500/10 transition-all duration-300"></div>
            
            <div class="relative">
              <div class="flex items-start justify-between mb-4">
                <div class="text-5xl">
                  @php
                    $icons = ['💪', '🏃', '🧘', '🥗', '🏋️', '🥇', '❤️', '🌟', '🚴', '⚽', '🤸', '🥊'];
                    echo $icons[array_rand($icons)];
                  @endphp
                </div>
                <div class="px-3 py-1 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 text-xs font-semibold rounded-full">
                  {{ $category->posts_count }} {{ Str::plural('post', $category->posts_count) }}
                </div>
              </div>

              <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition">
                {{ $category->name }}
              </h3>

              @if($category->description)
                <p class="text-gray-600 dark:text-gray-400 text-sm line-clamp-3">
                  {{ $category->description }}
                </p>
              @endif

              <div class="mt-4 flex items-center text-indigo-600 dark:text-indigo-400 text-sm font-medium">
                <span>Explore posts</span>
                <svg class="w-4 h-4 ml-2 group-hover:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
              </div>
            </div>
          </a>
        @endforeach
      </div>

      {{-- Pagination --}}
      <div class="mt-12">
        {{ $this->categories->links('vendor.livewire.simple-tailwind') }}
      </div>
    @else
      <div class="text-center py-16">
        <div class="text-6xl mb-4">🔍</div>
        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">No categories found</h3>
        <p class="text-gray-600 dark:text-gray-400">Try adjusting your search terms</p>
      </div>
    @endif
  </div>
</section>

</div>