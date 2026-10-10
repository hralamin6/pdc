<?php

namespace App\Livewire\Nexus;

use App\Models\NexusChat;
use App\Models\NexusMessage;
use App\Models\NexusWorkspace;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

#[Title('NexusAI')]
#[Layout('layouts.nexus')]
class NexusApp extends Component
{
    use Toast;

    // ── URL state ──────────────────────────────────────────────────────────────
    #[Url]
    public ?int $workspaceId = null;

    #[Url]
    public ?int $chatId = null;

    // ── UI state ───────────────────────────────────────────────────────────────
    public string $rightPanel     = 'tools';   // tools | documents | memory
    public bool   $sidebarOpen    = true;
    public bool   $chatListOpen   = true;
    public bool   $isStreaming    = false;
    public string $streamingText  = '';
    public string $messageInput   = '';
    public string $searchQuery    = '';

    // ── Modals ─────────────────────────────────────────────────────────────────
    public bool   $showNewWorkspaceModal = false;
    public bool   $showNewChatModal      = false;
    public bool   $showSettingsModal     = false;
    public bool   $showMemoryModal       = false;

    // ── Workspace form ─────────────────────────────────────────────────────────
    public string $wsName        = '';
    public string $wsDescription = '';
    public string $wsIcon        = '🤖';
    public string $wsColor       = '#6366f1';
    public string $wsProvider    = 'mistral';
    public string $wsModel       = '';
    public string $wsPrompt      = '';

    // ── Chat settings ──────────────────────────────────────────────────────────
    public string $chatProvider    = 'mistral';
    public string $chatModel       = '';
    public string $chatPrompt      = '';
    public bool   $useCustomModel  = false;
    public string $customModelInput = '';

    // ── Model loading ──────────────────────────────────────────────────────────
    public array  $availableModels  = [];
    public bool   $loadingModels    = false;

    public function mount(): void
    {
        abort_unless(auth()->user()->can('nexus.use'), 403);

        // Load first workspace if none selected
        if (!$this->workspaceId) {
            $ws = NexusWorkspace::where('user_id', auth()->id())
                ->orderBy('is_default', 'desc')
                ->orderBy('sort_order')
                ->first();

            if ($ws) {
                $this->workspaceId = $ws->id;
            } else {
                // Auto-create default workspace
                $ws = $this->createDefaultWorkspace();
                $this->workspaceId = $ws->id;
            }
        }

        // Load first chat if none selected
        if (!$this->chatId && $this->workspaceId) {
            $chat = NexusChat::where('nexus_workspace_id', $this->workspaceId)
                ->orderBy('is_pinned', 'desc')
                ->orderBy('last_message_at', 'desc')
                ->first();

            if ($chat) {
                $this->chatId = $chat->id;
            }
        }

        // Prefill chat settings
        if ($this->chatId) {
            $this->loadChatSettings();
        }
    }

    // ── Computed ───────────────────────────────────────────────────────────────
    #[Computed]
    public function workspaces()
    {
        return NexusWorkspace::where('user_id', auth()->id())
            ->orderBy('is_default', 'desc')
            ->orderBy('sort_order')
            ->get();
    }

    #[Computed]
    public function chats()
    {
        if (!$this->workspaceId) return collect();

        $query = NexusChat::where('nexus_workspace_id', $this->workspaceId)
            ->where('is_archived', false)
            ->orderBy('is_pinned', 'desc')
            ->orderBy('last_message_at', 'desc');

        if ($this->searchQuery) {
            $query->where(fn($q) =>
                $q->where('title', 'like', "%{$this->searchQuery}%")
                  ->orWhereHas('messages', fn($mq) =>
                      $mq->where('content', 'like', "%{$this->searchQuery}%")
                  )
            );
        }

        return $query->withCount('messages')->get();
    }

    #[Computed]
    public function selectedWorkspace()
    {
        return $this->workspaceId
            ? NexusWorkspace::find($this->workspaceId)
            : null;
    }

    #[Computed]
    public function selectedChat()
    {
        return $this->chatId
            ? NexusChat::with('workspace')->find($this->chatId)
            : null;
    }

    #[Computed]
    public function messages()
    {
        if (!$this->chatId) return collect();

        return NexusMessage::where('nexus_chat_id', $this->chatId)
            ->orderBy('created_at')
            ->get();
    }

    #[Computed]
    public function providers(): array
    {
        return collect(config('ai.providers', []))
            ->map(fn($v, $k) => ['id' => $k, 'name' => ucfirst($k)])
            ->values()
            ->all();
    }

    // ── Workspace CRUD ─────────────────────────────────────────────────────────
    public function createWorkspace(): void
    {
        $this->validate([
            'wsName'  => 'required|string|max:80',
            'wsIcon'  => 'nullable|string|max:5',
            'wsColor' => 'nullable|string|max:20',
        ]);

        $ws = NexusWorkspace::create([
            'user_id'          => auth()->id(),
            'name'             => $this->wsName,
            'description'      => $this->wsDescription,
            'icon'             => $this->wsIcon ?: '🤖',
            'color'            => $this->wsColor ?: '#6366f1',
            'default_provider' => $this->wsProvider,
            'default_model'    => $this->wsModel,
            'system_prompt'    => $this->wsPrompt,
        ]);

        $this->workspaceId        = $ws->id;
        $this->showNewWorkspaceModal = false;
        $this->reset(['wsName', 'wsDescription', 'wsIcon', 'wsColor', 'wsProvider', 'wsModel', 'wsPrompt']);
        $this->success('Workspace created!');
    }

    public function selectWorkspace(int $id): void
    {
        $this->workspaceId = $id;
        $this->chatId      = null;
        $this->searchQuery = '';

        // Auto-select first chat
        $chat = NexusChat::where('nexus_workspace_id', $id)
            ->orderBy('last_message_at', 'desc')
            ->first();

        if ($chat) {
            $this->chatId = $chat->id;
            $this->loadChatSettings();
        }
    }

    public function deleteWorkspace(int $id): void
    {
        $ws = NexusWorkspace::where('id', $id)->where('user_id', auth()->id())->first();
        if (!$ws) return;

        $ws->delete();

        if ($this->workspaceId === $id) {
            $this->workspaceId = null;
            $this->chatId      = null;
        }

        $this->success('Workspace deleted.');
    }

    // ── Chat CRUD ──────────────────────────────────────────────────────────────
    public function createChat(): void
    {
        abort_unless($this->workspaceId, 422);

        $ws   = $this->selectedWorkspace;
        $chat = NexusChat::create([
            'nexus_workspace_id' => $this->workspaceId,
            'user_id'            => auth()->id(),
            'provider'           => $ws->default_provider ?? 'mistral',
            'model'              => $ws->default_model,
            'system_prompt'      => $ws->system_prompt,
        ]);

        $this->chatId            = $chat->id;
        $this->showNewChatModal  = false;
        $this->loadChatSettings();
    }

    public function selectChat(int $id): void
    {
        $this->chatId      = $id;
        $this->isStreaming  = false;
        $this->streamingText = '';
        $this->loadChatSettings();
        $this->dispatch('chat-selected', chatId: $id);
    }

    public function deleteChat(int $id): void
    {
        NexusChat::where('id', $id)->where('user_id', auth()->id())->delete();

        if ($this->chatId === $id) {
            $this->chatId = null;
        }

        $this->success('Chat deleted.');
    }

    public function togglePin(int $id): void
    {
        $chat = NexusChat::where('id', $id)->where('user_id', auth()->id())->first();
        if ($chat) {
            $chat->update(['is_pinned' => !$chat->is_pinned]);
        }
    }

    public function archiveChat(int $id): void
    {
        NexusChat::where('id', $id)->where('user_id', auth()->id())->update(['is_archived' => true]);
        if ($this->chatId === $id) $this->chatId = null;
        $this->success('Chat archived.');
    }

    // ── Messaging ──────────────────────────────────────────────────────────────
    #[On('stream-complete')]
    public function onStreamComplete(string $content, int $chatId): void
    {
        $this->isStreaming   = false;
        $this->streamingText = '';
        unset($this->messages); // Clear cached computed
    }

    #[On('stream-error')]
    public function onStreamError(string $error): void
    {
        $this->isStreaming   = false;
        $this->streamingText = '';
        $this->error('AI Error: '.$error);
    }

    // ── Message actions ────────────────────────────────────────────────────────
    public function deleteMessage(int $id): void
    {
        NexusMessage::where('id', $id)
            ->whereHas('chat', fn($q) => $q->where('user_id', auth()->id()))
            ->delete();
    }

    public function branchFromMessage(int $messageId): void
    {
        $msg  = NexusMessage::with('chat')->find($messageId);
        if (!$msg || $msg->chat->user_id !== auth()->id()) return;

        $newChat = NexusChat::create([
            'nexus_workspace_id'  => $msg->chat->nexus_workspace_id,
            'user_id'             => auth()->id(),
            'provider'            => $msg->chat->provider,
            'model'               => $msg->chat->model,
            'system_prompt'       => $msg->chat->system_prompt,
            'title'               => 'Branch: '.$msg->chat->getDisplayTitle(),
            'branch_of_message_id' => $messageId,
        ]);

        // Copy messages up to branch point
        NexusMessage::where('nexus_chat_id', $msg->nexus_chat_id)
            ->where('created_at', '<=', $msg->created_at)
            ->orderBy('created_at')
            ->each(function ($m) use ($newChat) {
                $newChat->messages()->create([
                    'role'       => $m->role,
                    'content'    => $m->content,
                    'tokens_in'  => $m->tokens_in,
                    'tokens_out' => $m->tokens_out,
                    'model_used' => $m->model_used,
                ]);
            });

        $this->chatId = $newChat->id;
        $this->loadChatSettings();
        $this->success('Branched into new chat!');
    }

    // ── Settings ───────────────────────────────────────────────────────────────
    public function updateChatSettings(): void
    {
        if (!$this->chatId) return;

        $chat = NexusChat::with('workspace')->find($this->chatId);
        if (!$chat || $chat->user_id !== auth()->id()) return;

        $chat->update([
            'provider'      => $this->chatProvider,
            'model'         => trim($this->chatModel),
            'system_prompt' => $this->chatPrompt,
        ]);

        // Bust cached computed properties so the UI re-reads fresh data
        unset($this->selectedChat);
        unset($this->messages);

        $this->showSettingsModal = false;
        $this->success('Settings saved!');
    }

    public function openSettings(): void
    {
        $this->loadChatSettings();
        if ($this->chatProvider) {
            $this->fetchModelsForCurrentProvider($this->chatProvider);
        }
        $this->showSettingsModal = true;
    }

    public function setRightPanel(string $panel): void
    {
        $this->rightPanel = $panel;
    }

    // ── Dynamic Model Loading ───────────────────────────────────────────────────
    public function loadModels(string $provider): void
    {
        $this->chatProvider   = $provider;
        $this->useCustomModel = false;
        $this->fetchModelsForCurrentProvider($provider);
    }

    private function fetchModelsForCurrentProvider(string $provider): void
    {
        $this->loadingModels   = true;
        $this->availableModels = [];

        try {
            $this->availableModels = $this->fetchModelsForProvider($provider);
        } catch (\Throwable $e) {
            Log::warning("NexusApp: Could not load models for provider [{$provider}]", ['error' => $e->getMessage()]);
            $this->availableModels = [];
        }

        $this->loadingModels = false;
    }

    /**
     * Fetch available models for the given provider using stored API keys.
     * Returns an array of ['id' => string, 'name' => string].
     */
    private function fetchModelsForProvider(string $provider): array
    {
        $config = config("ai.providers.{$provider}", []);
        $key    = $config['key'] ?? null;
        $baseUrl = $config['url'] ?? null;

        return match ($provider) {
            'mistral' => $this->fetchMistralModels($key),
            'groq'    => $this->fetchOpenAICompatibleModels($key, 'https://api.groq.com/openai/v1/models'),
            'gemini'  => $this->fetchGeminiModels($key),
            'openrouter' => $this->fetchOpenAICompatibleModels($key, 'https://openrouter.ai/api/v1/models'),
            'cerebras'   => $this->fetchOpenAICompatibleModels($key, $baseUrl ? rtrim($baseUrl, '/').'/../v1/models' : 'https://api.cerebras.ai/v1/models'),
            'iflow'      => $this->fetchOpenAICompatibleModels($key, $baseUrl ? rtrim($baseUrl, '/').'/models' : null),
            'nvidia'     => $this->fetchOpenAICompatibleModels($key, $baseUrl ? rtrim($baseUrl, '/').'/models' : 'https://integrate.api.nvidia.com/v1/models'),
            'openai-custom' => $this->fetchOpenAICompatibleModels($key, $baseUrl ? rtrim($baseUrl, '/').'/models' : null),
            default => [],
        };
    }

    private function fetchMistralModels(?string $key): array
    {
        if (!$key) return [];
        $resp = Http::withToken($key)
            ->timeout(3)
            ->get('https://api.mistral.ai/v1/models');

        if (!$resp->ok()) return [];

        return collect($resp->json('data', []))
            ->map(fn($m) => ['id' => $m['id'], 'name' => $m['id']])
            ->sortBy('id')
            ->values()
            ->all();
    }

    private function fetchGeminiModels(?string $key): array
    {
        if (!$key) return [];
        $resp = Http::timeout(3)
            ->get("https://generativelanguage.googleapis.com/v1beta/models?key={$key}");

        if (!$resp->ok()) return [];

        return collect($resp->json('models', []))
            ->filter(fn($m) => str_contains($m['name'] ?? '', 'gemini'))
            ->map(fn($m) => [
                'id'   => str_replace('models/', '', $m['name']),
                'name' => $m['displayName'] ?? str_replace('models/', '', $m['name']),
            ])
            ->sortBy('id')
            ->values()
            ->all();
    }

    private function fetchOpenAICompatibleModels(?string $key, ?string $url): array
    {
        if (!$key || !$url) return [];

        $resp = Http::withToken($key)
            ->timeout(3)
            ->get($url);

        if (!$resp->ok()) return [];

        return collect($resp->json('data', []))
            ->map(fn($m) => ['id' => $m['id'], 'name' => $m['id']])
            ->sortBy('id')
            ->values()
            ->all();
    }

    // ── Private ────────────────────────────────────────────────────────────────
    private function loadChatSettings(): void
    {
        $chat = NexusChat::find($this->chatId);
        if (!$chat) return;

        $this->chatProvider    = $chat->provider;
        $this->chatModel       = $chat->model ?? '';
        $this->chatPrompt      = $chat->system_prompt ?? '';
        $this->useCustomModel  = false;
        $this->customModelInput = $chat->model ?? '';
        $this->availableModels = [];
    }

    private function createDefaultWorkspace(): NexusWorkspace
    {
        return NexusWorkspace::create([
            'user_id'    => auth()->id(),
            'name'       => 'My Workspace',
            'icon'       => '🤖',
            'color'      => '#6366f1',
            'is_default' => true,
        ]);
    }

    public function render()
    {
        return view('nexus.livewire.nexus-app');
    }
}
