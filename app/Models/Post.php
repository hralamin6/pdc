<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Post extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\PostFactory> */
    use HasFactory;

    use InteractsWithMedia;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'is_featured',
        'published_at',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
            'views_count' => 'integer',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($post): void {
            if (empty($post->slug)) {
                $post->slug = Str::slug($post->title);
            }
            if (empty($post->excerpt) && ! empty($post->content)) {
                $post->excerpt = Str::limit(strip_tags($post->content), 500);
            }
        });

        static::updating(function ($post): void {
            if ($post->isDirty('title') && empty($post->slug)) {
                $post->slug = Str::slug($post->title);
            }
        });
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured_image')
            ->singleFile()
            ->registerMediaConversions(function (?Media $media = null): void {
                $this->addMediaConversion('thumb')
                    ->width(400)
                    ->height(300)
                    ->sharpen(10)
                    ->nonQueued();

                $this->addMediaConversion('medium')
                    ->width(800)
                    ->height(600)
                    ->sharpen(10)
                    ->nonQueued();
            });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeDraft($query)
    {
        return $query->whereNull('published_at');
    }

    public function scopeScheduled($query)
    {
        return $query->whereNotNull('published_at')
            ->where('published_at', '>', now());
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function getStatusAttribute(): string
    {
        if ($this->published_at === null) {
            return 'draft';
        }

        if ($this->published_at->isFuture()) {
            return 'scheduled';
        }

        return 'published';
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'published' => __('Published'),
            'draft' => __('Draft'),
            'scheduled' => __('Scheduled'),
            default => __('Unknown'),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'published' => 'success',
            'draft' => 'warning',
            'scheduled' => 'info',
            default => 'neutral',
        };
    }

    public function getFeaturedImageUrlAttribute(): ?string
    {
        $media = $this->getFirstMedia('featured_image');

        if ($media && file_exists($media->getPath('medium'))) {
            return $media->getUrl('medium');
        }

        if ($media && file_exists($media->getPath())) {
            return $media->getUrl();
        }

        return null;
    }

    public function getFeaturedImageThumbUrlAttribute(): ?string
    {
        $media = $this->getFirstMedia('featured_image');

        if ($media && file_exists($media->getPath('thumb'))) {
            return $media->getUrl('thumb');
        }

        return null;
    }

    public function incrementViews(): void
    {
        $this->increment('views_count');
    }
}
