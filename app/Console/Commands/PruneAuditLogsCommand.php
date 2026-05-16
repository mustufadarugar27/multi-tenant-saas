<?php


namespace App\Console\Commands;

use App\Jobs\PruneAuditLogsJob;
use Illuminate\Console\Command;

class PruneAuditLogsCommand extends Command
{
    protected $signature = 'logs:prune-audit
                            {--days=365 : Prune audit logs older than this many days (default matches SOC2 / annual retention)}
                            {--queue : Dispatch to queue instead of running synchronously}
                            {--confirm : Skip the interactive confirmation prompt}';

    protected $description = 'Permanently delete audit logs that have exceeded the retention period';

    public function handle(): int
    {
        $days = (int) $this->option('days');

        if (! $this->option('confirm') && ! $this->confirm("This will permanently delete audit logs older than {$days} days. Continue?")) {
            $this->info('Aborted.');

            return self::SUCCESS;
        }

        if ($this->option('queue')) {
            PruneAuditLogsJob::dispatch($days)->onQueue('maintenance');
            $this->info("Dispatched prune job for audit logs older than {$days} days.");
        } else {
            $this->info("Pruning audit logs older than {$days} days…");
            (new PruneAuditLogsJob($days))->handle(app(\App\Repositories\Contracts\AuditLogRepositoryInterface::class));
            $this->info('Done.');
        }

        return self::SUCCESS;
    }
}
