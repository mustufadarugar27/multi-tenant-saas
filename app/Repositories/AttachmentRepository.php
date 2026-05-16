<?php


namespace App\Repositories;

use App\Models\Task;
use App\Models\TaskAttachment;
use App\Repositories\Contracts\AttachmentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AttachmentRepository implements AttachmentRepositoryInterface
{
    public function paginateForTask(Task $task, int $perPage): LengthAwarePaginator
    {
        return $task->attachments()
            ->with('uploader:id,name')
            ->paginate($perPage);
    }

    public function findForTask(Task $task, string $attachmentId): TaskAttachment
    {
        return $task->attachments()->findOrFail($attachmentId);
    }

    public function create(array $data): TaskAttachment
    {
        return TaskAttachment::create($data);
    }

    public function delete(TaskAttachment $attachment): void
    {
        $attachment->delete();
    }
}
