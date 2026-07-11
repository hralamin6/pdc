<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Book extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, LogsActivity;

    protected $guarded = [];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (self $model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->title);
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(BookCategory::class, 'book_category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    public function publication(): BelongsTo
    {
        return $this->belongsTo(Publication::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function interactions(): HasMany
    {
        return $this->hasMany(BookUserInteraction::class);
    }

    public function copies(): HasMany
    {
        return $this->hasMany(BookCopy::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile();
        $this->addMediaCollection('pdf_file')->singleFile();
    }

    public function getCoverUrlAttribute(): ?string
    {
        $media = $this->getFirstMedia('cover');

        return $media?->getUrl();
    }

    public function getPdfUrlAttribute(): ?string
    {
        $media = $this->getFirstMedia('pdf_file');

        return $media?->getUrl();
    }
}
