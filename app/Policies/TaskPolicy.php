<?php


namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
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

    public function view(User $user, Task $task): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->role->canManageProjects();
    }

    public function update(User $user, Task $task): bool
    {
        // Assignee can update their own task; managers+ can update any.
        return $user->role->canManageProjects()
            || $task->assigned_to === $user->id;
    }

    public function delete(User $user, Task $task): bool
    {
        return $user->isCompanyAdmin() || $user->isSuperAdmin();
    }

    public function restore(User $user, Task $task): bool
    {
        return $user->isCompanyAdmin() || $user->isSuperAdmin();
    }

    public function forceDelete(User $user, Task $task): bool
    {
        return $user->isSuperAdmin();
    }

    public function uploadAttachment(User $user, Task $task): bool
    {
        return $user->role->canManageProjects()
            || $task->assigned_to === $user->id;
    }

    public function deleteAttachment(User $user, Task $task): bool
    {
        return $user->isCompanyAdmin() || $user->isSuperAdmin();
    }
}
