<?php

namespace App\Nexus\Jobs;

use App\Models\NexusChat;
use App\Nexus\Services\NexusMemoryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExtractMemoriesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private int $chatId) {}

    public function handle(NexusMemoryService $memoryService): void
    {
        $chat = NexusChat::find($this->chatId);
        if (!$chat) return;

        $memoryService->extractFromChat($chat);
    }
}
