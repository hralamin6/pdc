{{-- Main Chat Area --}}
@unblaze
<div class="flex-1 flex flex-col bg-base-200">
    @if($this->selectedConversationId)
        {{-- Chat Header --}}
        <div class="p-3 md:p-4 bg-base-100 border-b border-base-300">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base md:text-lg font-semibold text-base-content">
                        {{ $this->selectedConversation->getDisplayTitle() }}
                    </h2>
                    <p class="text-xs md:text-sm text-base-content/60">
                        {{ ucfirst($this->selectedConversation->ai_provider) }} • {{ $this->selectedConversation->model }}
                    </p>
                </div>
                <div class="text-xs md:text-sm text-base-content/60">
                    {{ $this->selectedConversation->messages->count() }} messages
                </div>
            </div>
        </div>

        {{-- Messages Area --}}
        <div
            class="flex-1 overflow-y-auto p-3 md:p-4 lg:p-6 space-y-3 md:space-y-4"
            id="messages-container"
            x-ref="messagesContainer"
        >
            @forelse($this->aiMessages as $message)
                @include('app.⚡ai-chat.message', ['message' => $message])
            @empty
                <div class="flex items-center justify-center h-full">
                    <div class="text-center text-base-content/60">
                        <svg class="w-12 md:w-16 h-12 md:h-16 mx-auto mb-4 text-base-content/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        <p class="text-base md:text-lg font-medium">Start a conversation</p>
                        <p class="text-sm mt-1">Send a message to begin chatting with AI</p>
                    </div>
                </div>
            @endforelse

            {{-- Loading Indicator --}}
            <div wire:loading wire:target="sendMessage" class="flex justify-start">
                <div class="max-w-3xl mr-6 md:mr-12">
                    <div class="flex items-start gap-2 md:gap-3">
                        <div class="w-7 h-7 md:w-8 md:h-8 rounded-full bg-secondary flex items-center justify-center text-secondary-content text-sm">
                            <svg class="w-4 md:w-5 h-4 md:h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                        </div>
                        <div class="bg-base-100 border border-base-300 rounded-lg p-3 md:p-4">
                            <div class="flex gap-1">
                                <div class="w-2 h-2 bg-base-content/40 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                                <div class="w-2 h-2 bg-base-content/40 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                                <div class="w-2 h-2 bg-base-content/40 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Message Input Area --}}
        @include('app.⚡ai-chat.input-area')
    @else
        {{-- No Conversation Selected --}}
        <div class="flex items-center justify-center h-full p-4">
            <div class="text-center text-base-content/60">
                <svg class="w-16 md:w-20 h-16 md:h-20 mx-auto mb-4 text-base-content/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                <h3 class="text-lg md:text-xl font-semibold mb-2 text-base-content">Welcome to AI Chat</h3>
                <p class="mb-4 text-sm md:text-base">Select a conversation or start a new one to begin</p>
                <button
                    wire:click="createNewConversation"
                    class="btn btn-primary"
                >
                    Start New Conversation
                </button>
            </div>
        </div>
    @endif
</div>
