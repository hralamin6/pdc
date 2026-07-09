<div>
  {{-- Hero Section --}}
  <section class="relative overflow-hidden py-20 lg:py-32">
    <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/10 via-purple-500/10 to-pink-500/10"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
      <div class="text-center">
        <h1 class="text-5xl md:text-7xl font-extrabold mb-6">
        <span class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 bg-clip-text text-transparent">
          Transform Your Life
        </span>
          <br/>
          <span class="text-gray-900 dark:text-white">Through Fitness</span>
        </h1>
        <p class="text-xl md:text-2xl text-gray-600 dark:text-gray-300 mb-8 max-w-3xl mx-auto">
          Join thousands of fitness enthusiasts sharing knowledge, motivation, and success stories
        </p>

        {{-- Stats --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 max-w-4xl mx-auto mt-12">
          <div class="bg-white/60 dark:bg-gray-800/60 backdrop-blur-sm rounded-2xl p-6 border border-gray-200 dark:border-gray-700">
            <div class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">{{ number_format($this->stats['posts']) }}</div>
            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Posts</div>
          </div>
          <div class="bg-white/60 dark:bg-gray-800/60 backdrop-blur-sm rounded-2xl p-6 border border-gray-200 dark:border-gray-700">
            <div class="text-3xl font-bold text-purple-600 dark:text-purple-400">{{ number_format($this->stats['categories']) }}</div>
            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Categories</div>
          </div>
          <div class="bg-white/60 dark:bg-gray-800/60 backdrop-blur-sm rounded-2xl p-6 border border-gray-200 dark:border-gray-700">
            <div class="text-3xl font-bold text-pink-600 dark:text-pink-400">{{ number_format($this->stats['users']) }}</div>
            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Members</div>
          </div>
          <div class="bg-white/60 dark:bg-gray-800/60 backdrop-blur-sm rounded-2xl p-6 border border-gray-200 dark:border-gray-700">
            <div class="text-3xl font-bold text-orange-600 dark:text-orange-400">{{ number_format($this->stats['views']) }}</div>
            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Views</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- Categories Section --}}
  <section class="py-16 bg-white/50 dark:bg-gray-900/50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-12">
        <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
          Explore Topics
        </h2>
        <p class="text-lg text-gray-600 dark:text-gray-400">
          Discover content across various fitness and health categories
        </p>
      </div>

      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach($this->categories as $category)
          <a wire:navigate href="{{ route('web.posts') }}?category={{ $category->id }}" class="group relative overflow-hidden bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900 rounded-2xl p-6 border border-gray-200 dark:border-gray-700 hover:border-indigo-500 dark:hover:border-indigo-500 transition-all duration-300 hover:shadow-xl hover:shadow-indigo-500/20 hover:-translate-y-1 cursor-pointer">
            <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/0 to-purple-500/0 group-hover:from-indigo-500/10 group-hover:to-purple-500/10 transition-all duration-300"></div>
            <div class="relative">
              <div class="text-3xl mb-3">
                @php
                  $icons = ['💪', '🏃', '🧘', '🥗', '🏋️', '🥇', '❤️', '🌟'];
                  echo $icons[array_rand($icons)];
                @endphp
              </div>
              <h3 class="font-semibold text-gray-900 dark:text-white mb-2 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition">
                {{ $category->name }}
              </h3>
              <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ $category->posts_count }} {{ Str::plural('post', $category->posts_count) }}
              </p>
            </div>
          </a>
        @endforeach
      </div>
    </div>
  </section>

  {{-- Featured Posts Section --}}
  <section class="py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-12">
        <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
          Latest Articles
        </h2>
        <p class="text-lg text-gray-600 dark:text-gray-400">
          Fresh insights and tips from our community
        </p>
      </div>

      <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
        @foreach($this->featuredPosts as $post)
          <a wire:navigate href="{{ route('web.post', $post->slug) }}" class="group bg-white dark:bg-gray-800 rounded-2xl overflow-hidden border border-gray-200 dark:border-gray-700 hover:border-indigo-500 dark:hover:border-indigo-500 transition-all duration-300 hover:shadow-2xl hover:shadow-indigo-500/20 hover:-translate-y-2">
            {{-- Post Image --}}
            <div class="relative h-48 bg-gradient-to-br from-indigo-500 to-purple-600 overflow-hidden">
              @if($post->getFirstMediaUrl('featured_image'))
                <img src="{{ $post->getFirstMediaUrl('featured_image') }}" alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
              @else
                <div class="w-full h-full flex items-center justify-center text-white text-6xl">
                  @php
                    $emojis = ['💪', '🏃', '🧘', '🥗', '🏋️', '🥇', '❤️', '🌟'];
                    echo $emojis[array_rand($emojis)];
                  @endphp
                </div>
              @endif
              <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>

              {{-- Category Badge --}}
              @if($post->category)
                <div class="absolute top-4 left-4">
                <span class="px-3 py-1 text-xs font-medium text-white bg-white/20 backdrop-blur-sm rounded-full border border-white/30">
                  {{ $post->category->name }}
                </span>
                </div>
              @endif
            </div>

            {{-- Post Content --}}
            <div class="p-6">
              <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3 line-clamp-2 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition">
                {{ $post->title }}
              </h3>

              <p class="text-gray-600 dark:text-gray-400 text-sm mb-4 line-clamp-3">
                {{ $post->excerpt }}
              </p>

              {{-- Author & Meta --}}
              <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                <div class="flex items-center space-x-2">
                  <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold">
                    {{ substr($post->user->name ?? 'A', 0, 1) }}
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ Str::limit($post->user->name ?? 'Anonymous', 20) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $post->published_at?->diffForHumans() }}</p>
                  </div>
                </div>

                <div class="flex items-center space-x-3 text-gray-500 dark:text-gray-400">
                <span class="text-xs flex items-center">
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                  </svg>
                  {{ number_format($post->views_count) }}
                </span>
                </div>
              </div>
            </div>
          </a>
        @endforeach
      </div>

      {{-- View All Button --}}
      <div class="text-center mt-12">
        <a wire:navigate href="{{ route('web.posts') }}" class="inline-flex items-center px-8 py-3 text-sm font-medium text-white bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg hover:from-indigo-700 hover:to-purple-700 transition shadow-lg shadow-indigo-500/50">
          View All Posts
          <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
          </svg>
        </a>
      </div>
    </div>
  </section>

  {{-- Top Contributors Section --}}
  <section class="py-16 bg-white/50 dark:bg-gray-900/50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-12">
        <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
          Top Contributors
        </h2>
        <p class="text-lg text-gray-600 dark:text-gray-400">
          Meet our most active community members
        </p>
      </div>

      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6">
        @foreach($this->topContributors as $contributor)
          <a wire:navigate href="{{ route('web.user', $contributor->username ?? $contributor->id) }}" class="group text-center">
            <div class="relative inline-block mb-4">
              <div class="w-20 h-20 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-2xl font-bold ring-4 ring-white dark:ring-gray-900 group-hover:ring-indigo-500 transition-all duration-300 group-hover:scale-110">
                {{ substr($contributor->name, 0, 1) }}
              </div>
              <div class="absolute -bottom-1 -right-1 w-7 h-7 bg-gradient-to-br from-green-400 to-emerald-500 rounded-full flex items-center justify-center text-white text-xs font-bold border-2 border-white dark:border-gray-900">
                {{ $contributor->posts_count }}
              </div>
            </div>
            <h3 class="font-semibold text-gray-900 dark:text-white text-sm mb-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition">
              {{ Str::limit($contributor->name, 15) }}
            </h3>
            <p class="text-xs text-gray-500 dark:text-gray-400">
              {{ $contributor->posts_count }} {{ Str::plural('post', $contributor->posts_count) }}
            </p>
          </a>
        @endforeach
      </div>
    </div>
  </section>

  {{-- CTA Section --}}
  <section class="py-20 relative overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-600"></div>
    <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGRlZnM+PHBhdHRlcm4gaWQ9ImdyaWQiIHdpZHRoPSI2MCIgaGVpZ2h0PSI2MCIgcGF0dGVyblVuaXRzPSJ1c2VyU3BhY2VPblVzZSI+PHBhdGggZD0iTSAxMCAwIEwgMCAwIDAgMTAiIGZpbGw9Im5vbmUiIHN0cm9rZT0id2hpdGUiIHN0cm9rZS13aWR0aD0iMSIgb3BhY2l0eT0iMC4xIi8+PC9wYXR0ZXJuPjwvZGVmcz48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSJ1cmwoI2dyaWQpIi8+PC9zdmc+')] opacity-20"></div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 relative text-center">
      <h2 class="text-4xl md:text-5xl font-extrabold text-white mb-6">
        Ready to Start Your Journey?
      </h2>
      <p class="text-xl text-white/90 mb-8">
        Join our community today and get access to expert advice, workout plans, and motivation
      </p>
      <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <a wire:navigate href="{{ route('register') }}" class="px-8 py-4 text-lg font-medium text-indigo-600 bg-white rounded-lg hover:bg-gray-50 transition shadow-xl">
          Create Free Account
        </a>
        <a wire:navigate href="{{ route('web.posts') }}" class="px-8 py-4 text-lg font-medium text-white bg-white/20 backdrop-blur-sm rounded-lg hover:bg-white/30 transition border border-white/30">
          Browse Content
        </a>
      </div>
    </div>
  </section>

</div>
