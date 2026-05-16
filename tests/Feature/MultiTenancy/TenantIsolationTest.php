<?php


namespace Tests\Feature\MultiTenancy;

use App\Domain\Tenant\Models\Plan;
use App\Domain\Tenant\Models\Tenant;
use App\Models\User;
use App\Support\Enums\TenantStatus;
use App\Support\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Tenant isolation tests — the most critical security test suite.
 *
 * Verifies that stancl's BelongsToTenant scope, our EnforceTenantScope
 * middleware, and the overall request lifecycle prevent cross-tenant leakage.
 */
class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    private Plan $plan;
    private Tenant $tenantA;
    private Tenant $tenantB;
    private User $userA;
    private User $userB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plan = Plan::create([
            'name'       => 'Free',
            'slug'       => 'free',
            'limits'     => [],
            'features'   => [],
            'is_active'  => true,
            'is_default' => true,
        ]);

        $this->tenantA = Tenant::create([
            'id'      => Str::uuid()->toString(),
            'plan_id' => $this->plan->id,
            'name'    => 'Tenant A',
            'slug'    => 'tenant-a',
            'status'  => TenantStatus::Active,
        ]);

        $this->tenantB = Tenant::create([
            'id'      => Str::uuid()->toString(),
            'plan_id' => $this->plan->id,
            'name'    => 'Tenant B',
            'slug'    => 'tenant-b',
            'status'  => TenantStatus::Active,
        ]);

        tenancy()->initialize($this->tenantA);
        $this->userA = User::create([
            'tenant_id' => $this->tenantA->getTenantKey(),
            'name'      => 'User A',
            'email'     => 'a@a.com',
            'password'  => Hash::make('Secret123!'),
            'role'      => UserRole::SuperAdmin,
        ]);
        tenancy()->end();

        tenancy()->initialize($this->tenantB);
        $this->userB = User::create([
            'tenant_id' => $this->tenantB->getTenantKey(),
            'name'      => 'User B',
            'email'     => 'b@b.com',
            'password'  => Hash::make('Secret123!'),
            'role'      => UserRole::SuperAdmin,
        ]);
        tenancy()->end();
    }

    protected function tearDown(): void
    {
        tenancy()->end();
        parent::tearDown();
    }

    public function test_stancl_scope_prevents_cross_tenant_user_query(): void
    {
        tenancy()->initialize($this->tenantA);

        $users = User::all();

        $this->assertCount(1, $users);
        $this->assertTrue($users->first()->is($this->userA));
        $this->assertFalse($users->contains($this->userB));

        tenancy()->end();
    }

    public function test_tenant_a_token_cannot_access_tenant_b_routes(): void
    {
        $tokenA = $this->userA->createToken('test')->plainTextToken;

        // Token issued by tenant A used against tenant B endpoint — must be forbidden
        $this->withHeaders([
            'X-Tenant-Slug' => 'tenant-b',
            'Authorization' => "Bearer {$tokenA}",
        ])->getJson('/api/v1/auth/me')->assertForbidden();
    }

    public function test_tenant_a_user_only_sees_their_own_tenant(): void
    {
        $tokenA = $this->userA->createToken('test')->plainTextToken;

        $response = $this->withHeaders([
            'X-Tenant-Slug' => 'tenant-a',
            'Authorization' => "Bearer {$tokenA}",
        ])->getJson('/api/v1/auth/me');

        $response->assertOk();

        $tenantId = $response->json('user.user.tenant_id');
        $this->assertEquals($this->tenantA->getTenantKey(), $tenantId);
        $this->assertNotEquals($this->tenantB->getTenantKey(), $tenantId);
    }

    public function test_user_in_tenant_b_not_visible_when_scoped_to_tenant_a(): void
    {
        tenancy()->initialize($this->tenantA);

        $found = User::query()->where('email', 'b@b.com')->first();

        $this->assertNull($found, 'Cross-tenant user must not be visible through scoped query.');

        tenancy()->end();
    }

    public function test_both_tenants_have_isolated_user_counts(): void
    {
        tenancy()->initialize($this->tenantA);
        $countA = User::count();
        tenancy()->end();

        tenancy()->initialize($this->tenantB);
        $countB = User::count();
        tenancy()->end();

        $this->assertEquals(1, $countA);
        $this->assertEquals(1, $countB);
    }
}
