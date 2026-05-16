<?php


namespace App\Jobs\Tenant;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * stancl's QueueTenancyBootstrapper (configured in config/tenancy.php)
 * automatically serializes the current tenant into every queued job and
 * re-initializes tenancy() when the job is picked up by a worker.
 *
 * No custom base class or middleware is needed — stancl handles it.
 */
class ProvisionTenantJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(private readonly string $newTenantId)
    {
        $this->onQueue('high');
    }

    public function handle(): void
    {
        // tenancy() is already initialised by stancl's QueueTenancyBootstrapper.
        $tenant = Tenant::find($this->newTenantId);

        if ($tenant === null) {
            Log::error('ProvisionTenantJob: tenant not found.', ['id' => $this->newTenantId]);

            return;
        }

        Log::info('Provisioning tenant.', ['tenant_id' => $tenant->id, 'slug' => $tenant->slug]);

        // TODO: SeedDefaultProjectAction
        // TODO: SendWelcomeEmailJob
    }
}
