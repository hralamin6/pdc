<?php

namespace App\Nexus\Services;

use App\Models\NexusMemory;

class NexusMemoryService
{
    public function __construct(private NexusEmbeddingService $embedding) {}

    /**
     * Store or update a memory for a user.
     */
    public function remember(int $userId, string $key, string $value, string $category = 'general', ?int $chatId = null): NexusMemory
    {
        return NexusMemory::updateOrCreate(
            ['user_id' => $userId, 'key' => $key],
            [
                'value'          => $value,
                'category'       => $category,
                'source_chat_id' => $chatId,
                'embedding'      => $this->embedding->embed("{$key}: {$value}"),
            ]
        );
    }

    /**
     * Delete a memory.
     */
    public function forget(int $userId, string $key): void
    {
        NexusMemory::where('user_id', $userId)->where('key', $key)->delete();
    }

    /**
     * Get all memories for a user formatted as a context string.
     */
    public function getContextString(int $userId, string $query = ''): string
    {
        $memories = NexusMemory::where('user_id', $userId)->get();

        if ($memories->isEmpty()) {
            return '';
        }

        // If query given, prioritize relevant memories
        if ($query && $memories->count() > 10) {
            $queryEmbed = $this->embedding->embed($query);
            $memories = $memories->map(function ($m) use ($queryEmbed) {
                return [
                    'memory' => $m,
                    'score'  => $m->embedding
                        ? $this->embedding->cosineSimilarity($queryEmbed, $m->embedding)
                        : 0.0,
                ];
            })->sortByDesc('score')->take(8)->pluck('memory');
        }

        return $memories->map(fn($m) => "- {$m->key}: {$m->value}")->implode("\n");
    }

    /**
     * Extract and save memories from a conversation using AI.
     * Called as a background job after every 5 messages.
     */
    public function extractFromChat(\App\Models\NexusChat $chat): void
    {
        $messages = $chat->messages()
            ->whereIn('role', ['user', 'assistant'])
            ->orderBy('created_at')
            ->take(20)
            ->get()
            ->map(fn($m) => strtoupper($m->role).': '.substr($m->content, 0, 300))
            ->implode("\n");

        if (strlen($messages) < 50) return;

        $prompt = "Analyze this conversation and extract key facts about the user. "
                . "Return ONLY a valid JSON array of objects like: "
                . '[{"key":"user_name","value":"John","category":"personal"}, ...]. '
                . "Only include concrete facts (name, language preference, occupation, goals, etc). "
                . "Max 5 facts. If nothing to extract, return empty array [].\n\n"
                . "CONVERSATION:\n{$messages}";

        try {
            $agent    = \App\Nexus\Agents\TitleGeneratorAgent::make();
            $response = $agent->prompt($prompt);
            $text     = $response?->text ?? '';

            preg_match('/\[.*\]/s', $text, $matches);
            if (!$matches) return;

            $facts = json_decode($matches[0], true);
            if (!is_array($facts)) return;

            foreach ($facts as $fact) {
                if (isset($fact['key'], $fact['value'])) {
                    $this->remember(
                        $chat->user_id,
                        $fact['key'],
                        $fact['value'],
                        $fact['category'] ?? 'general',
                        $chat->id
                    );
                }
            }
        } catch (\Throwable $e) {
            \Log::warning('NexusMemory: extraction failed', ['error' => $e->getMessage()]);
        }
    }
}
