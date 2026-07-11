<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class Halaqah extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'is_registration_open' => 'boolean',
            'resources' => 'array',
        ];
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->qr_token)) {
                $model->qr_token = Str::random(12);
            }
        });
    }

    /**
     * Get the series that owns the halaqah.
     */
    public function series(): BelongsTo
    {
        return $this->belongsTo(HalaqahSeries::class, 'series_id');
    }

    /**
     * Get the speaker for the halaqah.
     */
    public function speaker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'speaker_id');
    }

    /**
     * Get the attendances for the halaqah.
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(HalaqahAttendance::class, 'halaqah_id');
    }

    public function getAvailableSeatsAttribute()
    {
        if (! $this->max_capacity) {
            return null;
        }

        $rsvps = $this->attendances()->where('status_new', 'rsvp')->count();

        return max(0, $this->max_capacity - $rsvps);
    }

    /**
     * Get the donations collected during this halaqah.
     */
    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class, 'halaqah_id');
    }

    /**
     * Get the quizzes associated with this halaqah session.
     */
    public function quizzes(): MorphMany
    {
        return $this->morphMany(Quiz::class, 'quizzable');
    }
}
