<?php

namespace App\Services;

use App\Models\Activity;
use Illuminate\Database\Eloquent\Model;

class ActivityLogger
{
    /**
     * Log a custom activity.
     */
    public static function log(string $description, ?Model $subject = null, array $properties = [], string $logName = 'custom', ?string $event = null): Activity
    {
        return Activity::create([
            'log_name' => $logName,
            'description' => $description,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->getKey(),
            'causer_type' => auth()->check() ? get_class(auth()->user()) : null,
            'causer_id' => auth()->id(),
            'event' => $event,
            'properties' => $properties,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Log user login.
     */
    public static function logLogin(?Model $user = null): Activity
    {
        $user = $user ?? auth()->user();

        return self::log(
            'User logged in',
            $user,
            [
                'user_name' => $user->name,
                'user_email' => $user->email,
            ],
            'authentication',
            'login'
        );
    }

    /**
     * Log user logout.
     */
    public static function logLogout(?Model $user = null): Activity
    {
        $user = $user ?? auth()->user();

        return self::log(
            'User logged out',
            $user,
            [
                'user_name' => $user->name,
                'user_email' => $user->email,
            ],
            'authentication',
            'logout'
        );
    }

    /**
     * Log failed login attempt.
     */
    public static function logFailedLogin(string $email): Activity
    {
        return self::log(
            'Failed login attempt',
            null,
            ['email' => $email],
            'authentication',
            'failed_login'
        );
    }

    /**
     * Log password change.
     */
    public static function logPasswordChange(?Model $user = null): Activity
    {
        $user = $user ?? auth()->user();

        return self::log(
            'Password changed',
            $user,
            ['user_name' => $user->name],
            'security',
            'password_changed'
        );
    }

    /**
     * Log profile update.
     */
    public static function logProfileUpdate(?Model $user = null, array $changes = []): Activity
    {
        $user = $user ?? auth()->user();

        return self::log(
            'Profile updated',
            $user,
            ['changes' => $changes],
            'profile',
            'profile_updated'
        );
    }

    /**
     * Log email verification.
     */
    public static function logEmailVerified(?Model $user = null): Activity
    {
        $user = $user ?? auth()->user();

        return self::log(
            'Email verified',
            $user,
            ['email' => $user->email],
            'authentication',
            'email_verified'
        );
    }

    /**
     * Log system event.
     */
    public static function logSystem(string $description, array $properties = []): Activity
    {
        return self::log($description, null, $properties, 'system');
    }
}

