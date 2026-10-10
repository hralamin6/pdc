<?php

namespace App\Nexus\Agents;

use App\Models\NexusChat;
use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Messages\AssistantMessage;
use Laravel\Ai\Messages\UserMessage;
use Laravel\Ai\Promptable;
use Stringable;

#[Temperature(0.7)]
#[MaxTokens(2048)]
class ChatAgent implements Agent, Conversational, HasTools
{
    use Promptable;

    private array $conversationMessages = [];
    private string $systemContext       = '';
    private array  $enabledTools        = [];

    public function __construct(
        array $conversationHistory,
        string $systemContext,
        array $enabledTools,
    ) {
        $this->conversationMessages = $conversationHistory;
        $this->systemContext        = $systemContext;
        $this->enabledTools         = $enabledTools;
    }

    public static function forChat(
        NexusChat $chat,
        string    $memoryContext,
        string    $ragContext,
        array     $enabledTools
    ): self {
        // Build conversation history
        $history = [];
        $messages = $chat->messages()
            ->whereIn('role', ['user', 'assistant'])
            ->orderBy('created_at')
            ->get();

        foreach ($messages as $msg) {
            if ($msg->isUser()) {
                $history[] = new UserMessage($msg->content);
            } elseif ($msg->isAssistant()) {
                $history[] = new AssistantMessage($msg->content);
            }
        }

        // Build system context
        $contextParts = [];
        if ($memoryContext) {
            $contextParts[] = "## What I know about you:\n{$memoryContext}";
        }
        if ($ragContext) {
            $contextParts[] = "## Relevant knowledge from your documents:\n{$ragContext}";
        }

        return new self($history, implode("\n\n", $contextParts), $enabledTools);
    }

    public function instructions(): Stringable|string
    {
        $base = "You are NexusAI — an intelligent, capable, and friendly AI assistant. "
              . "You have access to tools for real-time web search, calculations, weather, image generation, and code execution. "
              . "Always use tools when the user's request would benefit from live data or computation. "
              . "Format your responses in clean Markdown. "
              . "When generating code, always specify the language in the code block. "
              . "Be concise but thorough. If you generate an image, the [[NEXUS_IMAGE:...]] marker will be automatically rendered.";

        if ($this->systemContext) {
            $base .= "\n\n" . $this->systemContext;
        }

        return $base;
    }

    public function messages(): iterable
    {
        return $this->conversationMessages;
    }

    public function tools(): iterable
    {
        return $this->enabledTools;
    }
}
