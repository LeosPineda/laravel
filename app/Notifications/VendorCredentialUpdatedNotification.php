<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VendorCredentialUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public array $updatedFields,
        public ?string $newPassword = null
    ) {
        //
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('🔐 4Rodz Food Court - Account Credentials Updated')
            ->greeting('Hello '.$notifiable->name.'! 📝')
            ->line('Your **4Rodz Food Court** vendor account credentials have been updated by the administrator.')
            ->line('### 🔄 What Was Updated:');

        // Add specific fields that were updated
        if (in_array('name', $this->updatedFields)) {
            $message->line('👤 **Name:** Updated to: '.$notifiable->name);
        }

        if (in_array('email', $this->updatedFields)) {
            $message->line('📧 **Email:** Updated to: '.$notifiable->email);
        }

        if (in_array('password', $this->updatedFields)) {
            $message->line('🔐 **Password:** Has been changed')
                ->line("**New Password:** `{$this->newPassword}`")
                ->line('You can use these credentials to login to your dashboard.');
        }

        if (in_array('brand_name', $this->updatedFields)) {
            $vendor = $notifiable->vendor ?? null;
            if ($vendor) {
                $message->line('🏪 **Brand Name:** Updated to: '.$vendor->brand_name);
            }
        }

        $message->line('### 📧 Your Login Credentials:')
            ->line('📧 **Email:** '.$notifiable->email)
            ->line('🔐 **Password:** '.(in_array('password', $this->updatedFields) && $this->newPassword ? "`{$this->newPassword}`" : '(unchanged)'));

        $message->line('All account management is handled by our Food Court administration team.')
            ->line('If you have any questions about these changes, please contact the Food Court administration.')
            ->line('Thank you for your partnership with 4Rodz Food Court! 🙏')
            ->salutation('Best regards, The 4Rodz Food Court Administration Team');

        return $message;
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
