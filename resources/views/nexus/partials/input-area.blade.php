{{-- resources/views/nexus/partials/input-area.blade.php --}}
<div class="flex-shrink-0 border-t" style="border-color: var(--nx-border); background: var(--nx-bg);" x-data>
    <div class="max-w-3xl mx-auto px-4 py-3">

        {{-- ── Main input card ───────────────────────────────────────────────── --}}
        <div
            class="rounded-2xl transition-all duration-200"
            style="background: var(--nx-surface); border: 1.5px solid var(--nx-border2); box-shadow: 0 2px 12px rgba(0,0,0,.15);"
            x-on:focusin="$el.style.borderColor='var(--nx-accent)'; $el.style.boxShadow='0 0 0 4px var(--nx-accent-glow2), 0 2px 12px rgba(0,0,0,.2)';"
            x-on:focusout="$el.style.borderColor='var(--nx-border2)'; $el.style.boxShadow='0 2px 12px rgba(0,0,0,.15)';"
        >
            {{-- ── Textarea row ─────────────────────────────────────────────── --}}
            <div class="flex items-end gap-2 px-3 pt-3 pb-1">

                {{-- Textarea --}}
                <textarea
                    wire:model="messageInput"
                    @keydown.enter.prevent="if (!$event.shiftKey && !streaming) {
                        const msg = $wire.messageInput;
                        if (msg?.trim()) { sendMessage(msg, {{ $chatId }}); $wire.set('messageInput', ''); }
                    }"
                    placeholder="Ask NexusAI anything…"
                    rows="1"
                    id="nx-message-input"
                    class="flex-1 text-sm outline-none leading-relaxed min-h-[28px] max-h-48 overflow-y-auto"
                    style="background: transparent; color: var(--nx-text); resize: none; font-family: 'Inter', sans-serif; padding: 2px 0;"
                    x-ref="textarea"
                    @input="
                        $el.style.height = 'auto';
                        $el.style.height = Math.min($el.scrollHeight, 192) + 'px';
                    "
                    @paste="$nextTick(() => { $el.style.height='auto'; $el.style.height=Math.min($el.scrollHeight,192)+'px'; })"
                ></textarea>

            </div>

            {{-- ── Action bar ───────────────────────────────────────────────── --}}
            <div class="flex items-center justify-between px-2 pb-2 pt-1">

                {{-- Left: Voice + helpers --}}
                <div class="flex items-center gap-1">
                    {{-- Voice input --}}
                    <button
                        @click="recording ? stopRecording() : startRecording()"
                        :title="recording ? 'Stop recording' : 'Voice input'"
                        class="w-8 h-8 rounded-xl flex items-center justify-center transition-all duration-150"
                        :style="recording
                            ? 'background: rgba(244,63,94,.15); color: var(--nx-red);'
                            : 'background: transparent; color: var(--nx-text-dim);'"
                        @mouseenter="if (!recording) { $el.style.background='var(--nx-surface3)'; $el.style.color='var(--nx-accent)'; }"
                        @mouseleave="if (!recording) { $el.style.background='transparent'; $el.style.color='var(--nx-text-dim)'; }"
                    >
                        <span x-show="!recording">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                            </svg>
                        </span>
                        <span x-show="recording" class="relative flex">
                            <span class="absolute inline-flex w-4 h-4 rounded-full opacity-60 animate-ping" style="background: var(--nx-red);"></span>
                            <svg class="w-4 h-4 relative" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/>
                            </svg>
                        </span>
                    </button>

                    {{-- Keyboard hint --}}
                    <span class="text-xs hidden sm:inline-flex items-center gap-1 ml-1" style="color: var(--nx-text-dim);">
                        <kbd class="px-1.5 py-0.5 rounded text-xs" style="background: var(--nx-surface3); border: 1px solid var(--nx-border2); font-family: 'JetBrains Mono', monospace;">↵</kbd>
                        Send &nbsp;
                        <kbd class="px-1.5 py-0.5 rounded text-xs" style="background: var(--nx-surface3); border: 1px solid var(--nx-border2); font-family: 'JetBrains Mono', monospace;">⇧↵</kbd>
                        New line
                    </span>
                </div>

                {{-- Right: Model pill + Send --}}
                <div class="flex items-center gap-2">

                    {{-- Model pill --}}
                    <div class="hidden sm:flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs"
                         style="background: var(--nx-surface3); border: 1px solid var(--nx-border); color: var(--nx-text-dim);">
                        <span class="w-1.5 h-1.5 rounded-full" style="background: var(--nx-green);"></span>
                        <span>{{ ucfirst($this->selectedChat?->provider) }}</span>
                        @if($this->selectedChat?->model)
                        <span style="color: var(--nx-border2);">/</span>
                        <span class="truncate max-w-[100px]">{{ $this->selectedChat?->model }}</span>
                        @endif
                    </div>

                    {{-- Streaming indicator (replaces send when streaming) --}}
                    <div x-show="streaming" class="flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs"
                         style="background: var(--nx-accent-glow); color: var(--nx-accent-light); border: 1px solid var(--nx-accent-glow);">
                        <span class="flex gap-0.5">
                            <span class="typing-dot" style="background: var(--nx-accent-light);"></span>
                            <span class="typing-dot" style="background: var(--nx-accent-light);"></span>
                            <span class="typing-dot" style="background: var(--nx-accent-light);"></span>
                        </span>
                        <span>Thinking…</span>
                    </div>

                    {{-- Send button --}}
                    <button
                        x-show="!streaming"
                        @click="
                            const msg = $wire.messageInput;
                            if (msg?.trim() && !streaming) {
                                sendMessage(msg, {{ $chatId }});
                                $wire.set('messageInput', '');
                                $refs.textarea.style.height = 'auto';
                            }
                        "
                        :disabled="!$wire.messageInput?.trim()"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold transition-all duration-150"
                        :style="$wire.messageInput?.trim()
                            ? 'background: var(--nx-accent); color: #fff; box-shadow: 0 2px 8px var(--nx-accent-glow); transform: scale(1);'
                            : 'background: var(--nx-surface3); color: var(--nx-text-dim); cursor: not-allowed;'"
                        @mouseenter="if ($wire.messageInput?.trim()) { $el.style.transform='scale(1.04)'; $el.style.boxShadow='0 4px 16px var(--nx-accent-glow)'; }"
                        @mouseleave="$el.style.transform='scale(1)'; $el.style.boxShadow=$wire.messageInput?.trim() ? '0 2px 8px var(--nx-accent-glow)' : 'none';"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        Send
                    </button>
                </div>
            </div>
        </div>

        {{-- ── Disclaimer ────────────────────────────────────────────────────── --}}
        <p class="text-center text-xs mt-2" style="color: var(--nx-text-dim);">
            NexusAI can make mistakes. Verify important information.
        </p>
    </div>
</div>
