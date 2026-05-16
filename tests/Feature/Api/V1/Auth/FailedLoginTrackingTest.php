<?php


namespace Tests\Feature\Api\V1\Auth;

use App\Domain\Tenant\Models\Plan;
use App\Domain\Tenant\Models\Tenant;
use App\Models\User;
use App\Support\Enums\TenantStatus;
use App\Support\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class FailedLoginTrackingTest extends TestCase
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
            'password'  => Hash::make('CorrectPassword123!'),
            'role'      => UserRole::SuperAdmin,
        ]);

        tenancy()->end();
    }

    protected function tearDown(): void
    {
        tenancy()->end();
        parent::tearDown();
    }

    private function attemptLogin(string $password): \Illuminate\Testing\TestResponse
    {
        return $this->withHeaders(['X-Tenant-Slug' => 'acme'])
            ->postJson('/api/v1/auth/login', [
                'email'    => 'john@acme.com',
                'password' => $password,
            ]);
    }

    public function test_failed_login_increments_counter(): void
    {
        $this->attemptLogin('wrong')->assertUnprocessable();

        $count = DB::table('users')->where('id', $this->user->id)->value('failed_login_count');
        $this->assertEquals(1, $count);
    }

    public function test_successful_login_clears_failed_count(): void
    {
        // Simulate prior failures via direct DB (no HTTP round-trip needed)
        DB::table('users')->where('id', $this->user->id)->update(['failed_login_count' => 3]);

        $this->attemptLogin('CorrectPassword123!')->assertOk();

        $count = DB::table('users')->where('id', $this->user->id)->value('failed_login_count');
        $this->assertEquals(0, $count);
    }

    public function test_account_locks_after_5_failed_attempts(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->attemptLogin('wrong');
        }

        $lockedUntil = DB::table('users')->where('id', $this->user->id)->value('locked_until');
        $this->assertNotNull($lockedUntil, 'locked_until should be set after 5 failed attempts');
    }

    public function test_locked_account_returns_429(): void
    {
        DB::table('users')->where('id', $this->user->id)->update([
            'failed_login_count' => 5,
            'locked_until'       => now()->addMinutes(15)->toDateTimeString(),
        ]);

        $this->attemptLogin('CorrectPassword123!')->assertStatus(429);
    }

    public function test_lock_expires_and_login_succeeds(): void
    {
        DB::table('users')->where('id', $this->user->id)->update([
            'failed_login_count' => 5,
            'locked_until'       => now()->subHour()->toDateTimeString(),
        ]);

        $this->attemptLogin('CorrectPassword123!')->assertOk();
    }
}
