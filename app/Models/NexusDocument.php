<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NexusDocument extends Model
{
    protected $fillable = [
        'user_id', 'nexus_workspace_id', 'name', 'original_filename',
        'mime_type', 'file_path', 'file_size', 'status', 'chunk_count', 'error_message',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(NexusWorkspace::class, 'nexus_workspace_id');
    }

    public function chunks(): HasMany
    {
        return $this->hasMany(NexusDocumentChunk::class);
    }

    public function isIndexed(): bool  { return $this->status === 'indexed'; }
    public function isPending(): bool  { return $this->status === 'pending'; }
    public function isIndexing(): bool { return $this->status === 'indexing'; }
    public function isFailed(): bool   { return $this->status === 'failed'; }

    public function getFileSizeHumanAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) return round($bytes / 1048576, 1).' MB';
        if ($bytes >= 1024)    return round($bytes / 1024, 1).' KB';
        return $bytes.' B';
    }
}
