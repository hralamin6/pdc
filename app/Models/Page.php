<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Page extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'featured_image',
        'status',
        'published_at',
        'order',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured_image')->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(200)
            ->quality(75)
            ->nonQueued();
    }

    //    public function registerMediaCollections(): void
    //    {
    //        $this->addMediaCollection('featured_image')->singleFile()->registerMediaConversions(function (?Media $media = null) {
    //            $this->addMediaConversion('thumb')->quality('10')->nonQueued();
    //        });
    //    }

    public function getImageUrlAttribute()
    {
        $media = $this->getFirstMedia('featured_image');
        if ($media) {
            $path = $media->getPath('thumb');
            if (file_exists($path)) {
                return $media->getUrl('thumb');
            }
        }

        return 'https://placehold.co/400';
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($page) {
            if (empty($page->slug)) {
                $page->slug = Str::slug($page->title);
            }
        });

        static::updating(function ($page) {
            if ($page->isDirty('title') && empty($page->slug)) {
                $page->slug = Str::slug($page->title);
            }
        });
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function isPublished(): bool
    {
        return $this->status === 'published' &&
               ($this->published_at === null || $this->published_at <= now());
    }

    public function getRouteKeyName()
    {
        return 'id';
    }
}
