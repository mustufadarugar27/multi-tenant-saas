<?php


return [
    'models' => [
        'permission' => Spatie\Permission\Models\Permission::class,
        'role'       => Spatie\Permission\Models\Role::class,
    ],

    'table_names' => [
        'roles'                 => 'roles',
        'permissions'           => 'permissions',
        'model_has_permissions' => 'model_has_permissions',
        'model_has_roles'       => 'model_has_roles',
        'role_has_permissions'  => 'role_has_permissions',
    ],

    'column_names' => [
        'role_pivot_key'       => null,   // defaults to 'role_id'
        'permission_pivot_key' => null,   // defaults to 'permission_id'
        'model_morph_key'      => 'model_id',
        'team_foreign_key'     => 'team_id',
    ],

    /*
     * Teams mode disabled intentionally. Roles are global (super_admin, company_admin, etc.)
     * and role assignments are tenant-scoped by extension because User models carry tenant_id
     * and are always queried through BelongsToTenant global scope.
     *
     * If per-tenant custom roles are needed in future, enable teams and call
     * setPermissionsTeamId($tenant->id) in IdentifyTenant middleware.
     */
    'teams' => false,

    'register_permission_check_method' => true,

    'register_octane_reset_listener' => false,

    'use_passport_client_credentials' => false,

    'display_permission_in_exception' => false,

    'display_role_in_exception'       => false,

    'enable_wildcard_permission' => false,

    'cache' => [
        'expiration_time' => \DateInterval::createFromDateString('24 hours'),
        'key'             => 'spatie.permission.cache',
        'store'           => 'default',
    ],
];
