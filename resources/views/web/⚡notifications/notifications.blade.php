<div class="max-w-4xl mx-auto px-4 py-8">
    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-3xl font-black tracking-tight text-slate-900 dark:text-white flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center shadow-lg shadow-primary/30">
                <x-icon name="o-bell" class="w-5 h-5 text-white" />
            </div>
            {{ __('Notifications') }}
        </h1>
        <p class="text-sm font-semibold text-slate-500 dark:text-slate-400 mt-2">
            {{ __('Manage your notifications and notification preferences') }}
        </p>
    </div>

    {{-- Tab Navigation --}}
    <div class="flex items-center gap-2 mb-8 bg-slate-100 dark:bg-slate-950 p-1.5 rounded-2xl border border-slate-200/30 dark:border-slate-800/20 max-w-sm">
        <button
            wire:click="$set('activeTab', 'center')"
            class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs font-black uppercase tracking-wider transition-all {{ $activeTab === 'center' ? 'bg-white dark:bg-slate-900 text-primary shadow-sm border border-slate-250/20 dark:border-slate-800/40' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}"
        >
            <x-icon name="o-inbox" class="w-4 h-4" />
            {{ __('Center') }}
            @if($unreadCount > 0)
                <span class="px-1.5 py-0.5 rounded-full bg-primary/10 text-primary text-[10px] font-black">
                    {{ $unreadCount }}
                </span>
            @endif
        </button>
        <button
            wire:click="$set('activeTab', 'preferences')"
            class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs font-black uppercase tracking-wider transition-all {{ $activeTab === 'preferences' ? 'bg-white dark:bg-slate-900 text-primary shadow-sm border border-slate-250/20 dark:border-slate-800/40' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white' }}"
        >
            <x-icon name="o-cog-6-tooth" class="w-4 h-4" />
            {{ __('Preferences') }}
        </button>
    </div>

    {{-- Notification Center Tab --}}
    @if($activeTab === 'center')
        <div class="space-y-6">
            {{-- Filter and Bulk Actions Bar --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-200/50 dark:border-slate-800/80 rounded-3xl p-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                {{-- Filter buttons --}}
                <div class="flex items-center gap-1.5 bg-slate-50 dark:bg-slate-950 p-1 rounded-xl">
                    <button
                        wire:click="$set('selectedFilter', 'all')"
                        class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $selectedFilter === 'all' ? 'bg-white dark:bg-slate-900 text-slate-800 dark:text-white shadow-sm' : 'text-slate-500 hover:text-slate-800 dark:hover:text-white' }}"
                    >
                        {{ __('All') }}
                    </button>
                    <button
                        wire:click="$set('selectedFilter', 'unread')"
                        class="px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5 {{ $selectedFilter === 'unread' ? 'bg-white dark:bg-slate-900 text-slate-800 dark:text-white shadow-sm' : 'text-slate-500 hover:text-slate-800 dark:hover:text-white' }}"
                    >
                        {{ __('Unread') }}
                        @if($unreadCount > 0)
                            <span class="px-1.5 py-0.5 rounded-full bg-primary/10 text-primary text-[10px] font-black">
                                {{ $unreadCount }}
                            </span>
                        @endif
                    </button>
                    <button
                        wire:click="$set('selectedFilter', 'read')"
                        class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $selectedFilter === 'read' ? 'bg-white dark:bg-slate-900 text-slate-800 dark:text-white shadow-sm' : 'text-slate-500 hover:text-slate-800 dark:hover:text-white' }}"
                    >
                        {{ __('Read') }}
                    </button>
                </div>

                {{-- Action buttons --}}
                <div class="flex items-center gap-2.5">
                    @if($unreadCount > 0)
                        <button
                            wire:click="markAllAsRead"
                            wire:loading.attr="disabled"
                            class="btn btn-sm btn-outline border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-950/45 text-slate-700 dark:text-slate-350 rounded-xl font-bold gap-2 text-xs"
                        >
                            <x-icon name="o-check-circle" class="w-4 h-4" />
                            {{ __('Mark All Read') }}
                        </button>
                    @endif
                    <button
                        wire:click="deleteAll"
                        wire:confirm="{{ __('Are you sure you want to delete all notifications? This action cannot be undone.') }}"
                        wire:loading.attr="disabled"
                        class="btn btn-sm btn-ghost hover:bg-rose-50 dark:hover:bg-rose-950/20 text-rose-650 rounded-xl font-bold gap-2 text-xs"
                    >
                        <x-icon name="o-trash" class="w-4 h-4" />
                        {{ __('Delete All') }}
                    </button>
                </div>
            </div>

            {{-- Notifications List --}}
            <div class="space-y-4">
                @forelse($this->notifications as $notification)
                    @php
                        $data = $notification->data;
                        $type = $notification->type;
                        $isUnread = is_null($notification->read_at);
                        
                        // Determine if this is a chat notification
                        $isChatNotification = str_contains($type, 'NewMessageNotification');
                    @endphp
                    
                    <div class="relative bg-white dark:bg-slate-900 border transition-all duration-200 rounded-3xl p-5 {{ $isUnread ? 'border-primary/40 bg-gradient-to-r from-primary/[0.02] to-transparent shadow-sm' : 'border-slate-200/50 dark:border-slate-800/80' }}">
                        <div class="flex items-start gap-4">
                            {{-- Avatar or Icon --}}
                            <div class="flex-shrink-0">
                                @if($isChatNotification && isset($data['sender_avatar']))
                                    <div class="w-12 h-12 rounded-full overflow-hidden ring-2 ring-primary/20">
                                        <img src="{{ $data['sender_avatar'] }}" alt="{{ $data['sender_name'] ?? __('User') }}" class="w-full h-full object-cover" />
                                    </div>
                                @else
                                    @php
                                        $iconName = $data['icon'] ?? 'o-bell';
                                        $iconType = $data['type'] ?? 'info';
                                        $iconClass = match($iconType) {
                                            'success' => 'text-emerald-500 bg-emerald-500/10 dark:bg-emerald-500/20',
                                            'error' => 'text-rose-500 bg-rose-500/10 dark:bg-rose-500/20',
                                            'warning' => 'text-amber-500 bg-amber-500/10 dark:bg-amber-500/20',
                                            default => 'text-primary bg-primary/10 dark:bg-primary/20',
                                        };
                                    @endphp
                                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center {{ $iconClass }}">
                                        <x-icon :name="$iconName" class="w-6 h-6" />
                                    </div>
                                @endif
                            </div>

                            {{-- Content details --}}
                            <div class="flex-1 min-w-0">
                                @if($isChatNotification)
                                    <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2.5">
                                                <h3 class="font-black text-slate-800 dark:text-slate-250">{{ $data['sender_name'] ?? __('Unknown User') }}</h3>
                                                @if($isUnread)
                                                    <span class="px-1.5 py-0.5 rounded-full bg-primary/10 text-primary text-[9px] font-black uppercase tracking-wider">{{ __('New') }}</span>
                                                @endif
                                            </div>
                                            
                                            <p class="text-sm text-slate-650 dark:text-slate-400 font-medium mt-1 leading-relaxed">
                                                {{ $data['body'] ?? __('Sent you a message') }}
                                            </p>
                                            
                                            {{-- Metadata --}}
                                            <div class="flex items-center gap-4 mt-4">
                                                <span class="text-xs font-semibold text-slate-400 dark:text-slate-500 flex items-center gap-1.5">
                                                    <x-icon name="o-clock" class="w-3.5 h-3.5" />
                                                    {{ $notification->created_at->diffForHumans() }}
                                                </span>
                                                
                                                @if(isset($data['has_attachment']) && $data['has_attachment'])
                                                    <span class="text-xs font-semibold text-slate-450 dark:text-slate-500 flex items-center gap-1.5">
                                                        <x-icon name="o-paper-clip" class="w-3.5 h-3.5" />
                                                        {{ __('Attachment') }}
                                                    </span>
                                                @endif
                                            </div>
                                            
                                            {{-- Action Button --}}
                                            @if(isset($data['url']))
                                                <div class="mt-4">
                                                    <a href="{{ $data['url'] }}" class="btn btn-sm btn-primary rounded-xl font-bold gap-2" wire:navigate>
                                                        <x-icon name="o-chat-bubble-left-right" class="w-4 h-4" />
                                                        {{ __('Open Chat') }}
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                        
                                        {{-- Actions --}}
                                        <div class="flex items-center md:flex-col gap-2">
                                            @if($isUnread)
                                                <button
                                                    wire:click="markAsRead('{{ $notification->id }}')"
                                                    class="btn btn-xs btn-circle btn-ghost text-slate-500 hover:bg-slate-100 dark:hover:bg-white/5"
                                                    title="{{ __('Mark as read') }}"
                                                >
                                                    <x-icon name="o-check" class="w-4 h-4" />
                                                </button>
                                            @endif
                                            <button
                                                wire:click="deleteNotification('{{ $notification->id }}')"
                                                wire:confirm="{{ __('Delete this notification?') }}"
                                                class="btn btn-xs btn-circle btn-ghost text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/20"
                                                title="{{ __('Delete') }}"
                                            >
                                                <x-icon name="o-trash" class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </div>
                                @else
                                    <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2.5">
                                                <h3 class="font-black text-slate-800 dark:text-slate-250">{{ $data['title'] ?? __('Notification') }}</h3>
                                                @if($isUnread)
                                                    <span class="px-1.5 py-0.5 rounded-full bg-primary/10 text-primary text-[9px] font-black uppercase tracking-wider">{{ __('New') }}</span>
                                                @endif
                                            </div>
                                            
                                            <p class="text-sm text-slate-500 dark:text-slate-400 font-medium mt-1 leading-relaxed">{{ $data['message'] ?? '' }}</p>
                                            
                                            <div class="flex items-center gap-4 mt-4">
                                                <span class="text-xs font-semibold text-slate-400 dark:text-slate-500 flex items-center gap-1.5">
                                                    <x-icon name="o-clock" class="w-3.5 h-3.5" />
                                                    {{ $notification->created_at->diffForHumans() }}
                                                </span>
                                            </div>
                                            
                                            @if(isset($data['url']) || isset($data['action_url']))
                                                <div class="mt-4">
                                                    <a href="{{ $data['url'] ?? $data['action_url'] }}" class="btn btn-sm btn-outline border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-950/45 rounded-xl font-bold gap-2 text-xs" wire:navigate>
                                                        {{ $data['action_text'] ?? __('View Details') }}
                                                        <x-icon name="o-arrow-right" class="w-4 h-4" />
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <div class="flex items-center md:flex-col gap-2">
                                            @if($isUnread)
                                                <button
                                                    wire:click="markAsRead('{{ $notification->id }}')"
                                                    class="btn btn-xs btn-circle btn-ghost text-slate-500 hover:bg-slate-100 dark:hover:bg-white/5"
                                                    title="{{ __('Mark as read') }}"
                                                >
                                                    <x-icon name="o-check" class="w-4 h-4" />
                                                </button>
                                            @endif
                                            <button
                                                wire:click="deleteNotification('{{ $notification->id }}')"
                                                wire:confirm="{{ __('Delete this notification?') }}"
                                                class="btn btn-xs btn-circle btn-ghost text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/20"
                                                title="{{ __('Delete') }}"
                                            >
                                                <x-icon name="o-trash" class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white dark:bg-slate-900 border border-slate-200/50 dark:border-slate-800/80 rounded-3xl p-12 text-center">
                        <x-icon name="o-bell-slash" class="w-16 h-16 mx-auto text-slate-300 dark:text-slate-705 mb-4" />
                        <h3 class="text-lg font-black text-slate-700 dark:text-slate-300">{{ __('No notifications found') }}</h3>
                        <p class="text-sm font-semibold text-slate-550 dark:text-slate-500 mt-2">{{ __("You are all caught up!") }}</p>
                    </div>
                @endforelse
            </div>

            @if($this->notifications->hasPages())
                <div class="mt-8">{{ $this->notifications->links() }}</div>
            @endif
        </div>
    @endif

    {{-- Preferences Tab --}}
    @if($activeTab === 'preferences')
        <div class="space-y-6">
            {{-- Info Alert --}}
            <div class="bg-slate-50 dark:bg-slate-950 border border-slate-200/40 dark:border-slate-800/30 rounded-3xl p-5 flex items-start gap-4">
                <div class="w-10 h-10 rounded-2xl bg-primary/10 flex items-center justify-center text-primary flex-shrink-0">
                    <x-icon name="o-information-circle" class="w-5 h-5" />
                </div>
                <div class="flex-1">
                    <h3 class="font-black text-slate-800 dark:text-slate-250 text-sm mb-1">{{ __('Notification Channels') }}</h3>
                    <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 leading-relaxed">
                        <strong>{{ __('Push:') }}</strong> {{ __('Browser push notifications') }} &bull;
                        <strong>{{ __('Email:') }}</strong> {{ __('Email updates') }} &bull;
                        <strong>{{ __('Database:') }}</strong> {{ __('In-app notification center') }}
                    </p>
                </div>
            </div>

            {{-- Actions --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-200/50 dark:border-slate-800/80 rounded-3xl p-5 flex items-center justify-between">
                <h3 class="text-base font-black text-slate-800 dark:text-slate-200">{{ __('Notification Settings') }}</h3>
                <div class="flex gap-2">
                    <button
                        wire:click="enableAll"
                        wire:loading.attr="disabled"
                        class="btn btn-sm btn-outline border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-950/45 rounded-xl font-bold text-xs"
                    >
                        {{ __('Enable All') }}
                    </button>
                    <button
                        wire:click="disableAll"
                        wire:loading.attr="disabled"
                        class="btn btn-sm btn-ghost hover:bg-rose-50 dark:hover:bg-rose-950/20 text-rose-650 rounded-xl font-bold text-xs"
                    >
                        {{ __('Disable All') }}
                    </button>
                </div>
            </div>

            {{-- Preferences List --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-200/50 dark:border-slate-800/80 rounded-3xl overflow-hidden divide-y divide-slate-100 dark:divide-slate-800/60">
                @foreach($categories as $category => $details)
                    <div class="p-6 flex flex-col md:flex-row md:items-center justify-between gap-6 hover:bg-slate-50/50 dark:hover:bg-slate-950/10 transition">
                        <div class="flex-1 min-w-0">
                            <h4 class="font-black text-slate-800 dark:text-slate-200">{{ $details['name'] }}</h4>
                            <p class="text-xs font-semibold text-slate-500 dark:text-slate-450 mt-1">{{ $details['description'] }}</p>
                        </div>
                        
                        {{-- Channel Toggles --}}
                        <div class="flex items-center gap-6">
                            {{-- Push --}}
                            <div class="flex flex-col items-center gap-1.5">
                                <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">{{ __('Push') }}</span>
                                <x-toggle
                                    wire:model.live="preferences.{{ $category }}.push_enabled"
                                    wire:change="savePreferences"
                                    class="toggle-primary toggle-sm"
                                />
                            </div>
                            
                            {{-- Email --}}
                            <div class="flex flex-col items-center gap-1.5">
                                <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">{{ __('Email') }}</span>
                                <x-toggle
                                    wire:model.live="preferences.{{ $category }}.email_enabled"
                                    wire:change="savePreferences"
                                    class="toggle-secondary toggle-sm"
                                />
                            </div>

                            {{-- Database --}}
                            <div class="flex flex-col items-center gap-1.5">
                                <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 dark:text-slate-500">{{ __('Database') }}</span>
                                <x-toggle
                                    wire:model.live="preferences.{{ $category }}.database_enabled"
                                    wire:change="savePreferences"
                                    class="toggle-accent toggle-sm"
                                />
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
