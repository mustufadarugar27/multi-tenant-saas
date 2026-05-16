<?php


namespace App\Providers;

use App\Domain\Tenant\Events\TenantCreated as AppTenantCreated;
use App\Domain\Tenant\Events\TenantSuspended;
use App\Domain\Auth\Events\UserRegistered;
use App\Listeners\Tenant\ProvisionTenantOnCreated;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Stancl\JobPipeline\JobPipeline;
use Stancl\Tenancy\Events;
use Stancl\Tenancy\Jobs;
use Stancl\Tenancy\Listeners;
use Stancl\Tenancy\Middleware;

/**
 * Tenancy service provider — per-tenant-database mode (same as sangam-crm-11).
 *
 * On TenantCreated (stancl's internal event, fired when a Tenant row is saved):
 *   1. CreateDatabase  — creates the tenant's MySQL database
 *   2. MigrateDatabase — runs database/migrations/tenant/ against the new DB
 *   3. SeedDatabase    — (optional) runs TenantDatabaseSeeder
 *
 * On TenantDeleted: DeleteDatabase drops the tenant's database.
 *
 * shouldBeQueued(false) means these run synchronously during registration so
 * the tenant DB is ready before RegisterCompanyAction creates the first user.
 */
class TenancyServiceProvider extends ServiceProvider
{
    public static string $controllerNamespace = '';

    public function events(): array
    {
        return [
            Events\CreatingTenant::class => [],
            Events\TenantCreated::class  => [
                JobPipeline::make([
                    Jobs\CreateDatabase::class,
                    Jobs\MigrateDatabase::class,
                    Jobs\SeedDatabase::class,
                ])->send(function (Events\TenantCreated $event) {
                    return $event->tenant;
                })->shouldBeQueued(false),
            ],
            Events\SavingTenant::class   => [],
            Events\TenantSaved::class    => [],
            Events\UpdatingTenant::class => [],
            Events\TenantUpdated::class  => [],
            Events\DeletingTenant::class => [],
            Events\TenantDeleted::class  => [
                JobPipeline::make([
                    Jobs\DeleteDatabase::class,
                ])->send(function (Events\TenantDeleted $event) {
                    return $event->tenant;
                })->shouldBeQueued(false),
            ],

            Events\CreatingDomain::class => [],
            Events\DomainCreated::class  => [],
            Events\SavingDomain::class   => [],
            Events\DomainSaved::class    => [],
            Events\UpdatingDomain::class => [],
            Events\DomainUpdated::class  => [],
            Events\DeletingDomain::class => [],
            Events\DomainDeleted::class  => [],

            Events\InitializingTenancy::class     => [],
            Events\TenancyInitialized::class      => [
                Listeners\BootstrapTenancy::class,
            ],
            Events\EndingTenancy::class           => [],
            Events\TenancyEnded::class            => [
                Listeners\RevertToCentralContext::class,
            ],
            Events\BootstrappingTenancy::class    => [],
            Events\TenancyBootstrapped::class     => [],
            Events\RevertingToCentralContext::class => [],
            Events\RevertedToCentralContext::class  => [],

            AppTenantCreated::class => [
                ProvisionTenantOnCreated::class,
            ],
            TenantSuspended::class => [],
            UserRegistered::class  => [],
        ];
    }

    public function register(): void {}

    public function boot(): void
    {
        $this->bootEvents();
        $this->mapRoutes();
        $this->makeTenancyMiddlewareHighestPriority();
    }

    private function bootEvents(): void
    {
        foreach ($this->events() as $event => $listeners) {
            foreach ($listeners as $listener) {
                if ($listener instanceof JobPipeline) {
                    $listener = $listener->toListener();
                }
                Event::listen($event, $listener);
            }
        }
    }

    private function mapRoutes(): void
    {
        $this->app->booted(function (): void {
            if (file_exists(base_path('routes/tenant.php'))) {
                Route::namespace(static::$controllerNamespace)
                    ->group(base_path('routes/tenant.php'));
            }
        });
    }

    private function makeTenancyMiddlewareHighestPriority(): void
    {
        $middleware = [
            Middleware\PreventAccessFromCentralDomains::class,
            Middleware\InitializeTenancyByDomain::class,
            Middleware\InitializeTenancyBySubdomain::class,
            Middleware\InitializeTenancyByDomainOrSubdomain::class,
            Middleware\InitializeTenancyByPath::class,
            Middleware\InitializeTenancyByRequestData::class,
        ];

        foreach (array_reverse($middleware) as $m) {
            $this->app[\Illuminate\Contracts\Http\Kernel::class]->prependToMiddlewarePriority($m);
        }
    }
}
