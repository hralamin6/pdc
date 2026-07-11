<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Publication extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $guarded = [];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (self $model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }
        });
    }

    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')->singleFile();
    }

    public function getLogoUrlAttribute(): ?string
    {
        $media = $this->getFirstMedia('logo');

        return $media?->getUrl();
    }
}
