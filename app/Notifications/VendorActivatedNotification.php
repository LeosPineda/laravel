<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VendorActivatedNotification extends Notification implements ShouldQueue
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
            ->subject('🍴 4Rodz Food Court - Your Account is Active! 🎉')
            ->greeting('Hello '.$notifiable->name.'! ✨')
            ->line('Fantastic news! Your **4Rodz Food Court** vendor account has been reactivated! 🚀')
            ->line('You are now officially open for business and ready to serve delicious food to our customers.')
            ->line('### 🔄 What This Means:')
            ->line('✅ **Orders Rolling In:** Customers can now place orders from your restaurant')
            ->line('📱 **Live Dashboard:** Access your vendor dashboard to manage orders and menu')
            ->line('🔔 **Notifications:** Get instant alerts for new orders')
            ->line('💰 **Revenue Streaming:** Start earning from customer orders')
            ->line('### 🎯 Ready to Serve?')
            ->line('Your account is now active and you can start receiving orders and delighting customers with your amazing food! 🌟')
            ->line('Welcome back to the food court community! 🎊')
            ->salutation('Happy selling! The 4Rodz Food Court Team');
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
