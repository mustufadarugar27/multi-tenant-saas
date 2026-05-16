<?php


namespace App\Domain\Tenant\Services;

use App\Domain\Tenant\Repositories\Contracts\TenantRepositoryInterface;
use App\Infrastructure\Cache\Contracts\CacheManagerInterface;
use App\Models\Tenant;
use Illuminate\Http\Request;

class TenantResolverService
{
    private const CACHE_TTL = 60;

    public function __construct(
        private readonly TenantRepositoryInterface $repository,
        private readonly CacheManagerInterface $cache,
    ) {}

    public function resolveFromRequest(Request $request): ?Tenant
    {
        if ($request->hasHeader('X-Tenant-Slug')) {
            return $this->resolveBySlug((string) $request->header('X-Tenant-Slug'));
        }

        $slug = $this->extractSubdomain($request);
        if ($slug !== null) {
            return $this->resolveBySlug($slug);
        }

        return $this->resolveByDomain($request->getHost());
    }

    public function resolveBySlug(string $slug): ?Tenant
    {
        return $this->cache->remember(
            "tenant:slug:{$slug}",
            fn () => $this->repository->findBySlug($slug),
            self::CACHE_TTL,
        );
    }

    public function resolveByDomain(string $domain): ?Tenant
    {
        return $this->cache->remember(
            "tenant:domain:{$domain}",
            fn () => $this->repository->findByDomain($domain),
            self::CACHE_TTL,
        );
    }

    private function extractSubdomain(Request $request): ?string
    {
        $host    = $request->getHost();
        $appHost = parse_url((string) config('app.url'), PHP_URL_HOST) ?? '';
        $appHost = explode(':', $appHost)[0];

        if ($appHost === '' || ! str_ends_with($host, '.' . $appHost)) {
            return null;
        }

        $subdomain = substr($host, 0, strlen($host) - strlen('.' . $appHost));

        return ($subdomain === '' || $subdomain === 'www') ? null : $subdomain;
    }
}
