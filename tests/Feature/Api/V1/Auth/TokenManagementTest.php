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
use Tests\TestCase;

class TokenManagementTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $plan = Plan::create([
            'name'       => 'Free',
            'slug'       => 'free',
            'limits'     => ['seats' => 5],
            'features'   => [],
            'is_active'  => true,
            'is_default' => true,
        ]);

        $this->tenant = Tenant::create([
            'id'      => Str::uuid()->toString(),
            'plan_id' => $plan->id,
            'name'    => 'Acme Corp',
            'slug'    => 'acme',
            'status'  => TenantStatus::Active,
        ]);

        tenancy()->initialize($this->tenant);

        $this->user = User::create([
            'tenant_id' => $this->tenant->getTenantKey(),
            'name'      => 'John Doe',
            'email'     => 'john@acme.com',
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

    public function test_token_refresh_revokes_old_token_and_issues_new_one(): void
    {
        tenancy()->initialize($this->tenant);
        $oldToken = $this->user->createToken('api')->plainTextToken;
        tenancy()->end();

        $response = $this->withHeaders([
            'X-Tenant-Slug' => 'acme',
            'Authorization' => "Bearer {$oldToken}",
        ])->postJson('/api/v1/auth/refresh');

        $response->assertOk()
            ->assertJsonStructure(['token', 'token_type']);

        $newToken = $response->json('token');
        $this->assertNotEquals($oldToken, $newToken);

        // Old token should be deleted from the DB
        $oldTokenId = explode('|', $oldToken)[0];
        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $oldTokenId]);
    }

    public function test_logout_all_revokes_every_session(): void
    {
        tenancy()->initialize($this->tenant);
        $token1 = $this->user->createToken('device-1')->plainTextToken;
        $token2 = $this->user->createToken('device-2')->plainTextToken;
        tenancy()->end();

        $this->withHeaders([
            'X-Tenant-Slug' => 'acme',
            'Authorization' => "Bearer {$token1}",
        ])->postJson('/api/v1/auth/logout-all')->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_me_endpoint_returns_user_with_tenant(): void
    {
        tenancy()->initialize($this->tenant);
        $token = $this->user->createToken('api')->plainTextToken;
        tenancy()->end();

        $this->withHeaders([
            'X-Tenant-Slug' => 'acme',
            'Authorization' => "Bearer {$token}",
        ])->getJson('/api/v1/auth/me')
          ->assertOk()
          ->assertJsonStructure([
              'user' => ['user' => ['id', 'name', 'email', 'role']],
              'tenant',
          ]);
    }

    public function test_cross_tenant_token_is_rejected(): void
    {
        // Tenant B
        $plan = Plan::first();
        $tenantB = Tenant::create([
            'id'      => Str::uuid()->toString(),
            'plan_id' => $plan->id,
            'name'    => 'Beta Corp',
            'slug'    => 'beta',
            'status'  => TenantStatus::Active,
        ]);

        tenancy()->initialize($this->tenant);
        $tokenA = $this->user->createToken('api')->plainTextToken;
        tenancy()->end();

        // Use tenant A's token against tenant B's endpoint
        $this->withHeaders([
            'X-Tenant-Slug' => 'beta',
            'Authorization' => "Bearer {$tokenA}",
        ])->getJson('/api/v1/auth/me')->assertForbidden();
    }
}
