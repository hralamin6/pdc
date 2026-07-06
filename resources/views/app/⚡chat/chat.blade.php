{{-- ========================================== --}}
{{-- CHAT COMPONENT --}}
{{-- Real-time messaging with Laravel Echo --}}
{{-- ========================================== --}}
<div
    class="h-[calc(100vh-8rem)]"
    x-data="chatApp()"
    x-init="init()"
    @message-sent.window="handleMessageSent()"
>
    {{-- ========================================== --}}
    {{-- FLASH MESSAGES --}}
    {{-- ========================================== --}}
    @if (session()->has('message'))
        <div class="alert alert-success shadow-lg mb-4" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)">
            <x-icon name="o-check-circle" class="w-5 h-5" />
            <span>{{ session('message') }}</span>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-error shadow-lg mb-4" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
            <x-icon name="o-exclamation-triangle" class="w-5 h-5" />
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="flex h-full bg-base-100 rounded-xl shadow-xl overflow-hidden">
        {{-- ========================================== --}}
        {{-- SIDEBAR: CONVERSATIONS LIST --}}
        {{-- ========================================== --}}
        <div class="w-full md:w-96 border-r border-base-300 flex flex-col">
            {{-- Sidebar Header --}}
            <div class="p-4 border-b border-base-300 bg-gradient-to-r from-primary/10 to-secondary/10">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-xl font-bold text-base-content flex items-center gap-2">
                        <x-icon name="o-chat-bubble-left-right" class="w-6 h-6 text-primary" />
                        Messages
                    </h2>
                    <button wire:click="$set('showNewChatModal', true)" class="btn btn-primary btn-sm btn-circle shadow-lg">
                        <x-icon name="o-plus" class="w-5 h-5" />
                    </button>
                </div>

                {{-- Conversation Search --}}
                <x-input
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search conversations..."
                    icon="o-magnifying-glass"
                    class="input-sm"
                />
            </div>

            {{-- Conversations List --}}
            <div class="flex-1 overflow-y-auto">
                @forelse($this->conversations as $conversation)
                    @php
                        $otherUser = $conversation->getOtherUser(auth()->id());
                        $unreadCount = $conversation->getUnreadCount(auth()->id());
                        $latestMessage = $conversation->latestMessage;
                        $isActive = $selectedConversationId == $conversation->id;
                    @endphp

                    {{-- Conversation Item --}}
                    <div
                        wire:click="selectConversation({{ $conversation->id }})"
                        wire:key="conversation-{{ $conversation->id }}"
                        class="p-4 border-b border-base-300 cursor-pointer hover:bg-base-200/50 transition-all duration-200 {{ $isActive ? 'bg-primary/10 border-l-4 border-l-primary' : '' }}"
                    >
                        <div class="flex items-start gap-3">
                            <div class="avatar {{ $otherUser->isOnline() ? 'online' : 'offline' }}">
                                <div class="w-12 h-12 rounded-full ring-2 ring-base-300">
                                    <img src="{{ $otherUser->avatar_url }}" alt="{{ $otherUser->name }}" />
                                </div>
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between mb-1">
                                    <h3 class="font-semibold text-base-content truncate flex items-center gap-2">
                                        {{ $otherUser->name }}
                                        @if($unreadCount > 0)
                                            <span class="badge badge-primary badge-sm">{{ $unreadCount }}</span>
                                        @endif
                                    </h3>
                                    @if($latestMessage)
                                        <span class="text-xs text-base-content/60">
                                            {{ $latestMessage->created_at->diffForHumans(null, true) }}
                                        </span>
                                    @endif
                                </div>

                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-sm text-base-content/70 truncate flex-1">
                                        @if($latestMessage)
                                            @if($latestMessage->user_id === auth()->id())
                                                <span class="inline-flex items-center gap-1">
                                                    @if($latestMessage->read_at)
                                                        <svg class="w-3 h-3 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M0 11l2-2 5 5L18 3l2 2L7 18z"/>
                                                            <path d="M0 11l2-2 5 5L18 3l2 2L7 18z" transform="translate(4, 0)"/>
                                                        </svg>
                                                    @else
                                                        <svg class="w-3 h-3 text-base-content/50" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M0 11l2-2 5 5L18 3l2 2L7 18z"/>
                                                        </svg>
                                                    @endif
                                                </span>
                                            @endif
                                            {{ Str::limit($latestMessage->body ?? '📎 Attachment', 35) }}
                                        @else
                                            <span class="text-base-content/50 italic">No messages yet</span>
                                        @endif
                                    </p>
                                </div>

                                {{-- Typing Indicator --}}
                                <div class="text-xs text-primary flex items-center gap-2 mt-1"
                                     x-show="typingConversations[{{ $conversation->id }}]"
                                     x-cloak>
                                    <span class="loading loading-dots loading-xs"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    {{-- Empty State --}}
                    <div class="flex flex-col items-center justify-center h-full p-8 text-center">
                        <x-icon name="o-chat-bubble-left-right" class="w-16 h-16 text-base-content/30 mb-4" />
                        <p class="text-base-content/60 mb-4">No conversations yet</p>
                        <button wire:click="$set('showNewChatModal', true)" class="btn btn-primary btn-sm">
                            Start a conversation
                        </button>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ========================================== --}}
        {{-- MAIN CHAT AREA --}}
        {{-- ========================================== --}}
        <div class="flex-1 flex flex-col">
            @if($selectedConversationId && $this->selectedConversation)
                @php
                    $otherUser = $this->selectedConversation->getOtherUser(auth()->id());
                @endphp

                {{-- Chat Header --}}
                <div class="p-4 border-b border-base-300 bg-base-100 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="avatar {{ $otherUser->isOnline() ? 'online' : 'offline' }}">
                                <div class="w-10 h-10 rounded-full ring-2 ring-primary ring-offset-2">
                                    <img src="{{ $otherUser->avatar_url }}" alt="{{ $otherUser->name }}" />
                                </div>
                            </div>
                            <div>
                                <h3 class="font-semibold text-base-content">{{ $otherUser->name }}</h3>
                                <p class="text-xs text-base-content/60 flex items-center gap-1">
                                    @if($otherUser->isOnline())
                                        <span class="w-2 h-2 bg-success rounded-full"></span>
                                        Active now
                                    @elseif($otherUser->last_seen)
                                        Last seen {{ $otherUser->last_seen->diffForHumans() }}
                                    @else
                                        Offline
                                    @endif
                                </p>
                            </div>
                          <div class="mb-2 px-1" x-show="activeConversationTyping" x-cloak>
                            <div class="flex items-center gap-2 text-sm text-base-content/70">
                              <span class="loading loading-dots loading-xs"></span>
                            </div>
                          </div>
                        </div>

                        <div class="flex gap-2">
                            <div class="dropdown dropdown-end">
                                <label tabindex="0" class="btn btn-ghost btn-sm btn-circle">
                                    <x-icon name="o-magnifying-glass" class="w-5 h-5" />
                                </label>
                                <div tabindex="0" class="dropdown-content z-[1] card card-compact w-80 p-2 shadow-lg bg-base-100 border border-base-300 mt-2">
                                    <div class="card-body">
                                        <x-input
                                            wire:model.live.debounce.300ms="messageSearch"
                                            placeholder="Search messages..."
                                            icon="o-magnifying-glass"
                                            class="input-sm"
                                        />
                                        @if($messageSearch)
                                            <button wire:click="$set('messageSearch', '')" class="btn btn-ghost btn-xs mt-2">
                                                Clear search
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="dropdown dropdown-end">
                                <label tabindex="0" class="btn btn-ghost btn-sm btn-circle">
                                    <x-icon name="o-ellipsis-vertical" class="w-5 h-5" />
                                </label>
                                <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-56 border border-base-300">
                                    <li>
                                        <button class="text-sm">
                                            <x-icon name="o-bell" class="w-4 h-4" />
                                            Mute notifications
                                        </button>
                                    </li>
                                    <li>
                                        <button class="text-sm">
                                            <x-icon name="o-archive-box" class="w-4 h-4" />
                                            Archive conversation
                                        </button>
                                    </li>
                                    <div class="divider my-0"></div>
                                    @if($this->isUserBlocked())
                                        <li>
                                            <button wire:click="unblockUser" class="text-sm text-success">
                                                <x-icon name="o-check-circle" class="w-4 h-4" />
                                                Unblock {{ $otherUser->name }}
                                            </button>
                                        </li>
                                    @else
                                        <li>
                                            <button
                                                wire:click="blockUser"
                                                wire:confirm="Are you sure you want to block {{ $otherUser->name }}?"
                                                class="text-sm text-warning"
                                            >
                                                <x-icon name="o-no-symbol" class="w-4 h-4" />
                                                Block {{ $otherUser->name }}
                                            </button>
                                        </li>
                                    @endif
                                    <div class="divider my-0"></div>
                                    <li>
                                        <button
                                            wire:click="deleteConversation"
                                            wire:confirm="Are you sure you want to delete this conversation? This action cannot be undone."
                                            class="text-sm text-error"
                                        >
                                            <x-icon name="o-trash" class="w-4 h-4" />
                                            Delete conversation
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ========================================== --}}
                {{-- MESSAGES AREA --}}
                {{-- ========================================== --}}
                <div
                    class="flex-1 overflow-y-auto p-4 space-y-4 bg-base-100 relative"
                    id="message-container"
                    x-init="scrollToBottom($el)"
                    @scroll-to-bottom.window="scrollToBottom($el)"
                    @scroll="handleScroll($el)"
                    x-ref="messageContainer"
                >
                    {{-- Message Search Alert --}}
                    @if($messageSearch)
                        <div class="alert alert-info shadow-lg mb-4">
                            <x-icon name="o-magnifying-glass" class="w-5 h-5" />
                            <span>Searching for: <strong>{{ $messageSearch }}</strong></span>
                            <button wire:click="$set('messageSearch', '')" class="btn btn-ghost btn-sm btn-circle">
                                <x-icon name="o-x-mark" class="w-4 h-4" />
                            </button>
                        </div>
                    @endif

                    @php
                        $lastDate = null;

                        // Helper function for Messenger-style time
                        function messengerTime($datetime) {
                            $now = now();
                            $diff = $datetime->diffInSeconds($now);

                            if ($diff < 60) {
                                return 'Just now';
                            } elseif ($diff < 3600) {
                                return floor($datetime->diffInMinutes($now)) . 'm';
                            } elseif ($datetime->isToday()) {
                                return $datetime->format('g:i A');
                            } elseif ($datetime->isYesterday()) {
                                return 'Yesterday ' . $datetime->format('g:i A');
                            } elseif ($diff < 604800) { // Less than 7 days
                                return $datetime->format('l g:i A'); // Monday 3:45 PM
                            } else {
                                return $datetime->format('M j, g:i A'); // Jan 15, 3:45 PM
                            }
                        }
                    @endphp

                    {{-- Loading Indicator --}}
                    <div 
                        x-show="isLoadingMore" 
                        class="flex justify-center py-3"
                    >
                        <div class="flex items-center gap-2 text-sm text-base-content/60">
                            <span class="loading loading-spinner loading-sm"></span>
                            <span>Loading older messages...</span>
                        </div>
                    </div>

                    {{-- Messages Loop --}}
                    @forelse($this->chatMessages as $message)
                        @php
                            $isOwn = $message->user_id === auth()->id();
                            $isEdited = !is_null($message->edited_at);
                            $messageDate = $message->created_at->format('Y-m-d');
                            $showDateDivider = $lastDate !== $messageDate;
                            $lastDate = $messageDate;
                        @endphp

                        {{-- Date Divider --}}
                        @if($showDateDivider)
                            <div class="flex items-center justify-center my-4">
                                <div class="px-3 py-1 bg-base-200 rounded-full text-xs text-base-content/70 font-medium shadow-sm">
                                    @if($message->created_at->isToday())
                                        Today
                                    @elseif($message->created_at->isYesterday())
                                        Yesterday
                                    @else
                                        {{ $message->created_at->format('l, F j, Y') }}
                                    @endif
                                </div>
                            </div>
                        @endif

                        {{-- Message Bubble --}}
                        <div
                            class="flex {{ $isOwn ? 'justify-end' : 'justify-start' }}"
                            wire:key="message-{{ $message->id }}"
                            data-message-id="{{ $message->id }}"
                            data-is-own="{{ $isOwn ? 'true' : 'false' }}"
                        >
                            <div class="flex gap-2 max-w-[75%] {{ $isOwn ? 'flex-row-reverse' : 'flex-row' }}">
                                @if(!$isOwn)
                                    <div class="avatar">
                                        <div class="w-8 h-8 rounded-full">
                                            <img src="{{ $message->user->avatar_url }}" alt="{{ $message->user->name }}" />
                                        </div>
                                    </div>
                                @endif

                                <div class="flex flex-col gap-1 {{ $isOwn ? 'items-end' : 'items-start' }}">
                                    @if($message->parent)
                                        <div class="text-xs text-base-content/60 px-3 py-1 bg-base-200 rounded-lg border-l-2 border-primary">
                                            <span class="font-semibold">{{ $message->parent->user->name }}</span>:
                                            {{ Str::limit($message->parent->body ?? 'Attachment', 50) }}
                                        </div>
                                    @endif

                                    <div class="relative group">
                                        <div class="px-4 py-2 rounded-2xl {{ $isOwn ? 'bg-primary text-primary-content' : 'bg-base-200 text-base-content' }}">
                                            @if(!$isOwn)
                                                <p class="text-xs font-semibold mb-1">{{ $message->user->name }}</p>
                                            @endif

                                            @if($message->body)
                                                <div class="text-sm break-words max-w-none">
                                                    @if($messageSearch && str_contains(strtolower($message->body), strtolower($messageSearch)))
                                                        {!! preg_replace('/(' . preg_quote($messageSearch, '/') . ')/i', '<mark class="bg-yellow-300 text-black px-1 rounded">$1</mark>', $message->formatted_body) !!}
                                                    @else
                                                        {!! $message->formatted_body !!}
                                                    @endif
                                                </div>
                                            @endif

                                            @if($message->hasAttachments())
                                                <div class="mt-2 space-y-2">
                                                    @foreach($message->getMedia('attachments') as $media)
                                                        @if(str_starts_with($media->mime_type, 'image/'))
                                                            <img
                                                                src="{{ $media->getUrl() }}"
                                                                alt="{{ $media->file_name }}"
                                                                class="max-w-xs rounded-lg cursor-pointer hover:opacity-90 transition"
                                                                onclick="window.open('{{ $media->getUrl() }}', '_blank')"
                                                            />
                                                        @else
                                                            <a
                                                                href="{{ $media->getUrl() }}"
                                                                target="_blank"
                                                                download
                                                                class="flex items-center gap-2 p-2 bg-base-300/50 rounded-lg hover:bg-base-300 transition"
                                                            >
                                                                <x-icon name="o-document" class="w-5 h-5" />
                                                                <div class="flex-1 min-w-0">
                                                                    <p class="text-xs font-medium truncate">{{ $media->file_name }}</p>
                                                                    <p class="text-xs opacity-70">{{ $media->human_readable_size }}</p>
                                                                </div>
                                                            </a>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @endif

                                            @if($message->reactions->count() > 0)
                                                <div class="flex flex-wrap gap-1 mt-2">
                                                    @php
                                                        $groupedReactions = $message->reactions->groupBy('emoji');
                                                    @endphp
                                                    @foreach($groupedReactions as $emoji => $reactions)
                                                        <button
                                                            @click="addReaction({{ $message->id }}, '{{ $emoji }}')"
                                                            class="px-2 py-0.5 text-xs rounded-full bg-base-300/50 hover:bg-base-300 transition flex items-center gap-1"
                                                        >
                                                            <span>{{ $emoji }}</span>
                                                            <span class="font-semibold">{{ $reactions->count() }}</span>
                                                        </button>
                                                    @endforeach
                                                </div>
                                            @endif

                                            <div class="flex items-center gap-2 mt-1.5">
                                                <span class="text-xs opacity-70" title="{{ $message->created_at->format('l, F j, Y \a\t g:i A') }}">
                                                    {{ messengerTime($message->created_at) }}
                                                </span>
                                                @if($isEdited)
                                                    <span class="text-xs opacity-60 italic">edited</span>
                                                @endif
                                                @if($isOwn)
                                                    @if($message->read_at)
                                                        <span class="text-xs text-blue-500" title="Read at {{ $message->read_at->format('g:i A') }}">
                                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                                <path d="M0 11l2-2 5 5L18 3l2 2L7 18z"/>
                                                                <path d="M0 11l2-2 5 5L18 3l2 2L7 18z" transform="translate(4, 0)"/>
                                                            </svg>
                                                        </span>
                                                    @else
                                                        <span class="text-xs opacity-50" title="Sent">
                                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                                <path d="M0 11l2-2 5 5L18 3l2 2L7 18z"/>
                                                            </svg>
                                                        </span>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Message Actions Dropdown -->
                                        <div class="dropdown dropdown-end {{ $isOwn ? 'dropdown-left' : 'dropdown-right' }} absolute {{ $isOwn ? '-left-8' : '-right-8' }} top-0 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <label tabindex="0" class="btn btn-ghost btn-xs btn-circle">
                                                <x-icon name="o-ellipsis-vertical" class="w-4 h-4" />
                                            </label>
                                            <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-52 border border-base-300">
                                                <li>
                                                    <button @click="addReaction({{ $message->id }}, '👍')" class="text-sm">
                                                        <span class="text-lg">👍</span> Like
                                                    </button>
                                                </li>
                                                <li>
                                                    <button @click="addReaction({{ $message->id }}, '❤️')" class="text-sm">
                                                        <span class="text-lg">❤️</span> Love
                                                    </button>
                                                </li>
                                                <li>
                                                    <button @click="addReaction({{ $message->id }}, '😂')" class="text-sm">
                                                        <span class="text-lg">😂</span> Haha
                                                    </button>
                                                </li>
                                                <div class="divider my-0"></div>
                                                <li>
                                                    <button wire:click="setReplyingTo({{ $message->id }})" class="text-sm">
                                                        <x-icon name="o-arrow-uturn-left" class="w-4 h-4" />
                                                        Reply
                                                    </button>
                                                </li>
                                                @if($isOwn)
                                                    <li>
                                                        <button wire:click="editMessage({{ $message->id }})" class="text-sm">
                                                            <x-icon name="o-pencil" class="w-4 h-4" />
                                                            Edit
                                                        </button>
                                                    </li>
                                                    <div class="divider my-0"></div>
                                                    <li>
                                                        <button
                                                            wire:click="deleteMessage({{ $message->id }})"
                                                            wire:confirm="Delete this message?"
                                                            class="text-sm text-error"
                                                        >
                                                            <x-icon name="o-trash" class="w-4 h-4" />
                                                            Delete
                                                        </button>
                                                    </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        {{-- Empty State --}}
                        <div class="flex flex-col items-center justify-center h-full text-center">
                            <x-icon name="o-chat-bubble-left-right" class="w-16 h-16 text-base-content/20 mb-4" />
                            <p class="text-base-content/60">No messages yet. Start the conversation!</p>
                        </div>
                    @endforelse

                    {{-- Go to Latest Button --}}
                    <div 
                        x-show="showGoToLatest" 
                        x-transition
                        class="fixed bottom-24 right-8 z-10"
                    >
                        <button 
                            @click="scrollToBottom($refs.messageContainer); showGoToLatest = false"
                            class="btn btn-primary btn-circle shadow-lg"
                            title="Go to latest messages"
                        >
                            <x-icon name="o-chevron-down" class="w-5 h-5" />
                        </button>
                    </div>
                </div>

                {{-- ========================================== --}}
                {{-- MESSAGE INPUT FORM --}}
                {{-- ========================================== --}}
                <div class="p-4 border-t border-base-300 bg-base-100">
                    @php
                        $isBlocked = $this->isUserBlocked();
                        $otherUserBlocked = \DB::table('conversation_user')
                            ->where('conversation_id', $selectedConversationId)
                            ->where('user_id', $otherUser->id)
                            ->value('is_blocked');
                    @endphp

                    {{-- Blocked Warning --}}
                    @if($isBlocked || $otherUserBlocked)
                        <div class="alert alert-warning shadow-lg mb-4">
                            <x-icon name="o-no-symbol" class="w-5 h-5" />
                            <div>
                                @if($isBlocked)
                                    <p class="font-semibold">You have blocked this conversation</p>
                                    <p class="text-sm">Unblock to send messages</p>
                                @else
                                    <p class="font-semibold">You cannot send messages</p>
                                    <p class="text-sm">This user has blocked you</p>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- Editing indicator --}}
                    @if($editingMessageId)
                        <div class="mb-2 p-2 bg-warning/10 border-l-4 border-warning rounded-lg flex items-center justify-between">
                            <div class="flex items-center gap-2 text-sm">
                                <x-icon name="o-pencil" class="w-4 h-4 text-warning" />
                                <span class="text-base-content/70">Editing message</span>
                            </div>
                            <button wire:click="cancelEdit" class="btn btn-ghost btn-xs btn-circle">
                                <x-icon name="o-x-mark" class="w-4 h-4" />
                            </button>
                        </div>
                    @endif

                    {{-- Reply indicator --}}
                    @if($replyingTo)
                        @php
                            $replyMessage = $this->chatMessages->firstWhere('id', $replyingTo);
                        @endphp
                        @if($replyMessage)
                            <div class="mb-2 p-2 bg-primary/10 border-l-4 border-primary rounded-lg flex items-center justify-between">
                                <div class="flex items-center gap-2 text-sm">
                                    <x-icon name="o-arrow-uturn-left" class="w-4 h-4 text-primary" />
                                    <span class="text-base-content/70">
                                        Replying to <span class="font-semibold">{{ $replyMessage->user->name }}</span>:
                                        {{ Str::limit($replyMessage->body ?? 'Attachment', 50) }}
                                    </span>
                                </div>
                                <button wire:click="cancelReply" class="btn btn-ghost btn-xs btn-circle">
                                    <x-icon name="o-x-mark" class="w-4 h-4" />
                                </button>
                            </div>
                        @endif
                    @endif

                    @if(!empty($attachments))
                        <div class="mb-2 flex flex-wrap gap-2">
                            @foreach($attachments as $index => $attachment)
                                <div class="relative group">
                                    @if(str_starts_with($attachment->getMimeType(), 'image/'))
                                        <img
                                            src="{{ $attachment->temporaryUrl() }}"
                                            alt="Preview"
                                            class="w-20 h-20 object-cover rounded-lg"
                                        />
                                    @else
                                        <div class="w-20 h-20 bg-base-300 rounded-lg flex items-center justify-center">
                                            <x-icon name="o-document" class="w-8 h-8 text-base-content/50" />
                                        </div>
                                    @endif
                                    <button
                                        wire:click="removeAttachment({{ $index }})"
                                        class="absolute -top-2 -right-2 btn btn-error btn-xs btn-circle"
                                    >
                                        <x-icon name="o-x-mark" class="w-3 h-3" />
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <form wire:submit.prevent="sendMessage" class="flex items-end gap-2">
                        <div>
                            <input
                                type="file"
                                wire:model="attachments"
                                id="file-input"
                                class="hidden"
                                multiple
                                @if($isBlocked || $otherUserBlocked) disabled @endif
                            />
                            <label for="file-input" class="btn btn-ghost btn-circle @if($isBlocked || $otherUserBlocked) btn-disabled @endif">
                                <x-icon name="o-paper-clip" class="w-5 h-5" />
                            </label>
                        </div>

                        <div class="flex-1">
                            <textarea
                                wire:model="body"
                                placeholder="@if($isBlocked || $otherUserBlocked) Cannot send messages @else Type a message... @endif"
                                rows="1"
                                class="textarea textarea-bordered w-full resize-none"
                                @keydown.enter="if (!$event.shiftKey) { $event.preventDefault(); $wire.sendMessage(); }"
                                @input.debounce.500ms="whisperTyping()"
                                @if($isBlocked || $otherUserBlocked) disabled @endif
                            ></textarea>
                          @error('body')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                          @enderror
                        </div>

                        <button
                            type="submit"
                            class="btn btn-primary btn-circle shadow-lg"
                            wire:loading.attr="disabled"
                            @if($isBlocked || $otherUserBlocked) disabled @endif
                        >
                            @if($editingMessageId)
                                <x-icon name="o-check" class="w-5 h-5" />
                            @else
                                <x-icon name="o-paper-airplane" class="w-5 h-5" />
                            @endif
                        </button>
                    </form>
                </div>
            @else
                {{-- Empty State: No Conversation Selected --}}
                <div class="flex flex-col items-center justify-center h-full text-center p-8">
                    <x-icon name="o-chat-bubble-left-ellipsis" class="w-24 h-24 text-base-content/20 mb-4" />
                    <h3 class="text-xl font-semibold text-base-content/70 mb-2">Select a conversation</h3>
                    <p class="text-base-content/50">Choose a conversation from the list to start messaging</p>
                </div>
            @endif
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- NEW CHAT MODAL --}}
    {{-- ========================================== --}}
    @if($showNewChatModal)
        <div class="modal modal-open">
            <div class="modal-box">
                <h3 class="font-bold text-lg mb-4">Start New Conversation</h3>

                <x-input
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search users..."
                    icon="o-magnifying-glass"
                    class="mb-4"
                />

                <div class="max-h-96 overflow-y-auto">
                    @forelse($this->availableUsers as $user)
                        <div
                            wire:click="startNewChat({{ $user->id }})"
                            class="flex items-center gap-3 p-3 hover:bg-base-200 rounded-lg cursor-pointer transition-colors"
                        >
                            <div class="avatar">
                                <div class="w-10 h-10 rounded-full">
                                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" />
                                </div>
                            </div>
                            <div>
                                <h4 class="font-semibold text-base-content">{{ $user->name }}</h4>
                                <p class="text-sm text-base-content/60">{{ $user->email }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-base-content/60 py-8">No users found</p>
                    @endforelse
                </div>

                <div class="modal-action">
                    <button wire:click="$set('showNewChatModal', false)" class="btn">Close</button>
                </div>
            </div>
            <div class="modal-backdrop" wire:click="$set('showNewChatModal', false)"></div>
        </div>
    @endif

{{-- ========================================== --}}
{{-- ALPINE.JS COMPONENT --}}
{{-- Real-time features with Laravel Echo --}}
{{-- ========================================== --}}
@script
<script>
  Alpine.data('chatApp', () => ({
    // ==========================================
    // STATE
    // ==========================================
    conversationId: @js($selectedConversationId),
    echoChannel: null,
    allChannels: {},          // Track all conversation channels
    conversationIds: @js($this->conversations->pluck('id')->toArray()),
    conversationUsers: @js($this->conversationUsers),  // Map conversation ID to other user ID
    typingConversations: {},  // Track typing state per conversation (sidebar)
    typingTimeouts: {},       // Track timeouts per conversation
    activeConversationTyping: false,  // Typing state for active conversation
    activeTypingUserName: '',         // User name typing in active conversation
    activeTypingTimeout: null,        // Timeout for active conversation typing
    scrollHeight: 0,          // Track scroll height for load more
    isLoadingMore: false,     // Prevent multiple loads
    showGoToLatest: false,    // Show "Go to Latest" button

    // ==========================================
    // INITIALIZATION
    // ==========================================
    init() {
      console.log('🚀 Chat app initialized, conversation:', this.conversationId);
      console.log('📋 All conversations:', this.conversationIds);
      console.log('👥 Conversation users map:', this.conversationUsers);
      console.log('🔌 Echo available:', !!window.Echo);

      // Setup channels immediately
      this.setupChannels();

      // Listen for Livewire component updates (better than livewire:navigated)
      this.$wire.$on('$refresh', () => {
        console.log('🔄 Component refreshed, re-setting up channels...');
        this.setupChannels();
      });

      // Listen for conversation changes
      Livewire.on('conversationSelected', (data) => {
        console.log('📱 Conversation selected:', data.conversationId);
        this.conversationId = data.conversationId;

        // Switch to the new conversation channel
        this.setupEcho();

        // After switching channel, whisper read status if needed
        setTimeout(() => {
          this.safeWhisper('read', {
            conversationId: this.conversationId,
            readBy: {{ auth()->id() }},
            readAt: Date.now()
          });
        }, 500);
      });

      // Listen for message sent event
      Livewire.on('message-sent', (data) => {
        console.log('📨 Message sent, whispering to other user...');

        this.whisperNewMessage(data.messageId);
      });

      // Listen for messages marked as read (for current conversation)
      Livewire.on('messages-marked-read', () => {
        console.log('✓✓ Messages marked as read, whispering...');
        this.safeWhisper('read', {
          conversationId: this.conversationId,
          readBy: {{ auth()->id() }},
          readAt: Date.now()
        });
      });
    },

    // ==========================================
    // ECHO SETUP
    // ==========================================
    setupChannels() {
      console.log('🔄 Setting up all channels...');

      // Initialize typing state for all conversations
      this.conversationIds.forEach(convId => {
        this.typingConversations[convId] = false;
      });

      // Setup Echo listeners for ALL conversations
      this.setupAllConversationChannels();

      // Setup Echo listener for active conversation
      this.setupEcho();
    },

    setupAllConversationChannels() {
      if (!window.Echo) {
        console.warn('⚠️ Echo not available');
        return;
      }

      console.log('🔌 Setting up channels for all conversations');

      // Clear any existing channels first
      Object.keys(this.allChannels).forEach(convId => {
        if (this.allChannels[convId]) {
          console.log('🧹 Cleaning up old channel:', convId);
          Echo.leave(`chat.${convId}`);
        }
      });
      this.allChannels = {};

      // Subscribe to all conversation channels for typing indicators
      this.conversationIds.forEach(convId => {
        const channelName = `chat.${convId}`;
        console.log('📡 Subscribing to:', channelName);

        const channel = Echo.private(channelName);
        this.allChannels[convId] = channel;

        channel.listenForWhisper('typing', (e) => {
          console.log(`⌨️ Typing in conversation ${convId}:`, e);
          this.handleTypingForConversation(convId, e);
        });
      });
    },

    handleTypingForConversation(conversationId, e) {
      console.log(`📥 Typing event for conversation ${conversationId}:`, e);

      // Find which conversation this user belongs to
      const targetConversationId = Object.keys(this.conversationUsers).find(
        convId => this.conversationUsers[convId] == e.userId
      );

      if (!targetConversationId) {
        console.log('⚠️ User not found in any conversation');
        return;
      }

      console.log(`✅ Found user ${e.userId} in conversation ${targetConversationId}`);

      // Update typing state for sidebar
      this.typingConversations[targetConversationId] = true;

      // Clear existing timeout for this conversation
      if (this.typingTimeouts[targetConversationId]) {
        clearTimeout(this.typingTimeouts[targetConversationId]);
      }

      // Hide sidebar typing indicator after 3 seconds
      this.typingTimeouts[targetConversationId] = setTimeout(() => {
        this.typingConversations[targetConversationId] = false;
        console.log(`⏱️ Sidebar typing indicator hidden for conversation ${targetConversationId}`);
      }, 3000);

      // If this is the active conversation, also show typing above input
      if (targetConversationId == this.conversationId) {
        this.activeConversationTyping = true;
        this.activeTypingUserName = e.userName;

        if (this.activeTypingTimeout) {
          clearTimeout(this.activeTypingTimeout);
        }

        this.activeTypingTimeout = setTimeout(() => {
          this.activeConversationTyping = false;
          console.log('⏱️ Active conversation typing indicator hidden');
        }, 3000);
      }
    },

    setupEcho() {
      if (!window.Echo || !this.conversationId) {
        console.warn('⚠️ Echo not available or no conversation selected');
        return;
      }

      console.log('🔌 Setting up Echo for conversation:', this.conversationId);

      const channelName = `chat.${this.conversationId}`;
      const otherUserId = this.conversationUsers[this.conversationId];

      // Get the channel from allChannels
      this.echoChannel = this.allChannels[this.conversationId];

      if (!this.echoChannel) {
        console.error('❌ Channel not found in allChannels for conversation:', this.conversationId);
        return;
      }

      // Only attach listeners once per channel
      if (this.echoChannel._listenersAttached) {
        console.log('ℹ️ Listeners already attached to this channel');
        return;
      }
      this.echoChannel._listenersAttached = true;

      // Attach all event listeners
      this.echoChannel
        .listenForWhisper('new-message', (e) => {
          console.log('📨 New message (whisper):', e);
          if (e.senderId == otherUserId) {
            this.refreshMessages();
          } else {
            this.$wire.call('getConversationsProperty');
          }
        })
        .listen('.MessageUpdated', (e) => {
          console.log('✏️ Message updated:', e);
          if (e.message.user_id == otherUserId) {
            this.refreshMessages();
          } else {
            this.$wire.call('getConversationsProperty');
          }
        })
        .listen('.MessageDeleted', (e) => {
          console.log('🗑️ Message deleted:', e);
          if (e.userId == otherUserId) {
            this.refreshMessages();
          } else {
            this.$wire.call('getConversationsProperty');
          }
        })
        .listenForWhisper('reaction', (e) => {
          console.log('😊 Reaction (whisper):', e);
          if (e.userId == otherUserId) {
            this.handleWhisperReaction(e);
          } else {
            this.$wire.call('getConversationsProperty');
          }
        })
        .listenForWhisper('read', (e) => {
          console.log('👁️ Messages read (whisper):', e);
          if (e.readBy == otherUserId) {
            this.$wire.$refresh();
          } else {
            this.$wire.call('getConversationsProperty');
          }
        })
        .listenForWhisper('typing', (e) => {
          console.log('⌨️ User typing (whisper):', e);
          this.handleWhisperTyping(e);
        });

      // Log subscription events
      this.echoChannel.subscription.bind('pusher:subscription_succeeded', () => {
        console.log('✅ Successfully subscribed to', channelName);
      });

      this.echoChannel.subscription.bind('pusher:subscription_error', (error) => {
        console.error('❌ Subscription error:', error);
      });
    },

    refreshMessages() {
      console.log('🔄 Refreshing messages...');
      // Call Livewire method to refresh
      this.$wire.call('refreshMessages');

      // Scroll to bottom after refresh
      setTimeout(() => {
        this.scrollToBottom();
      }, 1000);
    },

    handleMessageSent() {
      console.log('✉️ Message sent locally');
      setTimeout(() => {
        this.scrollToBottom();
      }, 100);
    },

    scrollToBottom() {
      const container = document.getElementById('message-container');
      if (container) {
        container.scrollTop = container.scrollHeight;
        console.log('📜 Scrolled to bottom');
      }
    },

    // ==========================================
    // WHISPER HELPERS
    // ==========================================
    safeWhisper(eventName, data) {
      if (!this.echoChannel) {
        console.warn('⚠️ Cannot whisper: channel not ready');
        return false;
      }

      try {
        this.echoChannel.whisper(eventName, data);
        return true;
      } catch (error) {
        console.warn('⚠️ Whisper failed (channel may not be ready):', error.message);
        return false;
      }
    },

    whisperTyping() {
      this.safeWhisper('typing', {
        typing: true,
        userId: {{ auth()->id() }},
        userName: '{{ auth()->user()->name }}'
      });
    },

    whisperNewMessage(messageId) {
      const success = this.safeWhisper('new-message', {
        messageId: messageId,
        senderId: {{ auth()->id() }},
        timestamp: Date.now()
      });

      if (success) {
        console.log('📤 Whispered new message:', messageId);
        this.refreshMessages();
      }
    },

    handleWhisperTyping(e) {
      // This is now handled by handleTypingForConversation
      // Keep for backward compatibility
      if (this.conversationId) {
        this.handleTypingForConversation(this.conversationId, e);
      }
    },

    addReaction(messageId, emoji) {
      // Call Livewire to save reaction
      this.$wire.call('toggleReaction', messageId, emoji);

      // Whisper to other user
      if (this.echoChannel) {
        this.echoChannel.whisper('reaction', {
          messageId: messageId,
          emoji: emoji,
          userId: {{ auth()->id() }}
        });
        console.log('📤 Whispered reaction:', emoji);
      }
    },

    handleWhisperReaction(e) {
      console.log('📥 Received reaction whisper:', e);
      // Refresh messages to show new reaction
      this.refreshMessages();
    },

    // ==========================================
    // SCROLL HELPERS
    // ==========================================
    scrollToBottom(container) {
      setTimeout(() => {
        container.scrollTop = container.scrollHeight;
        this.showGoToLatest = false;
      }, 100);
    },

    handleScroll(container) {
      const scrollTop = container.scrollTop;
      const scrollHeight = container.scrollHeight;
      const clientHeight = container.clientHeight;
      const scrolledFromBottom = scrollHeight - scrollTop - clientHeight;

      // Show "Go to Latest" button if scrolled more than 200px from bottom
      this.showGoToLatest = scrolledFromBottom > 200;

      // Auto-load more messages when scrolled near top (Messenger style)
      if (scrollTop < 100 && !this.isLoadingMore && this.$wire.hasMoreMessages) {
        this.loadMoreMessages(container);
      }
    },

    loadMoreMessages(container) {
      if (this.isLoadingMore) return;
      
      this.isLoadingMore = true;
      
      // Save current scroll position and height
      const oldScrollHeight = container.scrollHeight;
      const oldScrollTop = container.scrollTop;

      // Load older messages
      this.$wire.loadOlderMessages().then(() => {
        this.$nextTick(() => {
          // Calculate new height and maintain scroll position
          const newScrollHeight = container.scrollHeight;
          const heightDiff = newScrollHeight - oldScrollHeight;
          
          // Keep user at same message (adjust for new content above)
          container.scrollTop = oldScrollTop + heightDiff;
          
          this.isLoadingMore = false;
        });
      });
    },

    destroy() {
      // Leave active conversation channel
      if (this.echoChannel) {
        console.log('👋 Leaving channel:', this.conversationId);
        Echo.leave(`chat.${this.conversationId}`);
      }

      // Leave all conversation channels
      Object.keys(this.allChannels).forEach(convId => {
        console.log('👋 Leaving channel:', convId);
        Echo.leave(`chat.${convId}`);
      });

      // Clear all timeouts
      Object.values(this.typingTimeouts).forEach(timeout => {
        clearTimeout(timeout);
      });
      if (this.activeTypingTimeout) {
        clearTimeout(this.activeTypingTimeout);
      }
    }
  }));
</script>
@endscript
</div>
