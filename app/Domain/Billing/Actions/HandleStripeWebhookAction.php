<?php


namespace App\Domain\Billing\Actions;

use App\Models\Invoice;
use App\Models\PendingRegistration;
use App\Models\Tenant;
use App\Notifications\Billing\InvoicePaidNotification;
use Illuminate\Support\Facades\Log;
use Stripe\Event;

final class HandleStripeWebhookAction
{
    public function __construct(
        private readonly ProvisionTenantFromPendingAction $provision,
    ) {}

    public function execute(Event $event): void
    {
        match ($event->type) {
            'checkout.session.completed'  => $this->handleCheckoutCompleted($event),
            'customer.subscription.updated' => $this->handleSubscriptionUpdated($event),
            'customer.subscription.deleted' => $this->handleSubscriptionDeleted($event),
            'invoice.payment_succeeded'   => $this->handleInvoicePaymentSucceeded($event),
            'invoice.payment_failed'      => $this->handleInvoicePaymentFailed($event),
            default => null,
        };
    }

    private function handleCheckoutCompleted(Event $event): void
    {
        $session = $event->data->object;
        $token   = $session->metadata->tenant_token ?? null;

        if (! $token) {
            Log::warning('checkout.session.completed: missing tenant_token in metadata');
            return;
        }

        $pending = PendingRegistration::where('token', $token)->first();

        if (! $pending || ! $pending->isPending()) {
            Log::warning("checkout.session.completed: pending registration not found or not pending for token {$token}");
            return;
        }

        $this->provision->execute($pending);
    }

    private function handleSubscriptionUpdated(Event $event): void
    {
        $subscription = $event->data->object;
        $tenant = Tenant::where('stripe_subscription_id', $subscription->id)->first();

        if (! $tenant) {
            return;
        }

        $status = match ($subscription->status) {
            'active'     => 'active',
            'trialing'   => 'trialing',
            'past_due'   => 'past_due',
            'canceled'   => 'cancelled',
            'incomplete' => 'incomplete',
            default      => 'incomplete',
        };

        $tenant->update([
            'subscription_status' => $status,
            'subscription_ends_at' => $subscription->current_period_end
                ? \Carbon\Carbon::createFromTimestamp($subscription->current_period_end)
                : null,
        ]);
    }

    private function handleSubscriptionDeleted(Event $event): void
    {
        $subscription = $event->data->object;
        $tenant = Tenant::where('stripe_subscription_id', $subscription->id)->first();

        if (! $tenant) {
            return;
        }

        $tenant->update([
            'subscription_status' => 'cancelled',
            'grace_period_ends_at' => now()->addDays(7),
        ]);
    }

    private function handleInvoicePaymentSucceeded(Event $event): void
    {
        $stripeInvoice = $event->data->object;
        $customerId    = $stripeInvoice->customer;

        $tenant = Tenant::where('stripe_customer_id', $customerId)->first();

        if (! $tenant) {
            return;
        }

        tenancy()->initialize($tenant);

        $invoice = Invoice::updateOrCreate(
            ['stripe_invoice_id' => $stripeInvoice->id],
            [
                'amount'             => $stripeInvoice->amount_paid,
                'currency'           => $stripeInvoice->currency,
                'status'             => 'paid',
                'paid_at'            => now(),
                'hosted_invoice_url' => $stripeInvoice->hosted_invoice_url,
                'invoice_pdf'        => null,
                'period_start'       => $stripeInvoice->period_start
                    ? \Carbon\Carbon::createFromTimestamp($stripeInvoice->period_start)
                    : null,
                'period_end'         => $stripeInvoice->period_end
                    ? \Carbon\Carbon::createFromTimestamp($stripeInvoice->period_end)
                    : null,
                'description'        => $stripeInvoice->description,
            ],
        );

        $adminUser = \App\Models\User::role('SuperAdmin')->first();
        $adminUser?->notify(new InvoicePaidNotification($tenant, $invoice));

        tenancy()->end();
    }

    private function handleInvoicePaymentFailed(Event $event): void
    {
        $stripeInvoice = $event->data->object;
        $customerId    = $stripeInvoice->customer;

        $tenant = Tenant::where('stripe_customer_id', $customerId)->first();

        if (! $tenant) {
            return;
        }

        $tenant->update(['subscription_status' => 'past_due']);
    }
}
