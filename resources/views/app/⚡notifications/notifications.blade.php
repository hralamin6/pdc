<div>
    <x-header :title="__('Notifications')" :subtitle="__('Manage your notifications and preferences')" separator />

    {{-- Tab Navigation --}}
    <div class="mb-6">
        <div role="tablist" class="tabs tabs-boxed">
            <a
                role="tab"
                class="tab {{ $activeTab === 'center' ? 'tab-active' : '' }}"
                wire:click="$set('activeTab', 'center')"
            >
                <x-icon name="o-inbox" class="w-4 h-4 mr-2" />
                {{ __('Notification Center') }}
                @if($unreadCount > 0)
                    <x-badge value="{{ $unreadCount }}" class="badge-error badge-sm ml-2" />
                @endif
            </a>
            <a
                role="tab"
                class="tab {{ $activeTab === 'preferences' ? 'tab-active' : '' }}"
                wire:click="$set('activeTab', 'preferences')"
            >
                <x-icon name="o-cog-6-tooth" class="w-4 h-4 mr-2" />
                {{ __('Preferences') }}
            </a>
        </div>
    </div>

    {{-- Notification Center Tab --}}
    @if($activeTab === 'center')
        <div class="space-y-6">
            {{-- Actions --}}
            <x-card>
                <div class="flex items-center justify-between">
                    <div role="tablist" class="tabs tabs-boxed">
                        <a role="tab" class="tab {{ $selectedFilter === 'all' ? 'tab-active' : '' }}" wire:click="$set('selectedFilter', 'all')">
                            {{ __('All') }}
                        </a>
                        <a role="tab" class="tab {{ $selectedFilter === 'unread' ? 'tab-active' : '' }}" wire:click="$set('selectedFilter', 'unread')">
                            {{ __('Unread') }}
                            @if($unreadCount > 0)
                                <x-badge value="{{ $unreadCount }}" class="badge-primary ml-2" />
                            @endif
                        </a>
                        <a role="tab" class="tab {{ $selectedFilter === 'read' ? 'tab-active' : '' }}" wire:click="$set('selectedFilter', 'read')">
                            {{ __('Read') }}
                        </a>
                    </div>
                    <div class="flex gap-2">
                        <x-button :label="__('Mark All Read')" icon="o-check-circle" class="btn-primary btn-sm" wire:click="markAllAsRead" spinner />
                        <x-button :label="__('Delete All')" icon="o-trash" class="btn-error btn-sm" wire:click="deleteAll" wire:confirm="{{ __('Delete all notifications?') }}" spinner />
                    </div>
                </div>
            </x-card>

            {{-- Notifications List --}}
            <div class="space-y-3">
                @forelse($this->notifications as $notification)
                    @php
                        $data = $notification->data;
                        $type = $notification->type;
                        $isUnread = is_null($notification->read_at);
                        
                        // Determine if this is a chat notification
                        $isChatNotification = str_contains($type, 'NewMessageNotification');
                    @endphp
                    
                    <x-card class="hover:shadow-lg transition-all duration-200 {{ $isUnread ? 'bg-primary/5 border-l-4 border-primary' : 'border-l-4 border-transparent' }}">
                        <div class="flex items-start gap-4">
                            {{-- Avatar/Icon --}}
                            <div class="flex-shrink-0">
                                @if($isChatNotification && isset($data['sender_avatar']))
                                    <div class="avatar {{ $isUnread ? 'online' : '' }}">
                                        <div class="w-12 h-12 rounded-full ring-2 ring-primary/20">
                                            <img src="{{ $data['sender_avatar'] }}" alt="{{ $data['sender_name'] ?? 'User' }}" />
                                        </div>
                                    </div>
                                @else
                                    @php
                                        $iconName = $data['icon'] ?? 'o-bell';
                                        $iconType = $data['type'] ?? 'info';
                                        $iconClass = match($iconType) {
                                            'success' => 'text-success bg-success/10',
                                            'error' => 'text-error bg-error/10',
                                            'warning' => 'text-warning bg-warning/10',
                                            default => 'text-info bg-info/10',
                                        };
                                    @endphp
                                    <div class="p-3 rounded-full {{ $iconClass }}">
                                        <x-icon :name="$iconName" class="w-6 h-6" />
                                    </div>
                                @endif
                            </div>

                            {{-- Content --}}
                            <div class="flex-1 min-w-0">
                                @if($isChatNotification)
                                    {{-- Chat Notification --}}
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2">
                                                <h3 class="font-semibold text-base">{{ $data['sender_name'] ?? __('Unknown User') }}</h3>
                                                @if($isUnread)
                                                    <span class="w-2 h-2 bg-primary rounded-full animate-pulse"></span>
                                                @endif
                                            </div>
                                            
                                            <p class="text-sm text-base-content/90 mt-1 line-clamp-2">
                                                {{ $data['body'] ?? __('Sent you a message') }}
                                            </p>
                                            
                                            {{-- Metadata --}}
                                            <div class="flex items-center gap-3 mt-3">
                                                <div class="flex items-center gap-1 text-xs text-base-content/60">
                                                    <x-icon name="o-clock" class="w-3.5 h-3.5" />
                                                    <span>{{ $notification->created_at->diffForHumans() }}</span>
                                                </div>
                                                
                                                @if(isset($data['has_attachment']) && $data['has_attachment'])
                                                    <div class="flex items-center gap-1 text-xs text-base-content/60">
                                                        <x-icon name="o-paper-clip" class="w-3.5 h-3.5" />
                                                        <span>{{ __('Attachment') }}</span>
                                                    </div>
                                                @endif
                                                
                                                @if($isUnread)
                                                    <x-badge :value="__('New')" class="badge-primary badge-xs" />
                                                @endif
                                            </div>
                                            
                                            {{-- Action Button --}}
                                            @if(isset($data['url']))
                                                <div class="mt-3">
                                                    <a href="{{ $data['url'] }}" class="btn btn-primary btn-sm gap-2" wire:navigate>
                                                        <x-icon name="o-chat-bubble-left-right" class="w-4 h-4" />
                                                        {{ __('Open Chat') }}
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                        
                                        {{-- Actions --}}
                                        <div class="flex flex-col gap-2">
                                            @if($isUnread)
                                                <x-button 
                                                    icon="o-check" 
                                                    class="btn-ghost btn-sm btn-circle" 
                                                    wire:click="markAsRead('{{ $notification->id }}')" 
                                                    :tooltip="__('Mark as read')" 
                                                    spinner 
                                                />
                                            @endif
                                            <x-button 
                                                icon="o-trash" 
                                                class="btn-ghost btn-sm btn-circle text-error hover:bg-error/10" 
                                                wire:click="deleteNotification('{{ $notification->id }}')" 
                                                wire:confirm="{{ __('Delete this notification?') }}" 
                                                :tooltip="__('Delete')" 
                                                spinner 
                                            />
                                        </div>
                                    </div>
                                @else
                                    {{-- Regular Notification --}}
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="flex-1">
                                            <h3 class="font-semibold text-base">{{ $data['title'] ?? __('Notification') }}</h3>
                                            <p class="text-sm text-base-content/70 mt-1">{{ $data['message'] ?? '' }}</p>
                                            
                                            <div class="flex items-center gap-3 mt-3">
                                                <div class="flex items-center gap-1 text-xs text-base-content/60">
                                                    <x-icon name="o-clock" class="w-3.5 h-3.5" />
                                                    <span>{{ $notification->created_at->diffForHumans() }}</span>
                                                </div>
                                                @if($isUnread)
                                                    <x-badge :value="__('New')" class="badge-primary badge-xs" />
                                                @endif
                                            </div>
                                            
                                            @if(isset($data['url']))
                                                <div class="mt-3">
                                                    <a href="{{ $data['url'] }}" class="btn btn-sm btn-outline gap-2" wire:navigate>
                                                        {{ __('View Details') }}
                                                        <x-icon name="o-arrow-right" class="w-4 h-4" />
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <div class="flex flex-col gap-2">
                                            @if($isUnread)
                                                <x-button 
                                                    icon="o-check" 
                                                    class="btn-ghost btn-sm btn-circle" 
                                                    wire:click="markAsRead('{{ $notification->id }}')" 
                                                    :tooltip="__('Mark as read')" 
                                                    spinner 
                                                />
                                            @endif
                                            <x-button 
                                                icon="o-trash" 
                                                class="btn-ghost btn-sm btn-circle text-error hover:bg-error/10" 
                                                wire:click="deleteNotification('{{ $notification->id }}')" 
                                                wire:confirm="{{ __('Delete this notification?') }}" 
                                                :tooltip="__('Delete')" 
                                                spinner 
                                            />
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </x-card>
                @empty
                    <x-card>
                        <div class="text-center py-12">
                            <x-icon name="o-bell-slash" class="w-16 h-16 mx-auto text-base-content/30" />
                            <h3 class="mt-4 text-lg font-semibold text-base-content/70">{{ __('No notifications') }}</h3>
                            <p class="mt-2 text-sm text-base-content/50">{{ __("You're all caught up!") }}</p>
                        </div>
                    </x-card>
                @endforelse
            </div>

            @if($this->notifications->hasPages())
                <div class="mt-6">{{ $this->notifications->links() }}</div>
            @endif
        </div>
    @endif

    {{-- Preferences Tab --}}
    @if($activeTab === 'preferences')
        <div class="space-y-6">
            {{-- Info Card --}}
            <x-card>
                <div class="alert alert-info">
                    <x-icon name="o-information-circle" class="w-5 h-5" />
                    <div class="text-sm">
                        <strong>{{ __('Push:') }}</strong> {{ __('Browser notifications') }} •
                        <strong>{{ __('Email:') }}</strong> {{ __('Email messages') }} •
                        <strong>{{ __('Database:') }}</strong> {{ __('In-app notifications') }}
                    </div>
                </div>
            </x-card>

            {{-- Actions --}}
            <x-card>
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold">{{ __('Notification Preferences') }}</h3>
                    <div class="flex gap-2">
                        <x-button :label="__('Enable All')" icon="o-check-circle" class="btn-success btn-sm" wire:click="enableAll" spinner />
                        <x-button :label="__('Disable All')" icon="o-x-circle" class="btn-error btn-sm" wire:click="disableAll" spinner />
                    </div>
                </div>
            </x-card>

            {{-- Preferences Table --}}
            <x-card>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="w-1/3">
                                    <div class="flex flex-col">
                                        <span class="font-semibold">{{ __('Category') }}</span>
                                        <span class="text-xs font-normal text-base-content/60">{{ __('Notification type') }}</span>
                                    </div>
                                </th>
                                <th class="text-center">
                                    <div class="flex flex-col items-center gap-1">
                                        <x-icon name="o-bell" class="w-5 h-5 text-primary" />
                                        <span class="text-xs font-semibold">{{ __('Push') }}</span>
                                        <span class="text-xs font-normal text-base-content/60">{{ __('Browser') }}</span>
                                    </div>
                                </th>
                                <th class="text-center">
                                    <div class="flex flex-col items-center gap-1">
                                        <x-icon name="o-envelope" class="w-5 h-5 text-secondary" />
                                        <span class="text-xs font-semibold">{{ __('Email') }}</span>
                                        <span class="text-xs font-normal text-base-content/60">{{ __('Inbox') }}</span>
                                    </div>
                                </th>
                                <th class="text-center">
                                    <div class="flex flex-col items-center gap-1">
                                        <x-icon name="o-inbox" class="w-5 h-5 text-accent" />
                                        <span class="text-xs font-semibold">{{ __('Database') }}</span>
                                        <span class="text-xs font-normal text-base-content/60">{{ __('In-app') }}</span>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categories as $category => $details)
                                <tr class="hover">
                                    <td>
                                        <div class="flex flex-col">
                                            <span class="font-semibold">{{ $details['name'] }}</span>
                                            <span class="text-xs text-base-content/60">{{ $details['description'] }}</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <x-toggle 
                                            wire:model.live="preferences.{{ $category }}.push_enabled" 
                                            wire:change="savePreferences"
                                            class="toggle-primary"
                                        />
                                    </td>
                                    <td class="text-center">
                                        <x-toggle 
                                            wire:model.live="preferences.{{ $category }}.email_enabled" 
                                            wire:change="savePreferences"
                                            class="toggle-secondary"
                                        />
                                    </td>
                                    <td class="text-center">
                                        <x-toggle 
                                            wire:model.live="preferences.{{ $category }}.database_enabled" 
                                            wire:change="savePreferences"
                                            class="toggle-accent"
                                        />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>
    @endif
</div>
