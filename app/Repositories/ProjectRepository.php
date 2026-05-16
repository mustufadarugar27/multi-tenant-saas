<?php


namespace App\Repositories;

use App\Models\Project;
use App\Repositories\Contracts\ProjectRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ProjectRepository implements ProjectRepositoryInterface
{
    public function paginate(array $filters, int $perPage): LengthAwarePaginator
    {
        return $this->buildQuery($filters)
            ->with('creator:id,name')
            ->paginate($perPage);
    }

    public function findById(string $id): Project
    {
        return Project::with('tasks')->findOrFail($id);
    }

    public function create(array $data): Project
    {
        return Project::create($data);
    }

    public function update(Project $project, array $data): Project
    {
        $project->update($data);

        return $project->refresh();
    }

    public function delete(Project $project): void
    {
        $project->delete();
    }

    public function restore(Project $project): Project
    {
        $project->restore();

        return $project->refresh();
    }

    public function getHistory(Project $project, int $perPage): LengthAwarePaginator
    {
        return $project->histories()
                       ->with('actor:id,name')
                       ->paginate($perPage, ['*'], 'history_page');
    }

    private function buildQuery(array $filters): Builder
    {
        $query = Project::query();

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('start_date', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('end_date', '<=', $filters['date_to']);
        }

        if (! empty($filters['created_by'])) {
            $query->where('created_by', $filters['created_by']);
        }

        $allowed = ['name', 'status', 'budget', 'start_date', 'end_date', 'created_at'];
        $sortBy = in_array($filters['sort_by'] ?? null, $allowed, true) ? $filters['sort_by'] : 'created_at';
        $sortDir = ($filters['sort_dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        $query->orderBy($sortBy, $sortDir);

        return $query;
    }
}
