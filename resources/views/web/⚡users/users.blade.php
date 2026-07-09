<div>
  {{-- Hero Section --}}
  <section class="relative overflow-hidden py-20 lg:py-32">
    {{-- Animated Background --}}
    <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/10 via-purple-500/10 to-pink-500/10">
      <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiM4YjViZjYiIGZpbGwtb3BhY2l0eT0iMC4wNSI+PHBhdGggZD0iTTM2IDE0YzMuMzE0IDAgNiAyLjY4NiA2IDZzLTIuNjg2IDYtNiA2LTYtMi42ODYtNi02IDIuNjg2LTYgNi02ek0wIDIwYzMuMzE0IDAgNiAyLjY4NiA2IDZzLTIuNjg2IDYtNiA2djhsMTIgMTJoOHYtOGMzLjMxNCAwIDYgMi42ODYgNiA2czIuNjg2IDYgNiA2aDh2LThoLTZ2LTZoNnYtNmgtNnYtNmg2di02aC02di02aDZ2LTZoLTZ2LTZoNlYwSDM2djZoLTZ2Nmg2djZoLTZ2Nmg2djZoLTZ2Nmg2djZoLTZ2Nmg2djZ6Ii8+PC9nPjwvZz48L3N2Zz4=')] opacity-40"></div>
    </div>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
      <div class="text-center">
        {{-- Main Heading with Animation --}}
        <div class="mb-6">
          <h1 class="text-5xl md:text-7xl font-extrabold mb-4 animate-fade-in-up">
            <span class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 bg-clip-text text-transparent">
              Our Community
            </span>
            <br/>
            <span class="text-gray-900 dark:text-white">Members</span>
          </h1>
          <div class="flex items-center justify-center gap-2 text-sm text-gray-600 dark:text-gray-400">
            <div class="flex -space-x-2">
              @foreach($this->topContributors->take(4) as $contributor)
                <img 
                  src="{{ $contributor->avatar_url }}" 
                  alt="{{ $contributor->name }}" 
                  class="w-8 h-8 rounded-full border-2 border-white dark:border-gray-900 object-cover"
                  title="{{ $contributor->name }}"
                >
              @endforeach
            </div>
            <span class="font-medium">Join {{ number_format($this->stats['total']) }}+ fitness enthusiasts worldwide</span>
          </div>
        </div>
        
        <p class="text-xl md:text-2xl text-gray-600 dark:text-gray-300 mb-8 max-w-3xl mx-auto animate-fade-in-up" style="animation-delay: 0.1s;">
          Meet the amazing people who are part of our fitness community
        </p>

        {{-- Stats Cards with Enhanced Design --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 max-w-4xl mx-auto mt-12 animate-fade-in-up" style="animation-delay: 0.2s;">
          {{-- Total Members --}}
          <div class="group bg-white/80 dark:bg-gray-800/80 backdrop-blur-md rounded-2xl p-6 border border-gray-200 dark:border-gray-700 hover:border-indigo-500 dark:hover:border-indigo-400 transition-all duration-300 hover:shadow-xl hover:shadow-indigo-500/20 hover:-translate-y-1">
            <div class="flex items-center justify-between mb-2">
              <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
              </div>
            </div>
            <div class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">{{ number_format($this->stats['total']) }}</div>
            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1 font-medium">Total Members</div>
          </div>

          {{-- Verified Members --}}
          <div class="group bg-white/80 dark:bg-gray-800/80 backdrop-blur-md rounded-2xl p-6 border border-gray-200 dark:border-gray-700 hover:border-purple-500 dark:hover:border-purple-400 transition-all duration-300 hover:shadow-xl hover:shadow-purple-500/20 hover:-translate-y-1">
            <div class="flex items-center justify-between mb-2">
              <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-pink-600 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
              </div>
            </div>
            <div class="text-3xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">{{ number_format($this->stats['verified']) }}</div>
            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1 font-medium">Verified</div>
          </div>

          {{-- New Members --}}
          <div class="group bg-white/80 dark:bg-gray-800/80 backdrop-blur-md rounded-2xl p-6 border border-gray-200 dark:border-gray-700 hover:border-pink-500 dark:hover:border-pink-400 transition-all duration-300 hover:shadow-xl hover:shadow-pink-500/20 hover:-translate-y-1">
            <div class="flex items-center justify-between mb-2">
              <div class="w-10 h-10 bg-gradient-to-br from-pink-500 to-rose-600 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
              </div>
            </div>
            <div class="text-3xl font-bold bg-gradient-to-r from-pink-600 to-rose-600 bg-clip-text text-transparent">{{ number_format($this->stats['recent']) }}</div>
            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1 font-medium">New (30d)</div>
          </div>

          {{-- Roles --}}
          <div class="group bg-white/80 dark:bg-gray-800/80 backdrop-blur-md rounded-2xl p-6 border border-gray-200 dark:border-gray-700 hover:border-orange-500 dark:hover:border-orange-400 transition-all duration-300 hover:shadow-xl hover:shadow-orange-500/20 hover:-translate-y-1">
            <div class="flex items-center justify-between mb-2">
              <div class="w-10 h-10 bg-gradient-to-br from-orange-500 to-amber-600 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
              </div>
            </div>
            <div class="text-3xl font-bold bg-gradient-to-r from-orange-600 to-amber-600 bg-clip-text text-transparent">{{ number_format($this->stats['roles']) }}</div>
            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1 font-medium">Roles</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- Analytics Section --}}
  <section class="py-16 bg-white/50 dark:bg-gray-900/50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-12">
        <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
          Community Analytics
        </h2>
        <p class="text-lg text-gray-600 dark:text-gray-400">
          Insights into our growing community
        </p>
      </div>

      <div class="grid md:grid-cols-2 gap-8 mb-8">
        {{-- Role Distribution --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-200 dark:border-gray-700">
          <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6 flex items-center">
            <span class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center mr-3">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
              </svg>
            </span>
            Role Distribution
          </h3>
          <div class="space-y-4">
            @foreach($this->roleDistribution as $role)
              <div>
                <div class="flex items-center justify-between mb-2">
                  <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ ucfirst($role['name']) }}</span>
                  <span class="text-sm font-bold text-gray-900 dark:text-white">{{ $role['count'] }} ({{ $role['percentage'] }}%)</span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                  <div class="bg-gradient-to-r from-indigo-600 to-purple-600 h-2 rounded-full transition-all duration-500" style="width: {{ $role['percentage'] }}%"></div>
                </div>
              </div>
            @endforeach
          </div>
        </div>

        {{-- Member Growth --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-200 dark:border-gray-700">
          <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6 flex items-center">
            <span class="w-10 h-10 bg-gradient-to-br from-pink-500 to-orange-600 rounded-lg flex items-center justify-center mr-3">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
              </svg>
            </span>
            Member Growth (6 months)
          </h3>
          <div class="flex items-end justify-between h-48 gap-2">
            @foreach($this->memberGrowth as $month)
              @php
                $maxCount = $this->memberGrowth->max('count');
                $height = $maxCount > 0 ? ($month['count'] / $maxCount) * 100 : 0;
              @endphp
              <div class="flex-1 flex flex-col items-center gap-2">
                <div class="relative w-full bg-gradient-to-t from-indigo-600 to-purple-600 rounded-t-lg transition-all duration-500 hover:from-indigo-700 hover:to-purple-700" style="height: {{ $height }}%">
                  <div class="absolute -top-6 left-1/2 -translate-x-1/2 text-xs font-bold text-gray-900 dark:text-white whitespace-nowrap">
                    {{ $month['count'] }}
                  </div>
                </div>
                <span class="text-xs font-medium text-gray-600 dark:text-gray-400">{{ $month['month'] }}</span>
              </div>
            @endforeach
          </div>
        </div>
      </div>

      {{-- Top Contributors --}}
      @if($this->topContributors->isNotEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 border border-gray-200 dark:border-gray-700 shadow-lg hover:shadow-xl transition-shadow duration-300">
          <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-8 flex items-center">
            <span class="w-10 h-10 bg-gradient-to-br from-yellow-500 to-orange-600 rounded-lg flex items-center justify-center mr-3 shadow-lg shadow-yellow-500/30">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
              </svg>
            </span>
            Top Contributors
            <span class="ml-auto text-sm font-normal text-gray-500 dark:text-gray-400">Most Active Members</span>
          </h3>
          <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-6">
            @foreach($this->topContributors as $contributor)
              <a wire:navigate href="{{ route('web.user', $contributor->id) }}" class="text-center group">
                <div class="relative inline-block mb-3">
                  {{-- Avatar with Medal Background --}}
                  <div class="relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-yellow-400/20 to-orange-500/20 rounded-full scale-110 group-hover:scale-125 transition-transform duration-300"></div>
                    <img
                      src="{{ $contributor->avatar_url }}"
                      alt="{{ $contributor->name }}"
                      class="relative w-20 h-20 rounded-full object-cover border-4 border-gray-100 dark:border-gray-700 group-hover:border-yellow-400 dark:group-hover:border-yellow-500 transition-all duration-300 shadow-lg group-hover:shadow-xl group-hover:shadow-yellow-500/30"
                    >
                  </div>
                  
                  {{-- Rank Badge --}}
                  <div class="absolute -bottom-2 left-1/2 -translate-x-1/2 px-2.5 py-1 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-full flex items-center justify-center text-white text-xs font-bold border-2 border-white dark:border-gray-800 shadow-lg group-hover:scale-110 transition-transform duration-300">
                    @if($loop->iteration === 1)
                      🥇
                    @elseif($loop->iteration === 2)
                      🥈
                    @elseif($loop->iteration === 3)
                      🥉
                    @else
                      #{{ $loop->iteration }}
                    @endif
                  </div>
                </div>
                <p class="text-sm font-semibold text-gray-900 dark:text-white truncate group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                  {{ $contributor->name }}
                </p>
                <div class="flex items-center justify-center gap-1 mt-1">
                  <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                  </svg>
                  <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $contributor->posts_count }}</p>
                </div>
              </a>
            @endforeach
          </div>
        </div>
      @endif
    </div>
  </section>

  {{-- Main Content --}}
  <section class="py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-12">
        <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
          All Members
        </h2>
        <p class="text-lg text-gray-600 dark:text-gray-400">
          Browse and connect with our community
        </p>
      </div>

      {{-- Search & Filters --}}
      <div class="mb-8 space-y-6">
        {{-- Enhanced Search Bar --}}
        <div class="relative max-w-3xl mx-auto group">
          <div class="absolute inset-0 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-2xl opacity-0 group-hover:opacity-10 blur transition-opacity duration-300"></div>
          <div class="relative">
            <input
              type="text"
              wire:model.live.debounce.500ms="search"
              placeholder="Search members by name or email..."
              class="w-full px-6 py-5 pl-14 pr-14 bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-2xl focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 dark:focus:border-indigo-400 transition-all duration-300 shadow-lg hover:shadow-xl text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 text-lg"
            >
            {{-- Search Icon --}}
            <div class="absolute left-5 top-1/2 -translate-y-1/2">
              <svg class="w-6 h-6 text-gray-400 group-hover:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
              </svg>
            </div>
            
            {{-- Clear Button or Loading --}}
            <div class="absolute right-5 top-1/2 -translate-y-1/2">
              @if($search)
                <button
                  wire:click="$set('search', '')"
                  class="text-gray-400 hover:text-red-500 dark:hover:text-red-400 transition-colors p-1 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20"
                  title="Clear search"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                </button>
              @else
                <div class="text-xs text-gray-400 font-medium px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded">
                  ⌘K
                </div>
              @endif
            </div>
            
            {{-- Loading Indicator --}}
            <div wire:loading wire:target="search" class="absolute right-16 top-1/2 -translate-y-1/2">
              <svg class="animate-spin h-5 w-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            </div>
          </div>
        </div>

        {{-- Filter Bar --}}
        <div class="flex flex-wrap gap-4 items-center justify-between">
          {{-- Role Filters --}}
          <div class="flex flex-wrap gap-2 justify-center flex-1">
            <button
              wire:click="$set('roleFilter', null)"
              @class([
                'px-4 py-2 rounded-lg text-sm font-medium transition',
                'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-500/50' => !$roleFilter,
                'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:border-indigo-500' => $roleFilter,
              ])
            >
              All Roles
            </button>

            @foreach($this->availableRoles->take(5) as $role)
              <button
                wire:click="$set('roleFilter', '{{ $role->name }}')"
                @class([
                  'px-4 py-2 rounded-lg text-sm font-medium transition',
                  'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-500/50' => $roleFilter === $role->name,
                  'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:border-indigo-500' => $roleFilter !== $role->name,
                ])
              >
                {{ ucfirst($role->name) }} ({{ $role->users_count }})
              </button>
            @endforeach

            @if($this->availableRoles->count() > 5)
              <button
                wire:click="$toggle('showFilters')"
                class="px-4 py-2 rounded-lg text-sm font-medium bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:border-indigo-500 transition"
              >
                {{ $showFilters ? 'Less' : 'More' }} Roles
              </button>
            @endif
          </div>

          {{-- View Controls --}}
          <div class="flex items-center gap-4">
            {{-- Sort Options --}}
            <div class="flex items-center gap-2">
              <span class="text-sm text-gray-600 dark:text-gray-400">Sort:</span>
              <select
                wire:model.live="sortBy"
                class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
              >
                <option value="latest">Latest</option>
                <option value="name">Name</option>
                <option value="oldest">Oldest</option>
              </select>
            </div>

            {{-- View Mode Toggle --}}
            <button
              wire:click="toggleViewMode"
              class="p-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:border-indigo-500 transition"
              title="Toggle view mode"
            >
              @if($viewMode === 'grid')
                <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
              @else
                <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
              @endif
            </button>
          </div>
        </div>

        {{-- More Roles (Expandable) --}}
        @if($showFilters && $this->availableRoles->count() > 5)
          <div class="flex flex-wrap gap-2 pt-4 border-t border-gray-200 dark:border-gray-700 justify-center">
            @foreach($this->availableRoles->skip(5) as $role)
              <button
                wire:click="$set('roleFilter', '{{ $role->name }}')"
                @class([
                  'px-4 py-2 rounded-lg text-sm font-medium transition',
                  'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-500/50' => $roleFilter === $role->name,
                  'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 hover:border-indigo-500' => $roleFilter !== $role->name,
                ])
              >
                {{ ucfirst($role->name) }} ({{ $role->users_count }})
              </button>
            @endforeach
          </div>
        @endif

        {{-- Active Filters --}}
        @if($search || $roleFilter)
          <div class="flex items-center gap-2 pt-2 justify-center">
            <span class="text-sm text-gray-600 dark:text-gray-400">Active filters:</span>
            @if($search)
              <span class="inline-flex items-center gap-2 px-3 py-1 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 text-sm rounded-lg">
                Search: "{{ $search }}"
                <button wire:click="$set('search', '')" class="hover:text-indigo-900 dark:hover:text-indigo-100">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                </button>
              </span>
            @endif
            @if($roleFilter)
              <span class="inline-flex items-center gap-2 px-3 py-1 bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 text-sm rounded-lg">
                Role: {{ ucfirst($roleFilter) }}
                <button wire:click="$set('roleFilter', null)" class="hover:text-purple-900 dark:hover:text-purple-100">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                </button>
              </span>
            @endif
            <button
              wire:click="resetFilters"
              class="text-sm text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 underline"
            >
              Clear all
            </button>
          </div>
        @endif
      </div>

      {{-- Users Grid/List --}}
      <div wire:loading.class="opacity-50 pointer-events-none" class="transition-opacity duration-300">
        <div class="grid md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-12">
          @forelse($this->users as $user)
            <article
              wire:key="user-{{ $user->id }}"
              class="group bg-white dark:bg-gray-800 rounded-2xl overflow-hidden border border-gray-200 dark:border-gray-700 hover:border-indigo-500 dark:hover:border-indigo-500 transition-all duration-300 hover:shadow-2xl hover:shadow-indigo-500/20 hover:-translate-y-2"
            >
              <a wire:navigate href="{{ route('web.user', $user->id) }}" class="block">
                {{-- User Banner with Avatar --}}
                <div class="relative h-32 bg-gradient-to-br from-indigo-500 to-purple-600 overflow-hidden">
                  @if($user->banner_url)
                    <img src="{{ $user->banner_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                  @else
                    <div class="w-full h-full flex items-center justify-center text-white text-4xl">
                      @php
                        $emojis = ['💪', '🏃', '🧘', '🥗', '🏋️', '🥇', '❤️', '🌟'];
                        echo $emojis[array_rand($emojis)];
                      @endphp
                    </div>
                  @endif
                  <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>

                  {{-- Avatar with Status --}}
                  <div class="absolute -bottom-12 left-1/2 -translate-x-1/2">
                    <div class="relative">
                      <img
                        src="{{ $user->avatar_url }}"
                        alt="{{ $user->name }}"
                        class="w-24 h-24 rounded-full object-cover border-4 border-white dark:border-gray-800 group-hover:border-indigo-500 dark:group-hover:border-indigo-400 transition-all duration-300 shadow-xl"
                      >
                      {{-- Online Status Indicator --}}
                      <div class="absolute bottom-2 right-2 w-5 h-5 bg-green-500 border-4 border-white dark:border-gray-800 rounded-full animate-pulse"></div>
                      
                      {{-- Verification Badge --}}
                      @if($user->email_verified_at)
                        <div class="absolute -top-1 -right-1 w-7 h-7 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-full flex items-center justify-center border-2 border-white dark:border-gray-800 shadow-lg" title="Verified Member">
                          <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                          </svg>
                        </div>
                      @endif
                    </div>
                  </div>
                </div>

                {{-- User Content --}}
                <div class="pt-14 px-6 pb-6">
                  {{-- Name and Email --}}
                  <div class="text-center mb-4">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition truncate">
                      {{ $user->name }}
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 truncate">
                      {{ $user->email }}
                    </p>
                  </div>

                  {{-- Stats Row --}}
                  @if($user->posts_count > 0)
                    <div class="flex items-center justify-center gap-4 mb-4 pb-4 border-b border-gray-200 dark:border-gray-700">
                      <div class="text-center">
                        <div class="text-lg font-bold text-gray-900 dark:text-white">{{ $user->posts_count }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Posts</div>
                      </div>
                      <div class="w-px h-8 bg-gray-200 dark:bg-gray-700"></div>
                      <div class="text-center">
                        <div class="text-lg font-bold text-gray-900 dark:text-white">{{ number_format($user->posts->sum('views_count') ?? 0) }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Views</div>
                      </div>
                    </div>
                  @endif

                  {{-- Roles --}}
                  @if($user->roles->isNotEmpty())
                    <div class="flex flex-wrap gap-1.5 justify-center mb-4">
                      @foreach($user->roles->take(2) as $role)
                        <span class="px-3 py-1 text-xs font-semibold bg-gradient-to-r from-indigo-500/10 to-purple-500/10 text-indigo-600 dark:text-indigo-400 rounded-full border border-indigo-200 dark:border-indigo-800">
                          {{ ucfirst($role->name) }}
                        </span>
                      @endforeach
                      @if($user->roles->count() > 2)
                        <span class="px-3 py-1 text-xs font-semibold bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 rounded-full border border-gray-200 dark:border-gray-600">
                          +{{ $user->roles->count() - 2 }}
                        </span>
                      @endif
                    </div>
                  @endif

                  {{-- Member Since --}}
                  <div class="flex items-center justify-center text-gray-500 dark:text-gray-400 text-xs">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span>Joined {{ $user->created_at->diffForHumans() }}</span>
                  </div>
                </div>
              </a>
            </article>
          @empty
            {{-- Enhanced Empty State --}}
            <div class="col-span-full text-center py-20">
              <div class="max-w-md mx-auto">
                {{-- Animated Icon --}}
                <div class="relative inline-flex items-center justify-center w-24 h-24 mb-6">
                  <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/20 to-purple-500/20 rounded-full animate-pulse"></div>
                  <div class="relative w-20 h-20 bg-gradient-to-br from-indigo-100 to-purple-100 dark:from-indigo-900/30 dark:to-purple-900/30 rounded-full flex items-center justify-center">
                    <svg class="w-10 h-10 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                  </div>
                </div>
                
                {{-- Message --}}
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">No members found</h3>
                <p class="text-gray-600 dark:text-gray-400 mb-6 text-lg">
                  @if($search || $roleFilter)
                    We couldn't find any members matching your criteria.
                  @else
                    The community is just getting started.
                  @endif
                </p>
                
                {{-- Actions --}}
                @if($search || $roleFilter)
                  <button
                    wire:click="resetFilters"
                    class="inline-flex items-center gap-2 px-8 py-4 text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all duration-300 shadow-lg shadow-indigo-500/50 hover:shadow-xl hover:shadow-indigo-500/60 hover:-translate-y-0.5"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Clear All Filters
                  </button>
                @endif
              </div>
            </div>
          @endforelse
        </div>

        {{-- Loading Overlay --}}
        <div wire:loading class="fixed inset-0 bg-black/20 backdrop-blur-sm z-50 flex items-center justify-center">
          <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-2xl border border-gray-200 dark:border-gray-700">
            <div class="flex flex-col items-center gap-4">
              <div class="relative w-16 h-16">
                <div class="absolute inset-0 border-4 border-indigo-200 dark:border-indigo-900 rounded-full"></div>
                <div class="absolute inset-0 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
              </div>
              <p class="text-gray-700 dark:text-gray-300 font-medium">Loading members...</p>
            </div>
          </div>
        </div>
      </div>

      {{-- Pagination --}}
      @if($this->users->isNotEmpty())
        <div class="mt-12">
          {{ $this->users->onEachSide(1)->links() }}
        </div>
      @endif
    </div>
  </section>

  {{-- Add Custom Animations --}}
  <style>
    @keyframes fade-in-up {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    .animate-fade-in-up {
      animation: fade-in-up 0.6s ease-out forwards;
    }
  </style>

</div>