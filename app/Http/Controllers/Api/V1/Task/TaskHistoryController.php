<?php


namespace App\Http\Controllers\Api\V1\Task;

use App\Http\Controllers\Controller;
use App\Http\Resources\Task\TaskHistoryResource;
use App\Services\TaskService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TaskHistoryController extends Controller
{
    public function __construct(private readonly TaskService $service) {}

    public function index(Request $request, string $taskId): AnonymousResourceCollection
    {
        $task = $this->service->findById($taskId);

        $this->authorize('view', $task);

        $history = $this->service->getHistory($task, (int) $request->query('per_page', 25));

        return TaskHistoryResource::collection($history);
    }
}
