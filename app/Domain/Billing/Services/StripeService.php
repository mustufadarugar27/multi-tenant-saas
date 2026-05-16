<?php


namespace App\Domain\Billing\Services;

use Stripe\Checkout\Session;
use Stripe\Customer;
use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Stripe\Subscription;
use Stripe\Webhook;

final class StripeService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function createCheckoutSession(
        string $priceId,
        string $tenantToken,
        string $successUrl,
        string $cancelUrl,
        ?string $customerId = null,
        array $metadata = [],
    ): Session {
        $params = [
            'mode'               => 'subscription',
            'line_items'         => [[
                'price'    => $priceId,
                'quantity' => 1,
            ]],
            'success_url'        => $successUrl . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'         => $cancelUrl . '?cancelled=1',
            'metadata'           => array_merge(['tenant_token' => $tenantToken], $metadata),
            'subscription_data'  => ['metadata' => ['tenant_token' => $tenantToken]],
            'allow_promotion_codes' => true,
        ];

        if ($customerId) {
            $params['customer'] = $customerId;
        }

        return Session::create($params);
    }

    public function createOrRetrieveCustomer(string $email, string $name): Customer
    {
        $existing = Customer::search(['query' => "email:'{$email}'"]);

        if ($existing->data) {
            return $existing->data[0];
        }

        return Customer::create(['email' => $email, 'name' => $name]);
    }

    public function retrieveSubscription(string $subscriptionId): Subscription
    {
        return Subscription::retrieve($subscriptionId);
    }

    /**
     * @throws SignatureVerificationException
     */
    public function constructWebhookEvent(string $payload, string $signature): Event
    {
        return Webhook::constructEvent(
            $payload,
            $signature,
            config('services.stripe.webhook_secret'),
        );
    }
}
