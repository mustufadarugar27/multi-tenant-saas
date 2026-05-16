<?php


namespace App\Providers;

use App\Domain\Auth\Repositories\Contracts\UserRepositoryInterface;
use App\Domain\Auth\Repositories\UserRepository;
use App\Domain\Tenant\Repositories\Contracts\TenantRepositoryInterface;
use App\Domain\Tenant\Repositories\TenantRepository;
use App\Infrastructure\Cache\Contracts\CacheManagerInterface;
use App\Infrastructure\Cache\TenantCacheManager;
use App\Repositories\ActivityLogRepository;
use App\Repositories\AttachmentRepository;
use App\Repositories\AuditLogRepository;
use App\Repositories\CommentRepository;
use App\Repositories\Contracts\ActivityLogRepositoryInterface;
use App\Repositories\Contracts\AttachmentRepositoryInterface;
use App\Repositories\Contracts\AuditLogRepositoryInterface;
use App\Repositories\Contracts\CommentRepositoryInterface;
use App\Repositories\Contracts\ProjectRepositoryInterface;
use App\Repositories\Contracts\TaskRepositoryInterface;
use App\Repositories\ProjectRepository;
use App\Repositories\TaskRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public array $bindings = [
        UserRepositoryInterface::class       => UserRepository::class,
        TenantRepositoryInterface::class     => TenantRepository::class,
        ProjectRepositoryInterface::class    => ProjectRepository::class,
        TaskRepositoryInterface::class       => TaskRepository::class,
        CacheManagerInterface::class         => TenantCacheManager::class,
        ActivityLogRepositoryInterface::class => ActivityLogRepository::class,
        AuditLogRepositoryInterface::class   => AuditLogRepository::class,
        CommentRepositoryInterface::class    => CommentRepository::class,
        AttachmentRepositoryInterface::class => AttachmentRepository::class,
    ];
}
