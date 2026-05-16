<?php


namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectHistoryPolicy
{
    public function before(User $user): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user, Project $project): bool
    {
        // Anyone who can view the project can view its history.
        return true;
    }
}
