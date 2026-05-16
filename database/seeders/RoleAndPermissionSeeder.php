<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class RoleAndPermissionSeeder extends Seeder
{
    /**
     * All permission names, organised by resource group.
     * Format: {resource}.{action}
     */
    private const PERMISSIONS = [
        // User management
        'users.view-any',
        'users.view',
        'users.create',
        'users.update',
        'users.delete',
        'users.restore',
        'users.assign-role',

        // Project management
        'projects.view-any',
        'projects.view',
        'projects.create',
        'projects.update',
        'projects.delete',

        // Task management
        'tasks.view-any',
        'tasks.view',
        'tasks.create',
        'tasks.update',
        'tasks.delete',
        'tasks.assign',

        // Reports
        'reports.view',
        'reports.export',

        // Settings
        'settings.view',
        'settings.manage',

        // Activity logs
        'activity-logs.view',

        // Billing
        'billing.view',
        'billing.manage',
    ];

    /**
     * Permissions per role (cumulative — higher roles get all lower-role permissions too).
     */
    private const ROLE_PERMISSIONS = [
        'employee' => [
            'tasks.view-any',
            'tasks.view',
            'tasks.create',
            'tasks.update',
            'projects.view-any',
            'projects.view',
            'reports.view',
        ],
        'manager' => [
            'tasks.view-any',
            'tasks.view',
            'tasks.create',
            'tasks.update',
            'tasks.delete',
            'tasks.assign',
            'projects.view-any',
            'projects.view',
            'projects.create',
            'projects.update',
            'users.view-any',
            'users.view',
            'reports.view',
            'reports.export',
            'settings.view',
        ],
        'company_admin' => [
            'users.view-any',
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'users.restore',
            'users.assign-role',
            'projects.view-any',
            'projects.view',
            'projects.create',
            'projects.update',
            'projects.delete',
            'tasks.view-any',
            'tasks.view',
            'tasks.create',
            'tasks.update',
            'tasks.delete',
            'tasks.assign',
            'reports.view',
            'reports.export',
            'settings.view',
            'settings.manage',
            'activity-logs.view',
            'billing.view',
        ],
        'super_admin' => '*',
    ];

    public function run(): void
    {
        // Reset cached roles/permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create all permissions
        foreach (self::PERMISSIONS as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'sanctum']);
        }

        // Create roles and assign permissions
        foreach (array_keys(self::ROLE_PERMISSIONS) as $roleName) {
            $roleModel = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'sanctum']);

            $permissions = self::ROLE_PERMISSIONS[$roleName];

            if ($permissions === '*') {
                $roleModel->syncPermissions(Permission::all());
            } else {
                $roleModel->syncPermissions($permissions);
            }
        }
    }
}
