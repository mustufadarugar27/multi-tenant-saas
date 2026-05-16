<?php


namespace App\Infrastructure\Cache;

class CacheKeys
{
    private function __construct() {}


    public static function tenantBySlug(string $slug): string
    {
        return "tenant:slug:{$slug}";
    }

    public static function tenantByDomain(string $domain): string
    {
        return "tenant:domain:{$domain}";
    }

    public static function tenantById(string $tenantId): string
    {
        return "tenant:id:{$tenantId}";
    }


    public static function userPermissions(string $tenantId, string $userId): string
    {
        return "tenant:{$tenantId}:user:{$userId}:permissions";
    }


    public static function projectList(string $tenantId, int $page = 1): string
    {
        return "tenant:{$tenantId}:projects:page:{$page}";
    }

    public static function project(string $tenantId, string $projectId): string
    {
        return "tenant:{$tenantId}:project:{$projectId}";
    }


    public static function taskList(string $tenantId, string $projectId): string
    {
        return "tenant:{$tenantId}:project:{$projectId}:tasks";
    }

    public static function task(string $tenantId, string $taskId): string
    {
        return "tenant:{$tenantId}:task:{$taskId}";
    }


    public static function tenantPattern(string $tenantId): string
    {
        return "tenant:{$tenantId}:*";
    }
}
