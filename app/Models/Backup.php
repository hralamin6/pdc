<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Backup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'disk',
        'path',
        'type',
        'status',
        'file_size',
        'includes',
        'error_message',
        'started_at',
        'completed_at',
        'created_by',
    ];

    protected $casts = [
        'includes' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'file_size' => 'integer',
    ];

    // Relationships
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeRunning($query)
    {
        return $query->where('status', 'running');
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Accessors
    public function getFormattedFileSizeAttribute()
    {
        if (!$this->file_size) return 'Unknown';

        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getDurationAttribute()
    {
        if (!$this->started_at || !$this->completed_at) return null;

        return $this->started_at->diffForHumans($this->completed_at, true);
    }

    public function getDisplayNameAttribute()
    {
        return $this->name ?: 'Backup #' . $this->id;
    }

    // Methods
    public function exists(): bool
    {
        if (!$this->disk || !$this->path) return false;

        try {
            return Storage::disk($this->disk)->exists($this->path);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function download()
    {
        if (!$this->exists()) {
            throw new \Exception('Backup file not found');
        }

        $downloadName = $this->name ?: 'backup_' . $this->id . '.zip';
        return Storage::disk($this->disk)->download($this->path, $downloadName);
    }

    protected static function booted()
    {
        static::deleting(function ($backup) {
            // Delete physical file when model is being deleted
            if ($backup->disk && $backup->path) {
                try {
                    if (Storage::disk($backup->disk)->exists($backup->path)) {
                        Storage::disk($backup->disk)->delete($backup->path);
                    }
                } catch (\Exception $e) {
                    \Log::error('Failed to delete backup file: ' . $e->getMessage(), [
                        'backup_id' => $backup->id,
                        'path' => $backup->path,
                        'disk' => $backup->disk
                    ]);
                }
            }
        });
    }

    public function markAsStarted()
    {
        $this->update([
            'status' => 'running',
            'started_at' => now(),
        ]);
    }

    public function markAsCompleted($fileSize = null)
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'file_size' => $fileSize,
        ]);
    }

    public function markAsFailed($errorMessage)
    {
        $this->update([
            'status' => 'failed',
            'completed_at' => now(),
            'error_message' => $errorMessage,
        ]);
    }
}
