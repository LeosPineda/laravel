<?php

use App\Http\Controllers\Superadmin\DashboardController as SuperadminDashboardController;
use App\Http\Controllers\Superadmin\VendorController as SuperadminVendorController;
use App\Http\Controllers\Vendor\AnalyticsController;
use App\Http\Controllers\Vendor\OrderController as VendorOrderController;
use App\Http\Controllers\Vendor\ProductController;
use App\Http\Controllers\Vendor\AddonController;
use App\Http\Controllers\Vendor\NotificationController as VendorNotificationController;
use App\Http\Controllers\Vendor\QrController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;
use Inertia\Inertia;

// ✅ CRITICAL: Broadcasting authentication route (required for real-time notifications)
Route::post('/broadcasting/auth', function () {
    return Broadcast::auth(request());
});

// Public route - redirect to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Auth GET routes for Inertia pages (Fortify only provides POST routes when views: false)
Route::get('/login', function () {
    return Inertia::render('auth/Login');
})->name('login');

Route::get('/register', function () {
    return Inertia::render('auth/Register');
})->name('register');

Route::get('/forgot-password', function () {
    return Inertia::render('auth/ForgotPassword');
})->name('forgot-password');

Route::get('/reset-password', function () {
    return Inertia::render('auth/ResetPassword');
})->name('reset-password');

// Password reset route for email notifications (required by Laravel's ResetPassword notification)
Route::get('/reset-password/{token}', function ($token) {
    return Inertia::render('auth/ResetPassword', [
        'token' => $token,
        'email' => request('email')
    ]);
})->name('password.reset');

// Auth POST routes are handled by Fortify
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('login');
})->name('logout');

// Dashboard redirect based on role - CUSTOMERS GO DIRECTLY TO BROWSE
Route::get('/dashboard', function () {
    $user = Auth::user();

    return match ($user->role) {
        'superadmin' => redirect()->route('superadmin.dashboard'),
        'vendor' => redirect()->route('vendor.dashboard'),
        'customer' => redirect()->route('customer.browse'), // ✅ CUSTOMERS GO TO BROWSE, NOT DASHBOARD
        default => redirect()->route('login'),
    };
})->middleware('auth')->name('dashboard');

// Superadmin routes (no email verification needed)
Route::middleware(['auth', 'role:superadmin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/dashboard', [SuperadminDashboardController::class, 'index'])->name('dashboard');

    Route::get('/vendors', [SuperadminVendorController::class, 'index'])->name('vendors.index');
    Route::get('/vendors/create', [SuperadminVendorController::class, 'create'])->name('vendors.create');
    Route::post('/vendors', [SuperadminVendorController::class, 'store'])->name('vendors.store');
    Route::get('/vendors/{vendor}/edit', [SuperadminVendorController::class, 'edit'])->name('vendors.edit');
    Route::post('/vendors/{vendor}', [SuperadminVendorController::class, 'update'])->name('vendors.update');
    Route::patch('/vendors/{vendor}/toggle-active', [SuperadminVendorController::class, 'toggleActive'])->name('vendors.toggle-active');
    Route::delete('/vendors/{vendor}', [SuperadminVendorController::class, 'destroy'])->name('vendors.destroy');
});

// ============================================
// API ROUTES - MOVED FROM api.php TO web.php
// ============================================

// Public API routes (no authentication required)
Route::post('/api/test', function () {
    return response()->json(['message' => 'API is working']);
});

// Vendor API Routes - SESSION-BASED AUTHENTICATION
Route::middleware(['auth', 'role:vendor', 'throttle:60,1'])->prefix('api/vendor')->name('vendor.')->group(function () {
    // Analytics
    Route::get('/analytics/sales', [AnalyticsController::class, 'sales'])->name('analytics.sales');
    Route::get('/analytics/best-sellers', [AnalyticsController::class, 'bestSellers'])->name('analytics.best-sellers');
    Route::get('/analytics/order-metrics', [AnalyticsController::class, 'orderMetrics'])->name('analytics.order-metrics');
    Route::get('/analytics/revenue', [AnalyticsController::class, 'revenue'])->name('analytics.revenue');
    Route::get('/analytics/profit', [AnalyticsController::class, 'profit'])->name('analytics.profit');

    // Orders - All order management operations
    Route::get('/orders', [VendorOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/stats', [VendorOrderController::class, 'stats'])->name('orders.stats');
    Route::get('/orders/{order}', [VendorOrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/accept', [VendorOrderController::class, 'accept'])->name('orders.accept');
    Route::patch('/orders/{order}/decline', [VendorOrderController::class, 'decline'])->name('orders.decline');
    Route::patch('/orders/{order}/ready', [VendorOrderController::class, 'markReady'])->name('orders.ready');
    Route::delete('/orders/{order}', [VendorOrderController::class, 'destroy'])->name('orders.destroy');
    Route::delete('/orders/batch', [VendorOrderController::class, 'batchDelete'])->name('orders.batch-delete');

    // Receipt functionality for vendors
    Route::get('/orders/{order}/receipt/download', [VendorOrderController::class, 'downloadReceipt'])->name('orders.receipt.download');
    Route::get('/orders/{order}/receipt/stream', [VendorOrderController::class, 'streamReceipt'])->name('orders.receipt.stream');

    // Products
    Route::get('/products/categories', [ProductController::class, 'getCategories'])->name('products.categories');
    Route::apiResource('products', ProductController::class);
    Route::patch('/products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('products.toggle');
    Route::post('/products/bulk', [ProductController::class, 'bulkToggle'])->name('products.bulk');

    // Addons
    Route::get('/products/{product}/addons', [AddonController::class, 'index'])->name('addons.index');
    Route::post('/products/{product}/addons', [AddonController::class, 'store'])->name('addons.store');
    Route::get('/products/{product}/addons/stats', [AddonController::class, 'getStatistics'])->name('addons.stats');
    Route::get('/addons/{addon}', [AddonController::class, 'show'])->name('addons.show');
    Route::put('/addons/{addon}', [AddonController::class, 'update'])->name('addons.update');
    Route::delete('/addons/{addon}', [AddonController::class, 'destroy'])->name('addons.destroy');
    Route::patch('/addons/{addon}/toggle', [AddonController::class, 'toggleStatus'])->name('addons.toggle');
    Route::post('/addons/bulk', [AddonController::class, 'bulkToggle'])->name('addons.bulk');

    // QR Code
    Route::get('/qr', [QrController::class, 'show'])->name('qr.show');
    Route::post('/qr', [QrController::class, 'upload'])->name('qr.upload');
    Route::put('/qr', [QrController::class, 'update'])->name('qr.update');
    Route::delete('/qr', [QrController::class, 'destroy'])->name('qr.destroy');
    Route::get('/qr/preview', [QrController::class, 'preview'])->name('qr.preview');
    Route::get('/qr/stats', [QrController::class, 'getStatistics'])->name('qr.stats');
    Route::get('/qr/public-url', [QrController::class, 'getPublicUrl'])->name('qr.public-url');
    Route::post('/qr/validate', [QrController::class, 'validate'])->name('qr.validate');
    Route::patch('/qr/mobile', [QrController::class, 'updateMobileNumber'])->name('qr.mobile');

    // Notifications
    Route::get('/notifications', [VendorNotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/count', [VendorNotificationController::class, 'count'])->name('notifications.count');
    Route::get('/notifications/recent', [VendorNotificationController::class, 'getRecent'])->name('notifications.recent');
    Route::get('/notifications/stats', [VendorNotificationController::class, 'getStatistics'])->name('notifications.stats');
    Route::get('/notifications/types', [VendorNotificationController::class, 'getTypes'])->name('notifications.types');
    Route::post('/notifications', [VendorNotificationController::class, 'create'])->name('notifications.create');
    Route::post('/notifications/mark-all-read', [VendorNotificationController::class, 'markAllAsRead'])->name('notifications.mark-all');
    Route::post('/notifications/bulk', [VendorNotificationController::class, 'bulkOperation'])->name('notifications.bulk');
    Route::post('/notifications/cleanup', [VendorNotificationController::class, 'cleanup'])->name('notifications.cleanup');
    Route::post('/notifications/{notification}/read', [VendorNotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::delete('/notifications/{notification}', [VendorNotificationController::class, 'destroy'])->name('notifications.destroy');
});

// ============================================
// CUSTOMER API ROUTES - SESSION-BASED AUTHENTICATION
// ============================================
Route::middleware(['auth', 'role:customer', 'throttle:60,1'])->prefix('api/customer')->name('customer.')->group(function () {
    // Orders - Customer order management
    Route::get('/orders', [\App\Http\Controllers\Customer\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [\App\Http\Controllers\Customer\OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders', [\App\Http\Controllers\Customer\OrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/{order}/track', [\App\Http\Controllers\Customer\OrderController::class, 'track'])->name('orders.track');
    Route::get('/orders/history', [\App\Http\Controllers\Customer\OrderController::class, 'history'])->name('orders.history');
    Route::get('/orders/{order}/receipt/download', [\App\Http\Controllers\Customer\OrderController::class, 'downloadReceipt'])->name('orders.receipt.download');
    Route::get('/orders/{order}/receipt/stream', [\App\Http\Controllers\Customer\OrderController::class, 'streamReceipt'])->name('orders.receipt.stream');
    Route::post('/orders/{order}/cancel', [\App\Http\Controllers\Customer\OrderController::class, 'cancel'])->name('orders.cancel');

    // Menu - Vendor products and menu browsing
    Route::get('/menu/vendors', [\App\Http\Controllers\Customer\MenuController::class, 'vendors'])->name('menu.vendors');
    Route::get('/menu/vendors/{vendor}', [\App\Http\Controllers\Customer\MenuController::class, 'showVendor'])->name('menu.vendors.show');
    Route::get('/menu/vendors/{vendor}/products', [\App\Http\Controllers\Customer\MenuController::class, 'vendorProducts'])->name('menu.vendors.products');
    Route::get('/menu/products', [\App\Http\Controllers\Customer\MenuController::class, 'allProducts'])->name('menu.products');
    Route::get('/menu/products/{product}', [\App\Http\Controllers\Customer\MenuController::class, 'showProduct'])->name('menu.products.show');

    // Cart - Shopping cart management
    Route::get('/cart', [\App\Http\Controllers\Customer\CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/items', [\App\Http\Controllers\Customer\CartController::class, 'addItem'])->name('cart.items.store');
    Route::put('/cart/items/{item}', [\App\Http\Controllers\Customer\CartController::class, 'updateItem'])->name('cart.items.update');
    Route::delete('/cart/items/{item}', [\App\Http\Controllers\Customer\CartController::class, 'removeItem'])->name('cart.items.destroy');
    Route::delete('/cart/items', [\App\Http\Controllers\Customer\CartController::class, 'clearCart'])->name('cart.clear');
    Route::post('/cart/merge', [\App\Http\Controllers\Customer\CartController::class, 'mergeCart'])->name('cart.merge');

    // Notifications - Customer notification management
    Route::get('/notifications', [\App\Http\Controllers\Customer\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/count', [\App\Http\Controllers\Customer\NotificationController::class, 'count'])->name('notifications.count');
    Route::get('/notifications/recent', [\App\Http\Controllers\Customer\NotificationController::class, 'recent'])->name('notifications.recent');
    Route::post('/notifications/mark-all-read', [\App\Http\Controllers\Customer\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all');
    Route::post('/notifications/{notification}/read', [\App\Http\Controllers\Customer\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::delete('/notifications/{notification}', [\App\Http\Controllers\Customer\NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::delete('/notifications', [\App\Http\Controllers\Customer\NotificationController::class, 'clearAll'])->name('notifications.clear-all');
    Route::post('/notifications/cleanup', [\App\Http\Controllers\Customer\NotificationController::class, 'cleanup'])->name('notifications.cleanup');
});

// Vendor routes (frontend for vendor management)
Route::middleware(['auth', 'role:vendor'])->prefix('vendor')->name('vendor.')->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('vendor/Dashboard');
    })->name('dashboard');

    Route::get('/orders', function () {
        return Inertia::render('vendor/Orders');
    })->name('orders');

    Route::get('/incoming-orders', function () {
        return Inertia::render('vendor/IncomingOrders');
    })->name('incoming-orders');

    Route::get('/order-history', function () {
        return Inertia::render('vendor/OrderHistory');
    })->name('order-history');

    Route::get('/products', function () {
        return Inertia::render('vendor/Products');
    })->name('products');

    Route::get('/analytics', function () {
        return Inertia::render('vendor/Analytics');
    })->name('analytics');

    // ✅ ADDED: Missing notifications route that was causing 404
    Route::get('/notifications', function () {
        return Inertia::render('vendor/Notifications');
    })->name('notifications');

    Route::get('/qr', function () {
        return Inertia::render('vendor/QrCode');
    })->name('qr');
});

// ============================================
// CUSTOMER FRONTEND ROUTES - SIMPLIFIED FLOW
// ============================================
Route::middleware(['auth', 'role:customer'])->prefix('customer')->name('customer.')->group(function () {
    // Browse - Main vendor selection page (customers land here)
    Route::get('/browse', function () {
        return Inertia::render('customer/Browse');
    })->name('browse');

    // Cart - Shopping cart management
    Route::get('/cart', function () {
        return Inertia::render('customer/Cart');
    })->name('cart');

    // Notifications - Customer notification center
    Route::get('/notifications', function () {
        return Inertia::render('customer/Notifications');
    })->name('notifications');

    // Profile - Customer account settings
    Route::get('/profile', function () {
        return Inertia::render('customer/Profile');
    })->name('profile');
});

require __DIR__.'/settings.php';
