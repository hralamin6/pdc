<?php

namespace App\Listeners;

use App\Services\ActivityLogger;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Verified;

class AuthActivityListener
{
    public function handleLogin(Login $event)
    {
        ActivityLogger::logLogin($event->user);
    }

    public function handleLogout(Logout $event)
    {
        ActivityLogger::logLogout($event->user);
    }

    public function handleFailed(Failed $event)
    {
        ActivityLogger::logFailedLogin($event->credentials['email'] ?? 'unknown');
    }

    public function handleVerified(Verified $event)
    {
        ActivityLogger::logEmailVerified($event->user);
    }

    public function subscribe($events)
    {
        return [
            Login::class => 'handleLogin',
            Logout::class => 'handleLogout',
            Failed::class => 'handleFailed',
            Verified::class => 'handleVerified',
        ];
    }
}

