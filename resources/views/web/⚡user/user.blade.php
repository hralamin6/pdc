<div>
  {{-- Profile Header with Banner --}}
  <section class="relative">
    {{-- Banner --}}
    <div class="relative h-72 md:h-96 bg-gradient-to-br from-indigo-500 via-purple-600 to-pink-600 overflow-hidden">
      @if($user->banner_url)
        <img src="{{ $user->banner_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
      @else
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGRlZnM+PHBhdHRlcm4gaWQ9ImdyaWQiIHdpZHRoPSI2MCIgaGVpZ2h0PSI2MCIgcGF0dGVyblVuaXRzPSJ1c2VyU3BhY2VPblVzZSI+PHBhdGggZD0iTSAxMCAwIEwgMCAwIDAgMTAiIGZpbGw9Im5vbmUiIHN0cm9rZT0id2hpdGUiIHN0cm9rZS13aWR0aD0iMSIgb3BhY2l0eT0iMC4xIi8+PC9wYXR0ZXJuPjwvZGVmcz48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSJ1cmwoI2dyaWQpIi8+PC9zdmc+')] opacity-20"></div>
        <div class="absolute inset-0 flex items-center justify-center">
          <div class="text-white/20 text-9xl font-black">
            @php
              $emojis = ['💪', '🏃', '🧘', '🥗', '🏋️', '🥇', '❤️', '🌟'];
              echo $emojis[array_rand($emojis)];
            @endphp
          </div>
        </div>
      @endif
      <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
    </div>

    {{-- Profile Card --}}
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="relative -mt-28 mb-8">
        <div class="bg-white/95 dark:bg-gray-800/95 backdrop-blur rounded-3xl p-6 md:p-8 border border-gray-200/50 dark:border-gray-700/50 shadow-2xl shadow-indigo-500/10">
          <div class="flex flex-col md:flex-row items-center md:items-start gap-6">
            {{-- Avatar --}}
            <div class="relative -mt-16 md:-mt-20 shrink-0">
              <img 
                src="{{ $user->avatar_url }}" 
                alt="{{ $user->name }}"
                class="w-36 h-36 md:w-44 md:h-44 rounded-3xl object-cover border-4 border-white dark:border-gray-800 shadow-2xl ring-4 ring-indigo-500/20"
              >
              @if($user->isOnline())
                <div class="absolute -bottom-2 -right-2 w-10 h-10 bg-green-500 border-4 border-white dark:border-gray-800 rounded-full shadow-lg flex items-center justify-center">
                  <div class="w-3 h-3 bg-white rounded-full animate-pulse"></div>
                </div>
              @else
                <div class="absolute -bottom-2 -right-2 w-10 h-10 bg-gray-400 border-4 border-white dark:border-gray-800 rounded-full shadow-lg"></div>
              @endif
            </div>

            {{-- User Details --}}
            <div class="flex-1 text-center md:text-left min-w-0">
              <div class="flex flex-col md:flex-row md:items-center gap-3 mb-3">
                <h1 class="text-3xl md:text-4xl font-black text-gray-900 dark:text-white tracking-tight">
                  {{ $user->name }}
                </h1>
                @if($user->email_verified_at)
                  <div class="flex items-center justify-center md:justify-start gap-1 text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/30 px-3 py-1 rounded-full text-sm font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Verified
                  </div>
                @endif
              </div>

              @if($user->detail?->occupation)
                <p class="text-sm text-indigo-600 dark:text-indigo-400 font-medium mb-2">
                  <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                  </svg>
                  {{ $user->detail->occupation }}
                </p>
              @endif

              @if($user->detail?->bio)
                <p class="text-lg text-gray-600 dark:text-gray-400 mb-4 max-w-2xl leading-relaxed">
                  {{ $user->detail->bio }}
                </p>
              @else
                <p class="text-lg text-gray-500 dark:text-gray-500 mb-4 italic">
                  No bio yet
                </p>
              @endif

              {{-- Social Links --}}
              @if($user->detail && ($user->detail->website || $user->detail->facebook || $user->detail->twitter || $user->detail->instagram || $user->detail->linkedin || $user->detail->github || $user->detail->youtube))
                <div class="flex flex-wrap gap-2 justify-center md:justify-start mb-4">
                  @if($user->detail->website)
                    <a href="{{ $user->detail->website }}" target="_blank" class="w-10 h-10 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center hover:bg-indigo-100 dark:hover:bg-indigo-900/50 hover:text-indigo-600 dark:hover:text-indigo-400 transition">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                      </svg>
                    </a>
                  @endif
                  @if($user->detail->facebook)
                    <a href="{{ $user->detail->facebook }}" target="_blank" class="w-10 h-10 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center hover:bg-blue-100 dark:hover:bg-blue-900/50 hover:text-blue-600 dark:hover:text-blue-400 transition">
                      <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                      </svg>
                    </a>
                  @endif
                  @if($user->detail->twitter)
                    <a href="{{ $user->detail->twitter }}" target="_blank" class="w-10 h-10 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center hover:bg-sky-100 dark:hover:bg-sky-900/50 hover:text-sky-500 dark:hover:text-sky-400 transition">
                      <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                      </svg>
                    </a>
                  @endif
                  @if($user->detail->instagram)
                    <a href="{{ $user->detail->instagram }}" target="_blank" class="w-10 h-10 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center hover:bg-pink-100 dark:hover:bg-pink-900/50 hover:text-pink-600 dark:hover:text-pink-400 transition">
                      <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z"/>
                      </svg>
                    </a>
                  @endif
                  @if($user->detail->linkedin)
                    <a href="{{ $user->detail->linkedin }}" target="_blank" class="w-10 h-10 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center hover:bg-blue-100 dark:hover:bg-blue-900/50 hover:text-blue-700 dark:hover:text-blue-400 transition">
                      <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                      </svg>
                    </a>
                  @endif
                  @if($user->detail->github)
                    <a href="{{ $user->detail->github }}" target="_blank" class="w-10 h-10 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center hover:bg-gray-800 dark:hover:bg-gray-600 hover:text-white transition">
                      <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                      </svg>
                    </a>
                  @endif
                  @if($user->detail->youtube)
                    <a href="{{ $user->detail->youtube }}" target="_blank" class="w-10 h-10 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center hover:bg-red-100 dark:hover:bg-red-900/50 hover:text-red-600 dark:hover:text-red-400 transition">
                      <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                      </svg>
                    </a>
                  @endif
                </div>
              @endif

              <div class="flex flex-wrap gap-3 justify-center md:justify-start items-center">
                @if($user->roles->isNotEmpty())
                  @foreach($user->roles as $role)
                    <span class="px-4 py-1.5 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 rounded-full shadow-lg shadow-indigo-500/30">
                      {{ ucfirst($role->name) }}
                    </span>
                  @endforeach
                @endif
                <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700/50 px-3 py-1.5 rounded-full">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                  </svg>
                  Joined {{ $user->created_at->format('M Y') }}
                </div>
                @if($user->isOnline())
                  <div class="flex items-center gap-2 text-sm text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/30 px-3 py-1.5 rounded-full">
                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                    Online
                  </div>
                @endif
              </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex gap-3 shrink-0">
              <button class="px-6 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl hover:from-indigo-700 hover:to-purple-700 transition shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/40 hover:scale-105 transform duration-200">
                Follow
              </button>
              <button class="px-4 py-2.5 text-sm font-semibold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- Stats Section --}}
  <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 mb-8">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-200/50 dark:border-gray-700/50 shadow-lg hover:shadow-xl transition-shadow duration-300 group">
        <div class="flex items-center justify-between mb-4">
          <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-500/30 group-hover:scale-110 transform transition duration-300">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5a2 2 0 00-2-2h-2"/>
            </svg>
          </div>
          <span class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider">Posts</span>
        </div>
        <div class="text-3xl font-black text-gray-900 dark:text-white">{{ number_format($this->stats['total_posts'] ?? 0) }}</div>
        <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Articles published</div>
      </div>

      <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-200/50 dark:border-gray-700/50 shadow-lg hover:shadow-xl transition-shadow duration-300 group">
        <div class="flex items-center justify-between mb-4">
          <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-500/30 group-hover:scale-110 transform transition duration-300">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
          </div>
          <span class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider">Views</span>
        </div>
        <div class="text-3xl font-black text-gray-900 dark:text-white">{{ number_format($this->stats['total_views'] ?? 0) }}</div>
        <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Total impressions</div>
      </div>

      <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-200/50 dark:border-gray-700/50 shadow-lg hover:shadow-xl transition-shadow duration-300 group">
        <div class="flex items-center justify-between mb-4">
          <div class="w-12 h-12 bg-gradient-to-br from-rose-500 to-pink-600 rounded-xl flex items-center justify-center shadow-lg shadow-rose-500/30 group-hover:scale-110 transform transition duration-300">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
          </div>
          <span class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider">Followers</span>
        </div>
        <div class="text-3xl font-black text-gray-900 dark:text-white">0</div>
        <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Community members</div>
      </div>

      <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-200/50 dark:border-gray-700/50 shadow-lg hover:shadow-xl transition-shadow duration-300 group">
        <div class="flex items-center justify-between mb-4">
          <div class="w-12 h-12 bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl flex items-center justify-center shadow-lg shadow-amber-500/30 group-hover:scale-110 transform transition duration-300">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
            </svg>
          </div>
          <span class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider">Avg. Views</span>
        </div>
        <div class="text-3xl font-black text-gray-900 dark:text-white">{{ number_format($this->stats['avg_views_per_post'] ?? 0) }}</div>
        <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Per post average</div>
      </div>
    </div>
  </section>

  {{-- Content Tabs Section --}}
  <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 mb-12">
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl overflow-hidden">
      {{-- Tab Navigation --}}
      <div class="border-b border-gray-200 dark:border-gray-700">
        <div class="flex">
          <button 
            wire:click="switchTab('posts')"
            @class([
              'px-6 py-4 text-sm font-semibold transition-all relative',
              'text-indigo-600 dark:text-indigo-400 border-b-2 border-indigo-600 dark:border-indigo-400 bg-indigo-50/50 dark:bg-indigo-900/20' => $activeTab === 'posts',
              'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 border-b-2 border-transparent' => $activeTab !== 'posts',
            ])
          >
            <span class="flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9.5a2 2 0 00-2-2h-2"/>
              </svg>
              Posts
              <span class="ml-1 px-2 py-0.5 text-xs bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 rounded-full">
                {{ $this->stats['total_posts'] }}
              </span>
            </span>
          </button>
          <button 
            wire:click="switchTab('about')"
            @class([
              'px-6 py-4 text-sm font-semibold transition-all relative',
              'text-indigo-600 dark:text-indigo-400 border-b-2 border-indigo-600 dark:border-indigo-400 bg-indigo-50/50 dark:bg-indigo-900/20' => $activeTab === 'about',
              'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 border-b-2 border-transparent' => $activeTab !== 'about',
            ])
          >
            <span class="flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              About
            </span>
          </button>
          <button 
            wire:click="switchTab('activity')"
            @class([
              'px-6 py-4 text-sm font-semibold transition-all relative',
              'text-indigo-600 dark:text-indigo-400 border-b-2 border-indigo-600 dark:border-indigo-400 bg-indigo-50/50 dark:bg-indigo-900/20' => $activeTab === 'activity',
              'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 border-b-2 border-transparent' => $activeTab !== 'activity',
            ])
          >
            <span class="flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
              </svg>
              Activity
            </span>
          </button>
        </div>
      </div>

      <div class="p-6 md:p-8">
        {{-- Posts Tab --}}
        @if($activeTab === 'posts')
          <div class="space-y-6">
            @if($this->posts->isEmpty())
              <div class="text-center py-16">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full mb-4">
                  <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                  </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">No posts yet</h3>
                <p class="text-gray-600 dark:text-gray-400">{{ $user->name }} hasn't published any posts yet.</p>
              </div>
            @else
              <div class="grid md:grid-cols-2 gap-6">
                @foreach($this->posts as $post)
                  <a wire:navigate href="{{ route('web.post', $post->slug) }}" wire:key="post-{{ $post->id }}" class="group bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-700/50 dark:to-gray-800/50 rounded-2xl overflow-hidden border border-gray-200 dark:border-gray-700 hover:border-indigo-500 dark:hover:border-indigo-500 transition-all duration-300 hover:shadow-xl hover:shadow-indigo-500/20 hover:-translate-y-1">
                    {{-- Post Image --}}
                    <div class="relative h-48 bg-gradient-to-br from-indigo-500 to-purple-600 overflow-hidden">
                      @if($post->getFirstMediaUrl('featured_image'))
                        <img src="{{ $post->getFirstMediaUrl('featured_image') }}" alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300" loading="lazy">
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
                    <div class="p-5">
                      <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2 line-clamp-2 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition">
                        {{ $post->title }}
                      </h3>
                      
                      <p class="text-gray-600 dark:text-gray-400 text-sm mb-4 line-clamp-2">
                        {{ $post->excerpt }}
                      </p>

                      {{-- Post Meta --}}
                      <div class="flex items-center justify-between text-sm text-gray-500 dark:text-gray-400 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <span class="flex items-center gap-1">
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                          </svg>
                          {{ $post->published_at?->diffForHumans() }}
                        </span>
                        <span class="flex items-center gap-1">
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                          </svg>
                          {{ number_format($post->views_count) }}
                        </span>
                      </div>
                    </div>
                  </a>
                @endforeach
              </div>

              {{-- Pagination --}}
              @if($this->posts->hasPages())
                <div class="mt-8">
                  {{ $this->posts->links() }}
                </div>
              @endif
            @endif
          </div>
        @endif

        {{-- About Tab --}}
        @if($activeTab === 'about')
          <div class="grid lg:grid-cols-3 gap-8">
          {{-- Personal Information --}}
          <div class="lg:col-span-2 space-y-6">
            <div>
              <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Personal Information
              </h3>
              <div class="bg-gray-50 dark:bg-gray-700/50 rounded-2xl p-5 space-y-4">
                @if($user->detail?->phone)
                  <div class="flex items-center justify-between py-2 border-b border-gray-200 dark:border-gray-600 last:border-0">
                    <span class="text-gray-500 dark:text-gray-400">Phone</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ $user->detail->phone }}</span>
                  </div>
                @endif
                @if($user->detail?->date_of_birth)
                  <div class="flex items-center justify-between py-2 border-b border-gray-200 dark:border-gray-600 last:border-0">
                    <span class="text-gray-500 dark:text-gray-400">Date of Birth</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ $user->detail->date_of_birth->format('F d, Y') }}</span>
                  </div>
                @endif
                @if($user->detail?->gender)
                  <div class="flex items-center justify-between py-2 border-b border-gray-200 dark:border-gray-600 last:border-0">
                    <span class="text-gray-500 dark:text-gray-400">Gender</span>
                    <span class="font-medium text-gray-900 dark:text-white capitalize">{{ $user->detail->gender }}</span>
                  </div>
                @endif
                @if($user->email)
                  <div class="flex items-center justify-between py-2 border-b border-gray-200 dark:border-gray-600 last:border-0">
                    <span class="text-gray-500 dark:text-gray-400">Email</span>
                    <span class="font-medium text-gray-900 dark:text-white">{{ $user->email }}</span>
                  </div>
                @endif
                <div class="flex items-center justify-between py-2 border-b border-gray-200 dark:border-gray-600 last:border-0">
                  <span class="text-gray-500 dark:text-gray-400">Member Since</span>
                  <span class="font-medium text-gray-900 dark:text-white">{{ $user->created_at->format('F d, Y') }}</span>
                </div>
              </div>
            </div>

            {{-- Location --}}
            @if($user->detail && ($user->detail->address || $user->detail->division || $user->detail->district))
              <div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                  <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                  </svg>
                  Location
                </h3>
                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-2xl p-5 space-y-4">
                  @if($user->detail->address)
                    <div class="flex items-start justify-between py-2 border-b border-gray-200 dark:border-gray-600 last:border-0">
                      <span class="text-gray-500 dark:text-gray-400">Address</span>
                      <span class="font-medium text-gray-900 dark:text-white text-right max-w-xs">{{ $user->detail->address }}</span>
                    </div>
                  @endif
                  @if($user->detail->division)
                    <div class="flex items-center justify-between py-2 border-b border-gray-200 dark:border-gray-600 last:border-0">
                      <span class="text-gray-500 dark:text-gray-400">Division</span>
                      <span class="font-medium text-gray-900 dark:text-white">{{ $user->detail->division->name ?? $user->detail->division_id }}</span>
                    </div>
                  @endif
                  @if($user->detail->district)
                    <div class="flex items-center justify-between py-2 border-b border-gray-200 dark:border-gray-600 last:border-0">
                      <span class="text-gray-500 dark:text-gray-400">District</span>
                      <span class="font-medium text-gray-900 dark:text-white">{{ $user->detail->district->name ?? $user->detail->district_id }}</span>
                    </div>
                  @endif
                  @if($user->detail->upazila)
                    <div class="flex items-center justify-between py-2 border-b border-gray-200 dark:border-gray-600 last:border-0">
                      <span class="text-gray-500 dark:text-gray-400">Upazila</span>
                      <span class="font-medium text-gray-900 dark:text-white">{{ $user->detail->upazila->name ?? $user->detail->upazila_id }}</span>
                    </div>
                  @endif
                  @if($user->detail->union)
                    <div class="flex items-center justify-between py-2 border-b border-gray-200 dark:border-gray-600 last:border-0">
                      <span class="text-gray-500 dark:text-gray-400">Union</span>
                      <span class="font-medium text-gray-900 dark:text-white">{{ $user->detail->union->name ?? $user->detail->union_id }}</span>
                    </div>
                  @endif
                  @if($user->detail->postal_code)
                    <div class="flex items-center justify-between py-2 border-b border-gray-200 dark:border-gray-600 last:border-0">
                      <span class="text-gray-500 dark:text-gray-400">Postal Code</span>
                      <span class="font-medium text-gray-900 dark:text-white">{{ $user->detail->postal_code }}</span>
                    </div>
                  @endif
                </div>
              </div>
            @endif
          </div>

          {{-- Sidebar Info --}}
          <div class="space-y-6">
            {{-- Account Status --}}
            <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl p-5 text-white">
              <h4 class="font-bold mb-3 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Account Status
              </h4>
              <div class="space-y-2 text-sm">
                <div class="flex items-center justify-between">
                  <span class="text-white/80">Status</span>
                  <span class="font-medium">{{ $user->detail?->is_active ? 'Active' : 'Inactive' }}</span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-white/80">Email Verified</span>
                  <span class="font-medium">{{ $user->email_verified_at ? 'Yes' : 'No' }}</span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-white/80">Online Status</span>
                  <span class="font-medium flex items-center gap-1">
                    @if($user->isOnline())
                      <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span> Online
                    @else
                      <span class="w-2 h-2 bg-gray-400 rounded-full"></span> Offline
                    @endif
                  </span>
                </div>
                @if($user->last_seen)
                  <div class="flex items-center justify-between">
                    <span class="text-white/80">Last Seen</span>
                    <span class="font-medium">{{ $user->last_seen->diffForHumans() }}</span>
                  </div>
                @endif
              </div>
            </div>

            {{-- Roles --}}
            @if($user->roles->isNotEmpty())
              <div>
                <h4 class="font-bold text-gray-900 dark:text-white mb-3">Roles</h4>
                <div class="flex flex-wrap gap-2">
                  @foreach($user->roles as $role)
                    <span class="px-3 py-1.5 text-sm font-medium text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 rounded-full">
                      {{ ucfirst($role->name) }}
                    </span>
                  @endforeach
                </div>
              </div>
            @endif

            {{-- Social Links --}}
            @if($user->detail && ($user->detail->website || $user->detail->facebook || $user->detail->twitter || $user->detail->instagram || $user->detail->linkedin || $user->detail->github || $user->detail->youtube))
              <div>
                <h4 class="font-bold text-gray-900 dark:text-white mb-3">Connect</h4>
                <div class="flex flex-wrap gap-2">
                  @if($user->detail->website)
                    <a href="{{ $user->detail->website }}" target="_blank" class="flex-1 min-w-[80px] px-3 py-2 bg-gray-100 dark:bg-gray-700 rounded-lg text-center text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-indigo-100 dark:hover:bg-indigo-900/30 hover:text-indigo-600 dark:hover:text-indigo-400 transition">
                      Website
                    </a>
                  @endif
                  @if($user->detail->facebook)
                    <a href="{{ $user->detail->facebook }}" target="_blank" class="flex-1 min-w-[80px] px-3 py-2 bg-gray-100 dark:bg-gray-700 rounded-lg text-center text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-blue-100 dark:hover:bg-blue-900/30 hover:text-blue-600 dark:hover:text-blue-400 transition">
                      Facebook
                    </a>
                  @endif
                  @if($user->detail->twitter)
                    <a href="{{ $user->detail->twitter }}" target="_blank" class="flex-1 min-w-[80px] px-3 py-2 bg-gray-100 dark:bg-gray-700 rounded-lg text-center text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-sky-100 dark:hover:bg-sky-900/30 hover:text-sky-500 dark:hover:text-sky-400 transition">
                      Twitter
                    </a>
                  @endif
                  @if($user->detail->instagram)
                    <a href="{{ $user->detail->instagram }}" target="_blank" class="flex-1 min-w-[80px] px-3 py-2 bg-gray-100 dark:bg-gray-700 rounded-lg text-center text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-pink-100 dark:hover:bg-pink-900/30 hover:text-pink-600 dark:hover:text-pink-400 transition">
                      Instagram
                    </a>
                  @endif
                  @if($user->detail->linkedin)
                    <a href="{{ $user->detail->linkedin }}" target="_blank" class="flex-1 min-w-[80px] px-3 py-2 bg-gray-100 dark:bg-gray-700 rounded-lg text-center text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-blue-100 dark:hover:bg-blue-900/30 hover:text-blue-700 dark:hover:text-blue-400 transition">
                      LinkedIn
                    </a>
                  @endif
                  @if($user->detail->github)
                    <a href="{{ $user->detail->github }}" target="_blank" class="flex-1 min-w-[80px] px-3 py-2 bg-gray-100 dark:bg-gray-700 rounded-lg text-center text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-800 dark:hover:bg-gray-600 hover:text-white transition">
                      GitHub
                    </a>
                  @endif
                  @if($user->detail->youtube)
                    <a href="{{ $user->detail->youtube }}" target="_blank" class="flex-1 min-w-[80px] px-3 py-2 bg-gray-100 dark:bg-gray-700 rounded-lg text-center text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-red-100 dark:hover:bg-red-900/30 hover:text-red-600 dark:hover:text-red-400 transition">
                      YouTube
                    </a>
                  @endif
                </div>
              </div>
            @endif
          </div>
        @endif

        {{-- Activity Tab --}}
        @if($activeTab === 'activity')
          <div class="space-y-6">
            @if($this->recentActivities->isEmpty())
              <div class="text-center py-16">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full mb-4">
                  <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                  </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">No recent activity</h3>
                <p class="text-gray-600 dark:text-gray-400">{{ $user->name }} hasn't been active recently.</p>
              </div>
            @else
              <div class="space-y-4">
                @foreach($this->recentActivities as $activity)
                  <div class="flex gap-4 p-4 bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-700/50 dark:to-gray-800/50 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-indigo-500 dark:hover:border-indigo-500 transition-all duration-300 group">
                    {{-- Activity Icon --}}
                    <div class="shrink-0">
                      <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center text-2xl shadow-lg group-hover:scale-110 transition-transform duration-300">
                        {{ $activity['icon'] }}
                      </div>
                    </div>

                    {{-- Activity Content --}}
                    <div class="flex-1 min-w-0">
                      <h4 class="font-semibold text-gray-900 dark:text-white mb-1">
                        {{ $activity['title'] }}
                      </h4>
                      <a wire:navigate href="{{ $activity['link'] }}" class="text-indigo-600 dark:text-indigo-400 hover:underline text-sm line-clamp-1 block mb-2">
                        {{ $activity['description'] }}
                      </a>
                      <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ $activity['date']->diffForHumans() }}
                      </div>
                    </div>

                    {{-- Arrow Icon --}}
                    <div class="shrink-0 self-center">
                      <svg class="w-5 h-5 text-gray-400 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                      </svg>
                    </div>
                  </div>
                @endforeach
              </div>
            @endif
          </div>
        @endif
      </div>
    </div>
  </section>

</div>