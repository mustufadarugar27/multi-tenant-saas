<?php


namespace App\Services;

use App\DataTransferObjects\Task\CreateCommentDTO;
use App\DataTransferObjects\Task\CreateTaskDTO;
use App\DataTransferObjects\Task\UpdateCommentDTO;
use App\DataTransferObjects\Task\UpdateTaskDTO;
use App\Events\TaskAssigned;
use App\Events\TaskCreated;
use App\Events\TaskDeleted;
use App\Events\TaskUpdated;
use App\Infrastructure\Cache\CacheKeys;
use App\Infrastructure\Cache\Contracts\CacheManagerInterface;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\TaskHistory;
use App\Models\User;
use App\Repositories\Contracts\TaskRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;

class TaskService
{
    public function __construct(
        private readonly TaskRepositoryInterface $repository,
        private readonly CacheManagerInterface $cache,
        private readonly ActivityLogService $activityLog,
        private readonly CommentService $commentService,
        private readonly AttachmentService $attachmentService,
    ) {}


    public function paginate(array $filters, int $perPage): LengthAwarePaginator
    {
        $projectId = $filters['project_id'] ?? null;

        if ($projectId && $this->isCacheable($filters)) {
            $page = request()->integer('page', 1);
            $cacheKey = CacheKeys::taskList($this->tenantId(), $projectId) . ":page:{$page}";

            return $this->cache->remember($cacheKey, fn () => $this->repository->paginate($filters, $perPage));
        }

        return $this->repository->paginate($filters, $perPage);
    }

    public function findById(string $id): Task
    {
        $cacheKey = CacheKeys::task($this->tenantId(), $id);

        return $this->cache->remember($cacheKey, fn () => $this->repository->findById($id));
    }

    public function getHistory(Task $task, int $perPage): LengthAwarePaginator
    {
        return $this->repository->getHistory($task, $perPage);
    }


    public function create(CreateTaskDTO $dto, User $actor): Task
    {
        $task = $this->repository->create([
            'project_id' => $dto->projectId,
            'title' => $dto->title,
            'description' => $dto->description,
            'assigned_to' => $dto->assignedTo,
            'created_by' => $actor->id,
            'priority' => $dto->priority->value,
            'status' => $dto->status->value,
            'due_date' => $dto->dueDate,
            'estimated_hours' => $dto->estimatedHours,
        ]);

        $this->recordHistory($task, $actor->id, 'task_created', context: [
            'title' => $task->title,
            'project_id' => $task->project_id,
        ]);

        $this->activityLog->log($actor, 'task.created', "Created task \"{$task->title}\"", $task);
        $this->bustTaskListCache($task->project_id);

        TaskCreated::dispatch($task, $actor);

        if ($dto->assignedTo && $dto->assignedTo !== $actor->id) {
            $assignee = User::find($dto->assignedTo);
            if ($assignee) {
                TaskAssigned::dispatch($task, $actor, $assignee);
            }
        }

        return $task;
    }

    public function update(Task $task, UpdateTaskDTO $dto, User $actor): Task
    {
        $previousAssignee = $task->assigned_to;

        $data = [];

        if ($dto->has('title')) { $data['title'] = $dto->title; }
        if ($dto->has('description')) { $data['description'] = $dto->description; }
        if ($dto->has('assigned_to')) { $data['assigned_to'] = $dto->assignedTo; }
        if ($dto->has('priority')) { $data['priority'] = $dto->priority->value; }
        if ($dto->has('status')) { $data['status'] = $dto->status->value; }
        if ($dto->has('due_date')) { $data['due_date'] = $dto->dueDate; }
        if ($dto->has('estimated_hours')) { $data['estimated_hours'] = $dto->estimatedHours; }
        if ($dto->has('actual_hours')) { $data['actual_hours'] = $dto->actualHours; }

        $updated = $this->repository->update($task, $data);

        if (isset($data['assigned_to']) && $data['assigned_to'] !== $previousAssignee) {
            $newAssignee = $data['assigned_to'] ? User::find($data['assigned_to']) : null;
            if ($newAssignee && $newAssignee->id !== $actor->id) {
                TaskAssigned::dispatch($updated, $actor, $newAssignee);
            }
        }

        $this->activityLog->log($actor, 'task.updated', "Updated task \"{$updated->title}\"", $updated, $data);
        $this->bustTaskCache($task->id);
        $this->bustTaskListCache($task->project_id);

        TaskUpdated::dispatch($updated, $actor, $data);

        return $updated;
    }

    public function delete(Task $task, User $actor): void
    {
        $title = $task->title;
        $id = $task->id;
        $projectId = $task->project_id;

        $this->repository->delete($task);

        $this->activityLog->log($actor, 'task.deleted', "Soft-deleted task \"{$title}\"", $task);
        $this->bustTaskCache($id);
        $this->bustTaskListCache($projectId);

        TaskDeleted::dispatch($task, $actor);
    }

    public function restore(Task $task, User $actor): Task
    {
        $restored = $this->repository->restore($task);

        $this->recordHistory($restored, $actor->id, 'task_restored');
        $this->activityLog->log($actor, 'task.restored', "Restored task \"{$restored->title}\"", $restored);
        $this->bustTaskListCache($restored->project_id);

        return $restored;
    }


    public function addComment(Task $task, CreateCommentDTO $dto): TaskComment
    {
        $comment = $this->commentService->add($task, $dto);
        $this->bustTaskCache($task->id);

        return $comment;
    }

    public function updateComment(TaskComment $comment, UpdateCommentDTO $dto, User $actor): TaskComment
    {
        $updated = $this->commentService->update($comment, $dto, $actor);
        $this->bustTaskCache($comment->task_id);

        return $updated;
    }

    public function deleteComment(TaskComment $comment, User $actor): void
    {
        $taskId = $comment->task_id;
        $this->commentService->delete($comment, $actor);
        $this->bustTaskCache($taskId);
    }


    public function uploadAttachment(Task $task, UploadedFile $file, User $actor): \App\Models\TaskAttachment
    {
        $attachment = $this->attachmentService->upload($task, $file, $actor);
        $this->bustTaskCache($task->id);

        return $attachment;
    }

    public function deleteAttachment(\App\Models\TaskAttachment $attachment, User $actor): void
    {
        $taskId = $attachment->task_id;
        $this->attachmentService->delete($attachment, $actor);
        $this->bustTaskCache($taskId);
    }


    private function recordHistory(Task $task, ?string $userId, string $event, ?string $field = null, ?string $oldValue = null, ?string $newValue = null, array $context = []): void
    {
        TaskHistory::create([
            'task_id' => $task->id,
            'user_id' => $userId,
            'event' => $event,
            'field' => $field,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'context' => $context ?: null,
        ]);
    }

    private function bustTaskCache(string $taskId): void
    {
        $this->cache->forget(CacheKeys::task($this->tenantId(), $taskId));
    }

    private function bustTaskListCache(string $projectId): void
    {
        $this->cache->flushByPattern("tenant:{$this->tenantId()}:project:{$projectId}:tasks:*");
    }

    private function isCacheable(array $filters): bool
    {
        $filteringKeys = ['assigned_to', 'status', 'priority', 'search', 'due_from', 'due_to', 'overdue', 'sort_by', 'sort_dir'];

        foreach ($filteringKeys as $key) {
            if (! empty($filters[$key])) {
                return false;
            }
        }

        return true;
    }

    private function tenantId(): string
    {
        return (string) (tenant()?->id ?? 'global');
    }
}
