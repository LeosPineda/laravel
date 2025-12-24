<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeCustomerNotification extends Notification implements ShouldQueue
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
            ->subject('🍴 Welcome to 4Rodz Food Court - Your Food Adventure Begins!')
            ->greeting('Hello '.$notifiable->name.'! 🎉')
            ->line('Welcome to **4Rodz Food Court** - your ultimate destination for delicious food! 🌟')
            ->line('Your account has been created successfully and you\'re ready to explore amazing vendors and mouth-watering dishes.')
            ->line('### 🍽️ What You Can Do:')
            ->line('🛒 **Browse Vendors:** Discover your favorite restaurants and food stalls')
            ->line('🍕 **Order Easily:** Add items to your cart and place orders seamlessly')
            ->line('⚡ **Real-time Updates:** Track your order status with instant notifications')
            ->line('💳 **Multiple Payment Options:** Pay at cashier or via QR code')
            ->line('📱 **Mobile Friendly:** Order on any device, anywhere')
            ->action('🛒 Start Browsing Vendors', url('/customer/home'))
            ->line('Ready to satisfy your cravings? Explore our vendors and place your first order! 🚀')
            ->salutation('Bon appétit! The 4Rodz Food Court Team');
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
