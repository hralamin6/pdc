<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HalaqahAttendance extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'attended' => 'boolean',
            'preparation_completed' => 'boolean',
            'rating' => 'integer',
            'checked_in_at' => 'datetime',
        ];
    }

    /**
     * Get the halaqah that owns the attendance.
     */
    public function halaqah(): BelongsTo
    {
        return $this->belongsTo(Halaqah::class, 'halaqah_id');
    }

    /**
     * Get the user that owns the attendance.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
