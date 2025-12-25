<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Product;
use App\Models\Addon;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;

uses(RefreshDatabase::class);

describe('Real Backend Functionality Test', function () {

    beforeEach(function () {
        $this->startTime = microtime(true);
        $this->startMemory = memory_get_usage();
    });

    afterEach(function () {
        $endTime = microtime(true);
        $endMemory = memory_get_usage();

        $executionTime = ($endTime - $this->startTime) * 1000; // Convert to milliseconds
        $memoryUsed = ($endMemory - $this->startMemory) / 1024 / 1024; // Convert to MB

        echo "\n📊 PERFORMANCE METRICS:\n";
        echo "Execution Time: " . round($executionTime, 2) . " ms\n";
        echo "Memory Used: " . round($memoryUsed, 2) . " MB\n";
    });

    test('customer can browse vendors with real database operations', function () {
        echo "\n🧪 Testing: Customer Vendor Browsing with Real Data\n";
        echo "==================================================\n";

        $createStart = microtime(true);

        // Create real test data
        $customer = User::create([
            'name' => 'Test Customer',
            'email' => 'customer_' . time() . '@test.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'email_verified_at' => now(),
        ]);

        $vendorUser = User::create([
            'name' => 'Test Vendor User',
            'email' => 'vendor_' . time() . '@test.com',
            'password' => bcrypt('password'),
            'role' => 'vendor',
            'email_verified_at' => now(),
        ]);

        $vendor = Vendor::create([
            'user_id' => $vendorUser->id,
            'brand_name' => 'Test Restaurant',
            'brand_description' => 'A test restaurant for performance testing',
            'is_active' => true,
        ]);

        // Create multiple products
        $products = [];
        for ($i = 1; $i <= 10; $i++) {
            $products[] = Product::create([
                'vendor_id' => $vendor->id,
                'name' => "Test Product {$i}",
                'price' => rand(50, 200) + 0.50,
                'category' => ['Burgers', 'Pizza', 'Drinks', 'Desserts'][array_rand(['Burgers', 'Pizza', 'Drinks', 'Desserts'])],
                'stock_quantity' => rand(10, 100),
                'is_active' => true,
            ]);
        }

        $createEnd = microtime(true);
        $createTime = ($createEnd - $createStart) * 1000;

        echo "Data creation: " . round($createTime, 2) . " ms\n";
        echo "Created: 1 customer, 1 vendor, 10 products\n";

        // Test vendor browsing performance
        $browseStart = microtime(true);

        $activeVendors = Vendor::withCount(['products' => function ($q) {
            $q->where('is_active', true);
        }])
        ->where('is_active', true)
        ->get();

        $browseEnd = microtime(true);
        $browseTime = ($browseEnd - $browseStart) * 1000;

        echo "Vendor browsing query: " . round($browseTime, 2) . " ms\n";

        // Test product retrieval by vendor
        $productStart = microtime(true);

        $vendorProducts = Product::where('vendor_id', $vendor->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $productEnd = microtime(true);
        $productTime = ($productEnd - $productStart) * 1000;

        echo "Product retrieval: " . round($productTime, 2) . " ms\n";

        // Assertions
        expect($activeVendors)->toHaveCount(1);
        expect($activeVendors->first()->products_count)->toBe(10);
        expect($vendorProducts)->toHaveCount(10);
        expect($browseTime)->toBeLessThan(100);
        expect($productTime)->toBeLessThan(50);

        echo "✅ Test completed successfully!\n";
    });

    test('customer cart operations with real data and performance tracking', function () {
        echo "\n🧪 Testing: Customer Cart Operations\n";
        echo "====================================\n";

        // Setup test data
        $customer = User::create([
            'name' => 'Cart Test Customer',
            'email' => 'cart_' . time() . '@test.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'email_verified_at' => now(),
        ]);

        $vendor = Vendor::create([
            'user_id' => User::create([
                'name' => 'Cart Test Vendor',
                'email' => 'cart_vendor_' . time() . '@test.com',
                'password' => bcrypt('password'),
                'role' => 'vendor',
                'email_verified_at' => now(),
            ])->id,
            'brand_name' => 'Cart Test Restaurant',
            'is_active' => true,
        ]);

        $product = Product::create([
            'vendor_id' => $vendor->id,
            'name' => 'Cart Test Product',
            'price' => 150.00,
            'category' => 'Test Category',
            'stock_quantity' => 100,
            'is_active' => true,
        ]);

        $addon = Addon::create([
            'product_id' => $product->id,
            'name' => 'Test Addon',
            'price' => 20.00,
            'is_active' => true,
        ]);

        // Test cart creation and item addition
        $cartStart = microtime(true);

        $cart = Cart::create([
            'user_id' => $customer->id,
            'vendor_id' => $vendor->id,
        ]);

        $cartItem = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 150.00,
            'selected_addons' => json_encode([
                ['id' => $addon->id, 'name' => 'Extra Cheese', 'price' => 20.00]
            ]),
        ]);

        $cartEnd = microtime(true);
        $cartTime = ($cartEnd - $cartStart) * 1000;

        echo "Cart creation and item addition: " . round($cartTime, 2) . " ms\n";

        // Test cart retrieval with relationships
        $retrieveStart = microtime(true);

        $cartWithItems = Cart::with(['items.product', 'items.selectedAddons'])
            ->where('id', $cart->id)
            ->first();

        $retrieveEnd = microtime(true);
        $retrieveTime = ($retrieveEnd - $retrieveStart) * 1000;

        echo "Cart retrieval with relationships: " . round($retrieveTime, 2) . " ms\n";

        // Test cart item update
        $updateStart = microtime(true);

        $cartItem->update(['quantity' => 3]);

        $updateEnd = microtime(true);
        $updateTime = ($updateEnd - $updateStart) * 1000;

        echo "Cart item update: " . round($updateTime, 2) . " ms\n";

        // Assertions
        expect($cart->user_id)->toBe($customer->id);
        expect($cart->vendor_id)->toBe($vendor->id);
        expect($cartItem->quantity)->toBe(3);
        expect($cartWithItems->items)->toHaveCount(1);
        expect($cartWithItems->items->first()->product->name)->toBe('Cart Test Product');
        expect($cartTime)->toBeLessThan(100);
        expect($retrieveTime)->toBeLessThan(50);
        expect($updateTime)->toBeLessThan(25);

        echo "✅ Cart operations test completed!\n";
    });

    test('complete order workflow with real performance measurement', function () {
        echo "\n🧪 Testing: Complete Order Workflow\n";
        echo "====================================\n";

        // Setup comprehensive test data
        $customer = User::create([
            'name' => 'Order Test Customer',
            'email' => 'order_' . time() . '@test.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'email_verified_at' => now(),
        ]);

        $vendorUser = User::create([
            'name' => 'Order Test Vendor',
            'email' => 'order_vendor_' . time() . '@test.com',
            'password' => bcrypt('password'),
            'role' => 'vendor',
            'email_verified_at' => now(),
        ]);

        $vendor = Vendor::create([
            'user_id' => $vendorUser->id,
            'brand_name' => 'Order Test Restaurant',
            'is_active' => true,
        ]);

        $product1 = Product::create([
            'vendor_id' => $vendor->id,
            'name' => 'Order Test Product 1',
            'price' => 100.00,
            'category' => 'Test Category',
            'stock_quantity' => 50,
            'is_active' => true,
        ]);

        $product2 = Product::create([
            'vendor_id' => $vendor->id,
            'name' => 'Order Test Product 2',
            'price' => 150.00,
            'category' => 'Test Category',
            'stock_quantity' => 30,
            'is_active' => true,
        ]);

        // Create cart with items
        $cart = Cart::create([
            'user_id' => $customer->id,
            'vendor_id' => $vendor->id,
        ]);

        $cartItem1 = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product1->id,
            'quantity' => 1,
            'unit_price' => 100.00,
            'selected_addons' => json_encode([]),
        ]);

        $cartItem2 = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product2->id,
            'quantity' => 2,
            'unit_price' => 150.00,
            'selected_addons' => json_encode([]),
        ]);

        // Test order placement workflow
        $orderStart = microtime(true);

        // Get cart items with products
        $cartItems = CartItem::with('product')
            ->where('cart_id', $cart->id)
            ->get();

        // Calculate total
        $subtotal = 0;
        foreach ($cartItems as $item) {
            $itemTotal = $item->unit_price * $item->quantity;
            $subtotal += $itemTotal;
        }

        // Create order
        $order = Order::create([
            'user_id' => $customer->id,
            'vendor_id' => $vendor->id,
            'order_number' => 'ORD-' . str_pad(Order::max('id') + 1, 6, '0', STR_PAD_LEFT),
            'status' => 'pending',
            'total_amount' => $subtotal,
            'payment_method' => 'cashier',
            'table_number' => 'T1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create order items
        foreach ($cartItems as $cartItem) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $cartItem->product_id,
                'quantity' => $cartItem->quantity,
                'unit_price' => $cartItem->unit_price,
                'total_price' => $cartItem->unit_price * $cartItem->quantity,
            ]);
        }

        // Clear cart
        CartItem::where('cart_id', $cart->id)->delete();

        $orderEnd = microtime(true);
        $orderTime = ($orderEnd - $orderStart) * 1000;

        echo "Order placement workflow: " . round($orderTime, 2) . " ms\n";

        // Test order retrieval with relationships
        $retrieveStart = microtime(true);

        $orderWithDetails = Order::with([
            'vendor:id,brand_name',
            'items.product:id,name,price',
            'customer:id,name,email'
        ])->where('id', $order->id)->first();

        $retrieveEnd = microtime(true);
        $retrieveTime = ($retrieveEnd - $retrieveStart) * 1000;

        echo "Order retrieval with relationships: " . round($retrieveTime, 2) . " ms\n";

        // Test vendor order processing
        $processStart = microtime(true);

        $order->update([
            'status' => 'accepted',
            'updated_at' => now(),
        ]);

        $processEnd = microtime(true);
        $processTime = ($processEnd - $processStart) * 1000;

        echo "Order status update: " . round($processTime, 2) . " ms\n";

        // Test vendor analytics queries
        $analyticsStart = microtime(true);

        $vendorStats = [
            'total_orders' => Order::where('vendor_id', $vendor->id)->count(),
            'pending_orders' => Order::where('vendor_id', $vendor->id)->where('status', 'pending')->count(),
            'accepted_orders' => Order::where('vendor_id', $vendor->id)->where('status', 'accepted')->count(),
            'total_revenue' => Order::where('vendor_id', $vendor->id)->where('status', 'completed')->sum('total_amount'),
        ];

        $analyticsEnd = microtime(true);
        $analyticsTime = ($analyticsEnd - $analyticsStart) * 1000;

        echo "Vendor analytics queries: " . round($analyticsTime, 2) . " ms\n";

        // Assertions
        expect($order->total_amount)->toBe(400.00); // 100 + (150*2)
        expect($order->status)->toBe('accepted');
        expect($order->items)->toHaveCount(2);
        expect($vendorStats['total_orders'])->toBe(1);
        expect($vendorStats['accepted_orders'])->toBe(1);
        expect($orderTime)->toBeLessThan(200);
        expect($retrieveTime)->toBeLessThan(100);
        expect($processTime)->toBeLessThan(25);
        expect($analyticsTime)->toBeLessThan(50);

        echo "✅ Complete order workflow test successful!\n";
        echo "📊 Summary:\n";
        echo "  - Order placed: ₱{$order->total_amount}\n";
        echo "  - Items: {$order->items->count()}\n";
        echo "  - Vendor stats: {$vendorStats['total_orders']} orders\n";
    });

    test('database query performance with complex relationships', function () {
        echo "\n🧪 Testing: Complex Query Performance\n";
        echo "=====================================\n";

        // Create comprehensive test data
        $vendor = Vendor::create([
            'user_id' => User::create([
                'name' => 'Complex Query Vendor',
                'email' => 'complex_' . time() . '@test.com',
                'password' => bcrypt('password'),
                'role' => 'vendor',
                'email_verified_at' => now(),
            ])->id,
            'brand_name' => 'Complex Query Restaurant',
            'is_active' => true,
        ]);

        $customers = [];
        for ($i = 1; $i <= 5; $i++) {
            $customers[] = User::create([
                'name' => "Complex Customer {$i}",
                'email' => "complex_customer_{$i}_" . time() . "@test.com",
                'password' => bcrypt('password'),
                'role' => 'customer',
                'email_verified_at' => now(),
            ]);
        }

        $products = [];
        for ($i = 1; $i <= 20; $i++) {
            $products[] = Product::create([
                'vendor_id' => $vendor->id,
                'name' => "Complex Product {$i}",
                'price' => rand(50, 200) + 0.50,
                'category' => ['Burgers', 'Pizza', 'Drinks', 'Desserts'][array_rand(['Burgers', 'Pizza', 'Drinks', 'Desserts'])],
                'stock_quantity' => rand(10, 100),
                'is_active' => true,
            ]);
        }

        // Create orders for each customer
        $orders = [];
        foreach ($customers as $customer) {
            $order = Order::create([
                'user_id' => $customer->id,
                'vendor_id' => $vendor->id,
                'order_number' => 'ORD-COMP-' . str_pad(count($orders) + 1, 4, '0', STR_PAD_LEFT),
                'status' => 'completed',
                'total_amount' => rand(100, 500) + 0.50,
                'payment_method' => 'cashier',
                'table_number' => 'T' . rand(1, 20),
                'created_at' => now()->subDays(rand(1, 30)),
            ]);

            $orders[] =
