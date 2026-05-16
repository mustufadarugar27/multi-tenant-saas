<?php


namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskHistoryPolicy
{
    public function before(User $user): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user, Task $task): bool
    {
        // Anyone who can view the task can view its history.
        return true;
    }
}
