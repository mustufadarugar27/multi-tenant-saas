<?php


namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Permission;

class PermissionPolicy
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

    public function manage(User $user): bool
    {
        // Only super admins can modify role-permission mappings (handled by before())
        return false;
    }
}
