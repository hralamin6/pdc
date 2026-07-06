<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class MessageAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'message_id',
        'file_name',
        'file_path',
        'file_type',
        'mime_type',
        'file_size',
    ];

    /**
     * Get the message that owns the attachment.
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    /**
     * Get the full URL of the attachment.
     */
    public function getUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }

    /**
     * Get human-readable file size.
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Check if attachment is an image.
     */
    public function isImage(): bool
    {
        return $this->file_type === 'image';
    }

    /**
     * Delete attachment file from storage.
     */
    public function deleteFile(): void
    {
        if (Storage::exists($this->file_path)) {
            Storage::delete($this->file_path);
        }
    }

    /**
     * Boot method to handle model events.
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($attachment) {
            $attachment->deleteFile();
        });
    }
}
