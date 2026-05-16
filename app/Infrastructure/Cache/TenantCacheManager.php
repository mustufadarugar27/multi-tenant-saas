<?php


namespace App\Infrastructure\Cache;

use Closure;
use Illuminate\Cache\Repository;
use Illuminate\Redis\Connections\Connection;
use Illuminate\Support\Facades\Redis;

/**
 * Tenant-aware cache manager.
 *
 * Wraps Laravel's cache repository and automatically prefixes keys with
 * the tenant namespace. Provides invalidation helpers that use Redis SCAN
 * (never KEYS or FLUSHDB) to avoid blocking the server.
 */
class TenantCacheManager implements Contracts\CacheManagerInterface
{
    private const DEFAULT_TTL = 300;

    public function __construct(private readonly Repository $cache) {}

    public function remember(string $key, Closure $callback, int $ttl = self::DEFAULT_TTL): mixed
    {
        return $this->cache->remember($key, $ttl, $callback);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->cache->get($key, $default);
    }

    public function put(string $key, mixed $value, int $ttl = self::DEFAULT_TTL): bool
    {
        return $this->cache->put($key, $value, $ttl);
    }

    public function forget(string $key): bool
    {
        return $this->cache->forget($key);
    }

    /**
     * Flush all cache entries for a given tenant using Redis SCAN.
     *
     * SCAN is non-blocking; KEYS would block production Redis.
     */
    public function flushTenant(string $tenantId): void
    {
        $pattern = CacheKeys::tenantPattern($tenantId);
        $this->scanAndDelete($pattern);
    }

    public function flushByPattern(string $pattern): void
    {
        $this->scanAndDelete($pattern);
    }

    private function scanAndDelete(string $pattern): void
    {
        $redis = Redis::connection();
        $prefix = config('cache.prefix');
        $fullPattern = $prefix ? "{$prefix}:{$pattern}" : $pattern;

        $cursor = '0';
        do {
            [$cursor, $keys] = $redis->scan($cursor, ['match' => $fullPattern, 'count' => 100]);

            if (! empty($keys)) {
                $redis->del(...$keys);
            }
        } while ($cursor !== '0');
    }
}
