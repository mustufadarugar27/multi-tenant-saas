<?php


namespace Tests\Feature\Api\V1\Auth;

use App\Domain\Tenant\Models\Plan;
use App\Domain\Tenant\Models\Tenant;
use App\Models\User;
use App\Support\Enums\TenantStatus;
use App\Support\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterCompanyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Plan::create([
            'name'       => 'Free',
            'slug'       => 'free',
            'limits'     => ['seats' => 5, 'projects' => 3],
            'features'   => [],
            'is_active'  => true,
            'is_default' => true,
        ]);
    }

    protected function tearDown(): void
    {
        tenancy()->end();
        parent::tearDown();
    }

    public function test_registers_company_successfully(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'company_name'          => 'Acme Corp',
            'name'                  => 'John Doe',
            'email'                 => 'john@acme.com',
            'password'              => 'Secret123!',
            'password_confirmation' => 'Secret123!',
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'user' => ['id', 'name', 'email', 'role', 'tenant_id'],
                    'token',
                    'token_type',
                ],
                'tenant' => ['id', 'name', 'slug', 'status'],
            ]);

        $this->assertDatabaseHas('tenants', ['slug' => 'acme-corp']);
        $this->assertDatabaseHas('users', [
            'email' => 'john@acme.com',
            'role'  => UserRole::SuperAdmin->value,
        ]);
    }

    public function test_tenant_gets_trial_status_on_registration(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'company_name'          => 'Acme Corp',
            'name'                  => 'John Doe',
            'email'                 => 'john@acme.com',
            'password'              => 'Secret123!',
            'password_confirmation' => 'Secret123!',
        ]);

        $tenant = Tenant::query()->where('slug', 'acme-corp')->first();

        $this->assertNotNull($tenant);
        $this->assertEquals(TenantStatus::Trial, $tenant->status);
        $this->assertNotNull($tenant->trial_ends_at);
    }

    public function test_same_email_can_register_on_different_companies(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'company_name'          => 'Acme Corp',
            'name'                  => 'John',
            'email'                 => 'john@example.com',
            'password'              => 'Secret123!',
            'password_confirmation' => 'Secret123!',
        ])->assertCreated();

        tenancy()->end();

        $this->postJson('/api/v1/auth/register', [
            'company_name'          => 'Beta Corp',
            'name'                  => 'John',
            'email'                 => 'john@example.com',
            'password'              => 'Secret123!',
            'password_confirmation' => 'Secret123!',
        ])->assertCreated();

        // Same email on two different tenants is valid by design
        $this->assertDatabaseCount('users', 2);
        $this->assertDatabaseCount('tenants', 2);
    }

    public function test_validation_fails_with_weak_password(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'company_name'          => 'Acme Corp',
            'name'                  => 'John',
            'email'                 => 'john@acme.com',
            'password'              => 'weak',
            'password_confirmation' => 'weak',
        ])->assertUnprocessable()
          ->assertJsonValidationErrors(['password']);
    }

    public function test_validation_fails_with_missing_fields(): void
    {
        $this->postJson('/api/v1/auth/register', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['company_name', 'name', 'email', 'password']);
    }

    public function test_slug_is_auto_generated_from_company_name(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'company_name'          => 'My Awesome Company!',
            'name'                  => 'Jane',
            'email'                 => 'jane@company.com',
            'password'              => 'Secret123!',
            'password_confirmation' => 'Secret123!',
        ])->assertCreated();

        $this->assertDatabaseHas('tenants', ['slug' => 'my-awesome-company']);
    }
}
