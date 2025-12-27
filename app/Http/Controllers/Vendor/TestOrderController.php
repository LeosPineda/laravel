<?php

namespace App\Http\Controllers\Vendor;

use App\Events\OrderReceived;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TestOrderController extends Controller
{
    /**
     * Create a test order to simulate customer placing an order.
     * This will trigger the OrderReceived event for real-time testing.
     */
    public function createTestOrder(Request $request)
    {
        $user = $request->user();
        $vendor = $user->vendor;

        if (!$vendor) {
            return response()->json(['error' => 'Vendor not found'], 404);
        }

        // Get random products from this vendor (or use specified)
        $productIds = $request->input('product_ids', []);

        if (empty($productIds)) {
            // Get 1-3 random products from vendor
            $products = Product::where('vendor_id', $vendor->id)
                ->where('is_active', true)
                ->inRandomOrder()
                ->limit(rand(1, 3))
                ->get();
        } else {
            $products = Product::where('vendor_id', $vendor->id)
                ->whereIn('id', $productIds)
                ->get();
        }

        if ($products->isEmpty()) {
            return response()->json([
                'error' => 'No products available. Please add some products first.'
            ], 400);
        }

        // Create a test customer if not exists
        $testCustomer = User::firstOrCreate(
            ['email' => 'test-customer@example.com'],
            [
                'name' => 'Test Customer',
                'password' => bcrypt('password'),
                'role' => 'customer',
            ]
        );

        DB::beginTransaction();

        try {
            // Generate order number
            $orderNumber = 'ORD-' . strtoupper(Str::random(8));

            // Calculate totals
            $totalAmount = 0;
            $orderItems = [];

            foreach ($products as $product) {
                $quantity = rand(1, 3);
                $price = $product->price;
                $itemTotal = $price * $quantity;
                $totalAmount += $itemTotal;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $price,
                    'total_price' => $itemTotal,
                    'selected_addons' => null,
                ];
            }

            // Create the order
            $order = Order::create([
                'order_number' => $orderNumber,
                'vendor_id' => $vendor->id,
                'user_id' => $testCustomer->id,
                'status' => 'pending',
                'payment_status' => 'paid', // Simulate paid order
                'payment_method' => $request->input('payment_method', 'qr_code'),
                'total_amount' => $totalAmount,
                'table_number' => $request->input('table_number', 'T-' . rand(1, 20)),
                'special_instructions' => $request->input('special_instructions', null),
            ]);

            // Create order items
            foreach ($orderItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['total_price'],
                    'selected_addons' => $item['selected_addons'],
                ]);
            }

            DB::commit();

            // Load relationships for broadcast
            $order->load('items.product', 'customer');

            // Broadcast the event to vendor channel
            broadcast(new OrderReceived($vendor, $order))->toOthers();

            return response()->json([
                'success' => true,
                'message' => "Test order #{$orderNumber} created and broadcast!",
                'order' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'total_amount' => $order->total_amount,
                    'table_number' => $order->table_number,
                    'items_count' => count($orderItems),
                    'created_at' => $order->created_at->toISOString(),
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Failed to create test order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get test order info - useful for debugging
     */
    public function getTestInfo(Request $request)
    {
        $user = $request->user();
        $vendor = $user->vendor;

        if (!$vendor) {
            return response()->json(['error' => 'Vendor not found'], 404);
        }

        $productCount = Product::where('vendor_id', $vendor->id)->where('is_active', true)->count();
        $pendingOrders = Order::where('vendor_id', $vendor->id)->where('status', 'pending')->count();

        return response()->json([
            'vendor_id' => $vendor->id,
            'vendor_name' => $vendor->name,
            'channel' => "vendor-orders.{$vendor->id}",
            'active_products' => $productCount,
            'pending_orders' => $pendingOrders,
            'pusher_config' => [
                'key' => config('broadcasting.connections.pusher.key'),
                'cluster' => config('broadcasting.connections.pusher.options.cluster'),
            ],
        ]);
    }
}
