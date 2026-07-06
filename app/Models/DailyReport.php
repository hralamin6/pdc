<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyReport extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(DailyReportEntry::class);
    }

    public function getCompletedCountAttribute(): int
    {
        return $this->entries->filter(function ($entry) {
            return $entry->boolean_value
                || ($entry->numeric_value !== null && $entry->numeric_value > 0)
                || ! empty(trim($entry->text_value ?? ''));
        })->count();
    }

    public function getTotalItemsCountAttribute(): int
    {
        return $this->entries->count();
    }

    public function getCompletionPercentageAttribute(): int
    {
        $total = $this->total_items_count;
        if ($total === 0) {
            return 0;
        }

        return (int) round(($this->completed_count / $total) * 100);
    }
}
