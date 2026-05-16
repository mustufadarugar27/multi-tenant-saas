<?php


namespace App\Services;

use App\DataTransferObjects\Project\CreateProjectDTO;
use App\DataTransferObjects\Project\UpdateProjectDTO;
use App\Events\ProjectCreated;
use App\Events\ProjectDeleted;
use App\Events\ProjectRestored;
use App\Events\ProjectUpdated;
use App\Infrastructure\Cache\CacheKeys;
use App\Infrastructure\Cache\Contracts\CacheManagerInterface;
use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\ProjectHistory;
use App\Models\User;
use App\Repositories\Contracts\ProjectRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProjectService
{
    public function __construct(
        private readonly ProjectRepositoryInterface $repository,
        private readonly CacheManagerInterface $cache,
    ) {}

    public function paginate(array $filters, int $perPage): LengthAwarePaginator
    {
        if ($this->isCacheable($filters)) {
            $page = request()->integer('page', 1);
            $cacheKey = CacheKeys::projectList($this->tenantId(), $page);

            return $this->cache->remember($cacheKey, fn () => $this->repository->paginate($filters, $perPage));
        }

        return $this->repository->paginate($filters, $perPage);
    }

    public function findById(string $id): Project
    {
        $cacheKey = CacheKeys::project($this->tenantId(), $id);

        return $this->cache->remember($cacheKey, fn () => $this->repository->findById($id));
    }

    public function getHistory(Project $project, int $perPage): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->repository->getHistory($project, $perPage);
    }

    public function create(CreateProjectDTO $dto, User $actor): Project
    {
        $project = $this->repository->create([
            'name' => $dto->name,
            'description' => $dto->description,
            'start_date' => $dto->startDate,
            'end_date' => $dto->endDate,
            'budget' => $dto->budget,
            'status' => $dto->status->value,
            'created_by' => $actor->id,
        ]);

        $this->recordHistory($project, $actor->id, 'project_created', context: [
            'name' => $project->name,
        ]);

        $this->log($actor, 'project.created', "Created project \"{$project->name}\"", $project);
        $this->bustListCache();

        ProjectCreated::dispatch($project, $actor);

        return $project;
    }

    public function update(Project $project, UpdateProjectDTO $dto, User $actor): Project
    {
        $data = [];

        if ($dto->has('name')) { $data['name'] = $dto->name; }
        if ($dto->has('description')) { $data['description'] = $dto->description; }
        if ($dto->has('start_date')) { $data['start_date'] = $dto->startDate; }
        if ($dto->has('end_date')) { $data['end_date'] = $dto->endDate; }
        if ($dto->has('budget')) { $data['budget'] = $dto->budget; }
        if ($dto->has('status')) { $data['status'] = $dto->status->value; }

        $updated = $this->repository->update($project, $data);

        $this->log($actor, 'project.updated', "Updated project \"{$updated->name}\"", $updated, $data);
        $this->bustProjectCache($project->id);
        $this->bustListCache();

        ProjectUpdated::dispatch($updated, $actor, $data);

        return $updated;
    }

    public function delete(Project $project, User $actor): void
    {
        $name = $project->name;
        $id = $project->id;

        $this->repository->delete($project);

        $this->log($actor, 'project.deleted', "Soft-deleted project \"{$name}\"", $project);
        $this->bustProjectCache($id);
        $this->bustListCache();

        ProjectDeleted::dispatch($project, $actor);
    }

    public function restore(Project $project, User $actor): Project
    {
        $restored = $this->repository->restore($project);

        $this->recordHistory($restored, $actor->id, 'project_restored');
        $this->log($actor, 'project.restored', "Restored project \"{$restored->name}\"", $restored);
        $this->bustListCache();

        ProjectRestored::dispatch($restored, $actor);

        return $restored;
    }


    private function recordHistory(Project $project, ?string $userId, string $event, ?string $field = null, ?string $oldValue = null, ?string $newValue = null, array $context = []): void
    {
        ProjectHistory::create([
            'project_id' => $project->id,
            'user_id' => $userId,
            'event' => $event,
            'field' => $field,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'context' => $context ?: null,
        ]);
    }

    private function log(User $actor, string $event, string $description, Project $subject, array $properties = []): void
    {
        ActivityLog::create([
            'user_id' => $actor->id,
            'event' => $event,
            'description' => $description,
            'subject_type' => Project::class,
            'subject_id' => $subject->id,
            'properties' => $properties ?: null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    private function bustProjectCache(string $projectId): void
    {
        $this->cache->forget(CacheKeys::project($this->tenantId(), $projectId));
    }

    private function bustListCache(): void
    {
        $this->cache->flushByPattern("tenant:{$this->tenantId()}:projects:*");
    }

    private function isCacheable(array $filters): bool
    {
        $filteringKeys = ['search', 'status', 'date_from', 'date_to', 'created_by', 'sort_by', 'sort_dir'];

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
