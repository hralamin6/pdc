<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DonationCampaign extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'goal_amount' => 'decimal:2',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class, 'campaign_id');
    }

    public function updates(): HasMany
    {
        return $this->hasMany(DonationCampaignUpdate::class, 'campaign_id');
    }

    public function faqs(): HasMany
    {
        return $this->hasMany(DonationCampaignFaq::class, 'campaign_id');
    }

    public function getCollectedAmountAttribute(): float
    {
        return (float) $this->donations()->where('status', 'confirmed')->sum('amount');
    }

    public function getProgressPercentageAttribute(): int
    {
        if (! $this->goal_amount || $this->goal_amount <= 0) {
            return 0;
        }

        $percentage = ($this->collected_amount / $this->goal_amount) * 100;

        return min(100, (int) round($percentage));
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile()->registerMediaConversions(function (?Media $media = null) {
            $this->addMediaConversion('thumb')->width(400)->quality(80)->nonQueued();
            $this->addMediaConversion('banner')->width(1200)->quality(80)->nonQueued();
        });
    }

    public function getCoverUrlAttribute(): ?string
    {
        $media = $this->getFirstMedia('cover');
        if ($media && file_exists($media->getPath('banner'))) {
            return $media->getUrl('banner');
        }
        if ($media && file_exists($media->getPath())) {
            return $media->getUrl();
        }

        return null; // Handle fallback in the UI
    }
}
