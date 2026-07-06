<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyReportEntry extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'boolean_value' => 'boolean',
            'numeric_value' => 'integer',
        ];
    }

    public function dailyReport(): BelongsTo
    {
        return $this->belongsTo(DailyReport::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(UserReportItem::class, 'user_report_item_id');
    }
}
