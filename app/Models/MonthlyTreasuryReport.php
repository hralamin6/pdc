<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonthlyTreasuryReport extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'total_income' => 'decimal:2',
            'total_expense' => 'decimal:2',
            'total_transfer_fees' => 'decimal:2',
            'opening_balance' => 'decimal:2',
            'closing_balance' => 'decimal:2',
            'published_at' => 'datetime',
        ];
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function getIsPublishedAttribute(): bool
    {
        return $this->published_at !== null;
    }

    public function getMonthNameAttribute(): string
    {
        return \Carbon\Carbon::create($this->year, $this->month)->format('F Y');
    }

    public function getSurplusAttribute(): float
    {
        return (float) ($this->total_income - $this->total_expense - $this->total_transfer_fees);
    }

    public function getSurplusColorAttribute(): string
    {
        return $this->surplus >= 0 ? 'text-emerald-600' : 'text-rose-600';
    }

    /**
     * Auto-calculate from actual data for the given month.
     */
    public static function calculate(int $year, int $month): array
    {
        $start = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $income = Donation::where('status', 'confirmed')
            ->whereBetween('donated_at', [$start, $end])
            ->sum('amount');

        $expense = Expense::where('status', 'confirmed')
            ->whereBetween('expense_date', [$start, $end])
            ->sum('amount');

        $fees = FundTransfer::where('status', 'completed')
            ->whereBetween('transfer_date', [$start, $end])
            ->sum('fee');

        return [
            'total_income' => (float) $income,
            'total_expense' => (float) $expense,
            'total_transfer_fees' => (float) $fees,
        ];
    }
}
