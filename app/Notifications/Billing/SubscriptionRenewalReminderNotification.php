<?php


namespace App\Notifications\Billing;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class SubscriptionRenewalReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Tenant $tenant,
        private readonly int $daysLeft,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Your subscription expires in {$this->daysLeft} day(s)")
            ->greeting("Hello {$notifiable->name}!")
            ->line("Your subscription for **{$this->tenant->name}** will expire in **{$this->daysLeft} day(s)**.")
            ->line('Please renew your subscription to avoid any interruption to your service.')
            ->action('Manage Billing', route('billing.index'))
            ->line('Thank you for being a valued customer!');
    }
}
