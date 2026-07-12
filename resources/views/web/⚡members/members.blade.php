<div>
  {{-- Hero Section --}}
  <section class="relative overflow-hidden py-16 lg:py-24 bg-slate-900 border-b-2 border-slate-800">
    {{-- Animated Background --}}
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-[500px] h-[500px] bg-cyan-500/10 rounded-full blur-[100px]"></div>
        <div class="absolute top-40 -left-40 w-[400px] h-[400px] bg-fuchsia-500/10 rounded-full blur-[80px]"></div>
    </div>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
      <div class="text-center">
        <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-white/5 rounded-full text-xs font-black uppercase tracking-widest text-cyan-400 mb-6 border border-white/10 shadow-lg">
            <x-icon name="o-users" class="w-4 h-4" />
            {{ __('Guild Roster') }}
        </div>
        <h1 class="text-4xl md:text-6xl font-black mb-6 text-white tracking-tight">
          PSTU Dawah <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-fuchsia-400">{{ __('Community') }}</span>
        </h1>
        <p class="text-lg text-slate-400 mb-10 max-w-2xl mx-auto font-medium">
          {{ __('Meet the dedicated seekers, mentors, and scholars leveling up their Deen and brotherhood together.') }}
        </p>

        {{-- Top Contributors Spotlight --}}
        @if($this->topContributors->isNotEmpty())
        <div class="flex flex-wrap items-center justify-center gap-4 text-sm text-slate-300">
          <div class="flex -space-x-4 mr-2">
            @foreach($this->topContributors->take(5) as $contributor)
              <a href="{{ route('web.user', $contributor->id) }}" wire:navigate class="w-12 h-12 rounded-full border-2 border-slate-900 bg-slate-800 flex items-center justify-center overflow-hidden hover:-translate-y-1 hover:z-20 transition-transform shadow-lg" title="{{ $contributor->name }} - XP: {{ $contributor->gamification_points }}">
                <img src="{{ $contributor->avatar_url }}" alt="{{ $contributor->name }}" class="w-full h-full object-cover">
              </a>
            @endforeach
          </div>
          <span class="font-bold text-white bg-white/10 px-4 py-2 rounded-full border border-white/10 backdrop-blur-sm">
            {{ __('Join') }} {{ number_format($this->stats['total']) }}+ {{ __('Active Players') }}
          </span>
        </div>
        @endif
      </div>
    </div>
  </section>

  {{-- Filters and Search Bar --}}
  <section class="py-8 bg-slate-50 dark:bg-slate-950 border-b border-slate-200 dark:border-slate-800/50 sticky top-0 z-40 backdrop-blur-xl bg-slate-50/80 dark:bg-slate-950/80">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col lg:flex-row gap-4 items-center justify-between">
            
            {{-- Search --}}
            <div class="relative w-full lg:w-96 group">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="{{ __('Search players by name...') }}"
                    class="w-full px-5 py-3 pl-12 bg-white dark:bg-slate-900 border-2 border-slate-200 dark:border-slate-700 rounded-2xl focus:ring-4 focus:ring-cyan-500/20 focus:border-cyan-500 dark:focus:border-cyan-400 transition-all font-medium text-slate-900 dark:text-white"
                >
                <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-hover:text-cyan-500 transition-colors">
                    <x-icon name="o-magnifying-glass" class="w-5 h-5" />
                </div>
                @if($search)
                    <button wire:click="$set('search', '')" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-rose-500 transition-colors">
                        <x-icon name="o-x-mark" class="w-5 h-5" />
                    </button>
                @endif
                <div wire:loading wire:target="search" class="absolute right-12 top-1/2 -translate-y-1/2">
                    <x-icon name="o-arrow-path" class="w-5 h-5 animate-spin text-cyan-500" />
                </div>
            </div>

            {{-- Controls --}}
            <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
                <select wire:model.live="roleFilter" class="px-4 py-3 bg-white dark:bg-slate-900 border-2 border-slate-200 dark:border-slate-700 rounded-2xl font-bold text-sm focus:ring-4 focus:ring-cyan-500/20 focus:border-cyan-500">
                    <option value="">{{ __('All Roles') }}</option>
                    @foreach($this->availableRoles as $role)
                        <option value="{{ $role->name }}">{{ ucfirst($role->name) }} ({{ $role->users_count }})</option>
                    @endforeach
                </select>

                <select wire:model.live="sortBy" class="px-4 py-3 bg-white dark:bg-slate-900 border-2 border-slate-200 dark:border-slate-700 rounded-2xl font-bold text-sm focus:ring-4 focus:ring-cyan-500/20 focus:border-cyan-500">
                    <option value="xp">{{ __('Highest Rank (XP)') }}</option>
                    <option value="latest">{{ __('Newest Recruits') }}</option>
                    <option value="oldest">{{ __('Veterans (Oldest)') }}</option>
                    <option value="name">{{ __('Alphabetical') }}</option>
                </select>

                <div class="bg-slate-200 dark:bg-slate-800 p-1 rounded-xl flex gap-1 border border-slate-300 dark:border-slate-700">
                    <button wire:click="$set('viewMode', 'grid')" class="p-2 rounded-lg {{ $viewMode === 'grid' ? 'bg-white dark:bg-slate-700 shadow-sm text-cyan-600 dark:text-cyan-400' : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300' }}">
                        <x-icon name="o-squares-2x2" class="w-5 h-5" />
                    </button>
                    <button wire:click="$set('viewMode', 'list')" class="p-2 rounded-lg {{ $viewMode === 'list' ? 'bg-white dark:bg-slate-700 shadow-sm text-cyan-600 dark:text-cyan-400' : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300' }}">
                        <x-icon name="o-list-bullet" class="w-5 h-5" />
                    </button>
                </div>
            </div>
        </div>
    </div>
  </section>

  {{-- Main Content Grid --}}
  <section class="py-12 bg-slate-50 dark:bg-slate-950 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      
      <div wire:loading.class="opacity-50 pointer-events-none" class="transition-opacity duration-300">
        @if($this->users->isEmpty())
            <div class="text-center py-32 bg-white dark:bg-slate-900 rounded-[3rem] border-2 border-dashed border-slate-300 dark:border-slate-800">
                <div class="w-24 h-24 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-6">
                    <x-icon name="o-users" class="w-12 h-12 text-slate-400 dark:text-slate-500" />
                </div>
                <h3 class="text-3xl font-black text-slate-900 dark:text-white mb-2">{{ __('No players found') }}</h3>
                <p class="text-slate-500 dark:text-slate-400 font-medium mb-6 text-lg">
                    {{ __('Try adjusting your filters or search query.') }}
                </p>
                @if($search || $roleFilter)
                    <button wire:click="resetFilters" class="btn btn-primary rounded-xl font-bold">
                        <x-icon name="o-arrow-path" class="w-4 h-4 mr-2" /> {{ __('Reset All Filters') }}
                    </button>
                @endif
            </div>
        @else
            @if($viewMode === 'grid')
                {{-- BENTO GRID VIEW --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($this->users as $user)
                    <div wire:key="user-{{ $user->id }}" class="group relative bg-white dark:bg-slate-900 rounded-[2rem] overflow-hidden border-2 border-slate-200 dark:border-slate-800 hover:border-cyan-500 dark:hover:border-cyan-500 shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all duration-300 flex flex-col">
                        
                        {{-- Banner Area --}}
                        <div class="h-32 bg-slate-200 dark:bg-slate-800 relative overflow-hidden">
                            @if($user->banner_url)
                                <img src="{{ $user->banner_url }}" class="w-full h-full object-cover opacity-80 group-hover:scale-110 transition-transform duration-700" alt="{{ __('Banner') }}">
                            @else
                                <div class="absolute inset-0 bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-800 dark:to-slate-900">
                                    <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiM4YjViZjYiIGZpbGwtb3BhY2l0eT0iMC4wNSI+PHBhdGggZD0iTTM2IDE0YzMuMzE0IDAgNiAyLjY4NiA2IDZzLTIuNjg2IDYtNiA2LTYtMi42ODYtNi02IDIuNjg2LTYgNi02ek0wIDIwYzMuMzE0IDAgNiAyLjY4NiA2IDZzLTIuNjg2IDYtNiA2djhsMTIgMTJoOHYtOGMzLjMxNCAwIDYgMi42ODYgNiA2czIuNjg2IDYgNiA2aDh2LThoLTZ2LTZoNnYtNmgtNnYtNmg2di02aC02di02aDZ2LTZoLTZ2LTZoNlYwSDM2djZoLTZ2Nmg2djZoLTZ2Nmg2djZoLTZ2Nmg2djZoLTZ2Nmg2djZ6Ii8+PC9nPjwvZz48L3N2Zz4=')] opacity-20 dark:opacity-10"></div>
                                </div>
                            @endif
                            <div class="absolute inset-0 bg-gradient-to-t from-white dark:from-slate-900 to-transparent"></div>
                        </div>

                        {{-- Avatar Profile --}}
                        <div class="relative px-6 flex justify-between items-end -mt-12 mb-4">
                            <div class="relative group/avatar cursor-pointer">
                                <a href="{{ route('web.user', $user->id) }}" wire:navigate>
                                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-24 h-24 rounded-full border-4 border-white dark:border-slate-900 bg-white dark:bg-slate-900 object-cover shadow-lg relative z-10 group-hover/avatar:scale-105 transition-transform">
                                </a>
                                {{-- Rank Glowing effect back of avatar --}}
                                <div class="absolute inset-0 rounded-full bg-cyan-400 blur-md opacity-0 group-hover:opacity-40 transition-opacity"></div>
                                
                                @if($user->email_verified_at)
                                <div class="absolute bottom-1 right-1 bg-cyan-500 text-white rounded-full p-1 border-2 border-white dark:border-slate-900 shadow-sm z-20 tooltip tooltip-right" data-tip="{{ __('Verified Account') }}">
                                    <x-icon name="o-check-badge" class="w-3.5 h-3.5" />
                                </div>
                                @endif
                            </div>
                            
                            {{-- XP Badge --}}
                            <div class="bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800 px-3 py-1 rounded-xl shadow-sm flex flex-col items-center">
                                <span class="text-[10px] font-black uppercase tracking-widest">{{ __('Rank XP') }}</span>
                                <span class="font-black text-lg leading-none">{{ number_format($user->gamification_points ?? 0) }}</span>
                            </div>
                        </div>

                        {{-- Info --}}
                        <div class="px-6 pb-8 flex-grow flex flex-col">
                            <a href="{{ route('web.user', $user->id) }}" wire:navigate class="block text-xl font-black text-slate-900 dark:text-white truncate hover:text-cyan-600 dark:hover:text-cyan-400 transition-colors">
                                {{ $user->name }}
                            </a>
                            <p class="text-sm font-bold text-slate-500 dark:text-slate-400 truncate mb-4">
                                {{ '@' . explode('@', $user->email)[0] }}
                            </p>
                            
                            <div class="flex flex-wrap gap-2 mb-4 mt-auto">
                                @forelse($user->roles->take(3) as $role)
                                    <span class="text-[10px] font-black uppercase tracking-widest px-2.5 py-1 rounded-md {{ $role->name === 'admin' || $role->name === 'super-admin' ? 'bg-fuchsia-100 text-fuchsia-700 dark:bg-fuchsia-900/30 dark:text-fuchsia-400' : ($role->name === 'mentor' ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400') }}">
                                        {{ ucfirst($role->name) }}
                                    </span>
                                @empty
                                    <span class="text-[10px] font-black uppercase tracking-widest px-2.5 py-1 rounded-md bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                                        {{ __('Seeker') }}
                                    </span>
                                @endforelse
                            </div>

                            <div class="flex gap-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                                <div class="flex items-center gap-1.5 text-slate-600 dark:text-slate-400">
                                    <x-icon name="o-document-text" class="w-4 h-4" />
                                    <span class="text-sm font-bold">{{ $user->posts_count }}</span>
                                </div>
                                <div class="flex items-center gap-1.5 text-slate-600 dark:text-slate-400" title="{{ __('Daily Reports Submitted') }}">
                                    <x-icon name="o-check-circle" class="w-4 h-4" />
                                    <span class="text-sm font-bold">{{ $user->daily_reports_count }}</span>
                                </div>
                                <div class="ml-auto text-xs font-bold text-slate-400 flex items-center">
                                    {{ __('Joined') }} {{ $user->created_at->format('M Y') }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
                </div>
            @else
                {{-- LIST VIEW --}}
                <div class="bg-white dark:bg-slate-900 rounded-[2rem] border-2 border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 text-xs uppercase tracking-widest border-b-2 border-slate-200 dark:border-slate-800">
                                    <th class="px-6 py-4 font-black">{{ __('Player') }}</th>
                                    <th class="px-6 py-4 font-black">{{ __('Roles') }}</th>
                                    <th class="px-6 py-4 font-black text-center">{{ __('XP / Level') }}</th>
                                    <th class="px-6 py-4 font-black text-center">{{ __('Stats') }}</th>
                                    <th class="px-6 py-4 font-black text-right">{{ __('Joined') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                @foreach($this->users as $user)
                                <tr wire:key="user-list-{{ $user->id }}" class="hover:bg-slate-50 dark:hover:bg-slate-800/20 transition-colors">
                                    <td class="px-6 py-4">
                                        <a href="{{ route('web.user', $user->id) }}" wire:navigate class="flex items-center gap-4">
                                            <div class="relative">
                                                <img src="{{ $user->avatar_url }}" class="w-12 h-12 rounded-full object-cover bg-slate-200 border border-slate-200 dark:border-slate-700">
                                                @if($user->email_verified_at)
                                                    <div class="absolute -bottom-1 -right-1 bg-cyan-500 text-white rounded-full p-0.5 border border-white dark:border-slate-900" title="{{ __('Verified Account') }}">
                                                        <x-icon name="o-check-badge" class="w-3 h-3" />
                                                    </div>
                                                @endif
                                            </div>
                                            <div>
                                                <div class="font-black text-slate-900 dark:text-white flex items-center gap-2">
                                                    {{ $user->name }}
                                                </div>
                                                <div class="text-sm font-bold text-slate-500 dark:text-slate-400">{{ '@' . explode('@', $user->email)[0] }}</div>
                                            </div>
                                        </a>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-1">
                                            @forelse($user->roles as $role)
                                                <span class="text-[9px] font-black uppercase tracking-widest px-2 py-0.5 rounded-md {{ $role->name === 'admin' || $role->name === 'super-admin' ? 'bg-fuchsia-100 text-fuchsia-700 dark:bg-fuchsia-900/30 dark:text-fuchsia-400' : ($role->name === 'mentor' ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400') }}">
                                                    {{ ucfirst($role->name) }}
                                                </span>
                                            @empty
                                                <span class="text-[9px] font-black uppercase tracking-widest px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">{{ __('Seeker') }}</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="inline-flex items-center gap-1.5 bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 px-3 py-1 rounded-lg border border-amber-200 dark:border-amber-800">
                                            <x-icon name="o-sparkles" class="w-4 h-4" />
                                            <span class="font-black">{{ number_format($user->gamification_points ?? 0) }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex justify-center gap-4 text-slate-500 dark:text-slate-400">
                                            <div class="flex items-center gap-1 tooltip" data-tip="{{ __('Posts') }}">
                                                <x-icon name="o-document-text" class="w-4 h-4" /> <span class="font-bold text-xs">{{ $user->posts_count }}</span>
                                            </div>
                                            <div class="flex items-center gap-1 tooltip" data-tip="{{ __('Daily Reports') }}">
                                                <x-icon name="o-check-circle" class="w-4 h-4" /> <span class="font-bold text-xs">{{ $user->daily_reports_count }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm font-bold text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                        {{ $user->created_at->format('M d, Y') }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <div class="mt-12">
                {{ $this->users->links() }}
            </div>
        @endif
      </div>
    </div>
  </section>
</div>