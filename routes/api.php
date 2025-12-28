<?php

use App\Http\Controllers\Vendor\AnalyticsController;
use App\Http\Controllers\Vendor\OrderController as VendorOrderController;
use App\Http\Controllers\Vendor\ProductController;
use App\Http\Controllers\Vendor\AddonController;
use App\Http\Controllers\Vendor\NotificationController as VendorNotificationController;
use App\Http\Controllers\Vendor\QrController;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\MenuController;
use App\Http\Controllers\Customer\NotificationController as CustomerNotificationController;
use App\Http\Controllers\Customer\OrderController as CustomerOrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public API routes (no authentication required)
Route::post('/test', function () {
    return response()->json(['message' => 'API is working']);
});

// Vendor API Routes (60 requests per minute)
// Using 'web' auth since this is an Inertia app with session-based authentication
Route::middleware(['web', 'auth', 'role:vendor', 'throttle:60,1'])->prefix('vendor')->name('vendor.')->group(function () {
    // Analytics
    Route::get('/analytics/sales', [AnalyticsController::class, 'sales'])->name('analytics.sales');
    Route::get('/analytics/best-sellers', [AnalyticsController::class, 'bestSellers'])->name('analytics.best-sellers');
    Route::get('/analytics/order-metrics', [AnalyticsController::class, 'orderMetrics'])->name('analytics.order-metrics');
    Route::get('/analytics/revenue', [AnalyticsController::class, 'revenue'])->name('analytics.revenue');
    Route::get('/analytics/profit', [AnalyticsController::class, 'profit'])->name('analytics.profit');

    // Orders - Core order management functionality
    Route::get('/orders', [VendorOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/stats', [VendorOrderController::class, 'stats'])->name('orders.stats');
    Route::get('/orders/{order}', [VendorOrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/accept', [VendorOrderController::class, 'accept'])->name('orders.accept');
    Route::patch('/orders/{order}/decline', [VendorOrderController::class, 'decline'])->name('orders.decline');
    Route::patch('/orders/{order}/ready', [VendorOrderController::class, 'markReady'])->name('orders.ready');
    Route::delete('/orders/batch', [VendorOrderController::class, 'batchDelete'])->name('orders.batch-delete');

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

// Customer API Routes (60 requests per minute)
// Using 'web' auth since this is an Inertia app with session-based authentication
Route::middleware(['web', 'auth', 'role:customer', 'throttle:60,1'])->prefix('customer')->name('customer.')->group(function () {
    // Menu & Vendors
    Route::get('/vendors', [MenuController::class, 'vendors'])->name('vendors');
    Route::get('/vendors/{vendor}', [MenuController::class, 'vendorMenu'])->name('vendor.menu');
    Route::get('/vendors/{vendor}/qr-download', [MenuController::class, 'downloadQr'])->name('vendor.qr-download');
    Route::get('/vendors/{vendor}/qr-payment', [MenuController::class, 'getQrPayment'])->name('vendor.qr-payment');
    Route::get('/products/search', [MenuController::class, 'searchProducts'])->name('products.search');
    Route::get('/products/{product}', [MenuController::class, 'productDetails'])->name('products.show');
    Route::post('/products/{product}/quick-add', [MenuController::class, 'quickAddToCart'])->name('products.quick-add');
    Route::get('/categories', [MenuController::class, 'categories'])->name('categories');

    // Cart
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::get('/cart/count', [CartController::class, 'count'])->name('cart.count');
    Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
    Route::put('/cart/{cartItem}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{cartItem}', [CartController::class, 'destroy'])->name('cart.destroy');
    Route::delete('/cart/clear/{vendor?}', [CartController::class, 'clear'])->name('cart.clear');

    // Orders
    Route::get('/orders', [CustomerOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/history', [CustomerOrderController::class, 'history'])->name('orders.history');
    Route::post('/orders', [CustomerOrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/{order}', [CustomerOrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/{order}/track', [CustomerOrderController::class, 'track'])->name('orders.track');
    Route::get('/orders/{order}/receipt', [CustomerOrderController::class, 'receipt'])->name('orders.receipt');
    Route::get('/orders/{order}/receipt/download', [CustomerOrderController::class, 'downloadReceipt'])->name('orders.receipt.download');
    Route::get('/orders/{order}/receipt/stream', [CustomerOrderController::class, 'streamReceipt'])->name('orders.receipt.stream');
    Route::post('/orders/{order}/cancel', [CustomerOrderController::class, 'cancel'])->name('orders.cancel');

    // Notifications
    Route::get('/notifications', [CustomerNotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/count', [CustomerNotificationController::class, 'count'])->name('notifications.count');
    Route::get('/notifications/recent', [CustomerNotificationController::class, 'recent'])->name('notifications.recent');
    Route::post('/notifications/mark-all-read', [CustomerNotificationController::class, 'markAllAsRead'])->name('notifications.mark-all');
    Route::delete('/notifications/clear-all', [CustomerNotificationController::class, 'clearAll'])->name('notifications.clear-all');
    Route::post('/notifications/cleanup', [CustomerNotificationController::class, 'cleanup'])->name('notifications.cleanup');
    Route::post('/notifications/{notification}/read', [CustomerNotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::delete('/notifications/{notification}', [CustomerNotificationController::class, 'destroy'])->name('notifications.destroy');
});
