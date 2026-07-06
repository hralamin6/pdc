<?php

namespace App\Traits;

use App\Models\Activity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Arr;

trait LogsActivity
{
    protected static $logAttributes = ['*'];
    protected static $logOnlyDirty = true;
    protected static $logName = 'default';

    /**
     * Boot the trait.
     */
    protected static function bootLogsActivity()
    {
        static::created(function (Model $model) {
            $model->logActivity('created');
        });

        static::updated(function (Model $model) {
            if (static::$logOnlyDirty && !$model->isDirty()) {
                return;
            }
            $model->logActivity('updated');
        });

        static::deleted(function (Model $model) {
            $model->logActivity('deleted');
        });
    }

    /**
     * Get all activities for this model.
     */
    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject')->orderBy('created_at', 'desc');
    }

    /**
     * Log an activity.
     */
    public function logActivity(string $event, array $properties = []): Activity
    {
        $logName = property_exists($this, 'activityLogName')
            ? $this->activityLogName
            : static::$logName;

        $attributes = $this->getActivityAttributes();

        $old = [];
        if ($event === 'updated') {
            $old = $this->getOriginalActivityAttributes();
        }

        return Activity::create([
            'log_name' => $logName,
            'description' => $this->getActivityDescription($event),
            'subject_type' => get_class($this),
            'subject_id' => $this->getKey(),
            'causer_type' => auth()->check() ? get_class(auth()->user()) : null,
            'causer_id' => auth()->id(),
            'event' => $event,
            'properties' => array_merge([
                'attributes' => $attributes,
                'old' => $old,
            ], $properties),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Get the description for the activity.
     */
    protected function getActivityDescription(string $event): string
    {
        $modelName = class_basename($this);
        return "{$modelName} {$event}";
    }

    /**
     * Get attributes to log.
     */
    protected function getActivityAttributes(): array
    {
        $logAttributes = property_exists($this, 'activityLogAttributes')
            ? $this->activityLogAttributes
            : static::$logAttributes;

        if (in_array('*', $logAttributes)) {
            return $this->attributesToArray();
        }

        return Arr::only($this->attributesToArray(), $logAttributes);
    }

    /**
     * Get original attributes to log.
     */
    protected function getOriginalActivityAttributes(): array
    {
        $logAttributes = property_exists($this, 'activityLogAttributes')
            ? $this->activityLogAttributes
            : static::$logAttributes;

        if (in_array('*', $logAttributes)) {
            return $this->getOriginal();
        }

        return Arr::only($this->getOriginal(), $logAttributes);
    }
}

