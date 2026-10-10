<?php

namespace App\Nexus\Jobs;

use App\Models\NexusDocument;
use App\Models\NexusDocumentChunk;
use App\Nexus\Services\NexusEmbeddingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class IndexNexusDocument implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;

    public function __construct(private int $documentId) {}

    public function handle(NexusEmbeddingService $embedding): void
    {
        $doc = NexusDocument::findOrFail($this->documentId);
        $doc->update(['status' => 'indexing']);

        try {
            $text = $this->extractText($doc);

            if (empty(trim($text))) {
                $doc->update(['status' => 'failed', 'error_message' => 'Could not extract text from document.']);
                return;
            }

            // Delete old chunks
            $doc->chunks()->delete();

            // Chunk: ~500 chars with 50-char overlap
            $chunks    = $this->chunkText($text, 500, 50);
            $chunkRows = [];

            foreach ($chunks as $i => $chunk) {
                $embed = $embedding->embed($chunk);
                NexusDocumentChunk::create([
                    'nexus_document_id' => $doc->id,
                    'chunk_index'       => $i,
                    'content'           => $chunk,
                    'embedding'         => $embed,
                    'metadata'          => ['chars' => strlen($chunk)],
                ]);
            }

            $doc->update(['status' => 'indexed', 'chunk_count' => count($chunks)]);
            Log::info("NexusRAG: indexed document #{$doc->id} with " . count($chunks) . " chunks");

        } catch (\Throwable $e) {
            $doc->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
            Log::error("NexusRAG: indexing failed for doc #{$doc->id}", ['error' => $e->getMessage()]);
        }
    }

    private function extractText(NexusDocument $doc): string
    {
        $path = Storage::disk('local')->path($doc->file_path);

        return match (true) {
            str_contains($doc->mime_type, 'pdf') => $this->extractPdf($path),
            str_contains($doc->mime_type, 'text') => file_get_contents($path),
            default => file_get_contents($path),
        };
    }

    private function extractPdf(string $path): string
    {
        if (!class_exists(\Smalot\PdfParser\Parser::class)) {
            return '';
        }
        $parser = new \Smalot\PdfParser\Parser();
        $pdf    = $parser->parseFile($path);
        return $pdf->getText();
    }

    private function chunkText(string $text, int $size = 500, int $overlap = 50): array
    {
        $text   = preg_replace('/\s+/', ' ', trim($text));
        $length = strlen($text);
        $chunks = [];
        $offset = 0;

        while ($offset < $length) {
            $chunk = substr($text, $offset, $size);
            if (strlen(trim($chunk)) > 20) {
                $chunks[] = trim($chunk);
            }
            $offset += ($size - $overlap);
        }

        return $chunks;
    }
}
