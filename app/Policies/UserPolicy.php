<?php


namespace App\Policies;

use App\Models\User;
use App\Support\EnumConfig;

class UserPolicy
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
        return $user->canManageUsers();
    }

    public function view(User $user, User $target): bool
    {
        // Users can always view themselves
        if ($user->id === $target->id) {
            return true;
        }

        return $user->canManageUsers();
    }

    public function create(User $user): bool
    {
        return EnumConfig::userRoleCanCreateUsers($user->role);
    }

    public function updateOwnProfile(User $user, User $target): bool
    {
        return $user->id === $target->id;
    }

    public function update(User $user, User $target): bool
    {
        if ($user->id === $target->id) {
            return true;
        }

        // Cannot update someone of equal or higher rank
        if (EnumConfig::userRolePrivilege($target->role) >= EnumConfig::userRolePrivilege($user->role)) {
            return false;
        }

        return $user->canManageUsers();
    }

    public function delete(User $user, User $target): bool
    {
        // Cannot delete yourself
        if ($user->id === $target->id) {
            return false;
        }

        // Cannot delete someone of equal or higher rank
        if (EnumConfig::userRolePrivilege($target->role) >= EnumConfig::userRolePrivilege($user->role)) {
            return false;
        }

        return $user->isCompanyAdmin() || $user->isSuperAdmin();
    }

    public function restore(User $user, User $target): bool
    {
        return $this->delete($user, $target);
    }

    public function assignRole(User $user, User $target): bool
    {
        // Cannot modify own role
        if ($user->id === $target->id) {
            return false;
        }

        // Cannot assign roles to users of equal or higher rank
        if (EnumConfig::userRolePrivilege($target->role) >= EnumConfig::userRolePrivilege($user->role)) {
            return false;
        }

        return $user->canManageUsers();
    }
}
