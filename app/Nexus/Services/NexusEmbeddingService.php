<?php

namespace App\Nexus\Services;

use App\Models\NexusDocumentChunk;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Ai;

class NexusEmbeddingService
{
    /**
     * Generate an embedding vector for the given text.
     * Falls back to a simple TF-IDF-like hash if embedding API unavailable.
     */
    public function embed(string $text): array
    {
        try {
            $response = Ai::embed(substr($text, 0, 8000));
            return $response->embedding ?? $this->fallbackEmbed($text);
        } catch (\Throwable $e) {
            Log::warning('NexusEmbedding: API unavailable, using fallback', ['error' => $e->getMessage()]);
            return $this->fallbackEmbed($text);
        }
    }

    /**
     * Find top-K similar chunks using cosine similarity.
     */
    public function findSimilar(string $query, int $limit = 5, ?int $workspaceId = null, ?int $userId = null): array
    {
        $queryEmbed = $this->embed($query);

        $dbQuery = NexusDocumentChunk::query()
            ->join('nexus_documents', 'nexus_document_chunks.nexus_document_id', '=', 'nexus_documents.id')
            ->where('nexus_documents.status', 'indexed');

        if ($workspaceId) {
            $dbQuery->where('nexus_documents.nexus_workspace_id', $workspaceId);
        } elseif ($userId) {
            $dbQuery->where('nexus_documents.user_id', $userId);
        }

        $chunks = $dbQuery->select('nexus_document_chunks.*')->get();

        if ($chunks->isEmpty()) {
            return [];
        }

        // Compute cosine similarity for each chunk
        $scored = $chunks->map(function ($chunk) use ($queryEmbed) {
            $chunkEmbed = $chunk->embedding ?? [];
            return [
                'chunk'      => $chunk,
                'similarity' => $this->cosineSimilarity($queryEmbed, $chunkEmbed),
            ];
        })->sortByDesc('similarity')->take($limit);

        return $scored->pluck('chunk')->values()->all();
    }

    /**
     * Cosine similarity between two float vectors.
     */
    public function cosineSimilarity(array $a, array $b): float
    {
        if (empty($a) || empty($b)) return 0.0;

        $minLen = min(count($a), count($b));
        $dot = $normA = $normB = 0.0;

        for ($i = 0; $i < $minLen; $i++) {
            $dot   += $a[$i] * $b[$i];
            $normA += $a[$i] * $a[$i];
            $normB += $b[$i] * $b[$i];
        }

        $denom = sqrt($normA) * sqrt($normB);
        return $denom > 0 ? $dot / $denom : 0.0;
    }

    /**
     * Simple fallback embedding: bag-of-words frequency vector (256 dimensions).
     */
    private function fallbackEmbed(string $text): array
    {
        $text   = strtolower(preg_replace('/[^a-z0-9\s]/i', '', $text));
        $words  = array_filter(explode(' ', $text));
        $vector = array_fill(0, 256, 0.0);

        foreach ($words as $word) {
            $idx = crc32($word) % 256;
            $idx = ($idx < 0) ? $idx + 256 : $idx;
            $vector[$idx] += 1.0;
        }

        // L2 normalize
        $norm = sqrt(array_sum(array_map(fn($v) => $v * $v, $vector)));
        if ($norm > 0) {
            $vector = array_map(fn($v) => $v / $norm, $vector);
        }

        return $vector;
    }
}
