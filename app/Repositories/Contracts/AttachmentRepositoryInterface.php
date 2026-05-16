<?php


namespace App\Repositories\Contracts;

use App\Models\Task;
use App\Models\TaskAttachment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AttachmentRepositoryInterface
{
    public function paginateForTask(Task $task, int $perPage): LengthAwarePaginator;

    public function findForTask(Task $task, string $attachmentId): TaskAttachment;

    public function create(array $data): TaskAttachment;

    public function delete(TaskAttachment $attachment): void;
}
