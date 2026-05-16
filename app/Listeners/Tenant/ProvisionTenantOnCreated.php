<?php


namespace App\Listeners\Tenant;

use App\Domain\Tenant\Events\TenantCreated;
use App\Jobs\Tenant\ProvisionTenantJob;

class ProvisionTenantOnCreated
{
    public function handle(TenantCreated $event): void
    {
        // Dispatch async — provisioning should not block the registration HTTP response.
        ProvisionTenantJob::dispatch($event->tenant->id);
    }
}
