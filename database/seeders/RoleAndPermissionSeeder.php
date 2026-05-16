<?php


namespace Database\Seeders;

use App\Support\LangTranslations;
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
        foreach (LangTranslations::keys('user_role') as $roleName) {
            $roleModel = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'sanctum']);

            $permissions = self::ROLE_PERMISSIONS[$roleName] ?? [];

            if ($permissions === '*') {
                $roleModel->syncPermissions(Permission::all());
            } else {
                $roleModel->syncPermissions($permissions);
            }
        }

        $this->command->info('Roles and permissions seeded successfully.');
        $this->command->table(
            ['Role', 'Permissions'],
            collect(LangTranslations::keys('user_role'))->map(fn (string $roleName) => [
                LangTranslations::attr('user_role', $roleName, 'label', $roleName),
                Role::where('name', $roleName)->first()?->permissions->count() . ' permissions',
            ])->toArray()
        );
    }
}
