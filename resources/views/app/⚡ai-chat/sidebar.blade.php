{{-- Sidebar - Conversations List --}}
@unblaze
<div class="w-full lg:w-80 border-b lg:border-b-0 lg:border-r border-base-300 bg-base-100 flex flex-col max-h-[40vh] lg:max-h-none">
    {{-- Sidebar Header --}}
    <div class="p-3 md:p-4 border-b border-base-300">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-base md:text-lg font-semibold text-base-content">AI Chat</h2>
            <button
                wire:click="createNewConversation"
                class="btn btn-circle btn-sm btn-ghost"
                title="New Conversation"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
            </button>
        </div>

        {{-- Search --}}
        <input
            type="text"
            wire:model.live.debounce.300ms="search"
            placeholder="Search conversations..."
            class="input input-sm input-bordered w-full bg-base-200 text-base-content"
        />
    </div>

    {{-- Conversations List --}}
    <div class="flex-1 overflow-y-auto">
        @forelse($this->conversations as $conversation)
            <div
                wire:key="conv-{{ $conversation->id }}"
                wire:click="selectConversation({{ $conversation->id }})"
                class="p-3 md:p-4 border-b border-base-300 cursor-pointer hover:bg-base-200 transition {{ $this->selectedConversationId === $conversation->id ? 'bg-primary/10 border-l-4 border-l-primary' : '' }}"
            >
                <div class="flex items-start justify-between">
                    <div class="flex-1 min-w-0">
                        <h3 class="text-sm font-medium text-base-content truncate">
                            {{ $conversation->getDisplayTitle() }}
                        </h3>
                        <p class="text-xs text-base-content/60 mt-1">
                            {{ $conversation->last_message_at?->diffForHumans() ?? 'No messages yet' }}
                        </p>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="badge badge-sm badge-ghost">
                                {{ $conversation->ai_provider }}
                            </span>
                            <span class="text-xs text-base-content/50">
                                {{ $conversation->total_tokens }} tokens
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="p-6 md:p-8 text-center text-base-content/60">
                <svg class="w-12 h-12 mx-auto mb-3 text-base-content/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                <p class="text-sm">No conversations yet</p>
                <button
                    wire:click="createNewConversation"
                    class="btn btn-sm btn-primary mt-3"
                >
                    Start a new chat
                </button>
            </div>
        @endforelse
    </div>

    {{-- Sidebar Footer - Actions --}}
    @if($this->selectedConversationId)
        <div class="p-2 md:p-3 border-t border-base-300 flex gap-1 md:gap-2">
            <button
                wire:click="$set('showSettingsModal', true)"
                class="btn btn-sm btn-ghost flex-1"
                title="Settings"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </button>
            <button
                wire:click="$set('showImageGeneratorModal', true)"
                class="btn btn-sm btn-secondary flex-1"
                title="Generate Image"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </button>
            <button
                wire:click="exportConversation"
                class="btn btn-sm btn-success flex-1"
                title="Export"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
            </button>
            <button
                wire:click="deleteConversation"
                onclick="return confirm('Are you sure you want to delete this conversation?')"
                class="btn btn-sm btn-error flex-1"
                title="Delete"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </button>
        </div>
    @endif
</div>
