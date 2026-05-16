<?php


namespace App\Domain\Billing\DTOs;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class InitiateRegistrationDTO
{
    public function __construct(
        public readonly string $companyName,
        public readonly string $slug,
        public readonly string $adminName,
        public readonly string $adminEmail,
        public readonly string $adminPassword,
        public readonly string $planSlug,
        public readonly string $billingCycle,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            companyName:   $request->string('company_name')->toString(),
            slug:          $request->filled('slug')
                               ? $request->string('slug')->toString()
                               : Str::slug($request->string('company_name')->toString()),
            adminName:     $request->string('name')->toString(),
            adminEmail:    $request->string('email')->toString(),
            adminPassword: $request->string('password')->toString(),
            planSlug:      $request->string('plan_slug', 'starter')->toString(),
            billingCycle:  $request->string('billing_cycle', 'monthly')->toString(),
        );
    }
}
