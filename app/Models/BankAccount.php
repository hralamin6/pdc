<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class BankAccount extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function transfersOut(): HasMany
    {
        return $this->hasMany(FundTransfer::class, 'from_account_id');
    }

    public function transfersIn(): HasMany
    {
        return $this->hasMany(FundTransfer::class, 'to_account_id');
    }

    /**
     * Real-time balance computed from all financial activity.
     */
    public function getBalanceAttribute(): float
    {
        $income = Donation::where('bank_account_id', $this->id)
            ->where('status', 'confirmed')
            ->sum('amount');

        $expenses = Expense::where('bank_account_id', $this->id)
            ->where('status', 'confirmed')
            ->sum('amount');

        $transfersOut = FundTransfer::where('from_account_id', $this->id)
            ->where('status', 'completed')
            ->sum('amount');

        $transfersIn = FundTransfer::where('to_account_id', $this->id)
            ->where('status', 'completed')
            ->sum(DB::raw('amount - fee'));

        return (float) ($income - $expenses - $transfersOut + $transfersIn);
    }

    public function getTypeColorAttribute(): string
    {
        return match ($this->type) {
            'cash' => 'text-emerald-600 bg-emerald-50 dark:bg-emerald-900/20',
            'bkash' => 'text-pink-600 bg-pink-50 dark:bg-pink-900/20',
            'nagad' => 'text-orange-600 bg-orange-50 dark:bg-orange-900/20',
            'bank' => 'text-blue-600 bg-blue-50 dark:bg-blue-900/20',
            default => 'text-slate-600 bg-slate-50 dark:bg-slate-900/20',
        };
    }

    public function getTypeIconAttribute(): string
    {
        return match ($this->type) {
            'cash' => 'o-banknotes',
            'bkash' => 'o-device-phone-mobile',
            'nagad' => 'o-device-phone-mobile',
            'bank' => 'o-building-library',
            default => 'o-credit-card',
        };
    }
}
