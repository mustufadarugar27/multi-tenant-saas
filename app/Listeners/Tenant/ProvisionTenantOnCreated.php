<?php


namespace App\Listeners\Tenant;

use App\Domain\Tenant\Events\TenantCreated;
use App\Jobs\Tenant\ProvisionTenantJob;

class ProvisionTenantOnCreated
{
    public function handle(TenantCreated $event): void
    {
        ProvisionTenantJob::dispatch($event->tenant->id);
    }
}
