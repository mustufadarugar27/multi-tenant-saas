<?php


namespace App\Http\Controllers\Api\V1\Billing;

use App\Domain\Billing\Actions\HandleStripeWebhookAction;
use App\Domain\Billing\Services\StripeService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Stripe\Exception\SignatureVerificationException;

final class WebhookController extends Controller
{
    public function __construct(
        private readonly StripeService $stripe,
        private readonly HandleStripeWebhookAction $handler,
    ) {}

    public function handle(Request $request): Response
    {
        $signature = $request->header('Stripe-Signature', '');

        try {
            $event = $this->stripe->constructWebhookEvent(
                $request->getContent(),
                $signature,
            );
        } catch (SignatureVerificationException) {
            return response('Invalid signature', 400);
        }

        $this->handler->execute($event);

        return response('OK', 200);
    }
}
