<?php


namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateTenantWithDomain extends Command
{
    protected $signature = 'tenant:create
                            {--name= : Tenant name (skips prompt)}
                            {--slug= : Tenant slug (skips prompt)}
                            {--status= : Tenant status: active|trial (default: active)}';

    protected $description = 'Create a tenant and optional custom domain step by step';

    public function handle(): int
    {
        $this->info('─── Step 1: Tenant ─────────────────────────────────');

        $name = $this->option('name') ?? $this->ask('Tenant name', 'Acme Corp');
        $slug = $this->option('slug') ?? $this->ask('Tenant slug', Str::slug($name));

        if (Tenant::where('slug', $slug)->exists()) {
            $this->error("Tenant with slug \"{$slug}\" already exists.");
            return self::FAILURE;
        }

        $statusInput = $this->option('status') ?? $this->choice('Status', ['active', 'trial'], 'active');
        $status = $statusInput;

        $tenantId = Str::uuid()->toString();

        $this->newLine();
        $this->info('─── Step 2: Database ───────────────────────────────');
        $this->line("  Database name  : <fg=cyan>{$tenantId}</>");
        $this->line('  Creating database and running migrations…');

        $tenant = Tenant::create([
            'id' => $tenantId,
            'name' => $name,
            'slug' => $slug,
            'status' => $status,
        ]);

        tenancy()->initialize($tenant);
        $tableCount = count(DB::select('SHOW TABLES'));
        tenancy()->end();

        $this->line("  <fg=green>✔</> Database created — {$tableCount} tables migrated.");
        $this->line("  Created tenant: <fg=green>{$tenant->name}</> (slug: {$tenant->slug}, id: {$tenant->id})");

        $this->newLine();
        $this->info('─── Step 3: Domain ──────────────────────────────────');

        $domainName = 'app.' . $slug . '.com';
        $domain = $tenant->domains()->create(['domain' => $domainName]);
        $this->line("  Attached domain: <fg=green>{$domain->domain}</>");

        $this->newLine();
        $this->info('─── Summary ────────────────────────────────────────');
        $this->table(
            ['Field', 'Value'],
            [
                ['Tenant',    $tenant->name],
                ['Slug',      $tenant->slug],
                ['Status',    $tenant->status->value],
                ['Database',  $tenant->id],
                ['Tables',    (string) $tableCount],
                ['Domain',    $domain->domain],
                ['Tenant ID', $tenant->id],
            ]
        );

        $this->newLine();
        $this->line('Test with:');
        $this->line("  curl -X POST http://localhost/api/v1/auth/login \\");
        $this->line("    -H 'X-Tenant-Slug: {$tenant->slug}' \\");
        $this->line("    -H 'Content-Type: application/json' \\");
        $this->line("    -d '{\"email\":\"user@example.com\",\"password\":\"secret\"}'");

        return self::SUCCESS;
    }
}
