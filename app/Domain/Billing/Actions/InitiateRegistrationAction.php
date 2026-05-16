<?php


namespace App\Domain\Billing\Actions;

use App\Domain\Billing\DTOs\InitiateRegistrationDTO;
use App\Domain\Billing\Services\StripeService;
use App\Models\PendingRegistration;
use App\Models\Plan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class InitiateRegistrationAction
{
    public function __construct(
        private readonly StripeService $stripe,
    ) {}

    /**
     * @return array{type: string, pending: PendingRegistration, checkout_url?: string}
     */
    public function execute(InitiateRegistrationDTO $dto): array
    {
        $plan = Plan::where('slug', $dto->planSlug)->firstOrFail();

        PendingRegistration::where('slug', $dto->slug)
            ->where(fn ($q) => $q->where('status', '!=', 'completed')->orWhere('expires_at', '<=', now()))
            ->delete();

        $pending = PendingRegistration::create([
            'token'          => Str::uuid()->toString(),
            'plan_id'        => $plan->id,
            'billing_cycle'  => $dto->billingCycle,
            'company_name'   => $dto->companyName,
            'slug'           => $dto->slug,
            'name'     => $dto->adminName,
            'email'    => $dto->adminEmail,
            'password' => Hash::make($dto->adminPassword),
            'status'         => 'pending',
            'expires_at'     => now()->addHours(2),
        ]);

        if ($plan->is_free) {
            return ['type' => 'free', 'pending' => $pending];
        }

        $priceId = $plan->stripePriceId($dto->billingCycle);

        if ($priceId === null) {
            throw new \RuntimeException(
                "Stripe price ID not configured for plan [{$plan->slug}] / cycle [{$dto->billingCycle}]. " .
                'Set STRIPE_BUSINESS_MONTHLY_PRICE_ID / STRIPE_BUSINESS_YEARLY_PRICE_ID / ' .
                'STRIPE_ENTERPRISE_MONTHLY_PRICE_ID / STRIPE_ENTERPRISE_YEARLY_PRICE_ID in .env and re-run php artisan db:seed --class=PlanSeeder.'
            );
        }

        $session = $this->stripe->createCheckoutSession(
            priceId:    $priceId,
            tenantToken: $pending->token,
            successUrl: route('register.success'),
            cancelUrl:  route('register.index'),
        );

        $pending->update(['stripe_session_id' => $session->id]);

        return [
            'type'         => 'paid',
            'pending'      => $pending,
            'checkout_url' => $session->url,
        ];
    }
}
