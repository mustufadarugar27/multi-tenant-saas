<?php


namespace App\Jobs;

use App\Repositories\Contracts\ActivityLogRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Moves activity logs older than the configured retention period to the archive table.
 * Scheduled via the Artisan command or the scheduler in console.php.
 */
class ArchiveActivityLogsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300; // 5 min — bulk inserts may take time on large tables

    public int $tries = 1;     // Archival is idempotent on the same day, so no retry needed

    public function __construct(
        public readonly int $olderThanDays = 90,
    ) {}

    public function handle(ActivityLogRepositoryInterface $repository): void
    {
        $archived = $repository->archiveOlderThan($this->olderThanDays);

        Log::info('Activity log archival complete', [
            'older_than_days' => $this->olderThanDays,
            'rows_archived' => $archived,
        ]);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('ArchiveActivityLogsJob failed', [
            'older_than_days' => $this->olderThanDays,
            'error' => $e->getMessage(),
        ]);
    }
}
