<?php


namespace Tests\Feature\Api\V1\Auth;

use App\Domain\Tenant\Models\Plan;
use App\Domain\Tenant\Models\Tenant;
use App\Models\User;
use App\Notifications\Auth\PasswordResetNotification;
use App\Support\Enums\TenantStatus;
use App\Support\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class PasswordResetTest extends TestCase
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
            'id'      => Str::uuid()->toString(),
            'plan_id' => $this->plan->id,
            'name'    => 'Acme Corp',
            'slug'    => 'acme',
            'status'  => TenantStatus::Active,
        ]);

        tenancy()->initialize($this->tenant);

        $this->user = User::create([
            'tenant_id' => $this->tenant->getTenantKey(),
            'name'      => 'John Doe',
            'email'     => 'john@acme.com',
            'password'  => Hash::make('OldPassword123!'),
            'role'      => UserRole::SuperAdmin,
        ]);

        tenancy()->end();
    }

    protected function tearDown(): void
    {
        tenancy()->end();
        parent::tearDown();
    }

    public function test_forgot_password_returns_200_for_existing_user(): void
    {
        Notification::fake();

        $this->withHeaders(['X-Tenant-Slug' => 'acme'])
            ->postJson('/api/v1/auth/forgot-password', ['email' => 'john@acme.com'])
            ->assertOk()
            ->assertJson(['message' => 'If an account with that email exists, a password reset link has been sent.']);

        Notification::assertSentTo($this->user, PasswordResetNotification::class);
    }

    public function test_forgot_password_returns_200_for_nonexistent_user_user_enumeration_prevention(): void
    {
        Notification::fake();

        $this->withHeaders(['X-Tenant-Slug' => 'acme'])
            ->postJson('/api/v1/auth/forgot-password', ['email' => 'nobody@acme.com'])
            ->assertOk();

        Notification::assertNothingSent();
    }

    public function test_password_reset_succeeds_with_valid_token(): void
    {
        // Store a known token
        $token = Str::random(64);
        \Illuminate\Support\Facades\DB::table('password_reset_tokens')->insert([
            'email'      => 'john@acme.com',
            'tenant_id'  => $this->tenant->getTenantKey(),
            'token'      => Hash::make($token),
            'created_at' => now(),
        ]);

        tenancy()->initialize($this->tenant);

        $this->withHeaders(['X-Tenant-Slug' => 'acme'])
            ->postJson('/api/v1/auth/reset-password', [
                'email'                 => 'john@acme.com',
                'token'                 => $token,
                'password'              => 'NewPassword456@',
                'password_confirmation' => 'NewPassword456@',
            ])
            ->assertOk()
            ->assertJson(['message' => 'Password has been reset successfully.']);

        // Verify password changed
        $this->user->refresh();
        $this->assertTrue(Hash::check('NewPassword456@', $this->user->password));

        // Token should be deleted
        $this->assertDatabaseMissing('password_reset_tokens', [
            'email'     => 'john@acme.com',
            'tenant_id' => $this->tenant->getTenantKey(),
        ]);

        tenancy()->end();
    }

    public function test_password_reset_fails_with_wrong_token(): void
    {
        \Illuminate\Support\Facades\DB::table('password_reset_tokens')->insert([
            'email'      => 'john@acme.com',
            'tenant_id'  => $this->tenant->getTenantKey(),
            'token'      => Hash::make(Str::random(64)),
            'created_at' => now(),
        ]);

        $this->withHeaders(['X-Tenant-Slug' => 'acme'])
            ->postJson('/api/v1/auth/reset-password', [
                'email'                 => 'john@acme.com',
                'token'                 => Str::random(64),
                'password'              => 'NewPassword456@',
                'password_confirmation' => 'NewPassword456@',
            ])
            ->assertUnprocessable();
    }

    public function test_weak_password_is_rejected(): void
    {
        $token = Str::random(64);
        \Illuminate\Support\Facades\DB::table('password_reset_tokens')->insert([
            'email'      => 'john@acme.com',
            'tenant_id'  => $this->tenant->getTenantKey(),
            'token'      => Hash::make($token),
            'created_at' => now(),
        ]);

        $this->withHeaders(['X-Tenant-Slug' => 'acme'])
            ->postJson('/api/v1/auth/reset-password', [
                'email'                 => 'john@acme.com',
                'token'                 => $token,
                'password'              => 'weakpassword',
                'password_confirmation' => 'weakpassword',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    public function test_tokens_revoked_after_password_reset(): void
    {
        tenancy()->initialize($this->tenant);
        $oldToken = $this->user->createToken('device-1')->plainTextToken;
        tenancy()->end();

        $token = Str::random(64);
        \Illuminate\Support\Facades\DB::table('password_reset_tokens')->insert([
            'email'      => 'john@acme.com',
            'tenant_id'  => $this->tenant->getTenantKey(),
            'token'      => Hash::make($token),
            'created_at' => now(),
        ]);

        tenancy()->initialize($this->tenant);

        $this->withHeaders(['X-Tenant-Slug' => 'acme'])
            ->postJson('/api/v1/auth/reset-password', [
                'email'                 => 'john@acme.com',
                'token'                 => $token,
                'password'              => 'NewPassword456@',
                'password_confirmation' => 'NewPassword456@',
            ])
            ->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);

        tenancy()->end();
    }
}
