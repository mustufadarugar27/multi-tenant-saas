<?php


namespace App\Jobs;

use App\Repositories\Contracts\ActivityLogRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ArchiveActivityLogsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;

    public int $tries = 1;

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
