<?php


namespace App\Http\Controllers\Web\Registration;

use App\Domain\Billing\Actions\InitiateRegistrationAction;
use App\Domain\Billing\Actions\ProvisionTenantFromPendingAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Registration\InitiateRegistrationRequest;
use App\Models\Plan;
use App\Models\PendingRegistration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public function __construct(
        private readonly InitiateRegistrationAction $initiate,
        private readonly ProvisionTenantFromPendingAction $provision,
    ) {}

    public function index(Request $request): View
    {
        $plans = Plan::active()->get();

        return view('register.index', [
            'plans'     => $plans,
            'cancelled' => $request->boolean('cancelled'),
        ]);
    }

    public function store(InitiateRegistrationRequest $request): RedirectResponse
    {
        $result = $this->initiate->execute($request->toDTO());

        if ($result['type'] === 'paid') {
            return redirect()->away($result['checkout_url']);
        }

        // Free plan — provision directly
        $this->provision->execute($result['pending']);

        return redirect()->route('register.success');
    }

    public function success(Request $request): View
    {
        $sessionId = $request->query('session_id');

        return view('register.success', [
            'sessionId' => $sessionId,
        ]);
    }

    public function expired(): View
    {
        return view('billing.expired');
    }
}
