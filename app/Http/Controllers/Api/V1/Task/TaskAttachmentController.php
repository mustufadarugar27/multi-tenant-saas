<?php


namespace App\Http\Controllers\Api\V1\Task;

use App\Http\Controllers\Controller;
use App\Http\Requests\Task\StoreAttachmentRequest;
use App\Http\Resources\Task\TaskAttachmentResource;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class TaskAttachmentController extends Controller
{
    public function __construct(private readonly TaskService $service) {}

    public function index(Request $request, string $taskId): AnonymousResourceCollection
    {
        $task = $this->service->findById($taskId);

        $this->authorize('view', $task);

        $attachments = $task->attachments()
                            ->with('uploader:id,name')
                            ->paginate((int) $request->query('per_page', 20));

        return TaskAttachmentResource::collection($attachments);
    }

    public function store(StoreAttachmentRequest $request, string $taskId): JsonResponse
    {
        $task = $this->service->findById($taskId);

        $this->authorize('uploadAttachment', $task);

        $attachment = $this->service->uploadAttachment(
            $task,
            $request->file('file'),
            $request->user(),
        );

        return (new TaskAttachmentResource($attachment))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function destroy(Request $request, string $taskId, string $attachmentId): Response
    {
        $task = $this->service->findById($taskId);
        $attachment = $task->attachments()->findOrFail($attachmentId);

        $this->authorize('deleteAttachment', $task);

        $this->service->deleteAttachment($attachment, $request->user());

        return response()->noContent();
    }
}
