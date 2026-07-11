<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class HalaqahSeries extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * Get the mentor that created the series.
     */
    public function mentor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }

    /**
     * Get the halaqahs for the series.
     */
    public function halaqahs(): HasMany
    {
        return $this->hasMany(Halaqah::class, 'series_id');
    }

    /**
     * Get the quizzes for this series (multiple allowed).
     */
    public function quizzes(): MorphMany
    {
        return $this->morphMany(Quiz::class, 'quizzable');
    }
}
