<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FundTransfer extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'fee' => 'decimal:2',
            'transfer_date' => 'date',
        ];
    }

    public function fromAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'from_account_id');
    }

    public function toAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'to_account_id');
    }

    public function transferredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'transferred_by');
    }

    public function feeExpense(): BelongsTo
    {
        return $this->belongsTo(Expense::class, 'fee_expense_id');
    }

    /**
     * Amount actually received in the destination account.
     */
    public function getNetReceivedAttribute(): float
    {
        return (float) ($this->amount - $this->fee);
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'completed' => 'badge-success',
            'pending' => 'badge-warning',
            'failed' => 'badge-error',
            default => 'badge-ghost',
        };
    }
}
