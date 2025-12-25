<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Product;
use App\ModelsAddon;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

describe('Customer Vendor Integration Performance Test', function () {

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

    test('customer can browse vendors with performance measurement', function () {
        // Create test data with timing
        $createStart = microtime(true);

        $customer = User::factory()->create([
            'role' => 'customer',
            'email' => 'test_customer_' . time() . '@test.com',
        ]);

        $vendor = Vendor::factory()->create([
            'user_id' => User::factory()->create(['role' => 'vendor'])->id,
            'brand_name' => 'Test Restaurant',
            'is_active' => true,
        ]);

        $products = Product::factory()->count(10)->create([
            'vendor_id' => $vendor->id,
            'is_active' => true,
        ]);

        $createEnd = microtime(true);
        $createTime = ($createEnd - $createStart) * 1000;

        echo "Data creation: " . round($createTime, 2) . " ms\n";

        // Test vendor browsing
        $browseStart = microtime(true);

        $vendors = Vendor::withCount(['products' => function ($q) {
            $q->where('is_active', true);
        }])
        ->where('is_active', true)
        ->get();

        $browseEnd = microtime(true);
        $browseTime = ($browseEnd - $browseStart) * 1000;

        echo "Vendor browsing: " . round($browseTime, 2) . " ms\n";

        // Assertions
        expect($vendors)->toHaveCount(1);
        expect($vendors->first()->products_count)->toBe(10);
        expect($browseTime)->toBeLessThan(100); // Should be under 100ms
    });

    test('customer can add products to cart with performance tracking', function () {
        // Setup test data
        $customer = User::factory()->create(['role' => 'customer']);
        $vendor = Vendor::factory()->create([
            'user_id' => User::factory()->create(['role' => 'vendor'])->id,
        ]);
        $product = Product::factory()->create([
            'vendor_id' => $vendor->id,
            'price' => 150.00,
            'stock_quantity' => 100,
        ]);

        $addon = Addon::factory()->create([
            'product_id' => $product->id,
            'price' => 20.00,
        ]);

        // Test cart operations with timing
        $cartStart = microtime(true);

        $cart = Cart::getOrCreate($customer->id, $vendor->id);

        $cartItem = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 150.00,
            'selected_addons' => [
                ['id' => $addon->id, 'name' => 'Extra Cheese', 'price' => 20.00]
            ]
        ]);

        $cartEnd = microtime(true);
        $cartTime = ($cartEnd - $cartStart) * 1000;

        echo "Cart operations: " . round($cartTime, 2) . " ms\n";

        // Assertions
        expect($cartItem->quantity)->toBe(2);
        expect($cartItem->unit_price)->toBe(150.00);
        expect($cartItem->selected_addons)->toHaveCount(1);
        expect($cartTime)->toBeLessThan(50); // Should be under 50ms
    });

    test('customer can place order with complete workflow timing', function () {
        // Setup comprehensive test data
        $customer = User::factory()->create(['role' => 'customer']);
        $vendor = User::factory()->create(['role' => 'vendor']);
        $vendorRecord = Vendor::factory()->create([
            'user_id' => $vendor->id,
        ]);

        $product1 = Product::factory()->create([
            'vendor_id' => $vendorRecord->id,
            'price' => 100.00,
            'stock_quantity' => 50,
        ]);

        $product2 = Product::factory()->create([
            'vendor_id' => $vendorRecord->id,
            'price' => 150.00,
            'stock_quantity' => 30,
        ]);

        // Create cart with items
        $cart = Cart::factory()->create([
            'user_id' => $customer->id,
            'vendor_id' => $vendorRecord->id,
        ]);

        $cartItem1 = CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product1->id,
            'quantity' => 1,
            'unit_price' => 100.00,
        ]);

        $cartItem2 = CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product2->id,
            'quantity' => 2,
            'unit_price' => 150.00,
        ]);

        // Test order placement timing
        $orderStart = microtime(true);

        // Simulate order placement
        $cartItems = $cart->items()->with('product')->get();

        $subtotal = 0;
        foreach ($cartItems as $item) {
            $itemTotal = $item->unit_price * $item->quantity;
            $subtotal += $itemTotal;
        }

        $order = Order::create([
            'user_id' => $customer->id,
            'vendor_id' => $vendorRecord->id,
            'order_number' => 'ORD-TEST-' . time(),
            'status' => 'pending',
            'total_amount' => $subtotal,
            'payment_method' => 'cashier',
            'table_number' => 'T1',
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
        $cart->items()->delete();

        $orderEnd = microtime(true);
        $orderTime = ($orderEnd - $orderStart) * 1000;

        echo "Order placement: " . round($orderTime, 2) . " ms\n";

        // Assertions
        expect($order->total_amount)->toBe(400.00); // 100 + (150*2)
        expect($order->status)->toBe('pending');
        expect($order->items)->toHaveCount(2);
        expect($orderTime)->toBeLessThan(100); // Should be under 100ms
    });

    test('vendor can process orders with performance tracking', function () {
        // Setup order data
        $vendor = User::factory()->create(['role' => 'vendor']);
        $vendorRecord = Vendor::factory()->create(['user_id' => $vendor->id]);

        $customer = User::factory()->create(['role' => 'customer']);
        $order = Order::factory()->create([
            'user_id' => $customer->id,
            'vendor_id' => $vendorRecord->id,
            'status' => 'pending',
            'total_amount' => 250.00,
        ]);

        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'quantity' => 1,
            'unit_price' => 250.00,
        ]);

        // Test vendor order processing timing
        $processStart = microtime(true);

        // Simulate vendor actions
        $order->update(['status' => 'accepted']);

        $processEnd = microtime(true);
        $processTime = ($processEnd - $processStart) * 1000;

        echo "Order processing: " . round($processTime, 2) . " ms\n";

        // Assertions
        expect($order->fresh()->status)->toBe('accepted');
        expect($processTime)->toBeLessThan(25); // Should be under 25ms
    });

    test('database query performance with relationships', function () {
        // Create comprehensive test data
        $vendor = User::factory()->create(['role' => 'vendor']);
        $vendorRecord = Vendor::factory()->create(['user_id' => $vendor->id]);

        $customers = User::factory()->count(5)->create(['role' => 'customer']);
        $products = Product::factory()->count(20)->create([
            'vendor_id' => $vendorRecord->id,
            'is_active' => true,
        ]);

        // Create orders for each customer
        foreach ($customers as $customer) {
            $order = Order::factory()->create([
                'user_id' => $customer->id,
                'vendor_id' => $vendorRecord->id,
                'status' => 'completed',
            ]);

            $orderItems = OrderItem::factory()->count(3)->create([
                'order_id' => $order->id,
            ]);
        }

        // Test complex query performance
        $queryStart = microtime(true);

        $results = Order::with([
            'vendor:id,brand_name',
            'user:id,name,email',
            'items.product:id,name,price'
        ])
        ->where('vendor_id', $vendorRecord->id)
        ->where('status', 'completed')
        ->get();

        $queryEnd = microtime(true);
        $queryTime = ($queryEnd - $queryStart) * 1000;

        echo "Complex query with relationships: " . round($queryTime, 2) . " ms\n";

        // Assertions
        expect($results)->toHaveCount(5);
        expect($queryTime)->toBeLessThan(200); // Should be under 200ms for complex query
    });

    test('memory usage tracking during bulk operations', function () {
        $initialMemory = memory_get_usage();

        // Create large dataset
        $vendor = Vendor::factory()->create();
        $products = Product::factory()->count(100)->create([
            'vendor_id' => $vendor->id,
        ]);

        $peakMemory = memory_get_peak_usage();

        // Test bulk operations
        $bulkStart = microtime(true);

        $activeProducts = Product::where('vendor_id', $vendor->id)
            ->where('is_active', true)
            ->get();

        $bulkEnd = microtime(true);
        $bulkTime = ($bulkEnd - $bulkStart) * 1000;

        $finalMemory = memory_get_usage();
        $memoryIncrease = ($finalMemory - $initialMemory) / 1024 / 1024;

        echo "Bulk operations: " . round($bulkTime, 2) . " ms\n";
        echo "Memory increase: " . round($memoryIncrease, 2) . " MB\n";
        echo "Peak memory: " . round($peakMemory / 1024 / 1024, 2) . " MB\n";

        // Assertions
        expect($activeProducts)->toHaveCount(100);
        expect($bulkTime)->toBeLessThan(500); // Should be under 500ms
        expect($memoryIncrease)->toBeLessThan(10); // Should use less than 10MB additional memory
    });
});
