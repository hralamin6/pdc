<div
    class="flex h-screen overflow-hidden"
    x-data="nexusApp(@this)"
    x-init="init()"
>
    {{-- ────────────────────────────────────────────────────────────────────────── --}}
    {{-- LEFT: Workspace Rail                                                       --}}
    {{-- ────────────────────────────────────────────────────────────────────────── --}}
    <div class="flex-shrink-0 w-[60px] flex flex-col items-center py-4 gap-2 border-r"
         style="background: var(--nx-rail-bg); border-color: var(--nx-border);">

        {{-- Logo --}}
        <div class="w-9 h-9 rounded-xl flex items-center justify-center mb-2 flex-shrink-0"
             style="background: var(--nx-gradient); box-shadow: 0 0 16px var(--nx-accent-glow);">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/>
            </svg>
        </div>

        {{-- Divider --}}
        <div class="w-8 h-px" style="background: var(--nx-border);"></div>

        {{-- Workspaces --}}
        @foreach($this->workspaces as $ws)
        <button
            wire:click="selectWorkspace({{ $ws->id }})"
            title="{{ $ws->name }}"
            class="relative w-9 h-9 rounded-xl flex items-center justify-center text-lg transition-all duration-200 flex-shrink-0"
            style="{{ $workspaceId == $ws->id
                ? 'background: '.$ws->color.'22; border: 1.5px solid '.$ws->color.'66; box-shadow: 0 0 12px '.$ws->color.'33;'
                : 'background: transparent; border: 1.5px solid var(--nx-border);' }}"
        >
            {{ $ws->icon }}
            @if($workspaceId == $ws->id)
            <span class="absolute -right-0.5 -top-0.5 w-2 h-2 rounded-full"
                  style="background: {{ $ws->color }}; border: 2px solid var(--nx-rail-bg);"></span>
            @endif
        </button>
        @endforeach

        {{-- New Workspace --}}
        <button
            wire:click="$set('showNewWorkspaceModal', true)"
            title="New Workspace"
            class="w-9 h-9 rounded-xl flex items-center justify-center transition-all duration-150 flex-shrink-0"
            style="border: 1.5px dashed var(--nx-border2); color: var(--nx-text-dim);"
            onmouseenter="this.style.borderColor='var(--nx-accent)'; this.style.color='var(--nx-accent)'; this.style.background='var(--nx-accent-glow2)';"
            onmouseleave="this.style.borderColor='var(--nx-border2)'; this.style.color='var(--nx-text-dim)'; this.style.background='transparent';"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        </button>

        {{-- Bottom controls --}}
        <div class="mt-auto flex flex-col items-center gap-2">
            {{-- Theme toggle --}}
            <button
                onclick="nexusTheme.toggle(); this.querySelector('.icon-dark').classList.toggle('hidden'); this.querySelector('.icon-light').classList.toggle('hidden');"
                title="Toggle theme"
                class="w-9 h-9 rounded-xl flex items-center justify-center transition-all"
                style="color: var(--nx-text-dim);"
                onmouseenter="this.style.background='var(--nx-surface3)'; this.style.color='var(--nx-text)';"
                onmouseleave="this.style.background='transparent'; this.style.color='var(--nx-text-dim)';"
            >
                {{-- Moon icon (shown in dark mode) --}}
                <svg class="icon-dark w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                {{-- Sun icon (shown in light mode) --}}
                <svg class="icon-light w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M18.364 17.657l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z"/></svg>
            </button>

            {{-- Back to App --}}
            <a
                href="{{ route('app.dashboard') }}"
                title="Back to App"
                class="w-9 h-9 rounded-xl flex items-center justify-center transition-all"
                style="color: var(--nx-text-dim);"
                onmouseenter="this.style.background='var(--nx-surface3)'; this.style.color='var(--nx-text)';"
                onmouseleave="this.style.background='transparent'; this.style.color='var(--nx-text-dim)';"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l9-9 9 9M5 10v10h5v-6h4v6h5V10"/></svg>
            </a>
        </div>
    </div>

    {{-- ────────────────────────────────────────────────────────────────────────── --}}
    {{-- LEFT: Chat List Panel                                                      --}}
    {{-- ────────────────────────────────────────────────────────────────────────── --}}
    <div class="flex-shrink-0 w-[240px] flex flex-col border-r nx-sidebar" :class="{'open': $wire.sidebarOpen}"
         id="nx-chat-panel"
         style="background: var(--nx-surface); border-color: var(--nx-border);">

        {{-- Workspace Header --}}
        <div class="p-3 border-b" style="border-color: var(--nx-border);">
            <div class="flex items-center justify-between mb-2.5">
                <div class="flex items-center gap-2 min-w-0">
                    @if($this->selectedWorkspace)
                    <span class="text-base leading-none flex-shrink-0">{{ $this->selectedWorkspace->icon }}</span>
                    <span class="text-xs font-semibold truncate" style="color: var(--nx-text-muted);">
                        {{ $this->selectedWorkspace->name }}
                    </span>
                    @else
                    <span class="text-xs font-semibold" style="color: var(--nx-text-dim);">Select Workspace</span>
                    @endif
                </div>
                <button
                    wire:click="createChat"
                    title="New Chat"
                    class="w-7 h-7 rounded-lg flex items-center justify-center transition-all flex-shrink-0"
                    style="color: var(--nx-accent); background: var(--nx-accent-glow2); border: 1px solid var(--nx-accent-glow);"
                    onmouseenter="this.style.background='var(--nx-accent-glow)';"
                    onmouseleave="this.style.background='var(--nx-accent-glow2)';"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                </button>
            </div>

            {{-- Search --}}
            <div class="relative">
                <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5" style="color: var(--nx-text-dim);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input
                    wire:model.live.debounce.300ms="searchQuery"
                    placeholder="Search chats…"
                    class="nx-input text-xs py-1.5 pl-8"
                >
            </div>
        </div>

        {{-- Chat List --}}
        <div class="flex-1 overflow-y-auto p-2 space-y-0.5">
            @forelse($this->chats as $chat)
            <div class="group relative">
                <button
                    wire:click="selectChat({{ $chat->id }})"
                    class="sidebar-item w-full {{ $chatId == $chat->id ? 'active' : '' }}"
                >
                    <svg class="w-3.5 h-3.5 flex-shrink-0 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    <span class="truncate flex-1 text-left">{{ $chat->getDisplayTitle() }}</span>
                    @if($chat->is_pinned)
                    <span class="text-xs flex-shrink-0" style="color: var(--nx-yellow);">📌</span>
                    @endif
                </button>
                {{-- Hover actions --}}
                <div class="absolute right-1.5 top-1/2 -translate-y-1/2 hidden group-hover:flex gap-0.5 z-10">
                    <button
                        wire:click="togglePin({{ $chat->id }})"
                        class="w-6 h-6 rounded flex items-center justify-center text-xs transition-colors"
                        style="color: var(--nx-text-dim);"
                        onmouseenter="this.style.background='var(--nx-surface3)'; this.style.color='var(--nx-yellow)';"
                        onmouseleave="this.style.background='transparent'; this.style.color='var(--nx-text-dim)';"
                        title="Pin"
                    >📌</button>
                    <button
                        wire:click="deleteChat({{ $chat->id }})"
                        class="w-6 h-6 rounded flex items-center justify-center transition-colors"
                        style="color: var(--nx-text-dim);"
                        onmouseenter="this.style.background='rgba(244,63,94,.12)'; this.style.color='var(--nx-red)';"
                        onmouseleave="this.style.background='transparent'; this.style.color='var(--nx-text-dim)';"
                        title="Delete"
                        onclick="return confirm('Delete this chat?')"
                    >
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </div>
            @empty
            <div class="flex flex-col items-center justify-center py-10 text-center">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-2xl mb-3"
                     style="background: var(--nx-surface3); border: 1px solid var(--nx-border);">💬</div>
                <p class="text-xs font-medium" style="color: var(--nx-text-dim);">No chats yet</p>
                <p class="text-xs mt-0.5" style="color: var(--nx-text-dim);">Click + to start</p>
            </div>
            @endforelse
        </div>

        {{-- Bottom actions --}}
        <div class="p-2 border-t" style="border-color: var(--nx-border);">
            <button
                wire:click="openSettings"
                class="nx-btn nx-btn-ghost w-full justify-center text-xs gap-1.5"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3"/></svg>
                Chat Settings
            </button>
        </div>
    </div>

    {{-- ────────────────────────────────────────────────────────────────────────── --}}
    {{-- MAIN: Chat Area                                                            --}}
    {{-- ────────────────────────────────────────────────────────────────────────── --}}
    <div class="flex-1 flex flex-col min-w-0">

        @if($chatId && $this->selectedChat)

            {{-- Chat Header --}}
            <div class="flex-shrink-0 flex items-center justify-between px-4 py-3 border-b glass"
                 style="border-color: var(--nx-border);">
                <div class="flex items-center gap-3 min-w-0">
                    <button class="md:hidden nx-btn nx-btn-ghost p-1.5" @click="$wire.sidebarOpen = !$wire.sidebarOpen">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0"
                         style="background: var(--nx-gradient); box-shadow: 0 0 10px var(--nx-accent-glow);">
                        <span class="text-white text-xs font-bold">N</span>
                    </div>
                    <div class="min-w-0">
                        <div class="font-semibold text-sm truncate" style="color: var(--nx-text);">{{ $this->selectedChat->getDisplayTitle() }}</div>
                        <div class="flex items-center gap-1.5 mt-0.5">
                            <span class="nx-badge nx-badge-accent">{{ ucfirst($this->selectedChat->provider) }}</span>
                            <span class="text-xs" style="color: var(--nx-text-dim);">{{ $this->selectedChat->model ?: 'default model' }}</span>
                            <span class="text-xs" style="color: var(--nx-text-dim);">· {{ number_format($this->selectedChat->getTotalTokens()) }} tokens</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-1 flex-shrink-0">
                    <button wire:click="openSettings" class="nx-btn nx-btn-ghost text-xs gap-1.5" title="Chat Settings">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3"/></svg>
                        Settings
                    </button>
                    <button @click="$wire.setRightPanel('documents')" class="nx-btn nx-btn-ghost text-xs" title="Documents">📄 Docs</button>
                    <button @click="$wire.setRightPanel('memory')" class="nx-btn nx-btn-ghost text-xs" title="Memory">🧠 Memory</button>
                </div>
            </div>

            {{-- Messages --}}
            <div id="nx-messages" class="flex-1 overflow-y-auto px-4 py-5 space-y-5" x-ref="messages"
                 style="background: var(--nx-bg);">
                @foreach($this->messages as $msg)
                    @include('nexus.partials.message', ['message' => $msg])
                @endforeach

                {{-- Streaming bubble --}}
                <div x-show="streaming" x-cloak class="flex gap-3 animate-fade-slide">
                    <div class="w-8 h-8 rounded-xl flex-shrink-0 flex items-center justify-center text-white text-xs font-bold"
                         style="background: var(--nx-gradient); box-shadow: 0 0 10px var(--nx-accent-glow);">N</div>
                    <div class="max-w-2xl flex-1">
                        <div class="rounded-2xl rounded-tl-sm px-4 py-3 nx-prose text-sm"
                             style="background: var(--nx-ai-bubble); border: 1px solid var(--nx-border);"
                             x-html="renderMarkdown(streamText)"></div>
                        <div x-show="!streamText" class="flex gap-1 mt-2 ml-2">
                            <span class="typing-dot"></span>
                            <span class="typing-dot"></span>
                            <span class="typing-dot"></span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Input area --}}
            @include('nexus.partials.input-area')

        @else
            {{-- Empty state --}}
            <div class="flex-1 flex flex-col items-center justify-center p-8 text-center" style="background: var(--nx-bg);">
                <div class="w-20 h-20 rounded-3xl flex items-center justify-center text-4xl mb-5 shadow-2xl"
                     style="background: var(--nx-gradient); box-shadow: 0 8px 32px var(--nx-accent-glow);">🤖</div>
                <h2 class="text-2xl font-bold mb-2 tracking-tight" style="color: var(--nx-text);">Welcome to NexusAI</h2>
                <p class="text-sm mb-6 max-w-sm" style="color: var(--nx-text-muted);">Your intelligent AI workspace. Select a workspace and start a conversation, or create a new one.</p>
                <div class="flex gap-3">
                    <button
                        wire:click="createChat"
                        @if(!$workspaceId) disabled @endif
                        class="nx-btn nx-btn-accent px-5 py-2.5 text-sm disabled:opacity-40"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        New Chat
                    </button>
                    @if(!$workspaceId)
                    <button wire:click="$set('showNewWorkspaceModal', true)" class="nx-btn nx-btn-outline px-5 py-2.5 text-sm">
                        Create Workspace
                    </button>
                    @endif
                </div>
                @if(!$workspaceId)
                <p class="text-xs mt-4" style="color: var(--nx-text-dim);">Create a workspace to get started →</p>
                @endif
            </div>
        @endif
    </div>

    {{-- ────────────────────────────────────────────────────────────────────────── --}}
    {{-- RIGHT: Contextual Panel                                                    --}}
    {{-- ────────────────────────────────────────────────────────────────────────── --}}
    <div class="flex-shrink-0 w-[280px] flex flex-col border-l nx-right-panel"
         style="background: var(--nx-surface); border-color: var(--nx-border);">

        {{-- Tabs --}}
        <div class="flex border-b px-2 pt-2 gap-1" style="border-color: var(--nx-border);">
            @foreach(['tools' => ['🔧', 'Tools'], 'documents' => ['📄', 'Docs'], 'memory' => ['🧠', 'Memory']] as $tab => [$icon, $label])
            <button
                wire:click="setRightPanel('{{ $tab }}')"
                class="nx-btn text-xs px-2.5 py-1.5 rounded-t-lg rounded-b-none transition-all"
                style="{{ $rightPanel === $tab
                    ? 'background: var(--nx-surface3); color: var(--nx-accent); border-bottom: 2px solid var(--nx-accent);'
                    : 'color: var(--nx-text-dim); background: transparent;' }}"
            >{{ $icon }} {{ $label }}</button>
            @endforeach
        </div>

        <div class="flex-1 overflow-y-auto p-3">
            @if($rightPanel === 'tools')
                <p class="text-xs font-semibold mb-3 uppercase tracking-wider" style="color: var(--nx-text-dim);">Available Tools</p>
                <div class="space-y-2">
                    @foreach([
                        ['🔍', 'Web Search',    'Real-time internet search',          'cyan'],
                        ['🧮', 'Calculator',    'Math expression evaluator',           'green'],
                        ['🌤', 'Weather',       'Live weather for any location',       'yellow'],
                        ['🎨', 'Image Gen',     'Generate images from descriptions',   'purple'],
                        ['💻', 'Code Exec',     'Run PHP, JS & Python code',           'accent'],
                    ] as [$icon, $name, $desc, $color])
                    <div class="flex items-start gap-2.5 p-2.5 rounded-xl transition-colors"
                         style="background: var(--nx-surface2); border: 1px solid var(--nx-border);"
                         onmouseenter="this.style.borderColor='var(--nx-border2)';"
                         onmouseleave="this.style.borderColor='var(--nx-border)';">
                        <span class="text-base leading-none mt-0.5">{{ $icon }}</span>
                        <div>
                            <div class="text-xs font-semibold" style="color: var(--nx-text);">{{ $name }}</div>
                            <div class="text-xs mt-0.5" style="color: var(--nx-text-dim);">{{ $desc }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @elseif($rightPanel === 'documents')
                @livewire('nexus.document-manager', ['workspaceId' => $workspaceId])
            @elseif($rightPanel === 'memory')
                @livewire('nexus.memory-manager')
            @endif
        </div>
    </div>

    {{-- ────────────────────────────────────────────────────────────────────────── --}}
    {{-- MODAL: Chat Settings                                                       --}}
    {{-- ────────────────────────────────────────────────────────────────────────── --}}
    @if($showSettingsModal)
    <div class="nx-modal-overlay" wire:click.self="$set('showSettingsModal', false)">
        <div class="nx-modal" style="max-width: 540px;">

            <div class="nx-modal-header">
                <div class="nx-modal-title">
                    <svg class="w-4 h-4" style="color: var(--nx-accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3"/></svg>
                    Chat Settings
                </div>
                <button wire:click="$set('showSettingsModal', false)" class="nx-btn-ghost rounded-lg p-1.5 transition-colors"
                        style="color: var(--nx-text-dim);"
                        onmouseenter="this.style.background='var(--nx-surface3)'; this.style.color='var(--nx-text)';"
                        onmouseleave="this.style.background='transparent'; this.style.color='var(--nx-text-dim)';">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="space-y-3">

                {{-- Provider --}}
                <div class="nx-modal-section">
                    <label class="nx-modal-label">AI Provider</label>
                    <select
                        wire:model="chatProvider"
                        @change="$wire.loadModels($event.target.value)"
                        class="nx-input"
                    >
                        @foreach($this->providers as $p)
                        <option value="{{ $p['id'] }}">{{ $p['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Model --}}
                <div class="nx-modal-section">
                    <div class="flex items-center justify-between mb-2">
                        <label class="nx-modal-label" style="margin-bottom: 0;">Model</label>
                        @if($loadingModels)
                        <span class="text-xs flex items-center gap-1" style="color: var(--nx-text-dim);">
                            <svg class="w-3 h-3 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            Loading…
                        </span>
                        @elseif(count($availableModels) > 0)
                        <span class="nx-badge nx-badge-green">{{ count($availableModels) }} models</span>
                        @endif
                    </div>

                    @if(!$useCustomModel && count($availableModels) > 0)
                    {{-- Dropdown from API --}}
                    <select wire:model="chatModel" class="nx-input">
                        <option value="">— Select model —</option>
                        @foreach($availableModels as $m)
                        <option value="{{ $m['id'] }}">{{ $m['name'] }}</option>
                        @endforeach
                    </select>
                    <button
                        wire:click="$set('useCustomModel', true)"
                        class="text-xs mt-1.5 transition-colors"
                        style="color: var(--nx-text-dim);"
                        onmouseenter="this.style.color='var(--nx-accent)';"
                        onmouseleave="this.style.color='var(--nx-text-dim)';"
                    >✏️ Enter custom model ID instead</button>

                    @else
                    {{-- Custom / Manual input --}}
                    <input
                        wire:model="chatModel"
                        class="nx-input"
                        placeholder="e.g. mistral-large-2411, gemini-2.0-flash…"
                    >
                    @if(count($availableModels) > 0)
                    <button
                        wire:click="$set('useCustomModel', false)"
                        class="text-xs mt-1.5 transition-colors"
                        style="color: var(--nx-text-dim);"
                        onmouseenter="this.style.color='var(--nx-accent)';"
                        onmouseleave="this.style.color='var(--nx-text-dim)';"
                    >← Back to model list</button>
                    @else
                    <p class="text-xs mt-1" style="color: var(--nx-text-dim);">
                        @if($loadingModels) Loading models… @else No models loaded — enter a model ID manually. @endif
                    </p>
                    @endif
                    @endif
                </div>

                {{-- System Prompt --}}
                <div class="nx-modal-section">
                    <label class="nx-modal-label">System Prompt</label>
                    <textarea wire:model="chatPrompt" class="nx-input text-sm" rows="4" placeholder="You are a helpful AI assistant…"></textarea>
                </div>

                {{-- Actions --}}
                <div class="flex gap-2 justify-end pt-1">
                    <button wire:click="$set('showSettingsModal', false)" class="nx-btn nx-btn-ghost">Cancel</button>
                    <button wire:click="updateChatSettings" class="nx-btn nx-btn-accent">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Save Settings
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ────────────────────────────────────────────────────────────────────────── --}}
    {{-- MODAL: New Workspace                                                       --}}
    {{-- ────────────────────────────────────────────────────────────────────────── --}}
    @if($showNewWorkspaceModal)
    <div class="nx-modal-overlay" wire:click.self="$set('showNewWorkspaceModal', false)">
        <div class="nx-modal">
            <div class="nx-modal-header">
                <div class="nx-modal-title">
                    <span>✨</span> New Workspace
                </div>
                <button wire:click="$set('showNewWorkspaceModal', false)" class="nx-btn-ghost rounded-lg p-1.5"
                        style="color: var(--nx-text-dim);"
                        onmouseenter="this.style.background='var(--nx-surface3)';"
                        onmouseleave="this.style.background='transparent';">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="space-y-3">
                <div class="nx-modal-section">
                    <label class="nx-modal-label">Name & Icon</label>
                    <div class="flex gap-2">
                        <input wire:model="wsIcon" class="nx-input w-16 text-center text-xl" placeholder="🤖" maxlength="2">
                        <input wire:model="wsName" class="nx-input flex-1" placeholder="Workspace name">
                    </div>
                </div>
                <div class="nx-modal-section">
                    <label class="nx-modal-label">Accent Color</label>
                    <input wire:model="wsColor" type="color" class="nx-input h-10 cursor-pointer">
                </div>
                <div class="nx-modal-section">
                    <label class="nx-modal-label">Default Provider</label>
                    <select wire:model="wsProvider" class="nx-input">
                        @foreach($this->providers as $p)<option value="{{ $p['id'] }}">{{ $p['name'] }}</option>@endforeach
                    </select>
                </div>
                <div class="nx-modal-section">
                    <label class="nx-modal-label">System Prompt (optional)</label>
                    <textarea wire:model="wsPrompt" class="nx-input text-sm" rows="3" placeholder="You are a helpful AI…"></textarea>
                </div>
                <div class="flex gap-2 justify-end pt-1">
                    <button wire:click="$set('showNewWorkspaceModal', false)" class="nx-btn nx-btn-ghost">Cancel</button>
                    <button wire:click="createWorkspace" class="nx-btn nx-btn-accent">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Create Workspace
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <script>
    function nexusApp(wire) {
        return {
            streaming: false,
            streamText: '',
            eventSource: null,

            init() {
                // Sync theme toggle icon on init
                const theme = localStorage.getItem('nx-theme') || 'dark';
                if (theme === 'light') {
                    document.querySelectorAll('.icon-dark').forEach(el => el.classList.add('hidden'));
                    document.querySelectorAll('.icon-light').forEach(el => el.classList.remove('hidden'));
                }

                this.$watch('streaming', v => {
                    if (!v) this.$nextTick(() => this.scrollToBottom());
                });
                Livewire.on('chat-selected', () => {
                    this.stopStream();
                    this.$nextTick(() => this.scrollToBottom());
                });
                // Listen for speak events dispatched by message buttons (plain JS onclick)
                document.addEventListener('nx-speak', (e) => this.speak(e.detail.text));
            },

            scrollToBottom() {
                const el = this.$refs.messages;
                if (el) el.scrollTop = el.scrollHeight;
            },

            stopStream() {
                if (this.eventSource) { this.eventSource.close(); this.eventSource = null; }
                this.streaming = false;
                this.streamText = '';
            },

            async sendMessage(msg, chatId) {
                if (!msg.trim() || this.streaming || !chatId) return;
                this.streaming = true;
                this.streamText = '';
                this.$nextTick(() => this.scrollToBottom());

                const url = `/nexus/stream/${chatId}`;
                const token = document.querySelector('meta[name="csrf-token"]').content;

                try {
                    const res = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'Accept': 'text/event-stream'
                        },
                        body: JSON.stringify({ message: msg }),
                    });

                    const reader = res.body.getReader();
                    const decoder = new TextDecoder();

                    while (true) {
                        const { done, value } = await reader.read();
                        if (done) break;
                        const lines = decoder.decode(value).split('\n');
                        for (const line of lines) {
                            if (!line.startsWith('data: ')) continue;
                            try {
                                const data = JSON.parse(line.slice(6));
                                if (data.type === 'delta') {
                                    this.streamText += data.text;
                                    this.$nextTick(() => this.scrollToBottom());
                                } else if (data.type === 'done') {
                                    wire.call('onStreamComplete', this.streamText, chatId);
                                    this.streaming = false;
                                    this.streamText = '';
                                } else if (data.type === 'error') {
                                    wire.call('onStreamError', data.message);
                                    this.streaming = false;
                                }
                            } catch {}
                        }
                    }
                } catch (e) {
                    wire.call('onStreamError', e.message);
                    this.streaming = false;
                }
            },

            renderMarkdown(text) {
                if (!text) return '';
                text = text.replace(/\[\[NEXUS_IMAGE:(https?:[^\]]+)\]\]/g, (_, url) =>
                    `<img src="${url}" class="nx-image-result" alt="Generated image">`
                );
                text = text.replace(/```(\w*)\n([\s\S]*?)```/g, (_, l, c) =>
                    `<pre data-lang="${l}"><code>${c.replace(/</g,'&lt;')}</code></pre>`
                );
                text = text.replace(/`([^`]+)`/g, '<code>$1</code>');
                text = text.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>').replace(/\*([^*]+)\*/g, '<em>$1</em>');
                text = text.replace(/^### (.+)$/gm,'<h3>$1</h3>').replace(/^## (.+)$/gm,'<h2>$1</h2>').replace(/^# (.+)$/gm,'<h1>$1</h1>');
                text = text.replace(/^[-•] (.+)$/gm,'<li>$1</li>').replace(/(<li>[\s\S]+?<\/li>)/g,'<ul>$1</ul>');
                text = text.replace(/\n\n/g,'</p><p>').replace(/\n/g,'<br>');
                return `<p>${text}</p>`;
            },

            // Voice
            mediaRecorder: null, audioChunks: [], recording: false,
            async startRecording() {
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                this.mediaRecorder = new MediaRecorder(stream);
                this.audioChunks = [];
                this.mediaRecorder.ondataavailable = e => this.audioChunks.push(e.data);
                this.mediaRecorder.onstop = () => this.sendAudio();
                this.mediaRecorder.start();
                this.recording = true;
            },
            stopRecording() { this.mediaRecorder?.stop(); this.recording = false; },
            async sendAudio() {
                const blob = new Blob(this.audioChunks, { type: 'audio/webm' });
                const fd = new FormData();
                fd.append('audio', blob, 'recording.webm');
                fd.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                const res = await fetch('/nexus/voice/transcribe', { method: 'POST', body: fd });
                const data = await res.json();
                if (data.text) wire.set('messageInput', data.text);
            },

            // TTS
            async speak(text) {
                const res = await fetch('/nexus/voice/synthesize', {
                    method:'POST',
                    headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},
                    body: JSON.stringify({ text })
                });
                const data = await res.json();
                if (data.audio) { const a = new Audio('data:audio/mp3;base64,'+data.audio); a.play(); }
            }
        };
    }
    </script>
</div>
