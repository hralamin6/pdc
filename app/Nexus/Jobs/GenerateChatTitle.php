<?php

namespace App\Nexus\Jobs;

use App\Models\NexusChat;
use App\Nexus\Agents\TitleGeneratorAgent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateChatTitle implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private int $chatId) {}

    public function handle(): void
    {
        $chat = NexusChat::with('messages')->find($this->chatId);
        if (!$chat || $chat->title) return;

        $messages = $chat->messages()
            ->whereIn('role', ['user', 'assistant'])
            ->orderBy('created_at')
            ->take(4)
            ->get()
            ->map(fn($m) => strtoupper($m->role).': '.substr($m->content, 0, 200))
            ->implode("\n");

        if (strlen($messages) < 10) return;

        try {
            $response = TitleGeneratorAgent::make()->prompt(
                "Generate a 3-5 word title for this conversation:\n\n{$messages}"
            );

            $title = trim($response?->text ?? '');
            if ($title && strlen($title) <= 80) {
                $chat->update(['title' => $title]);
            }
        } catch (\Throwable $e) {
            Log::warning('NexusAI: title generation failed', ['error' => $e->getMessage()]);
        }
    }
}
