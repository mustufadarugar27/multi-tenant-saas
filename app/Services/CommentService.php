<?php


namespace App\Services;

use App\DataTransferObjects\Task\CreateCommentDTO;
use App\DataTransferObjects\Task\UpdateCommentDTO;
use App\Events\CommentDeleted;
use App\Events\CommentUpdated;
use App\Events\TaskCommentAdded;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\TaskHistory;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CommentService
{
    private const MAX_REPLY_DEPTH = 3;

    public function __construct(
        private readonly ActivityLogService $activityLog,
    ) {}

    public function paginate(Task $task, int $perPage): LengthAwarePaginator
    {
        return $task->comments()
            ->whereNull('parent_id')
            ->with(['author:id,name', 'replies.author:id,name'])
            ->paginate($perPage);
    }

    public function add(Task $task, CreateCommentDTO $dto): TaskComment
    {
        $depth = 0;

        if ($dto->parentId !== null) {
            $parent = TaskComment::where('task_id', $task->id)
                ->findOrFail($dto->parentId);
            $depth = min($parent->depth + 1, self::MAX_REPLY_DEPTH);
        }

        $comment = DB::transaction(function () use ($task, $dto, $depth): TaskComment {
            $comment = TaskComment::create([
                'task_id' => $dto->taskId,
                'user_id' => $dto->userId,
                'parent_id' => $dto->parentId,
                'depth' => $depth,
                'content' => $dto->content,
            ]);

            TaskHistory::create([
                'task_id' => $task->id,
                'user_id' => $dto->userId,
                'event' => 'comment_added',
                'context' => ['comment_id' => $comment->id],
            ]);

            return $comment;
        });

        $comment->load('author:id,name');
        $actor = User::findOrFail($dto->userId);

        $this->activityLog->log(
            $actor,
            'task.comment_added',
            "Added comment on task \"{$task->title}\"",
            $task,
        );

        TaskCommentAdded::dispatch($task, $comment, $actor);

        return $comment;
    }

    public function update(TaskComment $comment, UpdateCommentDTO $dto, User $actor): TaskComment
    {
        $comment->update([
            'content' => $dto->content,
            'edited_at' => now(),
        ]);

        $updated = $comment->refresh();

        $this->activityLog->log(
            $actor,
            'task.comment_updated',
            "Edited comment on task",
            $comment->task,
        );

        CommentUpdated::dispatch($updated, $actor);

        return $updated;
    }

    public function delete(TaskComment $comment, User $actor): void
    {
        $task = $comment->task;

        $comment->delete();

        $this->activityLog->log(
            $actor,
            'task.comment_deleted',
            "Deleted comment on task \"{$task->title}\"",
            $task,
        );

        CommentDeleted::dispatch($comment, $actor);
    }
}
