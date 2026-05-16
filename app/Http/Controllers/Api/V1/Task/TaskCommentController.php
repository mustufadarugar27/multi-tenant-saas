<?php


namespace App\Http\Controllers\Api\V1\Task;

use App\Http\Controllers\Controller;
use App\Http\Requests\Task\StoreCommentRequest;
use App\Http\Requests\Task\UpdateCommentRequest;
use App\Http\Resources\Task\TaskCommentResource;
use App\Models\TaskComment;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class TaskCommentController extends Controller
{
    public function __construct(private readonly attachmentsTaskService $service) {}

    public function index(Request $request, string $taskId): AnonymousResourceCollection
    {
        $task = $this->service->findById($taskId);

        $this->authorize('view', $task);

        $comments = $task->comments()->with('author:id,name')->paginate(
            (int) $request->query('per_page', 20)
        );

        return TaskCommentResource::collection($comments);
    }

    public function store(StoreCommentRequest $request, string $taskId): JsonResponse
    {
        $task = $this->service->findById($taskId);

        $this->authorize('view', $task);
        $this->authorize('create', TaskComment::class);

        $comment = $this->service->addComment($task, $request->toDTO($taskId));

        return (new TaskCommentResource($comment))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function update(UpdateCommentRequest $request, string $taskId, string $commentId): TaskCommentResource
    {
        $task = $this->service->findById($taskId);
        $comment = $task->comments()->findOrFail($commentId);

        $this->authorize('update', $comment);

        $updated = $this->service->updateComment($comment, $request->toDTO(), $request->user());

        return new TaskCommentResource($updated);
    }

    public function destroy(Request $request, string $taskId, string $commentId): Response
    {
        $task = $this->service->findById($taskId);
        $comment = $task->comments()->findOrFail($commentId);

        $this->authorize('delete', $comment);

        $this->service->deleteComment($comment, $request->user());

        return response()->noContent();
    }
}
