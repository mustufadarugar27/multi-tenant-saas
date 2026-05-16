<?php


namespace App\Repositories;

use App\Models\Task;
use App\Models\TaskComment;
use App\Repositories\Contracts\CommentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CommentRepository implements CommentRepositoryInterface
{
    public function paginateForTask(Task $task, int $perPage): LengthAwarePaginator
    {
        return $task->comments()
            ->whereNull('parent_id')
            ->with(['author:id,name', 'replies.author:id,name'])
            ->paginate($perPage);
    }

    public function findForTask(Task $task, string $commentId): TaskComment
    {
        return $task->comments()->findOrFail($commentId);
    }

    public function create(array $data): TaskComment
    {
        return TaskComment::create($data);
    }

    public function update(TaskComment $comment, array $data): TaskComment
    {
        $comment->update($data);

        return $comment->refresh();
    }

    public function delete(TaskComment $comment): void
    {
        $comment->delete();
    }
}
