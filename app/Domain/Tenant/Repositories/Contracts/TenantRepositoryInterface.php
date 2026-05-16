<?php


namespace App\Domain\Tenant\Repositories\Contracts;

use App\Models\Tenant;

interface TenantRepositoryInterface
{
    public function findBySlug(string $slug): ?Tenant;

    public function findByDomain(string $domain): ?Tenant;
}
