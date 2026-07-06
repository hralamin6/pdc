<?php

namespace App\Observers;

use App\Models\User;
use App\Services\ActivityLogger;

class UserObserver
{
    public function updated(User $user)
    {
        // Log profile updates
        if ($user->wasChanged(['name', 'email'])) {
            ActivityLogger::logProfileUpdate($user, $user->getChanges());
        }
    }
}

