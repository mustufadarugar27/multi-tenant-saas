<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $invoice->id }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 13px; color: #1a1a2e; }
        .container { padding: 40px; max-width: 700px; margin: 0 auto; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 40px; }
        .logo { font-size: 24px; font-weight: 700; color: #4f46e5; }
        .invoice-title { text-align: right; }
        .invoice-title h1 { font-size: 28px; font-weight: 700; color: #4f46e5; }
        .invoice-title p { color: #6b7280; margin-top: 4px; }
        .divider { border: none; border-top: 2px solid #e5e7eb; margin: 24px 0; }
        .parties { display: flex; justify-content: space-between; margin-bottom: 40px; }
        .party h3 { font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: #9ca3af; margin-bottom: 8px; }
        .party p { line-height: 1.6; }
        .party strong { font-weight: 600; font-size: 15px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        thead tr { background: #f9fafb; }
        th { padding: 12px 16px; text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280; border-bottom: 1px solid #e5e7eb; }
        td { padding: 14px 16px; border-bottom: 1px solid #f3f4f6; }
        .amount-right { text-align: right; }
        .totals { margin-left: auto; width: 240px; }
        .totals tr td { border: none; padding: 6px 16px; }
        .totals tr.total td { font-size: 16px; font-weight: 700; color: #4f46e5; border-top: 2px solid #e5e7eb; padding-top: 12px; }
        .badge { display: inline-block; padding: 4px 12px; border-radius: 9999px; font-size: 11px; font-weight: 600; }
        .badge-paid { background: #d1fae5; color: #065f46; }
        .badge-open { background: #fef3c7; color: #92400e; }
        .footer { margin-top: 60px; text-align: center; color: #9ca3af; font-size: 11px; border-top: 1px solid #e5e7eb; padding-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">{{ config('app.name') }}</div>
            <div class="invoice-title">
                <h1>INVOICE</h1>
                <p>#{{ strtoupper(substr($invoice->id, 0, 8)) }}</p>
                @if($invoice->paid_at)
                    <p>Paid on {{ $invoice->paid_at->format('F j, Y') }}</p>
                @endif
            </div>
        </div>

        <hr class="divider">

        <div class="parties">
            <div class="party">
                <h3>From</h3>
                <p>
                    <strong>{{ config('app.name') }}</strong><br>
                    {{ config('app.base_domain') }}
                </p>
            </div>
            <div class="party" style="text-align: right;">
                <h3>Bill To</h3>
                <p>
                    <strong>{{ $tenant->name }}</strong><br>
                    {{ $tenant->slug }}.{{ config('app.base_domain') }}
                </p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Period</th>
                    <th>Status</th>
                    <th class="amount-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $invoice->description ?? 'Subscription' }}</td>
                    <td>
                        @if($invoice->period_start && $invoice->period_end)
                            {{ $invoice->period_start->format('M j') }} – {{ $invoice->period_end->format('M j, Y') }}
                        @else
                            —
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $invoice->isPaid() ? 'badge-paid' : 'badge-open' }}">
                            {{ strtoupper($invoice->status) }}
                        </span>
                    </td>
                    <td class="amount-right">{{ $invoice->formattedAmount() }}</td>
                </tr>
            </tbody>
        </table>

        <table class="totals">
            <tr>
                <td>Subtotal</td>
                <td class="amount-right">{{ $invoice->formattedAmount() }}</td>
            </tr>
            <tr class="total">
                <td>Total</td>
                <td class="amount-right">{{ $invoice->formattedAmount() }}</td>
            </tr>
        </table>

        <div class="footer">
            <p>Thank you for your business! — {{ config('app.name') }} &bull; {{ config('app.base_domain') }}</p>
        </div>
    </div>
</body>
</html>
