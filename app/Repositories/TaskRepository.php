<?php


namespace App\Repositories;

use App\Models\Task;
use App\Repositories\Contracts\TaskRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class TaskRepository implements TaskRepositoryInterface
{
    public function paginate(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->buildQuery($filters)
            ->with(['assignee:id,name', 'creator:id,name', 'project:id,name'])
            ->withCount(['comments', 'attachments'])
            ->paginate($perPage);
    }

    public function findById(string $id): Task
    {
        return Task::findOrFail($id);
    }

    public function create(array $data): Task
    {
        return Task::create($data);
    }

    public function update(Task $task, array $data): Task
    {
        $task->update($data);

        return $task->refresh();
    }

    public function delete(Task $task): void
    {
        $task->delete();
    }

    public function restore(Task $task): Task
    {
        $task->restore();

        return $task->refresh();
    }

    public function getHistory(Task $task, int $perPage): LengthAwarePaginator
    {
        return $task->histories()
                    ->with('actor:id,name')
                    ->paginate($perPage, ['*'], 'history_page');
    }

    private function buildQuery(array $filters): Builder
    {
        $query = Task::query();

        if (! empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }

        if (! empty($filters['assigned_to'])) {
            $query->where('assigned_to', $filters['assigned_to']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search): void {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['due_from'])) {
            $query->whereDate('due_date', '>=', $filters['due_from']);
        }

        if (! empty($filters['due_to'])) {
            $query->whereDate('due_date', '<=', $filters['due_to']);
        }

        if (! empty($filters['overdue'])) {
            $query->where('due_date', '<', now()->toDateString())
                  ->whereNotIn('status', ['done', 'cancelled']);
        }

        $allowed = ['title', 'status', 'priority', 'due_date', 'created_at'];
        $sortBy = in_array($filters['sort_by'] ?? null, $allowed, true) ? $filters['sort_by'] : 'created_at';
        $sortDir = ($filters['sort_dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        $query->orderBy($sortBy, $sortDir);

        return $query;
    }
}
