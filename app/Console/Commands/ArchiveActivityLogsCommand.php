<?php


namespace App\Console\Commands;

use App\Jobs\ArchiveActivityLogsJob;
use Illuminate\Console\Command;

class ArchiveActivityLogsCommand extends Command
{
    protected $signature = 'logs:archive-activity
                            {--days=90 : Archive logs older than this many days}
                            {--queue : Dispatch to queue instead of running synchronously}';

    protected $description = 'Archive activity logs older than the specified retention period';

    public function handle(): int
    {
        $days = (int) $this->option('days');

        if ($this->option('queue')) {
            ArchiveActivityLogsJob::dispatch($days)->onQueue('maintenance');
            $this->info("Dispatched archive job for logs older than {$days} days.");
        } else {
            $this->info("Archiving activity logs older than {$days} days…");
            (new ArchiveActivityLogsJob($days))->handle(app(\App\Repositories\Contracts\ActivityLogRepositoryInterface::class));
            $this->info('Done.');
        }

        return self::SUCCESS;
    }
}
