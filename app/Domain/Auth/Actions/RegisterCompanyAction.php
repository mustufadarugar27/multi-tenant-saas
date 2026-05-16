<?php


namespace App\Domain\Auth\Actions;

use App\Domain\Auth\DTOs\RegisterCompanyDTO;
use App\Domain\Auth\Events\UserRegistered;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\TenantSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RegisterCompanyAction
{
    public function execute(RegisterCompanyDTO $dto): array
    {
        $slug = $dto->slug ?: Str::slug($dto->companyName);

        // Create tenant in central DB — TenancyServiceProvider provisions the DB synchronously
        $tenant = Tenant::create([
            'id'           => Str::uuid()->toString(),
            'name'         => $dto->companyName,
            'slug'         => $slug,
            'status'       => 'trial',
            'trial_ends_at' => now()->addDays(14),
            'settings'     => ['admin_email' => $dto->email],
        ]);

        $tenant->domains()->create(['domain' => 'app.' . $slug . '.com']);

        // Switch to tenant DB to create the first user
        tenancy()->initialize($tenant);

        $user = User::create([
            'name'     => $dto->name,
            'email'    => $dto->email,
            'password' => $dto->password,
            'role'     => 'super_admin',
            'is_active' => true,
        ]);

        $user->assignRole('super_admin');

        Artisan::call('db:seed', ['--class' => TenantSeeder::class, '--force' => true]);

        $token = $user->createToken('api')->plainTextToken;

        event(new UserRegistered($user));

        return compact('user', 'tenant', 'token');
    }
}
