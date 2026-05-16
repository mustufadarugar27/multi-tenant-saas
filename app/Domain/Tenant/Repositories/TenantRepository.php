<?php


namespace App\Domain\Tenant\Repositories;

use App\Domain\Tenant\Repositories\Contracts\TenantRepositoryInterface;
use App\Models\Domain;
use App\Models\Tenant;

class TenantRepository implements TenantRepositoryInterface
{
    public function findBySlug(string $slug): ?Tenant
    {
        return Tenant::where('slug', $slug)->first();
    }

    public function findByDomain(string $domain): ?Tenant
    {
        $record = Domain::where('domain', $domain)->first();

        return $record ? Tenant::find($record->tenant_id) : null;
    }
}
