<?php


namespace App\Notifications\Billing;

use App\Models\Invoice;
use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class InvoicePaidNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Tenant $tenant,
        private readonly Invoice $invoice,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Payment received — ' . $this->invoice->formattedAmount())
            ->greeting("Hello {$notifiable->name}!")
            ->line("We received your payment of **{$this->invoice->formattedAmount()}** for {$this->tenant->name}.")
            ->when($this->invoice->hosted_invoice_url, fn ($m) => $m->action('View Invoice', $this->invoice->hosted_invoice_url))
            ->line('Thank you for your continued subscription!');
    }
}
