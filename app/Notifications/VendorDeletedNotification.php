<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VendorDeletedNotification extends Notification
{
    public function __construct(
        public string $vendorName,
        public ?string $deletedAt = null
    ) {
        //
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $deletedAt = $this->deletedAt ?? now()->format('Y-m-d H:i:s');

        return (new MailMessage)
            ->subject('🍴 4Rodz Food Court - Your Vendor Account Has Been Deleted')
            ->greeting('Hello '.$notifiable->name.'! 📋')
            ->line('We regret to inform you that your **4Rodz Food Court** vendor account has been permanently deleted by the administrator.')
            ->line('### 🏪 **Account Details:**')
            ->line("**Restaurant Name:** {$this->vendorName}")
            ->line("**Deletion Date:** {$deletedAt}")
            ->line('### 📋 What This Means:')
            ->line('❌ **Account Terminated:** Your vendor account has been permanently deleted')
            ->line('🚫 **No Access:** You can no longer access your vendor dashboard')
            ->line('📱 **No Orders:** Customers can no longer place orders from your restaurant')
            ->line('💼 **Business Ended:** Your partnership with 4Rodz Food Court has ended')
            ->line('### 🤔 Need Help?')
            ->line('If you believe this deletion was made in error or you have questions about this decision, please contact the Food Court administration team immediately.')
            ->line('Thank you for your time as a valued vendor partner. We appreciate your business! 🙏')
            ->salutation('Best regards, The 4Rodz Food Court Administration Team');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'vendor_name' => $this->vendorName,
            'deleted_at' => $this->deletedAt,
            'type' => 'vendor_account_deleted',
        ];
    }
}
