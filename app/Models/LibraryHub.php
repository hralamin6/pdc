<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LibraryHub extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function bookCopies(): HasMany
    {
        return $this->hasMany(BookCopy::class);
    }
}
