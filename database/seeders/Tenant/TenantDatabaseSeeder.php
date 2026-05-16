<?php


namespace Database\Seeders\Tenant;

use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Database\Seeder;

class TenantDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RoleAndPermissionSeeder::class);
    }
}
