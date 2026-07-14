<div class="min-h-screen bg-slate-50 dark:bg-slate-950">

    {{-- ══════════════════════════════════════════ --}}
    {{-- HERO BANNER                                --}}
    {{-- ══════════════════════════════════════════ --}}
    <div class="relative h-64 md:h-80 overflow-hidden bg-slate-900">
        @if($user->banner_url)
            <img src="{{ $user->banner_url }}" class="w-full h-full object-cover opacity-60" alt="{{ __('Banner') }}">
        @else
            @php
                $gradients = [
                    'from-cyan-600 via-blue-700 to-indigo-900',
                    'from-fuchsia-600 via-purple-700 to-indigo-900',
                    'from-emerald-500 via-teal-600 to-cyan-900',
                    'from-rose-600 via-pink-700 to-purple-900',
                    'from-amber-500 via-orange-600 to-rose-900',
                ];
                $gradient = $gradients[$user->id % count($gradients)];
            @endphp
            <div class="absolute inset-0 bg-gradient-to-br {{ $gradient }}"></div>
            <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 20% 50%, white 1px, transparent 1px), radial-gradient(circle at 80% 20%, white 1px, transparent 1px); background-size: 40px 40px;"></div>
        @endif
        <div class="absolute inset-0 bg-gradient-to-t from-slate-50 dark:from-slate-950 to-transparent via-transparent" style="background: linear-gradient(to bottom, transparent 40%, var(--tw-gradient-to, #f8fafc) 100%)"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-slate-50/80 dark:from-slate-950/80 to-transparent"></div>

        {{-- Back button --}}
        <div class="absolute top-4 left-4">
            <a href="{{ route('web.members') }}" wire:navigate class="inline-flex items-center gap-2 px-4 py-2 bg-black/30 backdrop-blur-md text-white text-sm font-bold rounded-xl border border-white/20 hover:bg-black/50 transition-all">
                <x-icon name="o-arrow-left" class="w-4 h-4" /> {{ __('Roster') }}
            </a>
        </div>
    </div>

    {{-- ══════════════════════════════════════════ --}}
    {{-- PROFILE HEADER SECTION                    --}}
    {{-- ══════════════════════════════════════════ --}}
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 -mt-24 relative z-10">
        <div class="flex flex-col md:flex-row md:items-end gap-6 mb-8">

            {{-- Avatar --}}
            <div class="relative shrink-0">
                <div class="w-36 h-36 rounded-[2rem] border-4 border-white dark:border-slate-900 shadow-2xl overflow-hidden bg-slate-200 dark:bg-slate-800 ring-4 ring-cyan-500/30">
                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                </div>
                {{-- Level Badge --}}
                <div class="absolute -bottom-3 -right-3 bg-gradient-to-br from-amber-400 to-orange-500 text-white text-xs font-black px-2.5 py-1.5 rounded-xl shadow-lg border-2 border-white dark:border-slate-900">
                    {{ __('LVL') }} {{ $this->level }}
                </div>
            </div>

            {{-- Name / Roles / XP Bar --}}
            <div class="flex-1 min-w-0 pb-1">
                <div class="flex flex-wrap items-center gap-3 mb-2">
                    <h1 class="text-3xl md:text-4xl font-black text-slate-900 dark:text-white tracking-tight">
                        {{ $user->name }}
                    </h1>
                    @if($user->email_verified_at)
                        <span class="inline-flex items-center gap-1 bg-cyan-100 dark:bg-cyan-900/40 text-cyan-700 dark:text-cyan-400 text-[10px] font-black uppercase tracking-widest px-2.5 py-1 rounded-full border border-cyan-200 dark:border-cyan-800">
                            <x-icon name="o-check-badge" class="w-3.5 h-3.5" /> {{ __('Verified') }}
                        </span>
                    @endif
                </div>

                {{-- Roles --}}
                <div class="flex flex-wrap gap-2 mb-4">
                    @forelse($user->roles as $role)
                        <span class="text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full
                            {{ $role->name === 'super-admin' ? 'bg-fuchsia-100 text-fuchsia-700 dark:bg-fuchsia-900/30 dark:text-fuchsia-400 border border-fuchsia-200 dark:border-fuchsia-800' :
                               ($role->name === 'admin'      ? 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400 border border-rose-200 dark:border-rose-800' :
                               ($role->name === 'mentor'     ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800' :
                                                               'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border border-slate-200 dark:border-slate-700')) }}">
                            {{ ucfirst($role->name) }}
                        </span>
                    @empty
                        <span class="text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                            {{ __('Seeker') }}
                        </span>
                    @endforelse

                    <span class="text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 border border-amber-200 dark:border-amber-800">
                        ⚔️ {{ $this->levelTitle }}
                    </span>
                </div>

                {{-- XP Progress Bar --}}
                @php $xpProg = $this->xpProgress; @endphp
                <div class="max-w-sm">
                    <div class="flex justify-between text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5">
                        <span>{{ __('XP') }}: {{ number_format($xpProg['current']) }}</span>
                        <span>{{ __('Next Level') }}: {{ number_format($xpProg['next']) }}</span>
                    </div>
                    <div class="h-2.5 bg-slate-200 dark:bg-slate-800 rounded-full overflow-hidden">
                        <div class="h-full rounded-full bg-gradient-to-r from-amber-400 to-orange-500 transition-all duration-700 ease-out" style="width: {{ $xpProg['percent'] }}%"></div>
                    </div>
                </div>
            </div>

            {{-- Chat / Message Button --}}
            <div class="flex gap-3 shrink-0">
                @auth
                    @if(auth()->id() !== $user->id)
                        <button wire:click="startChat" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-700 hover:to-blue-700 text-white font-bold rounded-xl shadow-lg transition-all hover:-translate-y-0.5">
                            <x-icon name="o-chat-bubble-left-right" class="w-4 h-4" /> {{ __('Chat now') }}
                        </button>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold rounded-xl shadow transition-all">
                        <x-icon name="o-chat-bubble-left-right" class="w-4 h-4" /> {{ __('Login to Message') }}
                    </a>
                @endauth
            </div>
        </div>

        {{-- ══════════════════════════════════════════ --}}
        {{-- STATS ROW                                 --}}
        {{-- ══════════════════════════════════════════ --}}
        @php $stats = $this->stats; @endphp
        <div class="grid grid-cols-3 sm:grid-cols-6 gap-3 mb-8">
            @foreach([
                ['icon' => 'o-document-text', 'value' => $stats['totalPosts'], 'label' => 'Posts', 'color' => 'cyan'],
                ['icon' => 'o-eye', 'value' => number_format($stats['totalViews']), 'label' => 'Views', 'color' => 'blue'],
                ['icon' => 'o-fire', 'value' => $stats['currentStreak'].'d', 'label' => 'Streak', 'color' => 'orange'],
                ['icon' => 'o-clipboard-document-check', 'value' => $stats['totalReports'], 'label' => 'Reports', 'color' => 'green'],
                ['icon' => 'o-question-mark-circle', 'value' => $stats['quizCount'], 'label' => 'Quizzes', 'color' => 'purple'],
                ['icon' => 'o-calendar', 'value' => $user->created_at->diffForHumans(['short' => true,
    'parts' => 1,
    'syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE,]), 'label' => 'Member', 'color' => 'rose'],
            ] as $stat)
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4 text-center hover:border-{{ $stat['color'] }}-400 hover:shadow-lg transition-all group">
                <x-icon name="{{ $stat['icon'] }}" class="w-5 h-5 mx-auto mb-1.5 text-{{ $stat['color'] }}-500 group-hover:scale-110 transition-transform" />
                <div class="text-xl font-black text-slate-900 dark:text-white">{{ $stat['value'] }}</div>
                <div class="text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">{{ __($stat['label']) }}</div>
            </div>
            @endforeach
        </div>

        {{-- ══════════════════════════════════════════ --}}
        {{-- MAIN CONTENT GRID                         --}}
        {{-- ══════════════════════════════════════════ --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 pb-16">

            {{-- LEFT SIDEBAR --}}
            <div class="space-y-5">

                {{-- Bio Card --}}
                @if($user->detail?->bio)
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5">
                    <h3 class="font-black text-slate-900 dark:text-white text-sm uppercase tracking-widest mb-3">{{ __('About') }}</h3>
                    <p class="text-slate-600 dark:text-slate-300 text-sm leading-relaxed">{{ $user->detail->bio }}</p>
                </div>
                @endif

                {{-- Info Card --}}
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 space-y-3">
                    <h3 class="font-black text-slate-900 dark:text-white text-sm uppercase tracking-widest mb-3">{{ __('Info') }}</h3>
                    <div class="flex items-center gap-3 text-sm text-slate-600 dark:text-slate-400">
                        <x-icon name="o-calendar-days" class="w-4 h-4 shrink-0 text-cyan-500" />
                        {{ __('Joined') }} {{ $user->created_at->format('F j, Y') }}
                    </div>
                    @if($user->detail?->phone)
                    <div class="flex items-center gap-3 text-sm text-slate-600 dark:text-slate-400">
                        <x-icon name="o-phone" class="w-4 h-4 shrink-0 text-cyan-500" />
                        {{ $user->detail->phone }}
                    </div>
                    @endif
                    @if($user->detail?->address)
                    <div class="flex items-center gap-3 text-sm text-slate-600 dark:text-slate-400">
                        <x-icon name="o-map-pin" class="w-4 h-4 shrink-0 text-cyan-500" />
                        {{ $user->detail->address }}
                    </div>
                    @endif
                </div>

                {{-- Social Links --}}
                @if($this->socialLinks->isNotEmpty())
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5">
                    <h3 class="font-black text-slate-900 dark:text-white text-sm uppercase tracking-widest mb-3">{{ __('Links') }}</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($this->socialLinks as $key => $social)
                        <a href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold {{ $social['color'] }} hover:shadow-md hover:-translate-y-0.5 transition-all">
                            <x-icon name="{{ $social['icon'] }}" class="w-3.5 h-3.5" />
                            {{ $social['label'] }}
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Achievements --}}
                @if(count($this->achievements) > 0)
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5">
                    <h3 class="font-black text-slate-900 dark:text-white text-sm uppercase tracking-widest mb-4">{{ __('Achievements') }}</h3>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach($this->achievements as $badge)
                        <div class="flex flex-col items-center gap-1.5 p-3 bg-slate-50 dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 text-center hover:border-amber-400 hover:shadow-md transition-all group" title="{{ $badge['desc'] }}">
                            <span class="text-2xl group-hover:scale-125 transition-transform">{{ $badge['icon'] }}</span>
                            <span class="text-[9px] font-black uppercase tracking-widest text-slate-600 dark:text-slate-300 leading-tight">{{ $badge['title'] }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            {{-- RIGHT MAIN CONTENT --}}
            <div class="lg:col-span-2">

                {{-- Tab Bar --}}
                <div class="flex gap-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-1.5 mb-6">
                    @foreach([
                        ['key' => 'overview',  'icon' => 'o-home',                          'label' => 'Overview'],
                        ['key' => 'posts',     'icon' => 'o-document-text',                 'label' => 'Posts'],
                        ['key' => 'bookshelf', 'icon' => 'o-book-open',                     'label' => 'Bookshelf'],
                        ['key' => 'reports',   'icon' => 'o-clipboard-document-check',       'label' => 'Reports'],
                        ['key' => 'quizzes',   'icon' => 'o-question-mark-circle',          'label' => 'Quizzes'],
                    ] as $tab)
                    <button wire:click="switchTab('{{ $tab['key'] }}')"
                        class="flex-1 flex items-center justify-center gap-2 py-2.5 rounded-xl font-bold text-sm transition-all duration-200
                            {{ $activeTab === $tab['key']
                                ? 'bg-slate-900 dark:bg-slate-700 text-white shadow-sm'
                                : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                        <x-icon name="{{ $tab['icon'] }}" class="w-4 h-4" />
                        <span class="hidden sm:inline">{{ __($tab['label']) }}</span>
                    </button>
                    @endforeach
                </div>

                {{-- ── OVERVIEW TAB ── --}}
                <div class="{{ $activeTab !== 'overview' ? 'hidden' : '' }} space-y-6">

                    {{-- Heatmap & Daily Activity --}}
                    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                        <h3 class="font-black text-slate-900 dark:text-white text-lg mb-4 flex items-center gap-2">
                            <x-icon name="o-calendar" class="w-5 h-5 text-emerald-500" />
                            {{ __('Routine & Activity Overview') }}
                        </h3>
                        <div class="mb-4">
                            <p class="text-sm text-slate-500 mb-3">{{ __('Report submissions in the last 7 days') }}</p>
                            <div class="flex gap-2">
                                @foreach($this->reportHeatmap as $date => $active)
                                <div class="flex-1 flex flex-col items-center">
                                    <div class="w-full h-8 rounded-lg border transition-colors {{ $active ? 'bg-emerald-500 border-emerald-600' : 'bg-slate-100 dark:bg-slate-800 border-slate-200 dark:border-slate-700' }}"
                                         title="{{ \Carbon\Carbon::parse($date)->format('M d, Y') }}: {{ $active ? __('Submitted') : __('Missed') }}"></div>
                                    <span class="text-[9px] font-bold text-slate-400 mt-1">{{ \Carbon\Carbon::parse($date)->format('D') }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="flex justify-between items-center bg-slate-50 dark:bg-slate-800/40 p-4 rounded-xl text-sm">
                            <span class="text-slate-600 dark:text-slate-400">{{ __('Current streak') }}</span>
                            <span class="font-black text-orange-500 flex items-center gap-1"><x-icon name="o-fire" class="w-4 h-4" /> {{ $stats['currentStreak'] }} {{ __('days') }}</span>
                        </div>
                    </div>

                    {{-- Bookshelf Preview --}}
                    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-black text-slate-900 dark:text-white text-lg flex items-center gap-2">
                                <x-icon name="o-book-open" class="w-5 h-5 text-cyan-500" />
                                {{ __('Currently Reading') }}
                            </h3>
                            <button wire:click="switchTab('bookshelf')" class="text-xs font-bold text-cyan-600 hover:underline">{{ __('View Shelf') }}</button>
                        </div>
                        @if($this->bookshelf['reading']->isEmpty())
                            <p class="text-slate-500 text-sm italic">{{ __('Not currently reading any books.') }}</p>
                        @else
                            <div class="space-y-3">
                                @foreach($this->bookshelf['reading']->take(2) as $interaction)
                                    @if($interaction->book)
                                    <a href="{{ route('web.book', $interaction->book->slug) }}" wire:navigate class="flex items-center gap-4 p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-800 hover:border-cyan-400 transition-colors">
                                        <div class="w-10 h-14 rounded-lg bg-slate-200 dark:bg-slate-700 overflow-hidden shrink-0 shadow">
                                            @if($interaction->book->cover_url)
                                                <img src="{{ $interaction->book->cover_url }}" class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center"><x-icon name="o-book-open" class="w-5 h-5 text-slate-400" /></div>
                                            @endif
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="font-bold text-slate-900 dark:text-white text-sm truncate">{{ $interaction->book->title }}</div>
                                            <p class="text-xs text-slate-500 mt-0.5">{{ $interaction->book->author?->name }}</p>
                                        </div>
                                        <div class="text-xs font-bold text-cyan-600 dark:text-cyan-400 shrink-0">
                                            {{ $interaction->pages_read }} / {{ $interaction->book->pages_count ?? '?' }} p.
                                        </div>
                                    </a>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Donations Section --}}
                    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                        <h3 class="font-black text-slate-900 dark:text-white text-lg mb-4 flex items-center gap-2">
                            <x-icon name="o-heart" class="w-5 h-5 text-rose-500" />
                            {{ __('Supported Causes') }}
                        </h3>
                        @if($this->donations->isEmpty())
                            <p class="text-slate-500 text-sm italic">{{ __('No public donations made yet.') }}</p>
                        @else
                            <div class="space-y-3">
                                @foreach($this->donations as $donation)
                                <div class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700">
                                    <div class="min-w-0">
                                        <div class="text-sm font-black text-slate-800 dark:text-slate-200 truncate">
                                            {{ $donation->campaign?->title ?? __('General Fund') }}
                                        </div>
                                        <span class="text-[10px] text-slate-400">{{ \Carbon\Carbon::parse($donation->donated_at)->format('d M Y') }}</span>
                                    </div>
                                    <span class="text-sm font-black text-rose-600 dark:text-rose-400 shrink-0">+{{ number_format($donation->amount) }} {{ __($donation->currency ?? 'BDT') }}</span>
                                </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                </div>

                {{-- ── POSTS TAB ── --}}
                <div class="{{ $activeTab !== 'posts' ? 'hidden' : '' }}">
                    @if($this->posts->isEmpty())
                        <div class="text-center py-16 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800">
                            <x-icon name="o-document-text" class="w-12 h-12 mx-auto text-slate-300 dark:text-slate-600 mb-3" />
                            <h3 class="font-black text-slate-700 dark:text-slate-300">{{ __('No posts yet') }}</h3>
                            <p class="text-slate-500 text-sm mt-1">{{ $user->name }} {{ __("hasn't published anything yet.") }}</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @foreach($this->posts as $post)
                            <a href="{{ route('web.post', $post->slug) }}" wire:navigate
                               class="group bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden hover:border-cyan-500 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 flex flex-col">
                                @if($post->featured_image_url ?? false)
                                <div class="h-40 overflow-hidden">
                                    <img src="{{ $post->featured_image_url }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt="">
                                </div>
                                @endif
                                <div class="p-4 flex-grow flex flex-col">
                                    @if($post->category)
                                    <span class="text-[10px] font-black uppercase tracking-widest text-cyan-600 dark:text-cyan-400 mb-2">{{ $post->category->name }}</span>
                                    @endif
                                    <h4 class="font-black text-slate-900 dark:text-white line-clamp-2 group-hover:text-cyan-600 dark:group-hover:text-cyan-400 transition-colors mb-2 flex-grow">{{ $post->title }}</h4>
                                    <div class="flex items-center gap-3 text-xs text-slate-500 dark:text-slate-400 mt-auto">
                                        <span class="flex items-center gap-1">
                                            <x-icon name="o-eye" class="w-3.5 h-3.5" />{{ $post->views_count ?? 0 }}
                                        </span>
                                        <span>{{ $post->published_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </a>
                            @endforeach
                        </div>
                        <div class="mt-6">{{ $this->posts->links() }}</div>
                    @endif
                </div>

                {{-- ── BOOKSHELF TAB ── --}}
                <div class="{{ $activeTab !== 'bookshelf' ? 'hidden' : '' }} space-y-6">
                    @foreach([
                        'reading'      => ['label' => 'Currently Reading', 'color' => 'cyan'],
                        'completed'    => ['label' => 'Completed Books', 'color' => 'emerald'],
                        'want_to_read' => ['label' => 'To Read / Wishlist', 'color' => 'indigo'],
                        'owned'        => ['label' => 'Shared Physical Copies', 'color' => 'amber'],
                        'uploaded'     => ['label' => 'Contributed to Library', 'color' => 'fuchsia'],
                    ] as $key => $info)
                    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6">
                        <h3 class="font-black text-slate-900 dark:text-white text-md mb-4 flex items-center gap-2 border-b border-slate-100 dark:border-slate-800 pb-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-{{ $info['color'] }}-500"></span>
                            {{ __($info['label']) }}
                            <span class="text-xs text-slate-400">({{ $this->bookshelf[$key]->count() }})</span>
                        </h3>
                        @if($this->bookshelf[$key]->isEmpty())
                            <p class="text-slate-400 text-sm italic">{{ __('No books in this section.') }}</p>
                        @else
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                @foreach($this->bookshelf[$key] as $item)
                                    @php
                                        $book = $key === 'uploaded' ? $item : $item->book;
                                    @endphp
                                    @if($book)
                                    <a href="{{ route('web.book', $book->slug) }}" wire:navigate class="flex items-center gap-4 p-3 bg-slate-50 dark:bg-slate-800/30 rounded-xl border border-slate-200 dark:border-slate-800 hover:border-{{ $info['color'] }}-400 transition-colors">
                                        <div class="w-12 h-16 rounded-lg bg-slate-200 dark:bg-slate-700 overflow-hidden shrink-0 shadow">
                                            @if($book->cover_url)
                                                <img src="{{ $book->cover_url }}" class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center"><x-icon name="o-book-open" class="w-6 h-6 text-slate-400" /></div>
                                            @endif
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="font-bold text-slate-900 dark:text-white text-sm truncate">{{ $book->title }}</div>
                                            <p class="text-xs text-slate-500 mt-0.5">{{ $book->author?->name }}</p>
                                            @if($key === 'reading' && $book->pages_count)
                                                <div class="h-1 w-full bg-slate-200 dark:bg-slate-700 rounded-full mt-2 overflow-hidden">
                                                    <div class="h-full bg-cyan-500 rounded-full" style="width: {{ min(100, round(($item->pages_read / $book->pages_count)*100)) }}%"></div>
                                                </div>
                                            @endif
                                            @if($key === 'owned' && $item->libraryHub)
                                                <div class="text-[10px] text-amber-600 dark:text-amber-400 font-semibold mt-1 flex items-center gap-1">
                                                    <x-icon name="o-map-pin" class="w-3 h-3" /> {{ $item->libraryHub->name }}
                                                </div>
                                            @endif
                                        </div>
                                    </a>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>
                    @endforeach
                </div>

                {{-- ── DAILY REPORTS TAB ── --}}
                <div class="{{ $activeTab !== 'reports' ? 'hidden' : '' }}">
                    @if($this->dailyReports->isEmpty())
                        <div class="text-center py-16 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800">
                            <x-icon name="o-clipboard-document-check" class="w-12 h-12 mx-auto text-slate-300 dark:text-slate-600 mb-3" />
                            <h3 class="font-black text-slate-700 dark:text-slate-300">{{ __('No reports submitted') }}</h3>
                            <p class="text-slate-500 text-sm mt-1">{{ __('Daily Shibir reports will appear here.') }}</p>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($this->dailyReports as $report)
                            <div class="flex items-center gap-4 p-4 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 hover:border-green-400 transition-all">
                                <div class="w-10 h-10 rounded-xl bg-green-100 dark:bg-green-900/30 flex items-center justify-center shrink-0">
                                    <x-icon name="o-check-circle" class="w-5 h-5 text-green-600 dark:text-green-400" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-bold text-slate-900 dark:text-white text-sm">
                                        {{ $report->report_date?->format('l, d M Y') ?? $report->created_at->format('l, d M Y') }}
                                    </div>
                                    @if(isset($report->notes) && $report->notes)
                                    <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-1 mt-0.5">{{ $report->notes }}</p>
                                    @endif
                                </div>
                                <span class="text-xs font-bold text-slate-400 dark:text-slate-500 shrink-0">{{ $report->created_at->diffForHumans() }}</span>
                            </div>
                            @endforeach
                        </div>
                        <div class="mt-6">{{ $this->dailyReports->links() }}</div>
                    @endif
                </div>

                {{-- ── QUIZZES TAB ── --}}
                <div class="{{ $activeTab !== 'quizzes' ? 'hidden' : '' }}">
                    @if($this->quizAttempts->isEmpty())
                        <div class="text-center py-16 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800">
                            <x-icon name="o-question-mark-circle" class="w-12 h-12 mx-auto text-slate-300 dark:text-slate-600 mb-3" />
                            <h3 class="font-black text-slate-700 dark:text-slate-300">{{ __('No quizzes completed') }}</h3>
                            <p class="text-slate-500 text-sm mt-1">{{ __('Quiz attempts will appear here once completed.') }}</p>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($this->quizAttempts as $attempt)
                            @php
                                $pct = $attempt->score_percentage ?? 0;
                                $color = $pct >= 80 ? 'green' : ($pct >= 50 ? 'amber' : 'rose');
                            @endphp
                            <div class="flex items-center gap-4 p-4 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 hover:border-purple-400 transition-all">
                                <div class="w-14 h-14 rounded-2xl bg-{{ $color }}-100 dark:bg-{{ $color }}-900/30 flex flex-col items-center justify-center shrink-0 border border-{{ $color }}-200 dark:border-{{ $color }}-800">
                                    <span class="text-lg font-black text-{{ $color }}-600 dark:text-{{ $color }}-400 leading-none">{{ $pct }}%</span>
                                    <span class="text-[9px] text-{{ $color }}-500 font-bold uppercase">{{ $attempt->passed ? __('Pass') : __('Fail') }}</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-black text-slate-900 dark:text-white line-clamp-1">{{ $attempt->quiz_title }}</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                        {{ $attempt->score_raw }} {{ __('pts raw') }} · {{ \Carbon\Carbon::parse($attempt->created_at)->diffForHumans() }}
                                    </div>
                                </div>
                                <div class="h-2 w-20 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden shrink-0">
                                    <div class="h-full bg-{{ $color }}-500 rounded-full" style="width: {{ $pct }}%"></div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="mt-6">{{ $this->quizAttempts->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>