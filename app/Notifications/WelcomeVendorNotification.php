<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeVendorNotification extends Notification implements ShouldQueue
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
            ->subject('🍴 Welcome to 4Rodz Food Court - Your Vendor Account is Ready!')
            ->greeting('Hello '.$notifiable->name.'! 👋')
            ->line('Welcome to **4Rodz Food Court** - where delicious food meets convenience! 🎉')
            ->line('Your vendor account has been successfully created and is ready to serve customers.')
            ->line('### Your Login Credentials:')
            ->line("📧 **Email:** `{$notifiable->email}`")
            ->line("🔐 **Password:** Please check with your administrator for your initial password")
            ->line('**Important:** You will be prompted to change your password on first login.')
            ->line('### What\'s Next?')
            ->line('🚀 **Start Your Journey:** Login and set up your restaurant profile')
            ->line('🍕 **Add Menu Items:** Create your food offerings with prices and descriptions')
            ->line('📊 **Manage Orders:** Receive and fulfill customer orders seamlessly')
            ->line('🎨 **Brand Your Store:** Upload your restaurant logo and customize your profile')
            ->line('Ready to delight customers with amazing food? Let\'s get started! 🌟')
            ->salutation('Happy serving! The 4Rodz Food Court Team');
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
