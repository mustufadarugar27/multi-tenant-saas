<?php


use Stancl\Tenancy\Bootstrappers\DatabaseTenancyBootstrapper;
use Stancl\Tenancy\Bootstrappers\CacheTenancyBootstrapper;
use Stancl\Tenancy\Bootstrappers\FilesystemTenancyBootstrapper;
use Stancl\Tenancy\Bootstrappers\QueueTenancyBootstrapper;

return [

    'tenant_model' => App\Models\Tenant::class,
    'domain_model' => Stancl\Tenancy\Database\Models\Domain::class,
    'id_generator' => Stancl\Tenancy\UUIDGenerator::class,

    /*
    |--------------------------------------------------------------------------
    | Per-tenant-database tenancy — bootstrappers
    |--------------------------------------------------------------------------
    | DatabaseTenancyBootstrapper MUST be first — it switches the DB connection.
    | Every subsequent bootstrapper (Cache, Queue, Filesystem) then operates
    | against the correct tenant database.
    */
    'bootstrappers' => [
        DatabaseTenancyBootstrapper::class,
        CacheTenancyBootstrapper::class,
        FilesystemTenancyBootstrapper::class,
        QueueTenancyBootstrapper::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Database
    |--------------------------------------------------------------------------
    | central_connection — the connection for the system/central DB (plans,
    |   tenants, domains, subscriptions).
    |
    | template_tenant_connection — null means stancl clones the central
    |   connection config and sets the database name to the tenant's DB name.
    |
    | prefix/suffix — tenant DB name = prefix + tenant_id + suffix.
    |   Default: empty prefix, empty suffix → DB name == tenant_id (UUID).
    */
    'database' => [
        'central_connection'         => env('DB_CONNECTION', 'mysql'),
        'template_tenant_connection' => null,
        'prefix'                     => env('TENANT_DB_PREFIX', ''),
        'suffix'                     => env('TENANT_DB_SUFFIX', ''),
        'managers' => [
            'sqlite' => Stancl\Tenancy\TenantDatabaseManagers\SQLiteDatabaseManager::class,
            'mysql'  => Stancl\Tenancy\TenantDatabaseManagers\MySQLDatabaseManager::class,
            'pgsql'  => Stancl\Tenancy\TenantDatabaseManagers\PostgreSQLDatabaseManager::class,
        ],
    ],

    'redis' => [
        'prefix_base'         => '',
        'prefixed_connections' => [],
    ],

    'cache' => [
        'tag_base' => 'tenant',
    ],

    'filesystem' => [
        'suffix_base'         => 'tenancy/tenants/',
        'disks'               => ['local', 'public'],
        'root_override'       => [
            'local'  => '%storage_path%/',
            'public' => '%storage_path%/public/',
        ],
        'suffix_storage_path' => false,
        'asset_helper_tenancy' => false,
    ],

    'central_domains' => [
        env('APP_DOMAIN', '127.0.0.1'),
        'localhost',
        '::1',
    ],

    /*
    |--------------------------------------------------------------------------
    | Tenant migrations
    |--------------------------------------------------------------------------
    | Parameters passed to `php artisan tenants:migrate`.
    | These migrations run inside each tenant's own database.
    */
    'migration_parameters' => [
        '--force'    => true,
        '--path'     => [database_path('migrations/tenant')],
        '--realpath' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Tenant seeders
    |--------------------------------------------------------------------------
    */
    'seeder_parameters' => [
        '--class' => 'Database\\Seeders\\Tenant\\TenantDatabaseSeeder',
    ],

    'trial_days' => (int) env('TENANT_TRIAL_DAYS', 14),
    'header'     => env('TENANT_HEADER', 'X-Tenant-Slug'),
    'cache_ttl'  => (int) env('TENANT_CACHE_TTL', 60),
    'per_page'   => (int) env('TENANT_PER_PAGE', 20),

];
