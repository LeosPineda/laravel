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

class VendorSystemSeederSimple extends Seeder
{
    /**
     * Run the database seeds for the vendor system (Simple Version).
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting Simple Vendor System Database Seeding...');

        // Clear existing data
        User::where('role', 'vendor')->delete();
        User::where('role', 'customer')->delete();
        User::where('role', 'superadmin')->delete();

        // Create superadmin
        User::create([
            'name' => 'System Administrator',
            'email' => 'admin@foodcourt.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => 'superadmin',
            'is_active' => true,
            'remember_token' => Str::random(10),
        ]);
        $this->command->info('✅ Created superadmin user');

        // Create vendor user
        $vendorUser = User::create([
            'name' => 'Mario\'s Pizza',
            'email' => 'mario@pizza.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => 'vendor',
            'is_active' => true,
            'remember_token' => Str::random(10),
        ]);

        // Create vendor profile
        $vendor = Vendor::create([
            'user_id' => $vendorUser->id,
            'brand_name' => 'Mario\'s Pizza',
            'is_active' => true,
        ]);
        $this->command->info('✅ Created vendor user and profile');

        // Create customers
        for ($i = 1; $i <= 5; $i++) {
            User::create([
                'name' => 'Customer ' . $i,
                'email' => 'customer' . $i . '@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'role' => 'customer',
                'is_active' => true,
                'remember_token' => Str::random(10),
            ]);
        }
        $this->command->info('✅ Created 5 customer users');

        // Create products
        $products = [
            ['name' => 'Margherita Pizza', 'price' => 250.00, 'category' => 'Pizza'],
            ['name' => 'Pepperoni Pizza', 'price' => 280.00, 'category' => 'Pizza'],
            ['name' => 'Quattro Stagioni', 'price' => 320.00, 'category' => 'Pizza'],
            ['name' => 'Spaghetti Carbonara', 'price' => 180.00, 'category' => 'Pasta'],
            ['name' => 'Chicken Alfredo', 'price' => 200.00, 'category' => 'Pasta'],
        ];

        foreach ($products as $productData) {
            $product = Product::create([
                'vendor_id' => $vendor->id,
                'name' => $productData['name'],
                'price' => $productData['price'],
                'category' => $productData['category'],
                'stock_quantity' => rand(10, 50),
                'is_active' => true,
            ]);

            // Create some addons
            Addon::create([
                'product_id' => $product->id,
                'name' => 'Extra Cheese',
                'price' => 30.00,
                'is_active' => true,
            ]);
        }
        $this->command->info('✅ Created products and addons');

        // Create orders
        $customers = User::where('role', 'customer')->get();
        $statuses = ['pending', 'accepted', 'ready_for_pickup', 'cancelled'];
        $paymentMethods = ['cashier', 'qr_code'];
        $tableNumbers = ['A1', 'A2', 'A3', 'B1', 'B2'];

        for ($i = 0; $i < 5; $i++) {
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
                'total_amount' => 0,
                'payment_method' => $paymentMethod,
                'table_number' => $tableNumber,
                'special_instructions' => null,
                'created_at' => now()->subDays(rand(0, 7)),
                'updated_at' => now()->subDays(rand(0, 7)),
            ]);

            // Create order items
            $products = Product::where('vendor_id', $vendor->id)->inRandomOrder()->limit(rand(1, 2))->get();
            $totalAmount = 0;

            foreach ($products as $product) {
                $quantity = rand(1, 2);
                $itemTotal = $product->price * $quantity;
                $totalAmount += $itemTotal;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $product->price,
                    'total_price' => $itemTotal,
                    'selected_addons' => [],
                ]);
            }

            $order->update(['total_amount' => $totalAmount]);
        }
        $this->command->info('✅ Created orders');

        $this->command->info('🎉 Vendor System Database Seeding Completed!');
        $this->printSummary();
    }

    private function printSummary(): void
    {
        $this->command->info('');
        $this->command->info('📊 SIMPLIFIED SEEDING SUMMARY:');
        $this->command->info('================================');
        $this->command->info('Users: ' . User::count() . ' (1 Superadmin, ' . User::where('role', 'vendor')->count() . ' Vendors, ' . User::where('role', 'customer')->count() . ' Customers)');
        $this->command->info('Vendors: ' . Vendor::count());
        $this->command->info('Products: ' . Product::count());
        $this->command->info('Addons: ' . Addon::count());
        $this->command->info('Orders: ' . Order::count());
        $this->command->info('Order Items: ' . OrderItem::count());
        $this->command->info('Notifications: ' . Notification::count());
        $this->command->info('');
        $this->command->info('🔑 TEST ACCOUNTS:');
        $this->command->info('==================');
        $this->command->info('Superadmin: admin@foodcourt.com / password');
        $this->command->info('Vendor: mario@pizza.com / password');
        $this->command->info('Customer: customer1@example.com / password');
        $this->command->info('');
        $this->command->info('✅ Ready to test the vendor system!');
    }
}
