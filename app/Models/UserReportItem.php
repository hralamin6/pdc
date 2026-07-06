<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserReportItem extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(DailyReportTemplate::class, 'daily_report_template_id');
    }

    public static function ensureDefaultsForUser(int $userId): void
    {
        $count = static::where('user_id', $userId)->count();
        if ($count > 0) {
            return;
        }

        $templates = DailyReportTemplate::orderBy('sort_order')->get();
        foreach ($templates as $template) {
            static::create([
                'user_id' => $userId,
                'daily_report_template_id' => $template->id,
                'type' => $template->type,
                'is_active' => true,
                'sort_order' => $template->sort_order ?? 0,
            ]);
        }
    }
}
