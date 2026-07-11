<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class GalleryAlbum extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\GalleryAlbumFactory> */
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'category',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('gallery_images')
            ->registerMediaConversions(function (?Media $media = null) {
                $this->addMediaConversion('thumb')
                    ->width(400)
                    ->height(400)
                    ->nonQueued();
                    
                $this->addMediaConversion('large')
                    ->width(1200)
                    ->nonQueued();
            });
    }

    public function getCoverUrlAttribute()
    {
        $media = $this->getFirstMedia('gallery_images');
        
        if ($media && file_exists($media->getPath('thumb'))) {
            return $media->getUrl('thumb');
        }

        return null;
    }
}
