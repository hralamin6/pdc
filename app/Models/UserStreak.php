<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserStreak extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'last_report_date' => 'date',
            'badges' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getMilestoneBadges(): array
    {
        $longest = $this->longest_streak ?? 0;
        $current = $this->current_streak ?? 0;

        return [
            [
                'id' => 'spark_3',
                'name' => '3-Day Spark',
                'description' => 'Logged reports for 3 consecutive days',
                'icon' => 'o-bolt',
                'color' => 'text-amber-500 bg-amber-500/10 border-amber-500/20',
                'target' => 3,
                'unlocked' => $longest >= 3,
            ],
            [
                'id' => 'warrior_7',
                'name' => '7-Day Warrior',
                'description' => 'Maintained a full week of consistency',
                'icon' => 'o-fire',
                'color' => 'text-orange-500 bg-orange-500/10 border-orange-500/20',
                'target' => 7,
                'unlocked' => $longest >= 7,
            ],
            [
                'id' => 'consistent_14',
                'name' => '14-Day Devotee',
                'description' => 'Two solid weeks of spiritual tracking',
                'icon' => 'o-sparkles',
                'color' => 'text-indigo-500 bg-indigo-500/10 border-indigo-500/20',
                'target' => 14,
                'unlocked' => $longest >= 14,
            ],
            [
                'id' => 'champion_30',
                'name' => '30-Day Champion',
                'description' => 'A full month of unbroken dedication',
                'icon' => 'o-trophy',
                'color' => 'text-emerald-500 bg-emerald-500/10 border-emerald-500/20',
                'target' => 30,
                'unlocked' => $longest >= 30,
            ],
            [
                'id' => 'master_100',
                'name' => '100-Day Legend',
                'description' => 'Centurion of daily self-accountability',
                'icon' => 'o-academic-cap',
                'color' => 'text-purple-500 bg-purple-500/10 border-purple-500/20',
                'target' => 100,
                'unlocked' => $longest >= 100,
            ],
        ];
    }
}
