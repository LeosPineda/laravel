<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Product;
use App\Models\Addon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Notification;
use Illuminate\Support\Str;

class VendorSystemSeeder extends Seeder
{
    /**
     * Run the database seeds for the vendor system (Fixed Version).
     * Creates vendors with products and test orders.
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting Fixed Vendor System Database Seeding...');

        // Clear existing data
        User::where('role', 'vendor')->delete();
        User::where('role', 'customer')->delete();
        User::where('role', 'superadmin')->delete();

        // Create users in order of hierarchy
        $superadmin = $this->createSuperadmin();
        $this->command->info('✅ Created superadmin user');

        $vendors = $this->createVendors();
        $this->command->info('✅ Created ' . $vendors->count() . ' vendor users');

        $customers = $this->createCustomers();
        $this->command->info('✅ Created ' . $customers->count() . ' customer users');

        // Create products for each vendor
        $this->createProductsForVendors($vendors);
        $this->command->info('✅ Created products for all vendors');

        // Create orders
        $this->createSampleOrders($vendors, $customers);
        $this->command->info('✅ Created sample orders');

        // Create notifications
        $this->createNotifications($vendors, $customers);
        $this->command->info('✅ Created notifications');

        $this->command->info('🎉 Vendor System Database Seeding Completed!');
        $this->printSummary();
    }

    private function createSuperadmin(): User
    {
        return User::create([
            'name' => 'System Administrator',
            'email' => 'admin@foodcourt.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => 'superadmin',
            'is_active' => true,
            'remember_token' => Str::random(10),
        ]);
    }

    private function createVendors(): \Illuminate\Database\Eloquent\Collection
    {
        $vendorData = [
            [
                'name' => 'Mario\'s Pizza',
                'email' => 'mario@pizza.com',
                'brand_name' => 'Mario\'s Pizza',
            ],
            [
                'name' => 'Burger Barn',
                'email' => 'orders@burgerbarn.com',
                'brand_name' => 'Burger Barn',
            ],
        ];

        $vendors = collect();

        foreach ($vendorData as $data) {
            // Create user
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'role' => 'vendor',
                'is_active' => true,
                'remember_token' => Str::random(10),
            ]);

            // Create vendor profile - ONLY using columns that exist in the database
            $vendor = Vendor::create([
                'user_id' => $user->id,
                'brand_name' => $data['brand_name'],
                'is_active' => true,
            ]);

            $vendors->push($vendor);
        }

        return $vendors;
    }

    private function createCustomers(): \Illuminate\Database\Eloquent\Collection
    {
        $customerData = [
            ['name' => 'John Smith', 'email' => 'john@example.com'],
            ['name' => 'Sarah Johnson', 'email' => 'sarah@example.com'],
            ['name' => 'Mike Chen', 'email' => 'mike@example.com'],
            ['name' => 'Emily Davis', 'email' => 'emily@example.com'],
            ['name' => 'David Wilson', 'email' => 'david@example.com'],
        ];

        $customers = collect();

        foreach ($customerData as $data) {
            $customer = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'role' => 'customer',
                'is_active' => true,
                'remember_token' => Str::random(10),
            ]);

            $customers->push($customer);
        }

        return $customers;
    }

    private function createProductsForVendors($vendors): void
    {
        $pizzaProducts = [
            ['name' => 'Margherita Pizza', 'price' => 250.00, 'category' => 'Pizza'],
            ['name' => 'Pepperoni Pizza', 'price' => 280.00, 'category' => 'Pizza'],
            ['name' => 'Quattro Stagioni', 'price' => 320.00, 'category' => 'Pizza'],
            ['name' => 'Spaghetti Carbonara', 'price' => 180.00, 'category' => 'Pasta'],
            ['name' => 'Chicken Alfredo', 'price' => 200.00, 'category' => 'Pasta'],
        ];

        $burgerProducts = [
            ['name' => 'Classic Cheeseburger', 'price' => 180.00, 'category' => 'Burgers'],
            ['name' => 'BBQ Bacon Burger', 'price' => 220.00, 'category' => 'Burgers'],
            ['name' => 'Crispy Chicken Sandwich', 'price' => 200.00, 'category' => 'Sandwiches'],
            ['name' => 'French Fries', 'price' => 80.00, 'category' => 'Sides'],
            ['name' => 'Onion Rings', 'price' => 90.00, 'category' => 'Sides'],
        ];

        foreach ($vendors as $vendor) {
            $products = $vendor->user->name === 'Mario\'s Pizza' ? $pizzaProducts : $burgerProducts;

            foreach ($products as $productData) {
                $product = Product::create([
                    'vendor_id' => $vendor->id,
                    'name' => $productData['name'],
                    'price' => $productData['price'],
                    'category' => $productData['category'],
                    'stock_quantity' => rand(10, 50),
                    'is_active' => true,
                ]);

                // Create addons for each product
                $this->createAddonsForProduct($product);
            }
        }
    }

    private function createAddonsForProduct(Product $product): void
    {
        $addonTemplates = [
            'extra_cheese' => ['name' => 'Extra Cheese', 'price' => 30.00],
            'bacon' => ['name' => 'Bacon', 'price' => 50.00],
            'mushrooms' => ['name' => 'Mushrooms', 'price' => 25.00],
            'olives' => ['name' => 'Black Olives', 'price' => 20.00],
        ];

        $numberOfAddons = rand(2, 3);
        $selectedAddons = array_rand($addonTemplates, $numberOfAddons);

        if (!is_array($selectedAddons)) {
            $selectedAddons = [$selectedAddons];
        }

        foreach ($selectedAddons as $addonKey) {
            $addonData = $addonTemplates[$addonKey];
            Addon::create([
                'product_id' => $product->id,
                'name' => $addonData['name'],
                'price' => $addonData['price'],
                'is_active' => true,
            ]);
        }
    }

    private function createSampleOrders($vendors, $customers): void
    {
        $statuses = ['pending', 'accepted', 'ready_for_pickup', 'cancelled'];
        $paymentMethods = ['cashier', 'qr_code'];
        $tableNumbers = ['A1', 'A2', 'A3', 'B1', 'B2'];

        for ($i = 0; $i < 10; $i++) {
            $vendor = $vendors->random();
            $customer = $customers->random();
            $status = $statuses[array_rand($statuses)];
            $paymentMethod = $paymentMethods[array_rand($paymentMethods)];
            $tableNumber = $tableNumbers[array_rand($tableNumbers)];

            // Create order
            $order = Order::create([
                'customer_id' => $customer->id,
                'vendor_id' => $vendor->id,
                'order_number' => 'ORD-' . str_pad($i + 1, 6, '0', STR_PAD_LEFT),
                'status' => $status,
                'total_amount' => 0, // Will be calculated
                'payment_method' => $paymentMethod,
                'table_number' => $tableNumber,
                'special_instructions' => rand(0, 1) ? 'Please make it spicy!' : null,
                'created_at' => now()->subDays(rand(0, 7)),
                'updated_at' => now()->subDays(rand(0, 7)),
            ]);

            // Create order items
            $products = $vendor->products()->inRandomOrder()->limit(rand(1, 2))->get();
            $totalAmount = 0;

            foreach ($products as $product) {
                $quantity = rand(1, 2);
                $selectedAddons = $product->addons()->inRandomOrder()->limit(rand(0, 1))->get();
                $addonsTotal = $selectedAddons->sum('price') * $quantity;
                $itemTotal = ($product->price * $quantity) + $addonsTotal;
                $totalAmount += $itemTotal;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $product->price,
                    'total_price' => $itemTotal,
                    'selected_addons' => $selectedAddons->map(function ($addon) {
                        return [
                            'id' => $addon->id,
                            'name' => $addon->name,
                            'price' => $addon->price,
                        ];
                    })->toArray(),
                ]);
            }

            // Update order total
            $order->update(['total_amount' => $totalAmount]);

            // Mark as completed if status is ready_for_pickup
            if ($status === 'ready_for_pickup') {
                $order->update(['completed_at' => $order->updated_at]);
            }
        }
    }

    private function createNotifications($vendors, $customers): void
    {
        $notificationTypes = [
            'order_received' => 'New Order Received',
            'order_accepted' => 'Order Accepted',
            'order_ready' => 'Order Ready for Pickup',
            'order_cancelled' => 'Order Cancelled',
            'system' => 'System Notification',
        ];

        $messages = [
            'order_received' => 'You have received a new order that needs attention.',
            'order_accepted' => 'Your order has been accepted and is being prepared.',
            'order_ready' => 'Your order is ready for pickup!',
            'order_cancelled' => 'Your order has been cancelled.',
            'system' => 'System maintenance scheduled for tonight.',
        ];

        // Create vendor notifications
        foreach ($vendors as $vendor) {
            for ($i = 0; $i < rand(2, 4); $i++) {
                $type = array_rand($notificationTypes);
                Notification::create([
                    'user_id' => $vendor->user->id,
                    'title' => $notificationTypes[$type],
                    'message' => $messages[$type],
                    'type' => $type,
                    'data' => json_encode(['vendor_id' => $vendor->id]),
                    'read_at' => rand(0, 1) ? now()->subHours(rand(1, 24)) : null,
                    'created_at' => now()->subHours(rand(1, 72)),
                ]);
            }
        }

        // Create customer notifications
        foreach ($customers as $customer) {
            for ($i = 0; $i < rand(1, 3); $i++) {
                $type = array_rand($notificationTypes);
                Notification::create([
                    'user_id' => $customer->id,
                    'title' => $notificationTypes[$type],
                    'message' => $messages[$type],
                    'type' => $type,
                    'data' => json_encode(['customer_id' => $customer->id]),
                    'read_at' => rand(0, 1) ? now()->subHours(rand(1, 24)) : null,
                    'created_at' => now()->subHours(rand(1, 72)),
                ]);
            }
        }
    }

    private function printSummary(): void
    {
        $this->command->info('');
        $this->command->info('📊 SIMPLIFIED SEEDING SUMMARY:');
        $this->command->info('================================');
        $this->command->info('Users: ' . User::count() . ' (1 Superadmin, ' . User::where('role', 'vendor')->count() . ' Vendors, ' . User::where('role', 'customer')->count() . ' Customers)');
        $this->command->info('Vendors: ' . Vendor::count());
        $this->command->info('Products: ' . Product::count() . ' (5 per vendor)');
        $this->command->info('Addons: ' . Addon::count());
        $this->command->info('Orders: ' . Order::count());
        $this->command->info('Order Items: ' . OrderItem::count());
        $this->command->info('Notifications: ' . Notification::count());
        $this->command->info('');
        $this->command->info('🔑 TEST ACCOUNTS:');
        $this->command->info('==================');
        $this->command->info('Superadmin: admin@foodcourt.com / password');
        $this->command->info('Vendor 1: mario@pizza.com / password');
        $this->command->info('Vendor 2: orders@burgerbarn.com / password');
        $this->command->info('Customer: john@example.com / password');
        $this->command->info('');
        $this->command->info('✅ Ready to test the vendor system!');
    }
}
