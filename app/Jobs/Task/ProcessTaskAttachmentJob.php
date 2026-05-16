<?php


namespace App\Jobs\Task;

use App\Models\TaskAttachment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessTaskAttachmentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(
        public readonly TaskAttachment $attachment,
    ) {}

    public function handle(): void
    {
        // Re-fetch to ensure we have the latest state (model may have changed since dispatch).
        $attachment = TaskAttachment::find($this->attachment->id);

        if ($attachment === null) {
            return; // Attachment was deleted before the job ran.
        }

        if (! Storage::disk($attachment->disk)->exists($attachment->path)) {
            Log::warning('TaskAttachment file not found during scan', ['id' => $attachment->id]);
            $attachment->update(['virus_scan_status' => 'error']);

            return;
        }

        // --- Virus scan hook -------------------------------------------------
        // Replace this block with your actual scanner (e.g. ClamAV, VirusTotal).
        // For now we mark the file clean if it passes basic extension validation.
        $dangerousExtensions = ['exe', 'bat', 'sh', 'cmd', 'ps1', 'vbs', 'js'];
        $ext = strtolower(pathinfo($attachment->filename, PATHINFO_EXTENSION));

        $status = in_array($ext, $dangerousExtensions, true) ? 'infected' : 'clean';
        // --- End virus scan hook ---------------------------------------------

        $attachment->update(['virus_scan_status' => $status]);

        if ($status === 'infected') {
            Log::alert('Infected file detected — deleting', [
                'attachment_id' => $attachment->id,
                'filename' => $attachment->filename,
            ]);

            Storage::disk($attachment->disk)->delete($attachment->path);
            $attachment->forceDelete();
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error('ProcessTaskAttachmentJob failed', [
            'attachment_id' => $this->attachment->id,
            'error' => $e->getMessage(),
        ]);

        TaskAttachment::where('id', $this->attachment->id)
                      ->update(['virus_scan_status' => 'error']);
    }
}
