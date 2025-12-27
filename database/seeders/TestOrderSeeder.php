<?php

namespace Database\Seeders;

use App\Events\OrderReceived;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TestOrderSeeder extends Seeder
{
    /**
     * Create test orders to simulate customer flow.
     *
     * Usage:
     *   php artisan db:seed --class=TestOrderSeeder
     */
    public function run(): void
    {
        // Get first vendor
        $vendor = Vendor::first();

        if (! $vendor) {
            $this->command->error('No vendor found. Please create a vendor first.');

            return;
        }

        // Get or create a test customer
        $customer = User::where('role', 'customer')->first();
        if (! $customer) {
            $customer = User::create([
                'name' => 'Test Customer',
                'email' => 'test.customer@example.com',
                'password' => bcrypt('password'),
                'role' => 'customer',
            ]);
            $this->command->info("Created test customer: {$customer->email}");
        }

        // Get vendor products
        $products = Product::where('vendor_id', $vendor->id)
            ->where('is_active', true)
            ->where('stock_quantity', '>', 0)
            ->get();

        if ($products->isEmpty()) {
            $this->command->error('No active products with stock found for this vendor.');

            return;
        }

        // Create test order
        $orderNumber = 'ORD-'.strtoupper(Str::random(8));
        $tableNumber = (string) rand(1, 20);

        $order = Order::create([
            'vendor_id' => $vendor->id,
            'customer_id' => $customer->id,
            'order_number' => $orderNumber,
            'status' => 'pending',
            'payment_method' => collect(['qr_code', 'cash'])->random(),
            'table_number' => $tableNumber,
            'special_instructions' => collect([null, 'No onions please', 'Extra spicy', 'Less salt'])->random(),
            'total_amount' => 0,
        ]);

        // Add 1-3 random items
        $itemCount = rand(1, min(3, $products->count()));
        $totalAmount = 0;
        $usedProducts = [];

        for ($i = 0; $i < $itemCount; $i++) {
            // Pick a product not already used
            $product = $products->whereNotIn('id', $usedProducts)->random();
            $usedProducts[] = $product->id;

            $quantity = rand(1, 2);
            $unitPrice = $product->price;
            $totalPrice = $unitPrice * $quantity;
            $totalAmount += $totalPrice;

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_price' => $totalPrice,
                'selected_addons' => null,
            ]);
        }

        // Update order total
        $order->update([
            'total_amount' => $totalAmount,
        ]);

        $this->command->info('');
        $this->command->info("✅ Created Order: {$order->order_number}");
        $this->command->info("   Vendor: {$vendor->business_name}");
        $this->command->info("   Table: {$tableNumber}");
        $this->command->info("   Items: {$itemCount}");
        $this->command->info("   Total: ₱".number_format($totalAmount, 2));

        // Dispatch real-time event
        $order->load('items.product', 'customer');
        event(new OrderReceived($vendor, $order));

        $this->command->info('');
        $this->command->info('📡 OrderReceived event dispatched!');
        $this->command->info('🔔 Check vendor dashboard for real-time notification.');
    }
}
