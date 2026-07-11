<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookCopy extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function libraryHub(): BelongsTo
    {
        return $this->belongsTo(LibraryHub::class);
    }

    public function borrowRequests(): HasMany
    {
        return $this->hasMany(BorrowRequest::class);
    }
}
