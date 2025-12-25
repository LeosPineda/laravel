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
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

describe('Customer Vendor Workflow Test', function () {

    beforeEach(function () {
        // Create test users
        $this->customer = User::factory()->create([
            'role' => 'customer',
            'email' => 'customer@test.com',
        ]);

        $this->vendor = User::factory()->create([
            'role' => 'vendor',
            'email' => 'vendor@test.com',
        ]);

        $this->vendorRecord = Vendor::create([
            'user_id' => $this->vendor->id,
            'brand_name' => 'Test Restaurant',
            'brand_description' => 'A test restaurant for testing',
            'is_active' => true,
        ]);
    });

    test('customer can browse vendors', function () {
        $response = $this->actingAs($this->customer)
            ->getJson('/api/customer/menu/vendors');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'vendors' => [
                '*' => [
                    'id',
                    'brand_name',
                    'brand_image',
                    'description',
                    'products_count'
                ]
            ]
        ]);
    });

    test('customer can view vendor menu', function () {
        $response = $this->actingAs($this->customer)
            ->getJson('/api/customer/menu/vendors/' . $this->vendorRecord->id);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'vendor' => [
                'id',
                'brand_name',
                'brand_image',
                'description',
                'qr_code_image'
            ],
            'products',
            'categories'
        ]);
    });

    test('customer can add products to cart', function () {
        // Create a test product
        $product = Product::create([
            'vendor_id' => $this->vendorRecord->id,
            'name' => 'Test Burger',
            'price' => 150.00,
            'category' => 'Burgers',
            'stock_quantity' => 10,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->customer)
            ->postJson('/api/customer/cart/items', [
                'vendor_id' => $this->vendorRecord->id,
                'product_id' => $product->id,
                'quantity' => 2,
                'unit_price' => 150.00,
                'selected_addons' => []
            ]);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => 'Item added to cart successfully'
        ]);

        // Verify cart item was created
        expect(CartItem::count())->toBe(1);

        // Verify cart count is returned
        expect($response->json('cartCount'))->toBe(2);
    });

    test('customer can update cart items', function () {
        // Create product and add to cart
        $product = Product::create([
            'vendor_id' => $this->vendorRecord->id,
            'name' => 'Test Pizza',
            'price' => 200.00,
            'category' => 'Pizza',
            'stock_quantity' => 5,
            'is_active' => true,
        ]);

        $cart = Cart::create([
            'user_id' => $this->customer->id,
            'vendor_id' => $this->vendorRecord->id,
        ]);

        $cartItem = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 200.00,
            'selected_addons' => []
        ]);

        $response = $this->actingAs($this->customer)
            ->putJson('/api/customer/cart/items/' . $cartItem->id, [
                'quantity' => 3
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Cart item updated successfully'
        ]);

        // Verify quantity was updated
        expect($cartItem->fresh()->quantity)->toBe(3);
    });

    test('customer can view cart', function () {
        // Create product and add to cart
        $product = Product::create([
            'vendor_id' => $this->vendorRecord->id,
            'name' => 'Test Salad',
            'price' => 120.00,
            'category' => 'Salads',
            'stock_quantity' => 8,
            'is_active' => true,
        ]);

        $cart = Cart::create([
            'user_id' => $this->customer->id,
            'vendor_id' => $this->vendorRecord->id,
        ]);

        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 120.00,
            'selected_addons' => []
        ]);

        $response = $this->actingAs($this->customer)
            ->getJson('/api/customer/cart');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'vendorCarts' => [
                '*' => [
                    'vendor' => [
                        'id',
                        'brand_name',
                        'brand_image',
                        'qr_code_image'
                    ],
                    'items' => [
                        '*' => [
                            'id',
                            'product_id',
                            'quantity',
                            'unit_price',
                            'selected_addons',
                            'product' => [
                                'id',
                                'name',
                                'image_url'
                            ]
                        ]
                    ]
                ]
            ],
            'cartCount'
        ]);
    });

    test('customer can place order from cart', function () {
        // Create products and add to cart
        $product1 = Product::create([
            'vendor_id' => $this->vendorRecord->id,
            'name' => 'Test Combo 1',
            'price' => 180.00,
            'category' => 'Combos',
            'stock_quantity' => 10,
            'is_active' => true,
        ]);

        $addon = Addon::create([
            'product_id' => $product1->id,
            'name' => 'Extra Fries',
            'price' => 30.00,
            'is_active' => true,
        ]);

        $cart = Cart::create([
            'user_id' => $this->customer->id,
            'vendor_id' => $this->vendorRecord->id,
        ]);

        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product1->id,
            'quantity' => 1,
            'unit_price' => 180.00,
            'selected_addons' => [
                ['id' => $addon->id, 'name' => 'Extra Fries', 'price' => 30.00]
            ]
        ]);

        $response = $this->actingAs($this->customer)
            ->postJson('/api/customer/orders', [
                'vendor_id' => $this->vendorRecord->id,
                'payment_method' => 'cashier',
                'table_number' => 'T5',
                'special_instructions' => 'No onions please'
            ]);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => 'Order placed successfully'
        ]);

        // Verify order was created
        expect(Order::count())->toBe(1);
        expect(OrderItem::count())->toBe(1);

        // Verify cart was cleared
        expect(CartItem::count())->toBe(0);

        // Verify order details
        $order = $response->json('order');
        expect($order['status'])->toBe('pending');
        expect($order['table_number'])->toBe('T5');
        expect($order['payment_method'])->toBe('cashier');
        expect($order['total_amount'])->toBe(210.00); // 180 + 30 addon
    });

    test('customer can track order status', function () {
        // Create order
        $order = Order::create([
            'user_id' => $this->customer->id,
            'vendor_id' => $this->vendorRecord->id,
            'order_number' => 'ORD-000001',
            'status' => 'accepted',
            'total_amount' => 250.00,
            'payment_method' => 'qr_code',
            'table_number' => 'T3',
            'special_instructions' => 'Extra spicy',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $response = $this->actingAs($this->customer)
            ->getJson('/api/customer/orders/track/' . $order->id);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'order',
            'status_history' => [
                '*' => [
                    'status',
                    'label',
                    'description',
                    'timestamp',
                    'completed'
                ]
            ]
        ]);
    });

    test('vendor can view incoming orders', function () {
        // Create order
        $order = Order::create([
            'user_id' => $this->customer->id,
            'vendor_id' => $this->vendorRecord->id,
            'order_number' => 'ORD-000002',
            'status' => 'pending',
            'total_amount' => 180.00,
            'payment_method' => 'cashier',
            'table_number' => 'T7',
            'special_instructions' => null,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $response = $this->actingAs($this->vendor)
            ->getJson('/api/vendor/orders');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'orders' => [
                '*' => [
                    'id',
                    'order_number',
                    'status',
                    'total_amount',
                    'payment_method',
                    'table_number',
                    'special_instructions',
                    'created_at',
                    'vendor',
                    'items'
                ]
            ],
            'stats'
        ]);
    });

    test('vendor can accept order', function () {
        // Create order
        $order = Order::create([
            'user_id' => $this->customer->id,
            'vendor_id' => $this->vendorRecord->id,
            'order_number' => 'ORD-000003',
            'status' => 'pending',
            'total_amount' => 160.00,
            'payment_method' => 'cashier',
            'table_number' => 'T2',
            'special_instructions' => null,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $response = $this->actingAs($this->vendor)
            ->putJson('/api/vendor/orders/' . $order->id . '/accept');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Order accepted successfully'
        ]);

        // Verify order status was updated
        expect($order->fresh()->status)->toBe('accepted');
    });

    test('vendor can decline order', function () {
        // Create order
        $order = Order::create([
            'user_id' => $this->customer->id,
            'vendor_id' => $this->vendorRecord->id,
            'order_number' => 'ORD-000004',
            'status' => 'pending',
            'total_amount' => 190.00,
            'payment_method' => 'qr_code',
            'table_number' => 'T8',
            'special_instructions' => 'Make it spicy',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $response = $this->actingAs($this->vendor)
            ->putJson('/api/vendor/orders/' . $order->id . '/decline');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Order declined successfully'
        ]);

        // Verify order status was updated
        expect($order->fresh()->status)->toBe('declined');
    });

    test('vendor can mark order as ready', function () {
        // Create accepted order
        $order = Order::create([
            'user_id' => $this->customer->id,
            'vendor_id' => $this->vendorRecord->id,
            'order_number' => 'ORD-000005',
            'status' => 'accepted',
            'total_amount' => 220.00,
            'payment_method' => 'cashier',
            'table_number' => 'T1',
            'special_instructions' => null,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $response = $this->actingAs($this->vendor)
            ->putJson('/api/vendor/orders/' . $order->id . '/ready');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Order marked as ready for pickup'
        ]);

        // Verify order status was updated
        expect($order->fresh()->status)->toBe('ready_for_pickup');
    });

    test('vendor can complete order', function () {
        // Create ready order
        $order = Order::create([
            'user_id' => $this->customer->id,
            'vendor_id' => $this->vendorRecord->id,
            'order_number' => 'ORD-000006',
            'status' => 'ready_for_pickup',
            'total_amount' => 175.00,
            'payment_method' => 'qr_code',
            'table_number' => 'T4',
            'special_instructions' => null,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $response = $this->actingAs($this->vendor)
            ->putJson('/api/vendor/orders/' . $order->id . '/complete');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Order completed successfully'
        ]);

        // Verify order status was updated
        expect($order->fresh()->status)->toBe('completed');
    });

    test('customer can get order receipt', function () {
        // Create ready order
        $order = Order::create([
            'user_id' => $this->customer->id,
            'vendor_id' => $this->vendorRecord->id,
            'order_number' => 'ORD-000007',
            'status' => 'ready_for_pickup',
            'total_amount' => 240.00,
            'payment_method' => 'cashier',
            'table_number' => 'T6',
            'special_instructions' => 'Extra sauce on the side',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $response = $this->actingAs($this->customer)
            ->getJson('/api/customer/orders/' . $order->id . '/receipt');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'receipt' => [
                'order_number',
                'vendor_name',
                'table_number',
                'order_date',
                'items',
                'total_amount',
                'payment_method',
                'status',
                'footer_message'
            ]
        ]);
    });

    test('cart count updates correctly', function () {
        // Create products and add to cart
        $product1 = Product::create([
            'vendor_id' => $this->vendorRecord->id,
            'name' => 'Test Item 1',
            'price' => 100.00,
            'category' => 'Test',
            'stock_quantity' => 10,
            'is_active' => true,
        ]);

        $product2 = Product::create([
            'vendor_id' => $this->vendorRecord->id,
            'name' => 'Test Item 2',
            'price' => 150.00,
            'category' => 'Test',
            'stock_quantity' => 10,
            'is_active' => true,
        ]);

        // Add first item
        $this->actingAs($this->customer)
            ->postJson('/api/customer/cart/items', [
                'vendor_id' => $this->vendorRecord->id,
                'product_id' => $product1->id,
                'quantity' => 2,
                'unit_price' => 100.00,
                'selected_addons' => []
            ]);

        // Add second item
        $this->actingAs($this->customer)
            ->postJson('/api/customer/cart/items', [
                'vendor_id' => $this->vendorRecord->id,
                'product_id' => $product2->id,
                'quantity' => 1,
                'unit_price' => 150.00,
                'selected_addons' => []
            ]);

        // Check cart count
        $response = $this->actingAs($this->customer)
