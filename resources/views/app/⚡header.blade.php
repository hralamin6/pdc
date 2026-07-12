<?php

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public array $languages = [
        'en' => ['name' => 'English', 'icon' => 'o-language'],
        'ar' => ['name' => 'العربية', 'icon' => 'o-language'],
        'bn' => ['name' => 'বাংলা', 'icon' => 'o-language'],
    ];

    public function switchLanguage($locale)
    {
        if (array_key_exists($locale, $this->languages)) {
            App::setLocale($locale);
            Session::put('locale', $locale);
            $this->redirect(url()->previous(), navigate: true);
        }
    }

    public function getUnreadConversationsProperty()
    {
        return auth()->user()
            ->conversations()
            ->with(['userOne', 'userTwo', 'latestMessage'])
            ->get()
            ->filter(function ($conversation) {
                return $conversation->getUnreadCount(auth()->id()) > 0;
            })
            ->sortByDesc(function ($conversation) {
                return $conversation->latestMessage?->created_at;
            })
            ->take(5);
    }

    public function getTotalUnreadCountProperty()
    {
        return $this->unreadConversations->sum(function ($conversation) {
            return $conversation->getUnreadCount(auth()->id());
        });
    }

    public function getUnreadNotificationsCountProperty()
    {
        return auth()->user()->unreadNotifications()->count();
    }

    public function getRecentNotificationsProperty()
    {
        return auth()->user()
            ->notifications()
            ->whereNull('read_at')
            ->latest()
            ->take(5)
            ->get();
    }

    public function markAsReadAndRedirect($notificationId, $url)
    {
        $notification = auth()->user()->notifications()->find($notificationId);
        if ($notification && !$notification->read_at) {
            $notification->markAsRead();
        }
        return $this->redirect($url, navigate: true);
    }

    #[On('message-received')]
    public function updateCount()
    {
        $this->dispatch('$refresh');
    }

    #[On('notification-received')]
    public function updateNotificationCount()
    {
        $this->dispatch('$refresh');
    }
};
?>

<div>
    <x-nav sticky class="border-b border-base-content/10 bg-base-100/70 -mt-1 z-20 h-15" x-data="{ messagesOpen: false, notificationsOpen: false }">
        
        {{-- BRAND --}}
        <x-slot:brand class="flex items-center gap-1">
            {{-- Mobile Drawer Toggle --}}
            <label for="main-drawer" class="lg:hidden btn btn-ghost btn-circle btn-sm text-base-content/80 hover:text-primary transition-colors shadow-none mr-1">
                <x-icon name="o-bars-3" class="w-5 h-5" />
            </label>

            {{-- App Brand --}}
            <a href="{{ route('web.home') }}" wire:navigate class="flex items-center gap-2 ml-1 lg:ml-0">
                <x-avatar image="{{ getSettingImage('iconImage', 'icon') }}" class="w-8 h-8 lg:w-10 lg:h-10" />
                <span class="hidden lg:flex text-lg font-bold bg-gradient-to-r from-indigo-600 to-purple-600 dark:from-indigo-400 dark:to-purple-400 bg-clip-text text-transparent capitalize">
                    {{ setting('app.name', 'Deenify') }}
                </span>
            </a>
        </x-slot:brand>

        {{-- ACTIONS --}}
        <x-slot:actions class="flex items-center gap-1 md:gap-2">
            
            {{-- Desktop Search --}}
            <label class="hidden md:inline-flex input input-sm border border-base-content/10 bg-base-200/30 hover:bg-base-200/80 rounded-full items-center gap-2 cursor-pointer w-48 lg:w-64 transition-all duration-300 focus-within:w-56 lg:focus-within:w-80 focus-within:ring-2 focus-within:ring-primary/20 focus-within:bg-base-100 mr-2 shadow-sm" @click.stop="$dispatch('mary-search-open')">
                <x-icon name="o-magnifying-glass" class="w-4 h-4 text-base-content/50" />
                <span class="flex-1 text-left font-medium text-sm text-base-content/50">{{ __('Search...') }}</span>
                <kbd class="kbd kbd-xs bg-base-100/50 border-none text-base-content/40 shadow-none font-sans font-bold">⌘K</kbd>
            </label>

            {{-- Mobile Search --}}
            <x-button icon="o-magnifying-glass" class="md:hidden btn-ghost btn-circle btn-sm text-base-content/80 hover:text-primary transition-colors shadow-none" @click.stop="$dispatch('mary-search-open')" />

            {{-- Messages Dropdown --}}
            <div class="dropdown dropdown-end" @click="messagesOpen = !messagesOpen">
                <label tabindex="0" class="btn btn-ghost btn-circle btn-sm text-base-content/80 hover:text-primary transition-colors shadow-none">
                    <div class="indicator">
                        <x-icon name="o-chat-bubble-left-right" class="w-5 h-5" />
                        @if($this->totalUnreadCount > 0)
                            <span class="badge badge-xs badge-primary indicator-item">{{ $this->totalUnreadCount }}</span>
                        @endif
                    </div>
                </label>
                
                <div tabindex="0" x-show="messagesOpen" @click.away="messagesOpen = false" x-cloak
                     class="dropdown-content z-[30] menu p-0 shadow-2xl bg-base-100 rounded-2xl w-[calc(100vw-2rem)] sm:w-80 mt-4 max-h-96 overflow-y-auto right-0 border border-base-200 backdrop-blur-sm">
                    <div class="sticky top-0 bg-base-200/90 backdrop-blur-md p-3 rounded-t-2xl border-b border-base-300">
                        <h3 class="font-semibold text-sm">{{ __('Unread Messages') }}</h3>
                    </div>

                    @if($this->unreadConversations->count() > 0)
                        <ul class="p-2">
                            @foreach($this->unreadConversations as $conversation)
                                @php
                                    $otherUser = $conversation->getOtherUser(auth()->id());
                                    $unreadCount = $conversation->getUnreadCount(auth()->id());
                                @endphp
                                <li>
                                    <a wire:navigate href="{{ route('app.chat', ['conversation' => $conversation->id]) }}"
                                       @click="messagesOpen = false"
                                       class="flex items-start gap-3 p-3 hover:bg-base-200 rounded-lg">
                                        <div class="avatar text-xs">
                                            <div class="w-10 h-10 rounded-full">
                                                <img src="{{ $otherUser->avatar_url }}" alt="{{ $otherUser->name }}" />
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between">
                                                <p class="font-semibold text-xs sm:text-sm truncate mr-2">{{ $otherUser->name }}</p>
                                                <span class="badge badge-primary badge-xs">{{ $unreadCount }}</span>
                                            </div>
                                            @if($conversation->latestMessage)
                                                <p class="text-[10px] sm:text-xs text-base-content/70 truncate mt-1">
                                                    {{ Str::limit($conversation->latestMessage->body ?? 'Attachment', 40) }}
                                                </p>
                                                <p class="text-[10px] text-base-content/50 mt-1">
                                                    {{ $conversation->latestMessage->created_at->diffForHumans() }}
                                                </p>
                                            @endif
                                        </div>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                        <div class="p-2 border-t border-base-300 bg-base-200/30">
                            <a wire:navigate href="{{ route('app.chat') }}" @click="messagesOpen = false" class="btn btn-sm btn-block btn-ghost rounded-xl">
                                {{ __('View All Messages') }}
                            </a>
                        </div>
                    @else
                        <div class="p-8 text-center text-base-content/50">
                            <x-icon name="o-chat-bubble-left-right" class="w-12 h-12 mx-auto mb-2 opacity-30" />
                            <p class="text-sm">{{ __('No unread messages') }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Notifications Dropdown --}}
            <div class="dropdown dropdown-end" @click="notificationsOpen = !notificationsOpen">
                <label tabindex="0" class="btn btn-ghost btn-circle btn-sm text-base-content/80 hover:text-primary transition-colors shadow-none relative">
                    <div class="indicator">
                        <x-icon name="o-bell" class="w-5 h-5" />
                        @if($this->unreadNotificationsCount > 0)
                            <span class="badge badge-xs badge-primary indicator-item">{{ $this->unreadNotificationsCount }}</span>
                            <span class="absolute top-1 right-1 w-2 h-2 bg-primary rounded-full ring-2 ring-base-100 animate-ping opacity-75"></span>
                        @endif
                    </div>
                </label>
                
                <div tabindex="0" x-show="notificationsOpen" @click.away="notificationsOpen = false" x-cloak
                     class="dropdown-content z-[30] menu p-0 shadow-2xl bg-base-100 rounded-2xl w-[calc(100vw-1rem)] sm:w-96 mt-4 border border-base-200 overflow-hidden backdrop-blur-sm right-0">
                    
                    {{-- Header --}}
                    <div class="flex items-center justify-between p-4 bg-base-200/90 backdrop-blur-md">
                        <div class="flex items-center gap-2">
                            <h3 class="font-bold text-base">{{ __('Notifications') }}</h3>
                            @if($this->unreadNotificationsCount > 0)
                                <span class="badge badge-primary badge-sm px-2 font-medium">{{ $this->unreadNotificationsCount }} {{ __('new') }}</span>
                            @endif
                        </div>
                        <a href="{{ route('app.notifications') }}" wire:navigate class="text-xs font-semibold text-primary hover:text-primary-focus underline-offset-4 hover:underline">
                            {{ __('Manage') }}
                        </a>
                    </div>

                    {{-- List --}}
                    <div class="max-h-[28rem] overflow-y-auto">
                        @if($this->recentNotifications->count() > 0)
                            <div class="divide-y divide-base-200">
                                @foreach($this->recentNotifications as $notification)
                                    @php
                                        $data = $notification->data;
                                        $targetUrl = $data['url'] ?? route('app.notifications');
                                        $isChat = str_contains($notification->type, 'NewMessageNotification');
                                        $icon = $data['icon'] ?? ($isChat ? 'o-chat-bubble-left-right' : 'o-bell');
                                        $type = $data['type'] ?? 'info';
                                        $colorClass = match($type) {
                                            'success' => 'text-success bg-success/10',
                                            'error'   => 'text-error bg-error/10',
                                            'warning' => 'text-warning bg-warning/10',
                                            default   => 'text-primary bg-primary/10',
                                        };
                                    @endphp
                                    <a wire:click.prevent="markAsReadAndRedirect('{{ $notification->id }}', '{{ $targetUrl }}')"
                                       href="{{ $targetUrl }}"
                                       class="flex items-start gap-4 p-4 hover:bg-base-200/80 transition-all duration-200 relative group">
                                        
                                        <div class="absolute left-1 top-1/2 -translate-y-1/2 w-1 h-8 bg-primary rounded-full opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                        
                                        <div class="flex-shrink-0 mt-1">
                                            @if($isChat && isset($data['sender_avatar']))
                                                <div class="avatar">
                                                    <div class="w-10 h-10 rounded-full ring-2 ring-primary/20 group-hover:ring-primary/40 transition-all">
                                                        <img src="{{ $data['sender_avatar'] }}" alt="{{ __('avatar') }}" />
                                                    </div>
                                                </div>
                                            @else
                                                <div class="p-2.5 rounded-xl {{ $colorClass }} shadow-sm group-hover:scale-110 transition-transform duration-300">
                                                    <x-icon :name="$icon" class="w-5 h-5" />
                                                </div>
                                            @endif
                                        </div>

                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between mb-0.5">
                                                <p class="font-bold text-sm text-base-content group-hover:text-primary transition-colors truncate">
                                                    {{ $data['title'] ?? $data['sender_name'] ?? __('Notification') }}
                                                </p>
                                                <span class="text-[10px] text-base-content/40 font-medium shrink-0 ml-2">
                                                    {{ $notification->created_at->diffForHumans(['short' => true]) }}
                                                </span>
                                            </div>
                                            <p class="text-xs text-base-content/70 line-clamp-2 leading-relaxed">
                                                {{ $data['message'] ?? $data['body'] ?? '' }}
                                            </p>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <div class="flex flex-col items-center justify-center py-12 px-4 opacity-60">
                                <div class="p-6 rounded-full bg-base-200 mb-4 animate-pulse">
                                    <x-icon name="o-bell-slash" class="w-12 h-12 text-base-content/30" />
                                </div>
                                <p class="text-sm font-medium text-base-content/50">{{ __('Everything caught up!') }}</p>
                                <p class="text-xs mt-1">{{ __('No new notifications') }}</p>
                            </div>
                        @endif
                    </div>

                    {{-- Footer --}}
                    @if($this->recentNotifications->count() > 0)
                        <div class="p-3 bg-base-200/30 border-t border-base-200">
                            <a wire:navigate href="{{ route('app.notifications') }}" @click="notificationsOpen = false"
                               class="btn btn-sm btn-block btn-primary btn-outline border-none hover:bg-primary/10 hover:text-primary transition-all rounded-xl">
                                {{ __('View all notifications') }}
                                <x-icon name="o-arrow-right" class="w-3.5 h-3.5 ml-1" />
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Language Switcher (Clean Refactor) --}}
            <x-dropdown icon="o-language" class="btn-ghost btn-circle btn-sm text-base-content/80 hover:text-primary transition-colors shadow-none" right no-x-anchor>
                @foreach($languages as $code => $lang)
                    <x-menu-item 
                        :title="$lang['name']" 
                        wire:click="switchLanguage('{{ $code }}')" 
                        class="rounded-lg m-1 {{ app()->getLocale() === $code ? 'text-primary font-bold bg-primary/10' : '' }}" 
                    />
                @endforeach
            </x-dropdown>
    <x-theme-toggle class="btn btn-circle btn-ghost" x-cloak />
        </x-slot:actions>
    </x-nav>
</div>