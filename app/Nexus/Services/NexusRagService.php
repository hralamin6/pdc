<?php

namespace App\Nexus\Services;

use App\Models\NexusDocumentChunk;

class NexusRagService
{
    public function __construct(private NexusEmbeddingService $embedding) {}

    /**
     * Find relevant document chunks and return as context string.
     */
    public function getContext(string $query, ?int $workspaceId, int $userId, int $limit = 4): string
    {
        $chunks = $this->embedding->findSimilar($query, $limit, $workspaceId, $userId);

        if (empty($chunks)) {
            return '';
        }

        $parts = [];
        foreach ($chunks as $i => $chunk) {
            $docName = $chunk->document->name ?? 'Document';
            $parts[] = "**[Source: {$docName}]**\n{$chunk->content}";
        }

        return implode("\n\n---\n\n", $parts);
    }
}
