<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VendorDeactivatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('⚠️ 4Rodz Food Court - Account Status Update')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('We\'re writing to inform you that your **4Rodz Food Court** vendor account has been temporarily deactivated by the administrator.')
            ->line('### 📋 What This Means:')
            ->line('❌ **Account Suspended:** Your restaurant is temporarily unavailable to customers')
            ->line('🚫 **No New Orders:** Customers cannot place orders from your restaurant')
            ->line('⏸️ **Dashboard Access:** Access to your vendor dashboard has been restricted')
            ->line('### 🔄 Next Steps:')
            ->line('If you believe this deactivation is an error or have questions about this decision, please contact our Food Court administration team immediately.')
            ->line('We\'ll work together to resolve any issues and get your restaurant back online as soon as possible.')
            ->line('Thank you for your understanding and patience. We appreciate your partnership with 4Rodz Food Court! 🙏')
            ->salutation('Best regards, The 4Rodz Food Court Administration Team');
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
