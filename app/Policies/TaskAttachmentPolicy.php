<?php


namespace App\Policies;

use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\User;

class TaskAttachmentPolicy
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
        return true; // any tenant member can list attachments on a task they can view
    }

    public function upload(User $user, Task $task): bool
    {
        return true; // any tenant member can upload
    }

    public function download(User $user, TaskAttachment $attachment): bool
    {
        if (! $attachment->isSafe()) {
            return false; // never allow download of unscanned/infected files
        }

        return true;
    }

    public function delete(User $user, TaskAttachment $attachment): bool
    {
        // Uploader or admin can delete
        return $attachment->user_id === $user->id
            || $user->isCompanyAdmin();
    }
}
