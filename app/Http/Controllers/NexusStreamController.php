<?php

namespace App\Http\Controllers;

use App\Models\NexusChat;
use App\Models\NexusMessage;
use App\Nexus\Agents\ChatAgent;
use App\Nexus\Jobs\ExtractMemoriesJob;
use App\Nexus\Jobs\GenerateChatTitle;
use App\Nexus\Services\NexusMemoryService;
use App\Nexus\Services\NexusRagService;
use App\Nexus\Tools\CalculatorTool;
use App\Nexus\Tools\CodeExecutorTool;
use App\Nexus\Tools\ImageGeneratorTool;
use App\Nexus\Tools\WeatherTool;
use App\Nexus\Tools\WebSearchTool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Streaming\Events\TextDelta;

class NexusStreamController extends Controller
{
    public function __construct(
        private NexusMemoryService $memory,
        private NexusRagService    $rag,
    ) {}

    public function stream(Request $request, NexusChat $chat)
    {
        // Auth check
        abort_if($chat->user_id !== auth()->id(), 403);

        $userMessage = $request->get('message', '');
        if (empty(trim($userMessage))) {
            abort(400, 'Message is required');
        }

        // Save user message
        $userMsg = NexusMessage::create([
            'nexus_chat_id' => $chat->id,
            'role'          => 'user',
            'content'       => $userMessage,
            'tokens_in'     => (int) ceil(strlen($userMessage) / 4),
        ]);

        $chat->update(['last_message_at' => now()]);
        $chat->increment('message_count');

        // Build context
        $memCtx = $this->memory->getContextString(auth()->id(), $userMessage);
        $ragCtx = $this->rag->getContext($userMessage, $chat->nexus_workspace_id, auth()->id());

        // Build tools from workspace settings
        $enabledToolNames = $chat->workspace?->getEnabledTools() ?? ['web_search', 'calculator', 'weather', 'image_generator', 'code_executor'];
        $toolMap = [
            'web_search'      => new WebSearchTool(),
            'calculator'      => new CalculatorTool(),
            'weather'         => new WeatherTool(),
            'image_generator' => new ImageGeneratorTool(),
            'code_executor'   => new CodeExecutorTool(),
        ];
        $tools = array_values(array_intersect_key($toolMap, array_flip($enabledToolNames)));

        // Build agent
        $agent = ChatAgent::forChat($chat, $memCtx, $ragCtx, $tools);

        return response()->stream(function () use ($chat, $agent, $userMessage) {
            $fullContent  = '';
            $tokensOut    = 0;
            $modelUsed    = $chat->model ?? config('ai.providers.'.$chat->provider.'.default_model', 'default');

            echo "data: " . json_encode(['type' => 'start']) . "\n\n";
            ob_flush(); flush();

            try {
                $stream = $agent->stream(
                    $userMessage,
                    provider: $chat->provider,
                    model:    $chat->model ?: null,
                    timeout:  120,
                );

                foreach ($stream as $event) {
                    if ($event instanceof TextDelta) {
                        $fullContent .= $event->delta;
                        $tokensOut++;
                        echo "data: " . json_encode([
                            'type' => 'delta',
                            'text' => $event->delta,
                        ]) . "\n\n";
                        ob_flush(); flush();
                    }
                }

                // Save assistant message
                $assistantMsg = NexusMessage::create([
                    'nexus_chat_id' => $chat->id,
                    'role'          => 'assistant',
                    'content'       => $fullContent,
                    'tokens_out'    => $tokensOut,
                    'model_used'    => $modelUsed,
                ]);

                $chat->incrementTokens(0, $tokensOut);

                // Auto-title (after 2nd message)
                if ($chat->message_count === 2 && !$chat->title) {
                    GenerateChatTitle::dispatch($chat->id);
                }

                // Extract memories every 5 assistant messages
                $assistantCount = $chat->messages()->where('role', 'assistant')->count();
                if ($assistantCount % 5 === 0) {
                    ExtractMemoriesJob::dispatch($chat->id);
                }

                echo "data: " . json_encode([
                    'type'          => 'done',
                    'message_id'    => $assistantMsg->id,
                    'tokens_out'    => $tokensOut,
                    'model'         => $modelUsed,
                ]) . "\n\n";

            } catch (\Throwable $e) {
                Log::error('NexusStream error', ['error' => $e->getMessage()]);

                // Save error message
                NexusMessage::create([
                    'nexus_chat_id' => $chat->id,
                    'role'          => 'assistant',
                    'content'       => "❌ **Error:** " . $e->getMessage(),
                    'is_error'      => true,
                ]);

                echo "data: " . json_encode([
                    'type'    => 'error',
                    'message' => $e->getMessage(),
                ]) . "\n\n";
            }

            ob_flush(); flush();
        }, 200, [
            'Content-Type'      => 'text/event-stream',
            'Cache-Control'     => 'no-cache, no-store',
            'X-Accel-Buffering' => 'no',
            'Connection'        => 'keep-alive',
        ]);
    }

    public function transcribe(Request $request)
    {
        $request->validate(['audio' => 'required|file|max:25600']);

        try {
            $service = app(\App\Nexus\Services\NexusVoiceService::class);
            $text    = $service->transcribe($request->file('audio'));
            return response()->json(['text' => $text]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function synthesize(Request $request)
    {
        $request->validate(['text' => 'required|string|max:4000']);

        try {
            $service = app(\App\Nexus\Services\NexusVoiceService::class);
            $audio   = $service->synthesize($request->text, $request->get('voice', 'alloy'));
            return response()->json(['audio' => $audio]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
