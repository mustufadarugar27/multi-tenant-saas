<?php


namespace App\Notifications\Billing;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class SubscriptionActivatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Tenant $tenant,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $domain = $this->tenant->slug . '.' . config('app.base_domain');

        return (new MailMessage)
            ->subject('Welcome to ' . config('app.name') . ' — Your account is ready!')
            ->greeting("Hello {$notifiable->name}!")
            ->line("Your company **{$this->tenant->name}** has been successfully set up.")
            ->line("You can now access your workspace at: **{$domain}**")
            ->action('Go to your workspace', 'http://' . $domain)
            ->line('Thank you for choosing ' . config('app.name') . '!');
    }
}
