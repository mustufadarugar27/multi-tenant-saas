<?php


namespace App\Jobs;

use App\Models\ActivityLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Writes activity log entries asynchronously.
 * Decouples the HTTP response time from DB writes on the activity_logs table.
 */
class LogActivityJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public int $backoff = 10;

    /**
     * @param array{user_id: string|null, event: string, description: string, subject_type: string, subject_id: string, properties: array|null, ip_address: string|null, user_agent: string|null} $payload
     */
    public function __construct(
        public readonly array $payload,
    ) {}

    public function handle(): void
    {
        ActivityLog::create($this->payload);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('LogActivityJob failed — activity entry lost', [
            'payload' => $this->payload,
            'error' => $e->getMessage(),
        ]);
    }
}
