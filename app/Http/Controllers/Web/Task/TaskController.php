<?php


namespace App\Http\Controllers\Web\Task;

use App\Http\Controllers\Controller;
use App\Http\Requests\Task\ListTasksRequest;
use App\Http\Requests\Task\StoreAttachmentRequest;
use App\Http\Requests\Task\StoreCommentRequest;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateCommentRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskService;
use App\Support\EnumConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function __construct(private readonly TaskService $service) {}


    public function index(ListTasksRequest $request): View
    {
        $this->authorize('viewAny', Task::class);

        $tasks = $this->service->paginate($request->filters(), $request->perPage());

        return view('tasks.index', [
            'tasks'      => $tasks,
            'filters'    => $request->filters(),
            'statuses'   => EnumConfig::options('task_status'),
            'priorities' => EnumConfig::options('task_priority'),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Task::class);

        return view('tasks.create', [
            'statuses'   => EnumConfig::options('task_status'),
            'priorities' => EnumConfig::options('task_priority'),
            'projects' => Project::orderBy('name')->get(['id', 'name']),
            'users' => User::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StoreTaskRequest $request): RedirectResponse
    {
        $this->authorize('create', Task::class);

        $task = $this->service->create($request->toDTO(), $request->user());

        return redirect()
            ->route('tenant.tasks.show', $task)
            ->with('success', "Task created successfully.");
    }

    public function show(Request $request, string $id): View
    {
        $task = $this->service->findById($id);

        $this->authorize('view', $task);

        $task->load(['project', 'assignee', 'creator'])
             ->loadCount(['comments', 'attachments']);

        $comments = $task->comments()->with('author:id,name')->get();
        $attachments = $task->attachments()->with('uploader:id,name')->get();
        $history = $this->service->getHistory($task, 5);

        return view('tasks.show', compact('task', 'comments', 'attachments', 'history'));
    }

    public function edit(string $id): View
    {
        $task = $this->service->findById($id);

        $this->authorize('update', $task);

        return view('tasks.edit', [
            'task'       => $task->loadMissing(['project', 'assignee']),
            'statuses'   => EnumConfig::options('task_status'),
            'priorities' => EnumConfig::options('task_priority'),
            'projects' => Project::orderBy('name')->get(['id', 'name']),
            'users' => User::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(UpdateTaskRequest $request, string $id): RedirectResponse
    {
        $task = $this->service->findById($id);

        $this->authorize('update', $task);

        $updated = $this->service->update($task, $request->toDTO(), $request->user());

        return redirect()
            ->route('tenant.tasks.show', $updated)
            ->with('success', "Task updated successfully.");
    }

    public function destroy(Request $request, string $id): RedirectResponse
    {
        $task = $this->service->findById($id);

        $this->authorize('delete', $task);

        $this->service->delete($task, $request->user());

        return redirect()
            ->route('tenant.tasks.index')
            ->with('success', 'Task deleted successfully.');
    }

    public function restore(Request $request, string $id): RedirectResponse
    {
        $task = Task::withTrashed()->where('id', $id)->firstOrFail();

        $this->authorize('restore', $task);

        $restored = $this->service->restore($task, $request->user());

        return redirect()
            ->route('tenant.tasks.show', $restored)
            ->with('success', "Task restored successfully.");
    }


    public function storeComment(StoreCommentRequest $request, string $id): RedirectResponse
    {
        $task = $this->service->findById($id);

        $this->service->addComment($task, $request->toDTO($id));

        return redirect()
            ->route('tenant.tasks.show', $task)
            ->with('success', 'Comment added.');
    }

    public function updateComment(UpdateCommentRequest $request, string $id, string $commentId): RedirectResponse
    {
        $task = $this->service->findById($id);
        $comment = $task->comments()->findOrFail($commentId);

        $this->authorize('update', $comment);

        $this->service->updateComment($comment, $request->toDTO(), $request->user());

        return redirect()
            ->route('tenant.tasks.show', $task)
            ->with('success', 'Comment updated.');
    }

    public function destroyComment(Request $request, string $id, string $commentId): RedirectResponse
    {
        $task = $this->service->findById($id);
        $comment = $task->comments()->findOrFail($commentId);

        $this->authorize('delete', $comment);

        $this->service->deleteComment($comment, $request->user());

        return redirect()
            ->route('tenant.tasks.show', $task)
            ->with('success', 'Comment deleted.');
    }


    public function storeAttachment(StoreAttachmentRequest $request, string $id): RedirectResponse
    {
        $task = $this->service->findById($id);

        $this->authorize('uploadAttachment', $task);

        $this->service->uploadAttachment($task, $request->file('file'), $request->user());

        return redirect()
            ->route('tenant.tasks.show', $task)
            ->with('success', 'File uploaded successfully.');
    }

    public function downloadAttachment(Request $request, string $id, string $attachmentId): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $task = $this->service->findById($id);
        $attachment = $task->attachments()->findOrFail($attachmentId);

        $this->authorize('view', $task);

        return \Illuminate\Support\Facades\Storage::disk($attachment->disk)
            ->download($attachment->path, $attachment->filename);
    }

    public function destroyAttachment(Request $request, string $id, string $attachmentId): RedirectResponse
    {
        $task = $this->service->findById($id);
        $attachment = $task->attachments()->findOrFail($attachmentId);

        $this->authorize('deleteAttachment', $task);

        $this->service->deleteAttachment($attachment, $request->user());

        return redirect()
            ->route('tenant.tasks.show', $task)
            ->with('success', 'Attachment removed.');
    }
}
