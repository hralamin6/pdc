<div class="min-h-screen bg-slate-50 dark:bg-slate-950 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        
        {{-- Header & Sub-navigation --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-slate-200 dark:border-slate-800 pb-5">
            <div>
                <h1 class="text-3xl font-black text-slate-800 dark:text-slate-100 tracking-tight">{{ __('Leaderboard') }}</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ __('See how you measure up against other members of the community.') }}</p>
            </div>
            
            <div class="flex items-center gap-2 bg-slate-100 dark:bg-slate-900 p-1.5 rounded-xl self-start md:self-auto border border-slate-200/60 dark:border-slate-800/60">
                <a href="{{ route('web.quizzes') }}" wire:navigate class="px-4 py-2 rounded-lg text-xs font-bold text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white transition-colors">
                    <span class="flex items-center gap-1.5">
                        <x-icon name="o-academic-cap" class="w-4 h-4" /> {{ __('Quizzes') }}
                    </span>
                </a>
                <a href="{{ route('web.quizzes.leaderboard') }}" wire:navigate class="px-4 py-2 rounded-lg text-xs font-bold bg-white dark:bg-slate-800 text-primary shadow-sm border border-slate-200/40 dark:border-slate-700/40">
                    <span class="flex items-center gap-1.5">
                        <x-icon name="o-trophy" class="w-4 h-4" /> {{ __('Leaderboard') }}
                    </span>
                </a>
                <a href="{{ route('web.quizzes.history') }}" wire:navigate class="px-4 py-2 rounded-lg text-xs font-bold text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white transition-colors">
                    <span class="flex items-center gap-1.5">
                        <x-icon name="o-clock" class="w-4 h-4" /> {{ __('My History') }}
                    </span>
                </a>
                <a href="{{ route('web.my-quizzes') }}" wire:navigate class="px-4 py-2 rounded-lg text-xs font-bold text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white transition-colors">
                    <span class="flex items-center gap-1.5">
                        <x-icon name="o-chart-bar" class="w-4 h-4" /> {{ __('Dashboard') }}
                    </span>
                </a>
            </div>
        </div>

        {{-- Podium Section (Top 3 Users) --}}
        @if($podium->isNotEmpty())
            <div class="grid grid-cols-3 items-end gap-2 sm:gap-6 bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800/60 rounded-3xl p-6 shadow-sm max-w-2xl mx-auto pt-12 relative overflow-hidden">
                <div class="absolute inset-0 opacity-[0.02] bg-[radial-gradient(#000_1px,transparent_1px)] dark:bg-[radial-gradient(#fff_1px,transparent_1px)] [background-size:16px_16px] pointer-events-none"></div>

                {{-- Rank 2 (Left) --}}
                @if($podium->count() >= 2)
                    @php $p2 = $podium[1]; @endphp
                    <div class="flex flex-col items-center">
                        <div class="relative group">
                            <div class="w-14 h-14 sm:w-20 sm:h-20 rounded-full border-4 border-slate-200 bg-slate-100 flex items-center justify-center overflow-hidden shadow-md">
                                <img src="{{ $p2->avatar_url }}" alt="{{ $p2->name }}" class="w-full h-full object-cover">
                            </div>
                            <span class="absolute -bottom-2 left-1/2 -translate-x-1/2 w-6 h-6 rounded-full bg-slate-350 text-white font-black text-xs flex items-center justify-center border-2 border-white dark:border-slate-900 shadow">2</span>
                        </div>
                        <p class="text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-200 mt-4 text-center truncate max-w-full px-1">{{ $p2->name }}</p>
                        <span class="text-[10px] sm:text-xs font-black text-slate-400 mt-1">{{ number_format($p2->gamification_points) }} {{ __('pts') }}</span>
                        
                        {{-- Pod column --}}
                        <div class="w-full h-24 sm:h-32 bg-slate-100 dark:bg-slate-800/50 border border-slate-200/60 dark:border-slate-800 rounded-t-2xl mt-4 flex items-end justify-center pb-2">
                            <span class="text-2xl font-black text-slate-300 dark:text-slate-700">🥈</span>
                        </div>
                    </div>
                @else
                    <div></div>
                @endif

                {{-- Rank 1 (Center) --}}
                @if($podium->count() >= 1)
                    @php $p1 = $podium[0]; @endphp
                    <div class="flex flex-col items-center z-10">
                        <div class="relative group">
                            <div class="absolute -inset-1 bg-gradient-to-r from-amber-400 to-amber-500 rounded-full blur-sm opacity-40 group-hover:opacity-75 transition-opacity pointer-events-none"></div>
                            <div class="w-16 h-16 sm:w-24 sm:h-24 rounded-full border-4 border-amber-400 bg-amber-50 flex items-center justify-center overflow-hidden shadow-lg relative">
                                <img src="{{ $p1->avatar_url }}" alt="{{ $p1->name }}" class="w-full h-full object-cover">
                            </div>
                            <span class="absolute -bottom-2 left-1/2 -translate-x-1/2 w-7 h-7 rounded-full bg-amber-400 text-white font-black text-sm flex items-center justify-center border-2 border-white dark:border-slate-900 shadow">1</span>
                        </div>
                        <p class="text-sm sm:text-base font-bold text-slate-800 dark:text-slate-100 mt-4 text-center truncate max-w-full px-1">{{ $p1->name }}</p>
                        <span class="text-xs font-black text-amber-500 mt-1">{{ number_format($p1->gamification_points) }} {{ __('pts') }}</span>
                        
                        {{-- Pod column --}}
                        <div class="w-full h-32 sm:h-44 bg-gradient-to-t from-amber-50/50 to-amber-100/40 dark:from-amber-950/10 dark:to-amber-950/20 border border-amber-200/50 dark:border-amber-900/20 rounded-t-2xl mt-4 flex items-end justify-center pb-2">
                            <span class="text-3xl font-black">🥇</span>
                        </div>
                    </div>
                @endif

                {{-- Rank 3 (Right) --}}
                @if($podium->count() >= 3)
                    @php $p3 = $podium[2]; @endphp
                    <div class="flex flex-col items-center">
                        <div class="relative group">
                            <div class="w-14 h-14 sm:w-20 sm:h-20 rounded-full border-4 border-amber-600/30 bg-orange-50 flex items-center justify-center overflow-hidden shadow-md">
                                <img src="{{ $p3->avatar_url }}" alt="{{ $p3->name }}" class="w-full h-full object-cover">
                            </div>
                            <span class="absolute -bottom-2 left-1/2 -translate-x-1/2 w-6 h-6 rounded-full bg-amber-700 text-white font-black text-xs flex items-center justify-center border-2 border-white dark:border-slate-900 shadow">3</span>
                        </div>
                        <p class="text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-200 mt-4 text-center truncate max-w-full px-1">{{ $p3->name }}</p>
                        <span class="text-[10px] sm:text-xs font-black text-slate-400 mt-1">{{ number_format($p3->gamification_points) }} {{ __('pts') }}</span>
                        
                        {{-- Pod column --}}
                        <div class="w-full h-20 sm:h-24 bg-slate-100 dark:bg-slate-800/50 border border-slate-200/60 dark:border-slate-800 rounded-t-2xl mt-4 flex items-end justify-center pb-2">
                            <span class="text-2xl font-black text-amber-700/60">🥉</span>
                        </div>
                    </div>
                @else
                    <div></div>
                @endif

            </div>
        @endif

        {{-- Leaderboard Table Section --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800/60 rounded-3xl p-5 shadow-sm space-y-4">
            
            <div class="flex justify-between items-center">
                <h3 class="text-base font-bold text-slate-800 dark:text-slate-100">{{ __('All Rankings') }}</h3>
                
                <div class="relative w-64">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <x-icon name="o-magnifying-glass" class="w-4 h-4 text-slate-400" />
                    </span>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="{{ __('Search ranking by name...') }}"
                        class="w-full pl-9 pr-4 py-1.5 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary text-slate-700 dark:text-slate-300">
                </div>
            </div>

            @if($leaderboard->isEmpty())
                <div class="text-center py-10 text-xs text-slate-400">
                    {{ __('No participants found.') }}
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="table table-compact w-full text-slate-700 dark:text-slate-300">
                        <thead>
                            <tr class="border-b border-slate-100 dark:border-slate-800 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                <th class="py-3 px-4 w-12 text-center">{{ __('Rank') }}</th>
                                <th class="py-3 px-4">{{ __('User') }}</th>
                                <th class="py-3 px-4 text-right">{{ __('Points Earned') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($leaderboard as $user)
                                @php $isMe = $user->id === auth()->id(); @endphp
                                <tr class="border-b border-slate-100 dark:border-slate-800/60 transition-colors {{ $isMe ? 'bg-primary/5 dark:bg-primary/10 font-bold' : 'hover:bg-slate-50/50 dark:hover:bg-slate-850/50' }}">
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full font-black text-xs 
                                            {{ $user->rank === 1 ? 'bg-amber-100 text-amber-600 dark:bg-amber-950 dark:text-amber-400' : '' }}
                                            {{ $user->rank === 2 ? 'bg-slate-100 text-slate-600 dark:bg-slate-850 dark:text-slate-400' : '' }}
                                            {{ $user->rank === 3 ? 'bg-orange-50 text-orange-600 dark:bg-orange-950 dark:text-orange-400' : '' }}
                                            {{ $user->rank > 3 ? 'text-slate-400' : '' }}">
                                            #{{ $user->rank }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full overflow-hidden bg-slate-100 border border-slate-200 dark:border-slate-700 flex-shrink-0">
                                                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                                            </div>
                                            <div class="min-w-0">
                                                <span class="block text-sm truncate {{ $isMe ? 'text-primary' : 'text-slate-800 dark:text-slate-200' }}">
                                                    {{ $user->name }}
                                                    @if($isMe) <span class="text-[10px] font-bold text-primary bg-primary/10 px-1.5 py-0.5 rounded ml-1">{{ __('You') }}</span> @endif
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-mono font-black text-sm text-slate-800 dark:text-slate-200">
                                        {{ number_format($user->gamification_points) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination Links --}}
                <div class="mt-4">
                    {{ $leaderboard->links() }}
                </div>
            @endif

        </div>

    </div>
</div>
