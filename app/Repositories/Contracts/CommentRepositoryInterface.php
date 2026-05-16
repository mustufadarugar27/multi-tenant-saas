<?php


namespace App\Repositories\Contracts;

use App\Models\Task;
use App\Models\TaskComment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CommentRepositoryInterface
{
    public function paginateForTask(Task $task, int $perPage): LengthAwarePaginator;

    public function findForTask(Task $task, string $commentId): TaskComment;

    public function create(array $data): TaskComment;

    public function update(TaskComment $comment, array $data): TaskComment;

    public function delete(TaskComment $comment): void;
}
