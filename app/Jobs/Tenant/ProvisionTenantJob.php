<?php


namespace App\Jobs\Tenant;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

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
