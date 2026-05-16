<?php


namespace Tests\Unit\Domain\Tenant;

use App\Models\Tenant;
use App\Domain\Tenant\Repositories\Contracts\TenantRepositoryInterface;
use App\Domain\Tenant\Services\TenantResolverService;
use App\Infrastructure\Cache\Contracts\CacheManagerInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;

class TenantResolverServiceTest extends TestCase
{
    use RefreshDatabase;

    private TenantRepositoryInterface $repository;
    private CacheManagerInterface $cache;
    private TenantResolverService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(TenantRepositoryInterface::class);
        $this->cache      = Mockery::mock(CacheManagerInterface::class);
        $this->service    = new TenantResolverService($this->repository, $this->cache);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_resolves_tenant_from_header(): void
    {
        $tenant       = new Tenant();
        $tenant->slug = 'acme';

        $request = Request::create('/api/v1/auth/login', 'POST');
        $request->headers->set('X-Tenant-Slug', 'acme');

        $this->cache->shouldReceive('remember')
            ->once()
            ->andReturn($tenant);

        $resolved = $this->service->resolveFromRequest($request);

        $this->assertSame($tenant, $resolved);
    }

    public function test_returns_null_when_tenant_not_found(): void
    {
        $request = Request::create('/api/v1/auth/login', 'POST');
        $request->headers->set('X-Tenant-Slug', 'nonexistent');

        $this->cache->shouldReceive('remember')
            ->once()
            ->andReturn(null);

        $resolved = $this->service->resolveFromRequest($request);

        $this->assertNull($resolved);
    }

    public function test_extracts_subdomain_from_host(): void
    {
        config(['app.url' => 'https://app.example.com']);

        $tenant       = new Tenant();
        $tenant->slug = 'acme';

        $request = Request::create('http://acme.app.example.com/api/v1/auth/login');

        $this->cache->shouldReceive('remember')
            ->once()
            ->andReturn($tenant);

        $resolved = $this->service->resolveFromRequest($request);

        $this->assertSame($tenant, $resolved);
    }
}
