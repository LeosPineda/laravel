<?php

namespace Database\Seeders;

use App\Events\OrderReceived;
use App\Events\OrderStatusChanged;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Addon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class ComprehensiveVendorSeeder extends Seeder
{
    /**
     * Create comprehensive vendor data for UI testing with extensive order history.
     *
     * Usage:
     *   php artisan db:seed --class=ComprehensiveVendorSeeder
     */
    public function run(): void
    {
        $this->command->info('🎯 Creating comprehensive vendor data with extensive order history...');

        // Get or create vendor
        $vendor = $this->getOrCreateVendor();

        // Create products and addons
        $products = $this->createProducts($vendor);
        $this->createAddons($products);

        // Create customers
        $customers = $this->createCustomers();

        // Create orders with comprehensive scenarios for order history testing
        $this->createOrderSimulation($vendor, $products, $customers);

        $this->command->info('✅ Comprehensive vendor data created successfully!');
        $this->command->info('🔔 Real-time notifications have been triggered for new orders.');
    }

    private function getOrCreateVendor(): Vendor
    {
        $vendor = Vendor::first();

        if (!$vendor) {
            $vendor = Vendor::create([
                'user_id' => User::where('role', 'superadmin')->first()->id,
                'business_name' => 'Test Food Court Vendor',
                'description' => 'Delicious food and beverages',
                'contact_number' => '09123456789',
                'is_active' => true,
                'address' => '123 Test Street, Test City',
            ]);
            $this->command->info("Created vendor: {$vendor->business_name}");
        }

        return $vendor;
    }

    private function createProducts(Vendor $vendor): array
    {
        $productsData = [
            ['name' => 'Classic Burger', 'price' => 150.00, 'category' => 'Main Course', 'image' => 'burger.jpg'],
            ['name' => 'Chicken Wings', 'price' => 120.00, 'category' => 'Main Course', 'image' => 'wings.jpg'],
            ['name' => 'Pasta Carbonara', 'price' => 180.00, 'category' => 'Main Course', 'image' => 'pasta.jpg'],
            ['name' => 'Caesar Salad', 'price' => 130.00, 'category' => 'Salad', 'image' => 'salad.jpg'],
            ['name' => 'French Fries', 'price' => 80.00, 'category' => 'Sides', 'image' => 'fries.jpg'],
            ['name' => 'Chocolate Shake', 'price' => 90.00, 'category' => 'Beverages', 'image' => 'shake.jpg'],
            ['name' => 'Margherita Pizza', 'price' => 250.00, 'category' => 'Main Course', 'image' => 'pizza.jpg'],
            ['name' => 'Iced Coffee', 'price' => 75.00, 'category' => 'Beverages', 'image' => 'coffee.jpg']
        ];

        $products = [];
        foreach ($productsData as $data) {
            $product = Product::create([
                'vendor_id' => $vendor->id,
                'name' => $data['name'],
                'price' => $data['price'],
                'category' => $data['category'],
                'image_url' => '/products/' . $data['image'],
                'is_active' => true,
                'stock_quantity' => rand(50, 200),
            ]);
            $products[] = $product;
        }

        $this->command->info("Created " . count($products) . " products");
        return $products;
    }

    private function createAddons(array $products): void
    {
        $addonData = [
            ['name' => 'Extra Cheese', 'price' => 20.00],
            ['name' => 'Bacon Bits', 'price' => 25.00],
            ['name' => 'Extra Sauce', 'price' => 15.00],
            ['name' => 'Double Patty', 'price' => 50.00],
            ['name' => 'Avocado', 'price' => 30.00],
            ['name' => 'Pickles', 'price' => 10.00],
        ];

        foreach ($products as $product) {
            $addonCount = rand(2, 4);
            $selectedAddons = array_rand(array_flip(array_column($addonData, 'name')), $addonCount);

            foreach ($selectedAddons as $addonName) {
                $addonInfo = collect($addonData)->firstWhere('name', $addonName);
                if ($addonInfo) {
                    Addon::create([
                        'product_id' => $product->id,
                        'name' => $addonInfo['name'],
                        'price' => $addonInfo['price'],
                        'is_active' => true,
                    ]);
                }
            }
        }

        $this->command->info('Created addons for products');
    }

    private function createCustomers(): array
    {
        $customersData = [
            ['name' => 'John Smith', 'email' => 'john@example.com'],
            ['name' => 'Maria Garcia', 'email' => 'maria@example.com'],
            ['name' => 'David Lee', 'email' => 'david@example.com'],
            ['name' => 'Sarah Johnson', 'email' => 'sarah@example.com'],
            ['name' => 'Mike Wilson', 'email' => 'mike@example.com'],
            ['name' => 'Lisa Brown', 'email' => 'lisa@example.com'],
            ['name' => 'Tom Davis', 'email' => 'tom@example.com'],
            ['name' => 'Anna Martinez', 'email' => 'anna@example.com'],
        ];

        $customers = [];
        foreach ($customersData as $data) {
            $customer = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => bcrypt('password'),
                    'role' => 'customer',
                    'email_verified_at' => now(),
                ]
            );
            $customers[] = $customer;
        }

        $this->command->info("Created " . count($customers) . " customers");
        return $customers;
    }

    private function createOrderSimulation(Vendor $vendor, array $products, array $customers): void
    {
        $this->command->info('Creating comprehensive order simulation with extensive order history...');

        // Create mock payment proof images
        $this->createMockPaymentProofs();

        // Define comprehensive order scenarios for order history testing
        // ONLY USING VALID DATABASE STATUSES: pending, accepted, ready_for_pickup, cancelled
        $orderScenarios = [
            // Current active orders (Incoming Orders)
            ['count' => 3, 'status' => 'pending', 'payment_methods' => ['qr_code', 'cash', 'gcash'], 'special_instructions' => ['No onions please', 'Extra spicy', 'Less salt']],
            ['count' => 2, 'status' => 'accepted', 'payment_methods' => ['qr_code', 'gcash'], 'special_instructions' => ['Medium rare', 'Add extra cheese']],
            ['count' => 4, 'status' => 'ready_for_pickup', 'payment_methods' => ['qr_code', 'gcash', 'cash', 'qr_code'], 'special_instructions' => ['Well done', 'No vegetables', null, 'Extra sauce']],

            // Order History examples - Cancelled orders (representing declined/declined orders)
            ['count' => 3, 'status' => 'cancelled', 'payment_methods' => ['cash', 'qr_code', 'gcash'], 'special_instructions' => [null, 'Customer cancelled', 'Order too large']],
            ['count' => 2, 'status' => 'cancelled', 'payment_methods' => ['cash', 'qr_code'], 'special_instructions' => ['Item out of stock', 'Kitchen closed']],

            // Order History examples - Completed Transactions (ready_for_pickup represents completed)
            ['count' => 4, 'status' => 'ready_for_pickup', 'payment_methods' => ['qr_code', 'gcash', 'cash', 'qr_code'], 'special_instructions' => ['Standard order', 'Extra toppings', null, 'Delivery order'], 'completed' => true],
            ['count' => 3, 'status' => 'ready_for_pickup', 'payment_methods' => ['cash', 'qr_code', 'gcash'], 'special_instructions' => ['Birthday celebration', 'Corporate order', 'Regular customer'], 'completed' => true],

            // Additional variety for better testing
            ['count' => 2, 'status' => 'cancelled', 'payment_methods' => ['gcash', 'cash'], 'special_instructions' => ['Payment failed', 'Customer no-show']],
            ['count' => 1, 'status' => 'cancelled', 'payment_methods' => ['qr_code'], 'special_instructions' => ['Large quantity unavailable']],
        ];

        $totalOrders = 0;
        $orderNumber = 1;

        foreach ($orderScenarios as $scenario) {
            for ($i = 0; $i < $scenario['count']; $i++) {
                $customer = $customers[array_rand($customers)];
                $paymentMethod = $scenario['payment_methods'][$i % count($scenario['payment_methods'])];
                $specialInstruction = $scenario['special_instructions'][$i % count($scenario['special_instructions'])];

                // Select 1-3 random products
                $itemCount = rand(1, 3);
                $selectedProducts = array_rand($products, min($itemCount, count($products)));
                if (!is_array($selectedProducts)) {
                    $selectedProducts = [$selectedProducts];
                }

                $order = $this->createOrderWithItems(
                    $vendor,
                    $customer,
                    $products,
                    $selectedProducts,
                    $scenario['status'],
                    $paymentMethod,
                    $specialInstruction,
                    $orderNumber++
                );

                $totalOrders++;

                // Simulate real-time notifications for new orders
                if ($scenario['status'] === 'pending') {
                    $order->load('items.product', 'customer');
                    event(new OrderReceived($vendor, $order));
                } elseif ($scenario['status'] === 'accepted') {
                    event(new OrderStatusChanged($vendor, $order, $customer, 'pending', 'accepted'));
                } elseif ($scenario['status'] === 'ready_for_pickup') {
                    event(new OrderStatusChanged($vendor, $order, $customer, 'pending', 'accepted'));
                    event(new OrderStatusChanged($vendor, $order, $customer, 'accepted', 'ready_for_pickup'));
                }
            }
        }

        $this->command->info("Created {$totalOrders} orders with comprehensive simulation");
        $this->command->info('📊 Order distribution:');
        $this->command->info('   Pending: ' . Order::where('status', 'pending')->count());
        $this->command->info('   Accepted: ' . Order::where('status', 'accepted')->count());
        $this->command->info('   Ready for Pickup: ' . Order::where('status', 'ready_for_pickup')->count());
        $this->command->info('   Cancelled: ' . Order::where('status', 'cancelled')->count());
    }

    private function createOrderWithItems(Vendor $vendor, User $customer, array $products, array $productIndexes, string $status, string $paymentMethod, ?string $specialInstruction, int $orderNumber): Order
    {
        $tableNumber = (string) rand(1, 25);
        $orderNumberStr = 'ORD-' . str_pad($orderNumber, 6, '0', STR_PAD_LEFT);

        // Create payment proof for non-cash payments
        $paymentProofUrl = null;
        if ($paymentMethod !== 'cash') {
            $paymentProofUrl = '/payment-proofs/' . $orderNumberStr . '.jpg';
        }

        $order = Order::create([
            'vendor_id' => $vendor->id,
            'customer_id' => $customer->id,
            'order_number' => $orderNumberStr,
            'status' => $status,
            'payment_method' => $paymentMethod,
            'payment_proof_url' => $paymentProofUrl,
            'table_number' => $tableNumber,
            'special_instructions' => $specialInstruction,
            'total_amount' => 0,
            'completed_at' => $status === 'ready_for_pickup' ? now() : null,
        ]);

        // Add order items
        $totalAmount = 0;
        foreach ($productIndexes as $productIndex) {
            $product = $products[$productIndex];
            $quantity = rand(1, 3);
            $unitPrice = $product->price;
            $totalPrice = $unitPrice * $quantity;
            $totalAmount += $totalPrice;

            // Add random addons
            $addons = Addon::where('product_id', $product->id)->where('is_active', true)->get();
            $selectedAddons = [];
            if ($addons->isNotEmpty() && rand(0, 1)) {
                $addonCount = rand(1, min(2, $addons->count()));
                $randomAddons = $addons->random($addonCount);
                foreach ($randomAddons as $addon) {
                    $selectedAmount[] = [
                        'id' => $addon->id,
                        'name' => $addon->name,
                        'price' => $addon->price
                    ];
                }
                $totalAmount += collect($selectedAddons)->sum('price') * $quantity;
            }

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_price' => $totalPrice,
                'selected_addons' => $selectedAddons,
            ]);
        }

        // Update order total
        $order->update(['total_amount' => $totalAmount]);

        $this->command->info("   📦 Order {$orderNumberStr}: Table {$tableNumber}, ₱{$totalAmount}, {$status}");

        return $order;
    }

    private function createMockPaymentProofs(): void
    {
        $this->command->info('Creating mock payment proof images...');

        // Create the directory if it doesn't exist
        $directory = public_path('payment-proofs');
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        // Create simple payment proof files
        for ($i = 1; $i <= 30; $i++) {
            $filename = 'ORD-' . str_pad($i, 6, '0', STR_PAD_LEFT) . '.jpg';
            $filepath = $directory . '/' . $filename;
            file_put_contents($filepath, "Mock payment proof for {$filename}");
        }

        $this->command->info('Mock payment proof images created');
    }
}
