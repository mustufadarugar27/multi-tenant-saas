<?php


namespace App\Policies;

use App\Models\AuditLog;
use App\Models\User;

class AuditLogPolicy
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
        // Only company admins and above can view the audit trail.
        return $user->canManageTenant();
    }

    public function view(User $user, AuditLog $log): bool
    {
        return $user->canManageTenant();
    }
}
