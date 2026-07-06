@unblaze
<div
    wire:key="msg-{{ $message->id }}"
    class="flex {{ $message->isUser() ? 'justify-end' : 'justify-start' }}"
>
    <div class="max-w-full md:max-w-3xl {{ $message->isUser() ? 'ml-6 md:ml-12' : 'mr-6 md:mr-12' }}">
        {{-- Message Bubble --}}
        <div class="flex items-start gap-2 md:gap-3 {{ $message->isUser() ? 'flex-row-reverse' : '' }}">
            {{-- Avatar --}}
            <div class="flex-shrink-0">
                @if($message->isUser())
                    <div class="avatar placeholder">
                        <div class="w-7 h-7 md:w-8 md:h-8 rounded-full bg-primary text-primary-content">
                            <span class="text-xs md:text-sm font-medium">{{ substr(auth()->user()->name, 0, 1) }}</span>
                        </div>
                    </div>
                @else
                    <div class="avatar placeholder">
                        <div class="w-7 h-7 md:w-8 md:h-8 rounded-full bg-secondary text-secondary-content">
                            <svg class="w-4 md:w-5 h-4 md:h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2 5a2 2 0 012-2h7a2 2 0 012 2v4a2 2 0 01-2 2H9l-3 3v-3H4a2 2 0 01-2-2V5z"/>
                                <path d="M15 7v2a4 4 0 01-4 4H9.828l-1.766 1.767c.28.149.599.233.938.233h2l3 3v-3h2a2 2 0 002-2V9a2 2 0 00-2-2h-1z"/>
                            </svg>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Message Content --}}
            <div class="flex-1">
                <div class="rounded-lg {{ $message->isUser() ? 'bg-primary text-primary-content p-3 md:p-4' : 'bg-base-100 border border-base-300 p-3 md:p-4' }}">
                  <article class="prose prose-sm md:prose {{ $message->isUser() ? 'prose-invert' : '' }} max-w-none">
                    {!! $message->content !!}
                  </article>



                  {{-- Attachments --}}
                    @if($message->media->count() > 0)
                        <div class="mt-3 space-y-2">
                            @foreach($message->media as $media)
                                @if(str_starts_with($media->mime_type, 'image/'))
                                    <img
                                        src="{{ $media->getUrl() }}"
                                        alt="Attachment"
                                        class="rounded-lg max-w-full md:max-w-sm cursor-pointer hover:opacity-90 transition"
                                    />
                                @else
                                    <a
                                        href="{{ $media->getUrl() }}"
                                        target="_blank"
                                        class="flex items-center gap-2 p-2 {{ $message->isUser() ? 'bg-primary-focus' : 'bg-base-200' }} rounded hover:opacity-80 transition"
                                    >
                                        <svg class="w-4 md:w-5 h-4 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                        </svg>
                                        <span class="text-xs md:text-sm">{{ $media->file_name }}</span>
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    @endif

                    {{-- Edited Badge --}}
                    @if($message->edited_at)
                        <span class="text-xs opacity-70 mt-2 inline-block">
                            (edited)
                        </span>
                    @endif
                </div>

                {{-- Message Actions --}}
                <div class="flex items-center gap-1 md:gap-2 mt-2 text-xs {{ $message->isUser() ? 'justify-end' : '' }}">
                    <span class="text-base-content/60">
                        {{ $message->created_at->format('g:i A') }}
                    </span>

                    {{-- Copy Button --}}
                    <button
                        @click="copyToClipboard(@js($message->content))"
                        class="btn btn-ghost btn-xs"
                        title="Copy"
                    >
                        <svg class="w-3 md:w-4 h-3 md:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                    </button>

                    @if($message->isUser())
                        {{-- Edit --}}
                        <button
                            wire:click="editMessage({{ $message->id }})"
                            class="btn btn-ghost btn-xs"
                            title="Edit"
                        >
                            <svg class="w-3 md:w-4 h-3 md:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </button>
                    @else
                        {{-- Regenerate --}}
                        <button
                            wire:click="regenerateResponse({{ $message->id }})"
                            class="btn btn-ghost btn-xs"
                            title="Regenerate"
                        >
                            <svg class="w-3 md:w-4 h-3 md:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                        </button>
                    @endif

                    {{-- Delete --}}
                    <button
                        wire:click="deleteMessage({{ $message->id }})"
                        onclick="return confirm('Delete this message?')"
                        class="btn btn-ghost btn-xs text-error"
                        title="Delete"
                    >
                        <svg class="w-3 md:w-4 h-3 md:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
