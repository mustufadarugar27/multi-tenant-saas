<?php


namespace App\Notifications\Billing;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionExpiredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Tenant $tenant,
        public readonly string $planName,
        public readonly \DateTimeInterface $expiredAt,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail', 'broadcast'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Your subscription has expired')
            ->greeting("Hello {$notifiable->name},")
            ->line("Your **{$this->planName}** subscription expired on {$this->expiredAt->format('F j, Y')}.")
            ->line('Some features may be restricted until you renew your subscription.')
            ->action('Renew Subscription', url('/billing/renew'))
            ->line('Contact support if you have any questions.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'subscription_expired',
            'plan_name' => $this->planName,
            'expired_at' => $this->expiredAt->format(\DateTimeInterface::ISO8601),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'subscription_expired',
            'plan_name' => $this->planName,
            'expired_at' => $this->expiredAt->format(\DateTimeInterface::ISO8601),
            'message' => "Your {$this->planName} subscription has expired.",
        ]);
    }
}
