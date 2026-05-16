<?php


namespace Tests\Feature\Api\V1\Auth;

use App\Domain\Tenant\Models\Plan;
use App\Domain\Tenant\Models\Tenant;
use App\Models\User;
use App\Support\Enums\TenantStatus;
use App\Support\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    private Plan $plan;
    private Tenant $tenant;
    private User $superAdmin;
    private User $companyAdmin;
    private User $manager;
    private User $employee;

    protected function setUp(): void
    {
        parent::setUp();

        // Bootstrap Spatie roles/permissions for tests (normally done by seeder)
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (UserRole::cases() as $role) {
            Role::firstOrCreate(['name' => $role->value, 'guard_name' => 'sanctum']);
        }

        foreach (['users.view-any', 'users.assign-role', 'activity-logs.view'] as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'sanctum']);
        }

        $this->plan = Plan::create([
            'name'       => 'Pro',
            'slug'       => 'pro',
            'limits'     => ['seats' => 10],
            'features'   => [],
            'is_active'  => true,
            'is_default' => true,
        ]);

        // Active status AND non-trial tenant so CheckTenantSubscription passes
        $this->tenant = Tenant::create([
            'id'            => Str::uuid()->toString(),
            'plan_id'       => $this->plan->id,
            'name'          => 'Acme Corp',
            'slug'          => 'acme',
            'status'        => TenantStatus::Active,
            'trial_ends_at' => null,  // no trial — passes subscription check
        ]);

        tenancy()->initialize($this->tenant);

        $this->superAdmin   = $this->makeUser(UserRole::SuperAdmin, verified: true);
        $this->companyAdmin = $this->makeUser(UserRole::CompanyAdmin, verified: true);
        $this->manager      = $this->makeUser(UserRole::Manager, verified: true);
        $this->employee     = $this->makeUser(UserRole::Employee, verified: true);

        tenancy()->end();
    }

    protected function tearDown(): void
    {
        tenancy()->end();
        parent::tearDown();
    }

    private function makeUser(UserRole $role, bool $verified = false): User
    {
        $user = User::create([
            'tenant_id'         => $this->tenant->getTenantKey(),
            'name'              => $role->label(),
            'email'             => $role->value . '@acme.com',
            'password'          => Hash::make('Secret123!'),
            'role'              => $role,
            'email_verified_at' => $verified ? now() : null,
        ]);

        $user->assignRole($role->value);

        return $user;
    }

    private function authAs(User $user): static
    {
        tenancy()->initialize($this->tenant);
        $token = $user->createToken('test')->plainTextToken;
        tenancy()->end();

        return $this->withHeaders([
            'X-Tenant-Slug' => 'acme',
            'Authorization' => "Bearer {$token}",
        ]);
    }

    public function test_super_admin_can_list_roles(): void
    {
        $this->authAs($this->superAdmin)
            ->getJson('/api/v1/roles')
            ->assertOk()
            ->assertJsonStructure(['data' => [['id', 'name', 'guard_name']]]);
    }

    public function test_employee_cannot_list_roles(): void
    {
        $this->authAs($this->employee)
            ->getJson('/api/v1/roles')
            ->assertForbidden();
    }

    public function test_company_admin_can_assign_role_to_lower_user(): void
    {
        $this->authAs($this->companyAdmin)
            ->postJson("/api/v1/users/{$this->employee->id}/roles", [
                'role' => UserRole::Manager->value,
            ])
            ->assertOk()
            ->assertJson(['message' => 'Role assigned successfully.']);

        tenancy()->initialize($this->tenant);
        $this->employee->refresh();
        $this->assertEquals(UserRole::Manager, $this->employee->role);
        tenancy()->end();
    }

    public function test_privilege_escalation_is_prevented(): void
    {
        // Manager tries to assign CompanyAdmin role (higher than their own privilege)
        $this->authAs($this->manager)
            ->postJson("/api/v1/users/{$this->employee->id}/roles", [
                'role' => UserRole::CompanyAdmin->value,
            ])
            ->assertForbidden();
    }

    public function test_cannot_assign_role_to_higher_ranked_user(): void
    {
        // CompanyAdmin tries to modify SuperAdmin's role
        $this->authAs($this->companyAdmin)
            ->postJson("/api/v1/users/{$this->superAdmin->id}/roles", [
                'role' => UserRole::Employee->value,
            ])
            ->assertForbidden();
    }

    public function test_cannot_assign_own_or_higher_role(): void
    {
        // CompanyAdmin tries to give Manager a CompanyAdmin role (same level as actor)
        $this->authAs($this->companyAdmin)
            ->postJson("/api/v1/users/{$this->manager->id}/roles", [
                'role' => UserRole::CompanyAdmin->value,
            ])
            ->assertForbidden();
    }

    public function test_super_admin_can_assign_any_role(): void
    {
        $this->authAs($this->superAdmin)
            ->postJson("/api/v1/users/{$this->employee->id}/roles", [
                'role' => UserRole::CompanyAdmin->value,
            ])
            ->assertOk();
    }

    public function test_invalid_role_name_is_rejected(): void
    {
        $this->authAs($this->superAdmin)
            ->postJson("/api/v1/users/{$this->employee->id}/roles", [
                'role' => 'god_mode',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['role']);
    }

    public function test_cross_tenant_role_assignment_blocked_with_404(): void
    {
        // Create a user in a different tenant
        $tenantB = Tenant::create([
            'id'      => Str::uuid()->toString(),
            'plan_id' => $this->plan->id,
            'name'    => 'Beta Corp',
            'slug'    => 'beta',
            'status'  => TenantStatus::Active,
        ]);

        tenancy()->initialize($tenantB);
        $otherUser = User::create([
            'tenant_id' => $tenantB->getTenantKey(),
            'name'      => 'Other User',
            'email'     => 'other@beta.com',
            'password'  => Hash::make('Secret123!'),
            'role'      => UserRole::Employee,
        ]);
        tenancy()->end();

        // SuperAdmin from tenant A tries to modify user from tenant B
        // BelongsToTenant scope scopes the query to tenant A → user not found → 404
        $this->authAs($this->superAdmin)
            ->postJson("/api/v1/users/{$otherUser->id}/roles", [
                'role' => UserRole::Manager->value,
            ])
            ->assertNotFound();
    }
}
