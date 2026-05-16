<?php


namespace App\Http\Controllers\Api\V1\Task;

use App\Http\Controllers\Controller;
use App\Http\Requests\Task\ListTasksRequest;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Resources\Task\TaskHistoryResource;
use App\Http\Resources\Task\TaskResource;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class TaskController extends Controller
{
    public function __construct(private readonly TaskService $service) {}

    public function index(ListTasksRequest $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Task::class);

        $tasks = $this->service->paginate($request->filters(), $request->perPage());

        return TaskResource::collection($tasks);
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        $this->authorize('create', Task::class);

        $task = $this->service->create($request->toDTO(), $request->user());

        return (new TaskResource($task))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Request $request, string $id): TaskResource
    {
        $task = $this->service->findById($id);

        $this->authorize('view', $task);

        return new TaskResource(
            $task->loadMissing(['project', 'assignee', 'creator'])
                 ->loadCount(['comments', 'attachments'])
        );
    }

    public function update(UpdateTaskRequest $request, string $id): TaskResource
    {
        $task = $this->service->findById($id);

        $this->authorize('update', $task);

        $updated = $this->service->update($task, $request->toDTO(), $request->user());

        return new TaskResource($updated->loadMissing(['project', 'assignee', 'creator']));
    }

    public function destroy(Request $request, string $id): Response
    {
        $task = $this->service->findById($id);

        $this->authorize('delete', $task);

        $this->service->delete($task, $request->user());

        return response()->noContent();
    }

    public function restore(Request $request, string $id): TaskResource
    {
        $task = Task::withTrashed()->where('id', $id)->firstOrFail();

        $this->authorize('restore', $task);

        $restored = $this->service->restore($task, $request->user());

        return new TaskResource($restored);
    }

    public function history(Request $request, string $id): AnonymousResourceCollection
    {
        $task = $this->service->findById($id);

        $this->authorize('view', $task);

        $history = $this->service->getHistory($task, (int) $request->query('per_page', 20));

        return TaskHistoryResource::collection($history);
    }
}
