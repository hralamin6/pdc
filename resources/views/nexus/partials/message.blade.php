{{-- resources/views/nexus/partials/message.blade.php --}}
@php
    $isUser = $message->role === 'user';
    // Safely JSON-encode content for Alpine — handles newlines, quotes, unicode, etc.
    $jsonContent     = json_encode($message->content, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
    $speakText       = json_encode(substr(strip_tags($message->content), 0, 500), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
@endphp

<div class="flex gap-3 animate-fade-slide {{ $isUser ? 'flex-row-reverse' : '' }}" id="msg-{{ $message->id }}">

    {{-- Avatar --}}
    <div class="flex-shrink-0 mt-0.5">
        @if($isUser)
            <div class="w-8 h-8 rounded-xl flex items-center justify-center text-xs font-bold"
                 style="background: var(--nx-surface3); border: 1.5px solid var(--nx-border2); color: var(--nx-accent);">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
        @elseif($message->role === 'tool')
            <div class="w-8 h-8 rounded-xl flex items-center justify-center text-base"
                 style="background: rgba(34,211,238,.1); border: 1.5px solid rgba(34,211,238,.25);">🔧</div>
        @else
            <div class="w-8 h-8 rounded-xl flex items-center justify-center text-white text-xs font-bold"
                 style="background: var(--nx-gradient); box-shadow: 0 0 10px var(--nx-accent-glow);">N</div>
        @endif
    </div>

    {{-- Bubble --}}
    <div class="max-w-[70%] {{ $isUser ? 'items-end' : 'items-start' }} flex flex-col">

        @if($message->role === 'tool')
            {{-- Tool Call Card --}}
            <div class="tool-card w-full animate-slide-right" x-data="{open: false}">
                <div class="tool-card-header" @click="open = !open">
                    <span class="status-dot status-{{ $message->tool_status ?? 'success' }}"></span>
                    <span class="font-mono text-xs" style="color: var(--nx-cyan);">{{ $message->tool_name }}</span>
                    <span class="text-xs ml-auto" style="color: var(--nx-text-dim);">{{ $message->tool_status }}</span>
                    <svg class="w-3 h-3 transition-transform" :class="open && 'rotate-180'"
                         style="color: var(--nx-text-dim);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
                <div class="tool-card-body" x-show="open" x-transition>
                    @if($message->tool_input)
                    <div class="mb-2">
                        <div class="text-xs mb-1 font-semibold" style="color: var(--nx-text-dim);">Input:</div>
                        <pre class="text-xs font-mono overflow-auto" style="color: var(--nx-text-muted);">{{ json_encode($message->tool_input, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                    @endif
                    @if($message->tool_output)
                    <div>
                        <div class="text-xs mb-1 font-semibold" style="color: var(--nx-text-dim);">Output:</div>
                        <div class="text-xs whitespace-pre-wrap" style="color: var(--nx-text-muted);">{{ $message->tool_output }}</div>
                    </div>
                    @endif
                </div>
            </div>

        @else
            {{-- Regular message bubble --}}
            <div class="group relative px-4 py-3 rounded-2xl
                {{ $isUser ? 'rounded-tr-sm' : 'rounded-tl-sm' }}"
                 style="{{ $isUser
                    ? 'background: var(--nx-user-bubble); border: 1.5px solid var(--nx-border2);'
                    : 'background: var(--nx-ai-bubble); border: 1.5px solid var(--nx-border);' }}
                 {{ $message->is_error ? 'border-color: rgba(244,63,94,.3) !important; background: rgba(244,63,94,.05) !important;' : '' }}"
            >
                @if($message->is_error)
                    <div class="text-sm" style="color: var(--nx-red);">{{ $message->content }}</div>
                @else
                    {{--
                        Use @json() Blade helper to safely produce a JS-compatible string literal:
                        handles newlines, single quotes, backticks, and all unicode correctly.
                        x-data initialises `rendered` once; x-html reactively displays it.
                    --}}
                    <div class="nx-prose text-sm"
                         x-data="{ rendered: window.renderMarkdown({{ $jsonContent }}) }"
                         x-html="rendered"></div>
                @endif

                {{-- Hover actions --}}
                @if(!$message->is_error)
                <div class="absolute {{ $isUser ? 'left-0 -translate-x-full pr-2' : 'right-0 translate-x-full pl-2' }} top-2 hidden group-hover:flex gap-0.5 z-10">
                    {{-- Copy (plain JS, no Alpine expression with dynamic content) --}}
                    <button
                        onclick="navigator.clipboard.writeText(this.dataset.text).then(() => { this.style.color = 'var(--nx-green)'; setTimeout(() => this.style.color = '', 1200); })"
                        data-text="{{ htmlspecialchars($message->content) }}"
                        class="nx-btn nx-btn-ghost p-1.5 rounded-lg"
                        title="Copy"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    </button>
                    @if(!$isUser)
                    {{-- Speak: data-text stores pre-encoded text, plain JS onclick avoids Alpine string escaping issues --}}
                    <button
                        onclick="window.nexusSpeakBtn(this)"
                        data-speak="{{ htmlspecialchars(substr(strip_tags($message->content), 0, 500)) }}"
                        class="nx-btn nx-btn-ghost p-1.5 rounded-lg"
                        title="Read aloud"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072M12 6a7 7 0 010 12M9 9v6m3-9v12"/></svg>
                    </button>
                    <button wire:click="branchFromMessage({{ $message->id }})" class="nx-btn nx-btn-ghost p-1.5 rounded-lg" title="Branch from here">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                    </button>
                    @endif
                    <button wire:click="deleteMessage({{ $message->id }})" class="nx-btn nx-btn-danger p-1.5 rounded-lg" title="Delete">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
                @endif
            </div>

            {{-- Meta: time + model --}}
            <div class="text-xs mt-1 px-1 flex items-center gap-1.5 {{ $isUser ? 'flex-row-reverse' : '' }}"
                 style="color: var(--nx-text-dim);">
                <span>{{ $message->created_at?->diffForHumans() }}</span>
                @if($message->model_used)
                <span>·</span>
                <span class="nx-badge" style="background: var(--nx-surface3); color: var(--nx-text-dim); padding: .1em .5em; font-size: .7em;">{{ $message->model_used }}</span>
                @endif
                @if($message->tokens_out)
                <span>·</span>
                <span>{{ $message->tokens_out }}t</span>
                @endif
            </div>
        @endif
    </div>
</div>
