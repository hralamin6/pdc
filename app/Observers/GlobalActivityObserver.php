<?php

namespace App\Observers;

use App\Models\Activity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class GlobalActivityObserver
{
    /**
     * Models to exclude from activity logging
     */
    protected array $excludedModels = [
        Activity::class,
        // Add other models you don't want to log
    ];

    /**
     * Handle the model "created" event.
     */
    public function created(Model $model): void
    {
        if ($this->shouldLog($model)) {
            $this->logActivity($model, 'created');
        }
    }

    /**
     * Handle the model "updated" event.
     */
    public function updated(Model $model): void
    {
        if ($this->shouldLog($model) && $model->wasChanged()) {
            $this->logActivity($model, 'updated');
        }
    }

    /**
     * Handle the model "deleted" event.
     */
    public function deleted(Model $model): void
    {
        if ($this->shouldLog($model)) {
            $this->logActivity($model, 'deleted');
        }
    }

    /**
     * Determine if the model should be logged.
     */
    protected function shouldLog(Model $model): bool
    {
        // Don't log excluded models
        if (in_array(get_class($model), $this->excludedModels)) {
            return false;
        }

        // Don't log if model has LogsActivity trait (it handles itself)
        if (method_exists($model, 'logActivity')) {
            return false;
        }

        return true;
    }

    /**
     * Log the activity.
     */
    protected function logActivity(Model $model, string $event): void
    {
        $modelName = class_basename($model);
        $logName = Str::plural(Str::snake($modelName));

        $attributes = $model->getAttributes();
        $old = [];

        if ($event === 'updated') {
            $old = $model->getOriginal();
        }

        Activity::create([
            'log_name' => $logName,
            'description' => $this->getDescription($model, $event),
            'subject_type' => get_class($model),
            'subject_id' => $model->getKey(),
            'causer_type' => auth()->check() ? get_class(auth()->user()) : null,
            'causer_id' => auth()->id(),
            'event' => $event,
            'properties' => [
                'attributes' => $attributes,
                'old' => $old,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Get activity description.
     */
    protected function getDescription(Model $model, string $event): string
    {
        $modelName = class_basename($model);

        // Try to get a display name for the model
        $identifier = $this->getModelIdentifier($model);

        if ($identifier) {
            return "{$modelName} '{$identifier}' was {$event}";
        }

        return "{$modelName} was {$event}";
    }

    /**
     * Get a human-readable identifier for the model.
     */
    protected function getModelIdentifier(Model $model): ?string
    {
        // Common identifier attributes
        $identifiers = ['name', 'title', 'email', 'username', 'slug'];

        foreach ($identifiers as $identifier) {
            if (isset($model->{$identifier})) {
                return $model->{$identifier};
            }
        }

        return null;
    }
}

