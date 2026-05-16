<?php


namespace App\Policies;

use App\Models\TaskComment;
use App\Models\User;

class TaskCommentPolicy
{
    public function before(User $user): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    public function create(User $user): bool
    {
        return true; // any authenticated tenant user can comment
    }

    public function update(User $user, TaskComment $comment): bool
    {
        return $comment->user_id === $user->id;
    }

    public function delete(User $user, TaskComment $comment): bool
    {
        return $comment->user_id === $user->id
            || $user->isCompanyAdmin();
    }
}
