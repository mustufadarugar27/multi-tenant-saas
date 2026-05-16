<?php


namespace App\Http\Resources\Billing;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'amount'             => $this->formattedAmount(),
            'amount_raw'         => $this->amount,
            'currency'           => $this->currency,
            'status'             => $this->status,
            'is_paid'            => $this->isPaid(),
            'paid_at'            => $this->paid_at?->toISOString(),
            'hosted_invoice_url' => $this->hosted_invoice_url,
            'period_start'       => $this->period_start?->toISOString(),
            'period_end'         => $this->period_end?->toISOString(),
            'description'        => $this->description,
            'created_at'         => $this->created_at?->toISOString(),
        ];
    }
}
