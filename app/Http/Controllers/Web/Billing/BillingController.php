<?php


namespace App\Http\Controllers\Web\Billing;

use App\Domain\Billing\Services\InvoiceService;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

final class BillingController extends Controller
{
    public function __construct(
        private readonly InvoiceService $invoiceService,
    ) {}

    public function index(Request $request): View
    {
        $tenant   = tenant();
        $invoices = Invoice::latest()->paginate(10);

        return view('billing.index', [
            'tenant'   => $tenant,
            'invoices' => $invoices,
        ]);
    }

    public function downloadInvoice(Invoice $invoice): Response
    {
        $tenant = tenant();

        return $this->invoiceService->download($tenant, $invoice);
    }
}
