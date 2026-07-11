<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BorrowRequest extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'due_date' => 'date',
        'returned_at' => 'datetime',
    ];

    public function borrower(): BelongsTo
    {
        return $this->belongsTo(User::class, 'borrower_id');
    }

    public function bookCopy(): BelongsTo
    {
        return $this->belongsTo(BookCopy::class);
    }
}
