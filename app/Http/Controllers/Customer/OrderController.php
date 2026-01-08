<?php

namespace App\Http\Controllers\Customer;

use App\Events\OrderReceived;
use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Vendor;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    /**
     * Get customer's orders
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();

            $query = Order::with([
                'vendor:id,brand_name,brand_image',
                'items.product:id,name,image_url',
            ])
                ->where('customer_id', $user->id);

            // Filter by status if provided
            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            $orders = $query->orderBy('created_at', 'desc')
                ->paginate(15);

            return response()->json([
                'orders' => $orders,
                'success' => true,
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting customer orders: '.$e->getMessage());

            return response()->json([
                'message' => 'Error retrieving orders',
                'success' => false,
            ], 500);
        }
    }

    /**
     * Get specific order details
     */
    public function show(Request $request, $orderId)
    {
        try {
            $user = Auth::user();

            $order = Order::with([
                'vendor:id,brand_name,brand_image',
                'items.product:id,name,image_url',
            ])
                ->where('customer_id', $user->id)
                ->where('id', $orderId)
                ->firstOrFail();

            return response()->json([
                'order' => $order,
                'success' => true,
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Order not found',
                'success' => false,
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error getting order: '.$e->getMessage());

            return response()->json([
                'message' => 'Error retrieving order',
                'success' => false,
            ], 500);
        }
    }

    /**
     * Place new order from cart
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'vendor_id' => 'required|exists:vendors,id',
                'payment_method' => ['required', Rule::in(['cashier', 'qr_code'])],
                'table_number' => 'required|string|max:10',
                'special_instructions' => 'nullable|string|max:500',
                'payment_proof' => 'nullable|file|image|mimes:jpeg,png,jpg,gif|max:5120',
            ]);

            $user = Auth::user();

            $cart = Cart::where('user_id', $user->id)
                ->where('vendor_id', $validated['vendor_id'])
                ->firstOrFail();

            $cartItems = $cart->items()->with('product')->get();

            if ($cartItems->isEmpty()) {
                return response()->json([
                    'message' => 'Cart is empty',
                    'success' => false,
                ], 400);
            }

            DB::beginTransaction();

            try {
                $subtotal = 0;
                $itemsData = [];

                foreach ($cartItems as $item) {
                    $addonsTotal = collect($item->selected_addons)->sum('price') ?? 0;
                    $itemTotal = ($item->unit_price + $addonsTotal) * $item->quantity;
                    $subtotal += $itemTotal;

                    $itemsData[] = [
                        'product_id' => $item->product_id,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'selected_addons' => $item->selected_addons,
                        'total_price' => $itemTotal,
                    ];
                }

                $paymentProofUrl = null;
                if ($validated['payment_method'] === 'qr_code' && $request->hasFile('payment_proof')) {
                    $paymentProofUrl = $request->file('payment_proof')->store('payment-proofs', 'public');
                }

                $orderNumber = 'ORD-'.str_pad(Order::max('id') + 1, 6, '0', STR_PAD_LEFT);

                $order = Order::create([
                    'customer_id' => $user->id,
                    'vendor_id' => $validated['vendor_id'],
                    'order_number' => $orderNumber,
                    'status' => 'pending',
                    'total_amount' => $subtotal,
                    'payment_method' => $validated['payment_method'],
                    'table_number' => $validated['table_number'],
                    'special_instructions' => $validated['special_instructions'] ?? null,
                    'payment_proof_url' => $paymentProofUrl,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                foreach ($itemsData as $itemData) {
                    $orderItem = OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $itemData['product_id'],
                        'quantity' => $itemData['quantity'],
                        'unit_price' => $itemData['unit_price'],
                        'selected_addons' => $itemData['selected_addons'],
                        'total_price' => $itemData['total_price'],
                    ]);

                    // 🔧 FIX: Deduct stock with locking to prevent race conditions
                    // Use lockForUpdate to prevent concurrent modifications
                    $product = Product::lockForUpdate()->find($itemData['product_id']);
                    if ($product->stock_quantity < $itemData['quantity']) {
                        throw new \Exception("Insufficient stock for product '{$product->name}'. Available: {$product->stock_quantity}, Requested: {$itemData['quantity']}");
                    }
                    $product->decrement('stock_quantity', $itemData['quantity']);
                }

                $cart->clear();

                // FIXED: Broadcast OrderReceived event to vendor
                event(new OrderReceived($order->vendor, $order));

                // ✅ FIXED: Create vendor notification in database
                $vendorNotification = Notification::create([
                    'vendor_id' => $order->vendor->id,
                    'type' => 'order',
                    'title' => 'New Order Received! 🛒',
                    'message' => "Order #{$order->order_number} from table {$order->table_number} - ₱{$order->total_amount}",
                    'is_read' => false,
                    'created_at' => now(),
                ]);

                DB::commit();

                return response()->json([
                    'message' => 'Order placed successfully',
                    'order' => $order->load(['vendor', 'items.product']),
                    'success' => true,
                ], 201);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors(),
                'success' => false,
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Cart not found',
                'success' => false,
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error placing order: '.$e->getMessage());

            return response()->json([
                'message' => 'Error placing order',
                'success' => false,
            ], 500);
        }
    }

    /**
     * Track order status
     */
    public function track(Request $request, $orderId)
    {
        try {
            $user = Auth::user();

            $order = Order::with([
                'vendor:id,brand_name,brand_image',
                'items.product:id,name,image_url',
            ])
                ->where('customer_id', $user->id)
                ->where('id', $orderId)
                ->firstOrFail();

            $statusHistory = [
                [
                    'status' => 'pending',
                    'label' => 'Order Placed',
                    'description' => 'Your order has been received',
                    'timestamp' => $order->created_at,
                    'completed' => true,
                ],
            ];

            if ($order->status === 'accepted') {
                $statusHistory[] = [
                    'status' => 'accepted',
                    'label' => 'Accepted & Preparing',
                    'description' => 'Your order has been accepted and is being prepared',
                    'timestamp' => $order->updated_at,
                    'completed' => true,
                ];
            } elseif (in_array($order->status, ['ready_for_pickup', 'completed'])) {
                $statusHistory[] = [
                    'status' => 'accepted',
                    'label' => 'Accepted & Preparing',
                    'description' => 'Your order has been accepted and is being prepared',
                    'timestamp' => $order->updated_at,
                    'completed' => true,
                ];
                // ✅ REMOVED: Separate "preparing" status (now combined with accepted)
                $statusHistory[] = [
                    'status' => 'ready_for_pickup',
                    'label' => 'Ready for Pickup',
                    'description' => 'Your order is ready for pickup',
                    'timestamp' => $order->updated_at,
                    'completed' => true,
                ];
            }

            if ($order->status === 'completed') {
                $statusHistory[] = [
                    'status' => 'completed',
                    'label' => 'Completed',
                    'description' => 'Order completed',
                    'timestamp' => $order->updated_at,
                    'completed' => true,
                ];
            } elseif ($order->status === 'cancelled') { // FIXED: Use 'cancelled' consistently
                $statusHistory[] = [
                    'status' => 'cancelled',
                    'label' => 'Cancelled',
                    'description' => 'Order was cancelled by vendor',
                    'timestamp' => $order->updated_at,
                    'completed' => true,
                ];
            }

            return response()->json([
                'order' => $order,
                'status_history' => $statusHistory,
                'success' => true,
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Order not found',
                'success' => false,
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error tracking order: '.$e->getMessage());

            return response()->json([
                'message' => 'Error tracking order',
                'success' => false,
            ], 500);
        }
    }

    /**
     * Get order history with filters
     */
    public function history(Request $request)
    {
        try {
            $user = Auth::user();

            $query = Order::with([
                'vendor:id,brand_name,brand_image',
                'items.product:id,name,image_url',
            ])
                ->where('customer_id', $user->id);

            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            if ($request->has('vendor_id') && $request->vendor_id) {
                $query->where('vendor_id', $request->vendor_id);
            }

            if ($request->has('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->has('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $orders = $query->orderBy('created_at', 'desc')->paginate(15);

            return response()->json([
                'orders' => $orders,
                'success' => true,
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting order history: '.$e->getMessage());

            return response()->json([
                'message' => 'Error retrieving order history',
                'success' => false,
            ], 500);
        }
    }

    /**
     * Generate and download PDF receipt for an order
     */
    public function downloadReceipt(Request $request, $orderId)
    {
        try {
            $order = Order::with(['vendor', 'items.product', 'customer'])
                ->where('customer_id', auth()->id())
                ->where('id', $orderId)
                ->whereIn('status', ['ready_for_pickup', 'completed'])
                ->firstOrFail();

            $pdf = Pdf::loadView('receipts.customer', compact('order'));

            $fileName = "receipt-{$order->order_number}.pdf";

            return $pdf->download($fileName);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Order not found or receipt not available',
                'success' => false,
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error generating customer receipt: '.$e->getMessage());

            return response()->json([
                'message' => 'Error generating receipt',
                'success' => false,
            ], 500);
        }
    }

    /**
     * Generate and stream PDF receipt for an order (view in browser)
     */
    public function streamReceipt(Request $request, $orderId)
    {
        try {
            $order = Order::with(['vendor', 'items.product', 'customer'])
                ->where('customer_id', auth()->id())
                ->where('id', $orderId)
                ->whereIn('status', ['ready_for_pickup', 'completed'])
                ->firstOrFail();

            $pdf = Pdf::loadView('receipts.customer', compact('order'));

            $fileName = "receipt-{$order->order_number}.pdf";

            return $pdf->stream($fileName);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Order not found or receipt not available',
                'success' => false,
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error streaming customer receipt: '.$e->getMessage());

            return response()->json([
                'message' => 'Error generating receipt',
                'success' => false,
            ], 500);
        }
    }

    /**
     * Cancel an order and restore cart items
     */
    public function cancel(Request $request, $orderId)
    {
        try {
            $user = Auth::user();

            $order = Order::with('items.product')
                ->where('customer_id', $user->id)
                ->where('id', $orderId)
                ->firstOrFail();

            if ($order->status !== 'pending') {
                return response()->json([
                    'message' => 'Only pending orders can be cancelled',
                    'success' => false,
                ], 400);
            }

            DB::beginTransaction();

            try {
                // Restore stock for cancelled order items
                foreach ($order->items as $item) {
                    if ($item->product) {
                        $item->product->increment('stock_quantity', $item->quantity);
                    }
                }

                // Restore items to cart
                $cart = Cart::firstOrCreate(
                    ['user_id' => $user->id, 'vendor_id' => $order->vendor_id],
                    ['created_at' => now(), 'updated_at' => now()]
                );

                foreach ($order->items as $item) {
                    CartItem::create([
                        'cart_id' => $cart->id,
                        'product_id' => $item->product_id,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'selected_addons' => $item->selected_addons,
                        'special_instructions' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // Update order status
                $order->update(['status' => 'cancelled']);

                DB::commit();

                return response()->json([
                    'message' => 'Order cancelled. Items restored to cart.',
                    'order' => $order,
                    'success' => true,
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Order not found',
                'success' => false,
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error cancelling order: '.$e->getMessage());

            return response()->json([
                'message' => 'Error cancelling order',
                'success' => false,
            ], 500);
        }
    }
}
