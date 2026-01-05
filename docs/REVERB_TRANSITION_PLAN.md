# Laravel Reverb Transition Plan

## Current Setup (Pusher)
- **Broadcasting Driver**: Pusher (api-ap1.pusher.com)
- **Frontend**: Laravel Echo with pusher-js
- **Issues**: Inconsistent real-time updates, potential rate limiting

---

## Real-Time Usage by Role

### 🧑‍💼 VENDOR (4 files)
| File | Channel | Events Listened |
|------|---------|-----------------|
| `pages/vendor/Dashboard.vue` | `vendor-orders.{vendorId}` | `.VendorNewOrder` |
| `pages/vendor/IncomingOrders.vue` | `vendor-orders.{vendorId}` | `.VendorNewOrder`, `.OrderStatusChanged` |
| `pages/vendor/Orders.vue` | `vendor-orders.{vendorId}` | `.VendorNewOrder`, `.OrderStatusChanged` |
| `layouts/vendor/VendorLayout.vue` | `vendor-toasts.{vendorId}` | Toast notifications |
| `components/vendor/VendorNotificationBell.vue` | `vendor-notifications.{vendorId}` | `.OrderReceived` |

**Purpose**: 
- New order alerts when customer places order
- Order status updates (cancellations)
- Toast notifications for new orders

### 👤 CUSTOMER (2 files)
| File | Channel | Events Listened |
|------|---------|-----------------|
| `pages/customer/Cart.vue` | `customer-orders.{userId}` | `.OrderStatusChanged` |
| `components/customer/CustomerNotificationBell.vue` | `customer-orders.{userId}` | `.OrderStatusChanged` |

**Purpose**:
- Order accepted/declined notifications
- Ready for pickup notifications

### 👑 SUPERADMIN (0 files)
- **No real-time features currently**

### 🔐 AUTH (0 files)
- **No real-time features**

---

## Backend Events (3 files)

### 1. `app/Events/OrderReceived.php`
- **Broadcasts to**: `vendor-orders.{vendorId}`, `vendor-notifications.{vendorId}`, `vendor-toasts.{vendorId}`
- **Event name**: `.VendorNewOrder` / `.OrderReceived`
- **Triggered by**: `Customer/OrderController.php` - when customer places order

### 2. `app/Events/OrderStatusChanged.php`
- **Broadcasts to**: `customer-orders.{customerId}`, `vendor-orders.{vendorId}`
- **Event name**: `.OrderStatusChanged`
- **Triggered by**: `Vendor/OrderController.php` - when vendor accepts/declines/marks ready

### 3. `app/Events/NewNotification.php`
- **General notification event**
- **Currently may not be actively used**

---

## Broadcast Channels (`routes/channels.php`)

| Channel | Authorization |
|---------|--------------|
| `vendor-notifications.{vendorId}` | Vendor with matching ID |
| `vendor-orders.{vendorId}` | Vendor with matching ID |
| `vendor-toasts.{vendorId}` | Vendor with matching ID |
| `customer-orders.{userId}` | Customer with matching ID |
| `public` | All authenticated users |

---

## Files to Modify for Reverb

### Configuration Files
1. **`config/broadcasting.php`** - Add reverb connection
2. **`.env`** - Update BROADCAST_DRIVER and Reverb settings
3. **`resources/js/echo.ts`** - Change broadcaster from pusher to reverb

### No Changes Needed
- ✅ `routes/channels.php` - Channel authorization stays the same
- ✅ `app/Events/*` - Events work the same with Reverb
- ✅ All frontend Vue files - They use `window.Echo` which abstracts the broadcaster

---

## Installation Steps

### Step 1: Install Reverb
```bash
composer require laravel/reverb
php artisan reverb:install
```

### Step 2: Update `.env`
```env
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=my-app-id
REVERB_APP_KEY=my-app-key
REVERB_APP_SECRET=my-app-secret
REVERB_HOST="localhost"
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

### Step 3: Update `config/broadcasting.php`
```php
'reverb' => [
    'driver' => 'reverb',
    'key' => env('REVERB_APP_KEY'),
    'secret' => env('REVERB_APP_SECRET'),
    'app_id' => env('REVERB_APP_ID'),
    'options' => [
        'host' => env('REVERB_HOST'),
        'port' => env('REVERB_PORT', 443),
        'scheme' => env('REVERB_SCHEME', 'https'),
        'useTLS' => env('REVERB_SCHEME', 'https') === 'https',
    ],
    'client_options' => [
        // Guzzle client options: https://docs.guzzlephp.org/en/stable/request-options.html
    ],
],
```

### Step 4: Update `resources/js/echo.ts`
```typescript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

declare global {
    interface Window {
        Pusher: typeof Pusher;
        Echo: Echo<'reverb'>;
    }
}

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
    },
});

export default window.Echo;
```

### Step 5: Run Reverb Server
```bash
php artisan reverb:start
```

Or for development with auto-reload:
```bash
php artisan reverb:start --debug
```

---

## Running in Development (Laragon)

You'll need to run these commands:
1. **Terminal 1**: `php artisan serve` (Laravel)
2. **Terminal 2**: `php artisan reverb:start --debug` (WebSocket server)
3. **Terminal 3**: `npm run dev` (Vite)

Or use `composer run dev` if configured.

---

## Summary

| Item | Count |
|------|-------|
| Frontend files using Echo | 8 |
| Backend broadcast events | 3 |
| Broadcast channels | 5 |
| Files to modify | 3 (config, .env, echo.ts) |
| Roles using real-time | 2 (Vendor, Customer) |
