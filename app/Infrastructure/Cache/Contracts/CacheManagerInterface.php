<?php


namespace App\Infrastructure\Cache\Contracts;

use Closure;

interface CacheManagerInterface
{
    public function remember(string $key, Closure $callback, int $ttl = 300): mixed;

    public function get(string $key, mixed $default = null): mixed;

    public function put(string $key, mixed $value, int $ttl = 300): bool;

    public function forget(string $key): bool;

    public function flushTenant(string $tenantId): void;

    public function flushByPattern(string $pattern): void;
}
