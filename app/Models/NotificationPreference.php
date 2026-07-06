<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category',
        'push_enabled',
        'email_enabled',
        'database_enabled',
        'settings',
    ];

    protected $casts = [
        'push_enabled' => 'boolean',
        'email_enabled' => 'boolean',
        'database_enabled' => 'boolean',
        'settings' => 'array',
    ];

    /**
     * Get the user that owns the preference.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if push notifications are enabled for this category
     */
    public function isPushEnabled(): bool
    {
        return $this->push_enabled;
    }

    /**
     * Check if email notifications are enabled for this category
     */
    public function isEmailEnabled(): bool
    {
        return $this->email_enabled;
    }

    /**
     * Check if database notifications are enabled for this category
     */
    public function isDatabaseEnabled(): bool
    {
        return $this->database_enabled;
    }
}

