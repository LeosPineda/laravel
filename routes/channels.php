<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\User;

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

// Public channel for general notifications (if needed)
Broadcast::channel('public', function (User $user) {
    return true; // Public access for general notifications
});
