<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application uses. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// ✅ VENDOR NOTIFICATIONS: Private channel for vendor-specific order alerts
Broadcast::channel('vendor-notifications.{vendorId}', function (User $user, int $vendorId) {
    // Only allow vendor to listen to their own notifications
    return $user->vendor && $user->vendor->id === $vendorId && $user->role === 'vendor';
});

// ✅ VENDOR ORDERS: Private channel for real-time order updates
Broadcast::channel('vendor-orders.{vendorId}', function (User $user, int $vendorId) {
    // Only allow vendor to listen to their own orders
    return $user->vendor && $user->vendor->id === $vendorId && $user->role === 'vendor';
});

// ✅ VENDOR TOASTS: Private channel for vendor toast notifications (new orders, cancellations)
Broadcast::channel('vendor-toasts.{vendorId}', function (User $user, int $vendorId) {
    // Only allow vendor to listen to their own toasts
    return $user->vendor && $user->vendor->id === $vendorId && $user->role === 'vendor';
});

// ✅ CUSTOMER ORDERS: Private channel for customer order status updates
Broadcast::channel('customer-orders.{userId}', function (User $user, int $userId) {
    // Only allow customers to listen to their own order updates
    return $user->id === $userId && $user->role === 'customer';
});

// Public channel for general notifications (if needed)
Broadcast::channel('public', function (User $user) {
    return true; // Public access for general notifications
});
