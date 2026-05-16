<?php


namespace App\Models;

use App\Support\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasUuid;

    protected $fillable = [
        'id',
        'stripe_invoice_id',
        'amount',
        'currency',
        'status',
        'paid_at',
        'invoice_pdf',
        'hosted_invoice_url',
        'period_start',
        'period_end',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'amount'       => 'integer',
            'paid_at'      => 'datetime',
            'period_start' => 'datetime',
            'period_end'   => 'datetime',
        ];
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function formattedAmount(): string
    {
        return number_format($this->amount / 100, 2) . ' ' . strtoupper($this->currency ?? 'usd');
    }
}
