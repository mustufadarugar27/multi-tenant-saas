<?php


namespace App\Domain\Billing\Services;

use App\Models\Invoice;
use App\Models\Tenant;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

final class InvoiceService
{
    public function generatePdf(Tenant $tenant, Invoice $invoice): string
    {
        $pdf = Pdf::loadView('billing.invoice-pdf', [
            'tenant'  => $tenant,
            'invoice' => $invoice,
        ]);

        $path = "tenants/{$tenant->id}/invoices/{$invoice->id}.pdf";

        Storage::put($path, $pdf->output());

        return $path;
    }

    public function download(Tenant $tenant, Invoice $invoice): \Symfony\Component\HttpFoundation\Response
    {
        if ($invoice->invoice_pdf && Storage::exists($invoice->invoice_pdf)) {
            return response()->download(
                Storage::path($invoice->invoice_pdf),
                "invoice-{$invoice->id}.pdf",
            );
        }

        $path = $this->generatePdf($tenant, $invoice);

        return response()->download(
            Storage::path($path),
            "invoice-{$invoice->id}.pdf",
        );
    }
}
