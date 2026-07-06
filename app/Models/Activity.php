<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'log_name',
        'description',
        'subject_type',
        'subject_id',
        'causer_type',
        'causer_id',
        'properties',
        'event',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the subject of the activity.
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who caused the activity.
     */
    public function causer(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get changes from properties.
     */
    public function getChangesAttribute()
    {
        return $this->properties['attributes'] ?? [];
    }

    /**
     * Get old values from properties.
     */
    public function getOldAttribute()
    {
        return $this->properties['old'] ?? [];
    }

    /**
     * Scope to filter by log name.
     */
    public function scopeInLog($query, string $logName)
    {
        return $query->where('log_name', $logName);
    }

    /**
     * Scope to filter by causer.
     */
    public function scopeCausedBy($query, Model $causer)
    {
        return $query->where('causer_type', get_class($causer))
                     ->where('causer_id', $causer->getKey());
    }

    /**
     * Scope to filter by subject.
     */
    public function scopeForSubject($query, Model $subject)
    {
        return $query->where('subject_type', get_class($subject))
                     ->where('subject_id', $subject->getKey());
    }

    /**
     * Scope to filter by event.
     */
    public function scopeForEvent($query, string $event)
    {
        return $query->where('event', $event);
    }

    /**
     * Scope to get recent activities.
     */
    public function scopeRecent($query, int $limit = 10)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }
}

