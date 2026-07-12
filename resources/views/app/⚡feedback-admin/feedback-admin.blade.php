<div>
    <x-header :title="__('Anonymous Nasiha & Feedback')" :subtitle="__('Inbox for community suggestions, complaints, and advice.')">
        <x-slot:actions>
            @if($unreadCount > 0)
                <x-button label="{{ __('Mark All Read (:count)', ['count' => $unreadCount]) }}" icon="o-check-circle" wire:click="markAllAsRead" class="btn-primary btn-sm" />
            @endif
        </x-slot:actions>
    </x-header>

    {{-- Filters --}}
    <x-card class="mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-input icon="o-magnifying-glass" :placeholder="__('Search messages...')" wire:model.live.debounce.300ms="search" />

            <x-select wire:model.live="filterType" :options="[
                ['id' => '', 'name' => __('All Types')],
                ['id' => 'advice', 'name' => __('Advice (Nasiha)')],
                ['id' => 'suggestion', 'name' => __('Suggestion')],
                ['id' => 'complaint', 'name' => __('Complaint')]
            ]" option-value="id" option-label="name" />

            <div class="flex items-center">
                <x-checkbox label="{{ __('Show Unread Only') }}" wire:model.live="showUnreadOnly" />
            </div>
        </div>
    </x-card>

    {{-- Messages List --}}
    <div class="space-y-4">
        @forelse($messages as $msg)
            <x-card class="shadow-sm border-l-4 {{ $msg->type === 'complaint' ? 'border-error' : ($msg->type === 'suggestion' ? 'border-info' : 'border-success') }} {{ !$msg->is_read ? 'bg-base-200/50 dark:bg-base-200' : 'bg-base-100' }}">
                <div class="flex justify-between items-start gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="badge {{ $msg->type === 'complaint' ? 'badge-error' : ($msg->type === 'suggestion' ? 'badge-info' : 'badge-success') }} badge-sm text-white uppercase font-bold tracking-wider">
                                {{ $msg->type }}
                            </span>
                            @if(!$msg->is_read)
                                <span class="badge badge-warning badge-sm">{{ __('New') }}</span>
                            @endif
                            <span class="text-xs text-base-content/50 ml-2" title="{{ $msg->created_at }}">
                                <x-icon name="o-clock" class="w-3 h-3 inline mr-1" />
                                {{ $msg->created_at->diffForHumans() }}
                            </span>
                        </div>
                        
                        <p class="text-base-content whitespace-pre-wrap leading-relaxed mt-2">{{ $msg->message }}</p>
                    </div>
                    
                    <div class="flex flex-col gap-2">
                        @if($msg->is_read)
                            <x-button icon="o-envelope" wire:click="toggleRead({{ $msg->id }})" class="btn-ghost btn-sm btn-circle" tooltip="{{ __('Mark as unread') }}" />
                        @else
                            <x-button icon="o-envelope-open" wire:click="toggleRead({{ $msg->id }})" class="btn-ghost btn-sm btn-circle text-primary" tooltip="{{ __('Mark as read') }}" />
                        @endif

                        <x-button icon="o-trash" wire:click="deleteMessage({{ $msg->id }})" wire:confirm="{{ __('Are you sure you want to delete this message?') }}" class="btn-ghost btn-sm btn-circle text-error" tooltip="{{ __('Delete') }}" />
                    </div>
                </div>
            </x-card>
        @empty
            <div class="text-center py-12">
                <x-icon name="o-inbox" class="w-16 h-16 mx-auto text-base-content/20 mb-4" />
                <p class="text-lg font-bold text-base-content/50">{{ __('Inbox is empty') }}</p>
                <p class="text-sm text-base-content/40">{{ __('No messages found matching your criteria.') }}</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="mt-6">
        {{ $messages->links() }}
    </div>
</div>
