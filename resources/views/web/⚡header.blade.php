<?php

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\On;
use Livewire\Component;
use App\Models\Page;

new class extends Component {
    public bool $mobileMenu = false;
    public ?string $openMenu = null;

    public array $languages = [
        'en' => ['name' => 'English', 'flag' => '🇬🇧'],
        'ar' => ['name' => 'العربية', 'flag' => '🇸🇦'],
        'bn' => ['name' => 'বাংলা', 'flag' => '🇧🇩'],
    ];

    public function switchLanguage(string $locale): void
    {
        if (array_key_exists($locale, $this->languages)) {
            App::setLocale($locale);
            Session::put('locale', $locale);
            $this->redirect(url()->previous(), navigate: true);
        }
    }

    public function getPagesProperty()
    {
        return Page::published()->orderBy('order')->get();
    }

    public function getUnreadNotificationsCountProperty(): int
    {
        return auth()->user()?->unreadNotifications()->count() ?? 0;
    }

    public function getUnreadMessagesCountProperty(): int
    {
        if (!auth()->check()) {
            return 0;
        }

        return \App\Models\Message::whereIn('conversation_id', auth()->user()->conversations->pluck('id'))
            ->where('user_id', '!=', auth()->id())
            ->whereNull('read_at')
            ->where('is_deleted', false)
            ->count();
    }

    public function getRecentNotificationsProperty()
    {
        return auth()->user()
            ?->unreadNotifications()
            ->latest()
            ->take(5)
            ->get() ?? collect();
    }

    public function markAsReadAndRedirect(string $notificationId, string $url)
    {
        if (auth()->check()) {
            $notification = auth()->user()->unreadNotifications()->find($notificationId);
            if ($notification) {
                $notification->markAsRead();
            }
        }
        return $this->redirect($url, navigate: true);
    }

    public function getListeners(): array
    {
        if (auth()->check()) {
            return [
                "echo-private:App.Models.User." . auth()->id() . ",.Illuminate\\Notifications\\Events\\BroadcastNotificationCreated" => 'refreshNotificationCount',
                "echo-private:user." . auth()->id() . ",notification" => 'refreshNotificationCount',
                "echo-private:App.Models.User." . auth()->id() . ",notification" => 'refreshNotificationCount',
                "notification-received" => 'refreshNotificationCount',
                "message-received" => 'refreshNotificationCount',
            ];
        }
        return [];
    }

    public function refreshNotificationCount(): void
    {
        $this->dispatch('$refresh');
    }
};
?>

<div x-cloak
    x-data="{
        scrolled: false,
        openMenu: null,
        mobileOpen: false,
        init() {
            this.scrolled = window.scrollY > 30;
        },
        toggleMenu(name) {
            this.openMenu = this.openMenu === name ? null : name;
        },
        closeAll() {
            this.openMenu = null;
        }
    }"
    @scroll.window="scrolled = (window.scrollY > 30)"
    @click.window="openMenu = null"
>
    <nav
        class="fixed top-0 inset-x-0 z-50 transition-colors duration-300"
        :class="scrolled
            ? 'bg-white dark:bg-slate-900 shadow-md border-b border-slate-200 dark:border-white/10'
            : 'bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border-b border-slate-200/60 dark:border-white/5'"
    >
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 lg:h-[72px]">

                {{-- Brand --}}
                <a href="{{ route('web.home') }}" wire:navigate class="flex items-center gap-2.5 shrink-0 group">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center shadow-lg shadow-primary/30 group-hover:scale-105 transition-transform overflow-hidden">
                        @if(file_exists(public_path('logo.png')))
                            <img src="{{ asset('logo.png') }}" class="w-full h-full object-cover bg-white" alt="{{ __('Logo') }}" />
                        @else
                            <x-icon name="o-moon" class="w-5 h-5 text-white" />
                        @endif
                    </div>
                    <span class="text-lg font-black tracking-tight text-slate-900 dark:text-white">
                        {{ setting('app.name', 'PSTU Dawah') }}
                    </span>
                </a>

                {{-- Desktop Mega-Nav --}}
                <div class="hidden lg:flex items-center gap-1">

                    {{-- Home --}}
                    <a href="{{ route('web.home') }}" wire:navigate
                       class="px-4 py-2 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('web.home') ? 'text-primary bg-primary/10' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/5' }}">
                        {{ __('Home') }}
                    </a>

                    {{-- Learn Dropdown --}}
                    <div class="relative" @click.stop>
                        <button
                            @click.stop="toggleMenu('learn')"
                            class="flex items-center gap-1 px-4 py-2 rounded-xl text-sm font-semibold transition-all text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/5">
                            {{ __('Learn') }}
                            <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="openMenu === 'learn' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div
                            x-show="openMenu === 'learn'"
                            @click.stop
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            class="absolute top-full left-0 mt-2 w-64 bg-white dark:bg-slate-800 rounded-2xl shadow-2xl shadow-black/10 dark:shadow-black/40 border border-slate-100 dark:border-white/5 py-2 z-50">
                            <div class="px-3 py-2">
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-slate-500 mb-2">{{ __('Study Programs') }}</p>
                                <a href="{{ route('web.halaqahs') }}" wire:navigate @click="openMenu = null" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-white/5 transition-colors group">
                                    <div class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center">
                                        <x-icon name="o-book-open" class="w-4 h-4 text-primary" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-200 group-hover:text-primary transition-colors">{{ __('Halaqahs') }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Study circles & sessions') }}</p>
                                    </div>
                                </a>
                                <a href="{{ route('web.library') }}" wire:navigate @click="openMenu = null" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-white/5 transition-colors group">
                                    <div class="w-8 h-8 bg-emerald-500/10 rounded-lg flex items-center justify-center">
                                        <x-icon name="o-academic-cap" class="w-4 h-4 text-emerald-600" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-200 group-hover:text-emerald-600 transition-colors">{{ __('Library') }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Books & Islamic resources') }}</p>
                                    </div>
                                </a>
                                <a href="{{ route('web.quizzes') }}" wire:navigate @click="openMenu = null" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-white/5 transition-colors group">
                                    <div class="w-8 h-8 bg-indigo-500/10 rounded-lg flex items-center justify-center">
                                        <x-icon name="o-beaker" class="w-4 h-4 text-indigo-600" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-200 group-hover:text-indigo-600 transition-colors">{{ __('Quizzes') }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Islamic knowledge competitions') }}</p>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Community Dropdown --}}
                    <div class="relative" @click.stop>
                        <button
                            @click.stop="toggleMenu('community')"
                            class="flex items-center gap-1 px-4 py-2 rounded-xl text-sm font-semibold transition-all text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/5">
                            {{ __('Community') }}
                            <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="openMenu === 'community' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div
                            x-show="openMenu === 'community'"
                            @click.stop
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            class="absolute top-full left-0 mt-2 w-64 bg-white dark:bg-slate-800 rounded-2xl shadow-2xl shadow-black/10 dark:shadow-black/40 border border-slate-100 dark:border-white/5 py-2 z-50">
                            <div class="px-3 py-2">
                                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-slate-500 mb-2">{{ __('People & Content') }}</p>
                                <a href="{{ route('web.members') }}" wire:navigate @click="openMenu = null" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-white/5 transition-colors group">
                                    <div class="w-8 h-8 bg-violet-500/10 rounded-lg flex items-center justify-center">
                                        <x-icon name="o-users" class="w-4 h-4 text-violet-600" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-200 group-hover:text-violet-600 transition-colors">{{ __('Members') }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Community directory') }}</p>
                                    </div>
                                </a>
                                <a href="{{ route('web.posts') }}" wire:navigate @click="openMenu = null" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-white/5 transition-colors group">
                                    <div class="w-8 h-8 bg-amber-500/10 rounded-lg flex items-center justify-center">
                                        <x-icon name="o-newspaper" class="w-4 h-4 text-amber-600" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-200 group-hover:text-amber-600 transition-colors">{{ __('Blog & Knowledge') }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Articles & reflections') }}</p>
                                    </div>
                                </a>
                                <a href="{{ route('web.campaigns') }}" wire:navigate @click="openMenu = null" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-white/5 transition-colors group">
                                    <div class="w-8 h-8 bg-rose-500/10 rounded-lg flex items-center justify-center">
                                        <x-icon name="o-heart" class="w-4 h-4 text-rose-600" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-200 group-hover:text-rose-600 transition-colors">{{ __('Campaigns') }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Support our causes') }}</p>
                                    </div>
                                </a>
                                <a href="{{ route('web.showcase') }}" wire:navigate @click="openMenu = null" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-white/5 transition-colors group">
                                    <div class="w-8 h-8 bg-cyan-500/10 rounded-lg flex items-center justify-center">
                                        <x-icon name="o-photo" class="w-4 h-4 text-cyan-600" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-200 group-hover:text-cyan-600 transition-colors">{{ __('Showcase') }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Community Gallery') }}</p>
                                    </div>
                                </a>
                                <a href="{{ route('web.finances') }}" wire:navigate @click="openMenu = null" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-white/5 transition-colors group">
                                    <div class="w-8 h-8 bg-emerald-500/10 rounded-lg flex items-center justify-center">
                                        <x-icon name="o-banknotes" class="w-4 h-4 text-emerald-600" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-200 group-hover:text-emerald-600 transition-colors">{{ __('Finances') }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Treasury & Transparency') }}</p>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Dynamic Pages (Discover) --}}
                    @if($this->pages->isNotEmpty())
                        <div class="relative" @click.stop>
                            <button
                                @click.stop="toggleMenu('discover')"
                                class="flex items-center gap-1 px-4 py-2 rounded-xl text-sm font-semibold transition-all text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/5">
                                {{ __('Discover') }}
                                <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="openMenu === 'discover' ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div
                                x-show="openMenu === 'discover'"
                                @click.stop
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                                class="absolute top-full right-0 mt-2 w-64 bg-white dark:bg-slate-800 rounded-2xl shadow-2xl shadow-black/10 dark:shadow-black/40 border border-slate-100 dark:border-white/5 py-2 z-50">
                                <div class="px-3 py-2">
                                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-slate-500 mb-2">{{ __('More Info') }}</p>
                                    @foreach($this->pages as $page)
                                    <a href="{{ route('web.page', $page->slug) }}" wire:navigate @click="openMenu = null" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-white/5 transition-colors group">
                                        <div class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center">
                                            <x-icon name="o-document-text" class="w-4 h-4 text-primary" />
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-slate-800 dark:text-slate-200 group-hover:text-primary transition-colors">{{ $page->title }}</p>
                                        </div>
                                    </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @else
                        {{-- About --}}
                        <a href="#about"
                           class="px-4 py-2 rounded-xl text-sm font-semibold transition-all text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/5">
                            {{ __('About') }}
                        </a>
                    @endif
                </div>

                {{-- Right Side Actions --}}
                <div class="flex items-center gap-1 sm:gap-2">
                    {{-- Universal Actions (Lang & Theme) --}}
                    <div class="flex items-center">
                        {{-- Language --}}
                        <x-dropdown class="btn-ghost btn-sm btn-circle shadow-none" no-x-anchor right>
                            <x-slot:trigger>
                                <button class="btn btn-ghost btn-sm btn-circle text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-all">
                                    <x-icon name="o-language" class="w-4 h-4" />
                                </button>
                            </x-slot:trigger>
                            @foreach($languages as $code => $lang)
                                <x-menu-item :title="$lang['flag'] . ' ' . $lang['name']"
                                    wire:click="switchLanguage('{{ $code }}')"
                                    class="rounded-xl m-1 {{ app()->getLocale() === $code ? 'text-primary font-bold bg-primary/10' : '' }}" />
                            @endforeach
                        </x-dropdown>

                        {{-- Theme --}}
                        <x-theme-toggle class="btn btn-ghost btn-sm btn-circle text-slate-500 dark:text-slate-400" x-cloak />
                    </div>

                    {{-- Desktop Only Actions --}}
                    <div class="hidden lg:flex items-center gap-2">
                        <div class="w-px h-5 bg-slate-200 dark:bg-white/10 mx-1"></div>

                        @auth
                        {{-- Messages Button --}}
                        <a href="{{ route('web.chat') }}" wire:navigate class="relative p-2.5 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-all rounded-xl hover:bg-slate-100 dark:hover:bg-white/5 focus:outline-none mr-1.5">
                            <x-icon name="o-chat-bubble-left-right" class="w-5 h-5" />
                            @if($this->unreadMessagesCount > 0)
                                <span class="absolute top-1 right-1 w-4 h-4 bg-primary text-[9px] font-black text-white rounded-full flex items-center justify-center ring-2 ring-white dark:ring-slate-900">
                                    {{ $this->unreadMessagesCount }}
                                </span>
                                <span class="absolute top-1 right-1 w-4 h-4 bg-primary rounded-full ring-2 ring-white dark:ring-slate-900 animate-ping opacity-75"></span>
                            @endif
                        </a>

                        {{-- Notifications Dropdown --}}
                        <div class="relative mr-3" x-data="{ open: false }" @click.away="open = false">
                            <button @click="open = !open" class="relative p-2.5 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-all rounded-xl hover:bg-slate-100 dark:hover:bg-white/5 focus:outline-none">
                                <x-icon name="o-bell" class="w-5 h-5" />
                                @if($this->unreadNotificationsCount > 0)
                                    <span class="absolute top-1 right-1 w-4 h-4 bg-primary text-[9px] font-black text-white rounded-full flex items-center justify-center ring-2 ring-white dark:ring-slate-900">
                                        {{ $this->unreadNotificationsCount }}
                                    </span>
                                    <span class="absolute top-1 right-1 w-4 h-4 bg-primary rounded-full ring-2 ring-white dark:ring-slate-900 animate-ping opacity-75"></span>
                                @endif
                            </button>

                            {{-- Dropdown Menu --}}
                            <div x-show="open"
                                 x-cloak
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute right-0 mt-2.5 w-80 bg-white dark:bg-slate-900 border border-slate-200/50 dark:border-slate-800/80 rounded-2xl shadow-xl z-50 overflow-hidden">
                                
                                {{-- Header --}}
                                <div class="px-4 py-3 bg-slate-50/50 dark:bg-slate-950/20 border-b border-slate-200/50 dark:border-slate-800/80 flex items-center justify-between">
                                    <span class="text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-350">
                                        {{ __('Notifications') }}
                                    </span>
                                    @if($this->unreadNotificationsCount > 0)
                                        <span class="px-2 py-0.5 rounded-full bg-primary/10 text-primary text-[10px] font-black">
                                            {{ $this->unreadNotificationsCount }} {{ __('New') }}
                                        </span>
                                    @endif
                                </div>

                                {{-- Notification list --}}
                                <div class="max-h-72 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800/60">
                                    @forelse($this->recentNotifications as $notification)
                                        @php
                                            $data = $notification->data;
                                            $targetUrl = $data['url'] ?? $data['action_url'] ?? route('web.notifications');
                                            $isChat = str_contains($notification->type, 'NewMessageNotification');
                                            $icon = $data['icon'] ?? ($isChat ? 'o-chat-bubble-left-right' : 'o-bell');
                                            $type = $data['type'] ?? 'info';
                                            $colorClass = match($type) {
                                                'success' => 'text-emerald-500 bg-emerald-500/10 dark:bg-emerald-500/20',
                                                'error'   => 'text-rose-500 bg-rose-500/10 dark:bg-rose-500/20',
                                                'warning' => 'text-amber-500 bg-amber-500/10 dark:bg-amber-500/20',
                                                default   => 'text-primary bg-primary/10 dark:bg-primary/20',
                                            };
                                        @endphp
                                        <a href="#"
                                           wire:click.prevent="markAsReadAndRedirect('{{ $notification->id }}', '{{ $targetUrl }}')"
                                           class="block p-4 hover:bg-slate-50 dark:hover:bg-slate-950/40 transition duration-150">
                                            <div class="flex items-start gap-3">
                                                <div class="flex-shrink-0">
                                                    @if($isChat && isset($data['sender_avatar']))
                                                        <div class="w-8 h-8 rounded-full overflow-hidden ring-2 ring-primary/20">
                                                            <img src="{{ $data['sender_avatar'] }}" alt="{{ __('avatar') }}" class="w-full h-full object-cover" />
                                                        </div>
                                                    @else
                                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center {{ $colorClass }}">
                                                            <x-icon :name="$icon" class="w-4 h-4" />
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <div class="flex items-center justify-between mb-0.5">
                                                        <p class="text-xs font-black text-slate-800 dark:text-slate-200 truncate">
                                                            {{ $data['title'] ?? $data['sender_name'] ?? __('Notification') }}
                                                        </p>
                                                        <span class="text-[9px] font-bold text-slate-450 dark:text-slate-500 ml-2 whitespace-nowrap">
                                                            {{ $notification->created_at->diffForHumans(['short' => true]) }}
                                                        </span>
                                                    </div>
                                                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium line-clamp-2 leading-relaxed">
                                                        {{ $data['message'] ?? $data['body'] ?? '' }}
                                                    </p>
                                                </div>
                                            </div>
                                        </a>
                                    @empty
                                        <div class="py-10 px-4 text-center">
                                            <x-icon name="o-bell-slash" class="w-8 h-8 mx-auto text-slate-300 dark:text-slate-650 mb-2" />
                                            <p class="text-xs font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">
                                                {{ __('No New Notifications') }}
                                            </p>
                                        </div>
                                    @endforelse
                                </div>

                                {{-- Footer link to notifications panel --}}
                                <div class="px-4 py-2 border-t border-slate-200/50 dark:border-slate-800/80 bg-slate-50/50 dark:bg-slate-950/20 text-center">
                                    <a href="{{ route('web.notifications') }}" wire:navigate @click="open = false" class="text-[10px] font-black uppercase tracking-wider text-primary hover:text-primary-focus transition">
                                        {{ __('View All Notifications') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endauth
                    </div>

                    {{-- Auth Menu (Mobile & Desktop) --}}
                    <div class="flex items-center ml-1 lg:ml-0 gap-2">
                        @auth
                            <div class="w-px h-5 bg-slate-200 dark:bg-white/10 mx-1 hidden lg:block"></div>
                            <x-dropdown class="btn-ghost shadow-none p-0" no-x-anchor right>
                                <x-slot:trigger>
                                    <button class="flex items-center gap-2.5 hover:opacity-80 transition-opacity focus:outline-none">
                                        <div class="relative">
                                            <div class="w-8 h-8 lg:w-9 lg:h-9 rounded-xl bg-slate-100 dark:bg-slate-800 ring-2 ring-primary/20 overflow-hidden">
                                                <img src="{{ userImage(auth()->user()) }}" class="w-full h-full object-cover" />
                                            </div>
                                            @if(($totalUnread = $this->unreadMessagesCount + $this->unreadNotificationsCount) > 0)
                                                <span class="absolute -top-1.5 -right-1.5 w-4 h-4 bg-primary text-[9px] font-black text-white rounded-full flex items-center justify-center ring-2 ring-white dark:ring-slate-900 shadow-sm z-10">
                                                    {{ $totalUnread }}
                                                </span>
                                            @endif
                                        </div>
                                        <span class="hidden md:inline text-sm font-semibold text-slate-700 dark:text-slate-300">
                                            {{ auth()->user()->name }}
                                        </span>
                                    </button>
                                </x-slot:trigger>

                                <div class="px-4 py-2 border-b border-slate-100 dark:border-slate-800">
                                    <p class="text-xs text-slate-400 dark:text-slate-500 font-bold uppercase tracking-wider">{{ __('Signed in as') }}</p>
                                    <p class="text-sm font-bold text-slate-800 dark:text-slate-200 truncate max-w-[200px]">{{ auth()->user()->email }}</p>
                                </div>

                                <x-menu-item title="{{ __('My Profile') }}" icon="o-user" link="{{ route('web.profile') }}" wire:navigate class="rounded-xl m-1 text-slate-700 dark:text-slate-300 font-semibold" />
                                <x-menu-item title="{{ __('Messages') }}" icon="o-chat-bubble-left-right" link="{{ route('web.chat') }}" wire:navigate class="rounded-xl m-1 text-slate-700 dark:text-slate-300 font-semibold">
                                    @if($this->unreadMessagesCount > 0)
                                        <x-slot:badge>
                                            <x-badge value="{{ $this->unreadMessagesCount }}" class="badge-primary badge-sm" />
                                        </x-slot:badge>
                                    @endif
                                </x-menu-item>
                                <x-menu-item title="{{ __('Notifications') }}" icon="o-bell" link="{{ route('web.notifications') }}" wire:navigate class="rounded-xl m-1 text-slate-700 dark:text-slate-300 font-semibold">
                                    @if($this->unreadNotificationsCount > 0)
                                        <x-slot:badge>
                                            <x-badge value="{{ $this->unreadNotificationsCount }}" class="badge-primary badge-sm" />
                                        </x-slot:badge>
                                    @endif
                                </x-menu-item>
                                <x-menu-item title="{{ __('My Quizzes') }}" icon="o-academic-cap" link="{{ route('web.my-quizzes') }}" wire:navigate class="rounded-xl m-1 text-slate-700 dark:text-slate-300 font-semibold" />
                                <x-menu-item title="{{ __('My Daily Report') }}" icon="o-chart-bar-square" link="{{ route('web.my-report') }}" wire:navigate class="rounded-xl m-1 text-slate-700 dark:text-slate-300 font-semibold" />
                                <x-menu-item title="{{ __('My Books') }}" icon="o-book-open" link="{{ route('web.my-books') }}" wire:navigate class="rounded-xl m-1 text-slate-700 dark:text-slate-300 font-semibold" />
                                <x-menu-item title="{{ __('My Blog') }}" icon="o-pencil-square" link="{{ route('web.my-blog') }}" wire:navigate class="rounded-xl m-1 text-slate-700 dark:text-slate-300 font-semibold" />
                                <x-menu-item title="{{ __('My Donations') }}" icon="o-heart" link="{{ route('web.my-donations') }}" wire:navigate class="rounded-xl m-1 text-slate-700 dark:text-slate-300 font-semibold" />
                                <x-menu-item title="{{ __('Dashboard') }}" icon="o-squares-2x2" link="{{ route('app.dashboard') }}" wire:navigate class="rounded-xl m-1 text-slate-700 dark:text-slate-300 font-semibold" />
                                <x-menu-separator class="my-1 opacity-70" />
                                <x-menu-item title="{{ __('Sign Out') }}" icon="o-power" onclick="document.getElementById('web-logout-form').submit();" class="rounded-xl m-1 text-error font-semibold" />
                            </x-dropdown>
                            <form id="web-logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
                        @else
                            <div class="hidden lg:flex items-center gap-2">
                                <a href="{{ route('login') }}" wire:navigate
                                   class="btn btn-ghost btn-sm rounded-xl font-semibold text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white transition-all">
                                    {{ __('Sign in') }}
                                </a>
                                <a href="{{ route('register') }}" wire:navigate
                                   class="btn btn-sm bg-gradient-to-r from-primary to-secondary border-none text-white rounded-xl px-5 font-bold shadow-lg shadow-primary/25 hover:scale-105 transition-transform">
                                    {{ __('Join Free') }}
                                </a>
                            </div>
                        @endauth
                    </div>

                    {{-- Mobile Hamburger --}}
                    <div class="lg:hidden relative ml-1">
                        <button @click.stop="mobileOpen = !mobileOpen" class="flex flex-col gap-1.5 w-8 h-8 items-center justify-center relative">
                            <span class="block w-5 h-0.5 bg-slate-600 dark:bg-slate-300 transition-all duration-300" :class="mobileOpen ? 'rotate-45 translate-y-2' : ''"></span>
                            <span class="block w-5 h-0.5 bg-slate-600 dark:bg-slate-300 transition-all duration-300" :class="mobileOpen ? 'opacity-0' : ''"></span>
                            <span class="block w-5 h-0.5 bg-slate-600 dark:bg-slate-300 transition-all duration-300" :class="mobileOpen ? '-rotate-45 -translate-y-2' : ''"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    {{-- Mobile Menu --}}
    <div x-show="mobileOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 -translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-4"
        class="fixed inset-x-0 top-16 z-40 lg:hidden bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-white/5 shadow-2xl">
        <div class="max-w-7xl mx-auto px-4 py-6 space-y-1">
            {{-- <a href="{{ route('web.home') }}" wire:navigate @click="mobileOpen=false" class="flex items-center gap-3 px-4 py-3 rounded-2xl {{ request()->routeIs('web.home') ? 'bg-primary/10 text-primary' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-white/5' }} font-semibold transition-colors">
                <x-icon name="o-home" class="w-5 h-5" /> {{ __('Home') }}
            </a> --}}
            <div class="text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-slate-500 px-4 pt-4 pb-1">{{ __('Learn') }}</div>
            <a href="{{ route('web.halaqahs') }}" wire:navigate @click="mobileOpen=false" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-white/5 font-semibold transition-colors">
                <x-icon name="o-book-open" class="w-5 h-5 text-primary" /> {{ __('Halaqahs') }}
            </a>
            <a href="{{ route('web.library') }}" wire:navigate @click="mobileOpen=false" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-white/5 font-semibold transition-colors">
                <x-icon name="o-academic-cap" class="w-5 h-5 text-emerald-600" /> {{ __('Library') }}
            </a>
            <a href="{{ route('web.quizzes') }}" wire:navigate @click="mobileOpen=false" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-white/5 font-semibold transition-colors">
                <x-icon name="o-beaker" class="w-5 h-5 text-indigo-500" /> {{ __('Quizzes') }}
            </a>
            <div class="text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-slate-500 px-4 pt-4 pb-1">{{ __('Community') }}</div>
            <a href="{{ route('web.members') }}" wire:navigate @click="mobileOpen=false" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-white/5 font-semibold transition-colors">
                <x-icon name="o-users" class="w-5 h-5 text-violet-600" /> {{ __('Members') }}
            </a>
            <a href="{{ route('web.posts') }}" wire:navigate @click="mobileOpen=false" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-white/5 font-semibold transition-colors">
                <x-icon name="o-newspaper" class="w-5 h-5 text-amber-600" /> {{ __('Blog') }}
            </a>
            <a href="{{ route('web.campaigns') }}" wire:navigate @click="mobileOpen=false" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-white/5 font-semibold transition-colors">
                <x-icon name="o-heart" class="w-5 h-5 text-rose-600" /> {{ __('Campaigns') }}
            </a>
            <a href="{{ route('web.showcase') }}" wire:navigate @click="mobileOpen=false" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-white/5 font-semibold transition-colors">
                <x-icon name="o-photo" class="w-5 h-5 text-cyan-600" /> {{ __('Showcase') }}
            </a>
            <a href="{{ route('web.finances') }}" wire:navigate @click="mobileOpen=false" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-white/5 font-semibold transition-colors">
                <x-icon name="o-banknotes" class="w-5 h-5 text-emerald-600" /> {{ __('Finances') }}
            </a>
            {{-- @if($this->pages->isNotEmpty())
                <div class="text-[10px] font-black uppercase tracking-widest text-slate-400 dark:text-slate-500 px-4 pt-4 pb-1">{{ __('Discover') }}</div>
                @foreach($this->pages as $page)
                    <a href="{{ route('web.page', $page->slug) }}" wire:navigate @click="mobileOpen=false" class="flex items-center gap-3 px-4 py-3 rounded-2xl text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-white/5 font-semibold transition-colors">
                        <x-icon name="o-document-text" class="w-5 h-5 text-primary" /> {{ $page->title }}
                    </a>
                @endforeach
            @endif --}}

            <div class="border-t border-slate-100 dark:border-white/5 pt-4 mt-4 flex items-center justify-between">
                {{-- <x-theme-toggle class="btn btn-ghost btn-sm btn-circle text-slate-500" x-cloak /> --}}
                <div class="flex items-center gap-2 w-full">
                    @guest
                        <a href="{{ route('login') }}" wire:navigate class="btn btn-ghost btn-sm rounded-xl font-semibold">{{ __('Sign in') }}</a>
                        <a href="{{ route('register') }}" wire:navigate class="btn btn-sm bg-gradient-to-r from-primary to-secondary border-none text-white rounded-xl px-5 font-bold">{{ __('Join Free') }}</a>
                    @endguest
                </div>
            </div>
        </div>
    </div>

    {{-- Spacer --}}
    <div class="h-16 lg:h-[72px]"></div>
</div>
