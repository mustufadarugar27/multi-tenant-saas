<?php


namespace App\Jobs;

use App\Repositories\Contracts\AuditLogRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Prunes audit logs that have exceeded the regulatory retention period.
 * Default retention is 365 days — adjust via the $retentionDays parameter
 * to match the applicable regulatory requirement (GDPR = 90d, SOC2 = 365d, etc.).
 */
class PruneAuditLogsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 120;

    public int $tries = 1;

    public function __construct(
        public readonly int $retentionDays = 365,
    ) {}

    public function handle(AuditLogRepositoryInterface $repository): void
    {
        $pruned = $repository->pruneOlderThan($this->retentionDays);

        Log::info('Audit log pruning complete', [
            'retention_days' => $this->retentionDays,
            'rows_pruned' => $pruned,
        ]);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('PruneAuditLogsJob failed', [
            'retention_days' => $this->retentionDays,
            'error' => $e->getMessage(),
        ]);
    }
}
