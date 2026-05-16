<?php


namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
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
        return true;
    }

    public function view(User $user, Project $project): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->canManageProjects();
    }

    public function update(User $user, Project $project): bool
    {
        return $user->canManageProjects();
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->isCompanyAdmin() || $user->isSuperAdmin();
    }

    public function restore(User $user, Project $project): bool
    {
        return $user->isCompanyAdmin() || $user->isSuperAdmin();
    }

    public function forceDelete(User $user, Project $project): bool
    {
        return $user->isSuperAdmin();
    }
}
