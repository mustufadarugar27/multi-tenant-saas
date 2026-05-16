<?php


namespace App\Policies;

use App\Models\ActivityLog;
use App\Models\User;

class ActivityLogPolicy
{
    public function before(User $user): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->canManageTenant();
    }

    public function view(User $user, ActivityLog $log): bool
    {
        // Users can always view their own activity
        if ($user->id === $log->user_id) {
            return true;
        }

        return $user->canManageTenant();
    }
}
