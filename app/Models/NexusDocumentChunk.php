<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NexusDocumentChunk extends Model
{
    protected $fillable = [
        'nexus_document_id', 'chunk_index', 'content', 'embedding', 'metadata',
    ];

    protected function casts(): array
    {
        return [
            'embedding' => 'array',
            'metadata'  => 'array',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(NexusDocument::class, 'nexus_document_id');
    }
}
