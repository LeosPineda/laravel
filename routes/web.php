<?php

use App\Http\Controllers\Superadmin\DashboardController as SuperadminDashboardController;
use App\Http\Controllers\Superadmin\VendorController as SuperadminVendorController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

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

// Dashboard redirect based on role
Route::get('/dashboard', function () {
    $user = Auth::user();

    return match ($user->role) {
        'superadmin' => redirect()->route('superadmin.dashboard'),
        'vendor' => redirect()->route('vendor.dashboard'),
        'customer' => redirect()->route('customer.home'),
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

// Vendor routes (no email verification needed)
Route::middleware(['auth', 'role:vendor'])->prefix('vendor')->name('vendor.')->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('vendor/Dashboard', [
            'stats' => [
                'todayOrders' => 12,
                'todayRevenue' => 4850.00,
                'pendingOrders' => 3,
                'totalOrders' => 156,
            ],
            'recentOrders' => [
                ['id' => 1, 'customer_name' => 'Juan Dela Cruz', 'items' => '2x Chicken Joy, 1x Spaghetti', 'total' => 253.00, 'status' => 'pending', 'table_number' => 'T5'],
                ['id' => 2, 'customer_name' => 'Maria Santos', 'items' => '1x Burger Steak', 'total' => 89.00, 'status' => 'preparing', 'table_number' => 'T12'],
            ],
            'notifications' => [
                ['id' => 1, 'type' => 'order', 'message' => 'New order from Juan Dela Cruz - Table T5', 'time' => '2 mins ago', 'isNew' => true],
                ['id' => 2, 'type' => 'payment', 'message' => 'Payment received for Order #1234', 'time' => '10 mins ago', 'isNew' => true],
                ['id' => 3, 'type' => 'order', 'message' => 'New order from Maria Santos - Table T12', 'time' => '15 mins ago', 'isNew' => false],
            ],
            'unreadNotifications' => 2,
        ]);
    })->name('dashboard');

    Route::get('/orders', function () {
        return Inertia::render('vendor/Orders/Index', [
            'orders' => [
                [
                    'id' => 1,
                    'order_number' => 'ORD-001',
                    'status' => 'pending',
                    'total_amount' => 303.00,
                    'payment_method' => 'cash',
                    'table_number' => 'T5',
                    'special_instructions' => 'No onions please',
                    'payment_proof_url' => null,
                    'created_at' => '2025-12-25T14:30:00',
                    'items' => [
                        ['id' => 1, 'quantity' => 2, 'unit_price' => 99.00, 'total_price' => 248.00, 'selected_addons' => [['name' => 'Extra Rice', 'price' => 15.00], ['name' => 'Extra Gravy', 'price' => 10.00]], 'product' => ['name' => 'Chicken Joy']],
                        ['id' => 2, 'quantity' => 1, 'unit_price' => 55.00, 'total_price' => 55.00, 'selected_addons' => null, 'product' => ['name' => 'Jolly Spaghetti']],
                    ],
                ],
                [
                    'id' => 2,
                    'order_number' => 'ORD-002',
                    'status' => 'pending',
                    'total_amount' => 124.00,
                    'payment_method' => 'qr_code',
                    'table_number' => 'T12',
                    'special_instructions' => null,
                    'payment_proof_url' => null,
                    'created_at' => '2025-12-25T14:25:00',
                    'items' => [
                        ['id' => 3, 'quantity' => 1, 'unit_price' => 89.00, 'total_price' => 124.00, 'selected_addons' => [['name' => 'Extra Patty', 'price' => 35.00]], 'product' => ['name' => 'Burger Steak']],
                    ],
                ],
                [
                    'id' => 3,
                    'order_number' => 'ORD-003',
                    'status' => 'accepted',
                    'total_amount' => 130.00,
                    'payment_method' => 'cash',
                    'table_number' => 'T3',
                    'special_instructions' => 'Extra spicy',
                    'payment_proof_url' => null,
                    'created_at' => '2025-12-25T14:10:00',
                    'items' => [
                        ['id' => 4, 'quantity' => 2, 'unit_price' => 65.00, 'total_price' => 130.00, 'selected_addons' => null, 'product' => ['name' => 'Palabok Fiesta']],
                    ],
                ],
                [
                    'id' => 4,
                    'order_number' => 'ORD-004',
                    'status' => 'ready_for_pickup',
                    'total_amount' => 297.00,
                    'payment_method' => 'cash',
                    'table_number' => 'T8',
                    'special_instructions' => null,
                    'payment_proof_url' => null,
                    'created_at' => '2025-12-25T13:45:00',
                    'items' => [
                        ['id' => 5, 'quantity' => 3, 'unit_price' => 99.00, 'total_price' => 297.00, 'selected_addons' => null, 'product' => ['name' => 'Chicken Joy']],
                    ],
                ],
                [
                    'id' => 5,
                    'order_number' => 'ORD-005',
                    'status' => 'completed',
                    'total_amount' => 189.00,
                    'payment_method' => 'qr_code',
                    'table_number' => 'T2',
                    'special_instructions' => null,
                    'payment_proof_url' => null,
                    'created_at' => '2025-12-25T12:30:00',
                    'items' => [
                        ['id' => 6, 'quantity' => 2, 'unit_price' => 89.00, 'total_price' => 178.00, 'selected_addons' => null, 'product' => ['name' => 'Burger Steak']],
                        ['id' => 7, 'quantity' => 1, 'unit_price' => 11.00, 'total_price' => 11.00, 'selected_addons' => null, 'product' => ['name' => 'Coke Float']],
                    ],
                ],
            ],
            'notifications' => [
                ['id' => 1, 'type' => 'order', 'message' => 'New order from Juan Dela Cruz - Table T5', 'time' => '2 mins ago', 'isNew' => true],
                ['id' => 2, 'type' => 'order', 'message' => 'New order from Maria Santos - Table T12', 'time' => '5 mins ago', 'isNew' => true],
            ],
            'unreadNotifications' => 2,
        ]);
    })->name('orders.index');

    Route::get('/products', function () {
        return Inertia::render('vendor/Products/Index', [
            'products' => [
                ['id' => 1, 'name' => 'Chicken Joy', 'price' => 99.00, 'category' => 'Chicken', 'image_url' => null, 'stock_quantity' => 50, 'is_active' => true, 'addons' => [
                    ['id' => 1, 'name' => 'Extra Rice', 'price' => 15.00],
                    ['id' => 2, 'name' => 'Extra Gravy', 'price' => 10.00],
                    ['id' => 3, 'name' => 'Upgrade to Large Drink', 'price' => 20.00],
                ]],
                ['id' => 2, 'name' => 'Jolly Spaghetti', 'price' => 55.00, 'category' => 'Pasta', 'image_url' => null, 'stock_quantity' => 30, 'is_active' => true, 'addons' => [
                    ['id' => 4, 'name' => 'Extra Cheese', 'price' => 15.00],
                    ['id' => 5, 'name' => 'Add Garlic Bread', 'price' => 25.00],
                ]],
                ['id' => 3, 'name' => 'Burger Steak', 'price' => 89.00, 'category' => 'Burgers', 'image_url' => null, 'stock_quantity' => 25, 'is_active' => true, 'addons' => [
                    ['id' => 6, 'name' => 'Extra Rice', 'price' => 15.00],
                    ['id' => 7, 'name' => 'Extra Patty', 'price' => 35.00],
                ]],
                ['id' => 4, 'name' => 'Palabok Fiesta', 'price' => 65.00, 'category' => 'Pasta', 'image_url' => null, 'stock_quantity' => 0, 'is_active' => false, 'addons' => [
                    ['id' => 8, 'name' => 'Extra Egg', 'price' => 15.00],
                ]],
                ['id' => 5, 'name' => 'Coke Float', 'price' => 45.00, 'category' => 'Drinks', 'image_url' => null, 'stock_quantity' => 100, 'is_active' => true, 'addons' => []],
                ['id' => 6, 'name' => 'Iced Tea', 'price' => 35.00, 'category' => 'Drinks', 'image_url' => null, 'stock_quantity' => 100, 'is_active' => true, 'addons' => []],
            ],
            'categories' => ['Chicken', 'Pasta', 'Burgers', 'Drinks'],
        ]);
    })->name('products.index');

    Route::get('/products/create', function () {
        return Inertia::render('vendor/Products/Create');
    })->name('products.create');

    Route::get('/products/{product}/edit', function ($product) {
        return Inertia::render('vendor/Products/Edit', [
            'product' => [
                'id' => $product,
                'name' => 'Sample Product',
                'price' => 150.00,
                'category' => 'Main',
                'image_url' => null,
                'stock_quantity' => 50,
                'addons' => [],
            ],
        ]);
    })->name('products.edit');

    Route::get('/analytics', function () {
        return Inertia::render('vendor/Analytics', [
            'analytics' => [
                'totalSales' => 24500.00,
                'totalOrders' => 156,
                'averageOrder' => 157.05,
                'salesByDay' => [],
                'topProducts' => [],
            ],
            'period' => 'week',
        ]);
    })->name('analytics');

    Route::get('/qr', function () {
        return Inertia::render('vendor/QrUpload', [
            'vendor' => ['qr_code_image' => null],
        ]);
    })->name('qr');
});

// Customer routes (no email verification needed)
Route::middleware(['auth', 'role:customer'])->prefix('customer')->name('customer.')->group(function () {
    Route::get('/home', function () {
        return Inertia::render('customer/Home', [
            'vendors' => [
                ['id' => 1, 'brand_name' => 'Jollibee', 'brand_image' => null, 'is_active' => true, 'products_count' => 15],
                ['id' => 2, 'brand_name' => 'Mang Inasal', 'brand_image' => null, 'is_active' => true, 'products_count' => 12],
                ['id' => 3, 'brand_name' => 'Chowking', 'brand_image' => null, 'is_active' => true, 'products_count' => 20],
            ],
        ]);
    })->name('home');

    Route::get('/vendor/{vendor}', function ($vendor) {
        return Inertia::render('customer/VendorMenu', [
            'vendor' => [
                'id' => $vendor,
                'brand_name' => 'Jollibee',
                'brand_image' => null,
                'qr_code_image' => 'qr-codes/sample-qr.png', // Sample QR
            ],
            'products' => [
                ['id' => 1, 'name' => 'Chicken Joy', 'price' => 99.00, 'category' => 'Chicken', 'image_url' => null, 'stock_quantity' => 50, 'is_active' => true, 'addons' => [
                    ['id' => 1, 'name' => 'Extra Rice', 'price' => 15.00],
                    ['id' => 2, 'name' => 'Extra Gravy', 'price' => 10.00],
                    ['id' => 3, 'name' => 'Upgrade to Large Drink', 'price' => 20.00],
                ]],
                ['id' => 2, 'name' => 'Jolly Spaghetti', 'price' => 55.00, 'category' => 'Pasta', 'image_url' => null, 'stock_quantity' => 30, 'is_active' => true, 'addons' => [
                    ['id' => 4, 'name' => 'Extra Cheese', 'price' => 15.00],
                    ['id' => 5, 'name' => 'Add Garlic Bread', 'price' => 25.00],
                ]],
                ['id' => 3, 'name' => 'Burger Steak', 'price' => 89.00, 'category' => 'Burgers', 'image_url' => null, 'stock_quantity' => 25, 'is_active' => true, 'addons' => [
                    ['id' => 6, 'name' => 'Extra Rice', 'price' => 15.00],
                    ['id' => 7, 'name' => 'Extra Patty', 'price' => 35.00],
                ]],
                ['id' => 4, 'name' => 'Palabok Fiesta', 'price' => 65.00, 'category' => 'Pasta', 'image_url' => null, 'stock_quantity' => 20, 'is_active' => true, 'addons' => [
                    ['id' => 8, 'name' => 'Extra Egg', 'price' => 15.00],
                ]],
            ],
            'categories' => ['Chicken', 'Pasta', 'Burgers'],
        ]);
    })->name('vendor.menu');

    Route::get('/cart', function () {
        return Inertia::render('customer/Cart', [
            'vendorCarts' => [
                [
                    'vendor' => ['id' => 1, 'brand_name' => 'Jollibee', 'brand_image' => null, 'qr_code_image' => 'qr-codes/jollibee-gcash.png'],
                    'items' => [
                        ['id' => 1, 'product_id' => 1, 'quantity' => 2, 'unit_price' => 99.00, 'selected_addons' => [['name' => 'Extra Rice', 'price' => 15.00], ['name' => 'Extra Gravy', 'price' => 10.00]], 'product' => ['id' => 1, 'name' => 'Chicken Joy', 'image_url' => null]],
                        ['id' => 2, 'product_id' => 2, 'quantity' => 1, 'unit_price' => 55.00, 'selected_addons' => [['name' => 'Extra Cheese', 'price' => 15.00]], 'product' => ['id' => 2, 'name' => 'Jolly Spaghetti', 'image_url' => null]],
                    ],
                ],
                [
                    'vendor' => ['id' => 2, 'brand_name' => 'Mang Inasal', 'brand_image' => null, 'qr_code_image' => null],
                    'items' => [
                        ['id' => 3, 'product_id' => 10, 'quantity' => 1, 'unit_price' => 139.00, 'selected_addons' => [['name' => 'Unli Rice', 'price' => 0.00]], 'product' => ['id' => 10, 'name' => 'Paa Chicken Inasal', 'image_url' => null]],
                    ],
                ],
            ],
        ]);
    })->name('cart');

    Route::get('/notifications', function () {
        return Inertia::render('customer/Notifications', [
            'notifications' => [
                [
                    'id' => 1,
                    'type' => 'order_ready',
                    'title' => '🎉 Order Ready!',
                    'message' => 'Your order is ready for pickup at Table T5',
                    'time' => '5 mins ago',
                    'isNew' => true,
                    'order' => [
                        'order_number' => 'ORD-001',
                        'vendor_name' => 'Jollibee',
                        'table_number' => 'T5',
                        'items' => [
                            ['name' => 'Chicken Joy', 'quantity' => 2, 'price' => 99.00, 'addons' => ['Extra Rice', 'Extra Gravy']],
                            ['name' => 'Jolly Spaghetti', 'quantity' => 1, 'price' => 55.00, 'addons' => []],
                        ],
                        'total' => 283.00,
                        'status' => 'ready',
                    ],
                ],
                [
                    'id' => 2,
                    'type' => 'order_preparing',
                    'title' => '👨‍🍳 Being Prepared',
                    'message' => 'Your order is being prepared',
                    'time' => '15 mins ago',
                    'isNew' => true,
                    'order' => [
                        'order_number' => 'ORD-002',
                        'vendor_name' => 'Mang Inasal',
                        'table_number' => 'T12',
                        'items' => [
                            ['name' => 'Paa Chicken Inasal', 'quantity' => 1, 'price' => 139.00, 'addons' => ['Unli Rice']],
                        ],
                        'total' => 139.00,
                        'status' => 'preparing',
                    ],
                ],
                [
                    'id' => 3,
                    'type' => 'order_completed',
                    'title' => '✅ Order Complete',
                    'message' => 'Thank you for your order!',
                    'time' => '1 hour ago',
                    'isNew' => false,
                    'order' => [
                        'order_number' => 'ORD-003',
                        'vendor_name' => 'Chowking',
                        'table_number' => 'T8',
                        'items' => [
                            ['name' => 'Lauriat', 'quantity' => 2, 'price' => 145.00, 'addons' => []],
                        ],
                        'total' => 290.00,
                        'status' => 'completed',
                    ],
                ],
            ],
        ]);
    })->name('notifications');

    Route::get('/profile', function () {
        return Inertia::render('customer/Profile');
    })->name('profile');
});

require __DIR__.'/settings.php';
