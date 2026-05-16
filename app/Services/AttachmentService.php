<?php


namespace App\Services;

use App\Events\AttachmentDeleted;
use App\Events\AttachmentUploaded;
use App\Jobs\Task\ProcessTaskAttachmentJob;
use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\TaskHistory;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AttachmentService
{
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg', 'image/png', 'image/gif', 'image/webp',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'text/plain', 'text/csv',
        'application/zip',
        'application/x-zip-compressed',
    ];

    private const BLOCKED_EXTENSIONS = [
        'exe', 'bat', 'sh', 'cmd', 'ps1', 'vbs', 'js', 'jar',
        'app', 'dmg', 'msi', 'dll', 'so', 'php', 'py', 'rb',
        'pl', 'asp', 'aspx', 'jsp', 'cfm', 'cgi',
    ];

    public function __construct(
        private readonly ActivityLogService $activityLog,
    ) {}

    public function paginate(Task $task, int $perPage): LengthAwarePaginator
    {
        return $task->attachments()
            ->with('uploader:id,name')
            ->paginate($perPage);
    }

    public function upload(Task $task, UploadedFile $file, User $actor): TaskAttachment
    {
        $this->validateFile($file);

        $tenantId = $this->tenantId();
        $uuid = (string) Str::uuid();
        $ext = strtolower($file->getClientOriginalExtension());
        $path = "{$tenantId}/tasks/{$task->id}/{$uuid}.{$ext}";

        $file->storeAs(dirname($path), basename($path), ['disk' => 'tenant_public']);

        $attachment = TaskAttachment::create([
            'task_id' => $task->id,
            'user_id' => $actor->id,
            'filename' => $file->getClientOriginalName(),
            'disk' => 'tenant_public',
            'path' => $path,
            'mime_type' => $this->resolvedMimeType($file),
            'size' => $file->getSize(),
            'virus_scan_status' => 'pending',
        ]);

        TaskHistory::create([
            'task_id' => $task->id,
            'user_id' => $actor->id,
            'event' => 'attachment_uploaded',
            'context' => [
                'filename' => $attachment->filename,
                'attachment_id' => $attachment->id,
            ],
        ]);

        ProcessTaskAttachmentJob::dispatch($attachment)->onQueue('attachments');

        $this->activityLog->log(
            $actor,
            'task.attachment_uploaded',
            "Uploaded \"{$attachment->filename}\" to task \"{$task->title}\"",
            $task,
            ['filename' => $attachment->filename, 'size' => $attachment->size],
        );

        AttachmentUploaded::dispatch($task, $attachment, $actor);

        return $attachment;
    }

    public function delete(TaskAttachment $attachment, User $actor): void
    {
        $task = $attachment->task;

        Storage::disk($attachment->disk)->delete($attachment->path);

        $attachment->delete();

        $this->activityLog->log(
            $actor,
            'task.attachment_deleted',
            "Deleted attachment \"{$attachment->filename}\"",
            $task,
            ['filename' => $attachment->filename],
        );

        AttachmentDeleted::dispatch($attachment, $actor);
    }

    public function downloadUrl(TaskAttachment $attachment): string
    {
        if (! $attachment->isSafe()) {
            Log::warning('Attempted download of unsafe attachment', ['id' => $attachment->id]);
            abort(403, 'File is not cleared for download.');
        }

        return Storage::disk($attachment->disk)->url($attachment->path);
    }


    private function validateFile(UploadedFile $file): void
    {
        $ext = strtolower($file->getClientOriginalExtension());

        if (in_array($ext, self::BLOCKED_EXTENSIONS, true)) {
            throw ValidationException::withMessages([
                'file' => ['File extension is not permitted.'],
            ]);
        }

        $detectedMime = $this->resolvedMimeType($file);

        if (! in_array($detectedMime, self::ALLOWED_MIME_TYPES, true)) {
            throw ValidationException::withMessages([
                'file' => ["File type \"{$detectedMime}\" is not allowed."],
            ]);
        }
    }

    private function resolvedMimeType(UploadedFile $file): string
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $detected = $finfo->file($file->getRealPath());

        return $detected ?: $file->getMimeType() ?? $file->getClientMimeType();
    }

    private function tenantId(): string
    {
        return (string) (tenant()?->id ?? 'global');
    }
}
