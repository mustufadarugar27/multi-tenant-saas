<?php


namespace Tests\Feature\Api\V1\Auth;

use App\Domain\Tenant\Models\Plan;
use App\Domain\Tenant\Models\Tenant;
use App\Models\User;
use App\Support\Enums\TenantStatus;
use App\Support\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    private Plan $plan;
    private Tenant $tenant;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->plan = Plan::create([
            'name'       => 'Free',
            'slug'       => 'free',
            'limits'     => ['seats' => 5],
            'features'   => [],
            'is_active'  => true,
            'is_default' => true,
        ]);

        $this->tenant = Tenant::create([
            'id'      => \Illuminate\Support\Str::uuid()->toString(),
            'plan_id' => $this->plan->id,
            'name'    => 'Acme Corp',
            'slug'    => 'acme',
            'status'  => TenantStatus::Active,
        ]);

        // stancl: initialize tenancy so BelongsToTenant scope works for user creation
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

    public function test_user_can_login_with_correct_credentials(): void
    {
        $response = $this->withHeaders(['X-Tenant-Slug' => 'acme'])
            ->postJson('/api/v1/auth/login', [
                'email'    => 'john@acme.com',
                'password' => 'Secret123!',
            ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'user' => ['id', 'name', 'email', 'role'],
                    'token',
                    'token_type',
                ],
            ]);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $this->withHeaders(['X-Tenant-Slug' => 'acme'])
            ->postJson('/api/v1/auth/login', [
                'email'    => 'john@acme.com',
                'password' => 'WrongPassword!',
            ])
            ->assertUnprocessable();
    }

    public function test_login_fails_without_tenant_header(): void
    {
        $this->postJson('/api/v1/auth/login', [
            'email'    => 'john@acme.com',
            'password' => 'Secret123!',
        ])->assertNotFound();
    }

    public function test_login_fails_with_nonexistent_tenant(): void
    {
        $this->withHeaders(['X-Tenant-Slug' => 'does-not-exist'])
            ->postJson('/api/v1/auth/login', [
                'email'    => 'john@acme.com',
                'password' => 'Secret123!',
            ])->assertNotFound();
    }

    public function test_user_can_logout(): void
    {
        $token = $this->user->createToken('test')->plainTextToken;

        $this->withHeaders([
            'X-Tenant-Slug' => 'acme',
            'Authorization' => "Bearer {$token}",
        ])->postJson('/api/v1/auth/logout')
          ->assertOk()
          ->assertJson(['message' => 'Logged out successfully.']);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_suspended_tenant_cannot_login(): void
    {
        $this->tenant->update(['status' => TenantStatus::Suspended]);

        $this->withHeaders(['X-Tenant-Slug' => 'acme'])
            ->postJson('/api/v1/auth/login', [
                'email'    => 'john@acme.com',
                'password' => 'Secret123!',
            ])->assertStatus(503);
    }
}
