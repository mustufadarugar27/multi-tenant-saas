<?php


namespace App\Domain\Billing\Actions;

use App\Domain\Auth\Events\UserRegistered;
use App\Models\PendingRegistration;
use App\Models\Tenant;
use App\Notifications\Billing\SubscriptionActivatedNotification;
use Database\Seeders\TenantSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

final class ProvisionTenantFromPendingAction
{
    public function execute(PendingRegistration $pending): Tenant
    {
        $tenant = Tenant::create([
            'id'                  => Str::uuid()->toString(),
            'name'                => $pending->company_name,
            'slug'                => $pending->slug,
            'status'              => 'active',
            'plan_id'             => $pending->plan_id,
            'billing_cycle'       => $pending->billing_cycle,
            'subscription_status' => 'active',
        ]);

        $tenant->domains()->create(['domain' => $pending->slug . '.' . config('app.base_domain')]);

        tenancy()->initialize($tenant);

        $user = \App\Models\User::create([
            'name'      => $pending->name,
            'email'     => $pending->email,
            'password'  => $pending->password,
            'role'      => 'super_admin',
            'is_active' => true,
        ]);

        $user->assignRole('super_admin');

        Artisan::call('db:seed', ['--class' => TenantSeeder::class, '--force' => true]);

        $user->notify(new SubscriptionActivatedNotification($tenant));

        event(new UserRegistered($user));

        tenancy()->end();

        $pending->update(['status' => 'completed']);

        return $tenant;
    }
}
