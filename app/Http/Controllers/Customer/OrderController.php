<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Vendor;
use App\Events\OrderReceived;
use App\Events\OrderStatusChanged;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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

            $orders = Order::with([
                'vendor:id,brand_name,brand_image',
                'items.product:id,name,image_url',
                'items.selectedAddons'
            ])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

            return response()->json([
                'orders' => $orders,
                'success' => true
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting customer orders: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error retrieving orders',
                'success' => false
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
                'items.selectedAddons'
            ])
            ->where('user_id', $user->id)
            ->where('id', $orderId)
            ->firstOrFail();

            return response()->json([
                'order' => $order,
                'success' => true
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Order not found',
                'success' => false
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error getting order: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error retrieving order',
                'success' => false
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
                'payment_proof' => 'nullable|file|image|mimes:jpeg,png,jpg,gif|max:5120' // 5MB max
            ]);

            $user = Auth::user();

            // Get cart for this vendor
            $cart = Cart::where('user_id', $user->id)
                ->where('vendor_id', $validated['vendor_id'])
                ->firstOrFail();

            // Get cart items
            $cartItems = $cart->items()->with('product')->get();

            if ($cartItems->isEmpty()) {
                return response()->json([
                    'message' => 'Cart is empty',
                    'success' => false
                ], 400);
            }

            DB::beginTransaction();

            try {
                // Calculate total
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
                        'total_price' => $itemTotal
                    ];
                }

                // Handle payment proof upload
                $paymentProofUrl = null;
                if ($validated['payment_method'] === 'qr_code' && $request->hasFile('payment_proof')) {
                    $paymentProofUrl = $request->file('payment_proof')->store('payment-proofs', 'public');
                }

                // Create order
                $orderNumber = 'ORD-' . str_pad(Order::max('id') + 1, 6, '0', STR_PAD_LEFT);

                $order = Order::create([
                    'user_id' => $user->id,
                    'vendor_id' => $validated['vendor_id'],
                    'order_number' => $orderNumber,
                    'status' => 'pending',
                    'total_amount' => $subtotal,
                    'payment_method' => $validated['payment_method'],
                    'table_number' => $validated['table_number'],
                    'special_instructions' => $validated['special_instructions'] ?? null,
                    'payment_proof_url' => $paymentProofUrl,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // Create order items
                foreach ($itemsData as $itemData) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $itemData['product_id'],
                        'quantity' => $itemData['quantity'],
                        'unit_price' => $itemData['unit_price'],
                        'selected_addons' => $itemData['selected_addons'],
                        'total_price' => $itemData['total_price']
                    ]);
                }

                // Clear cart
                $cart->clear();

                // Broadcast events
                event(new OrderReceived($order->vendor, $order));
                event(new OrderStatusChanged($order->vendor, $order, $order->customer, 'pending', 'accepted'));

                DB::commit();

                return response()->json([
                    'message' => 'Order placed successfully',
                    'order' => $order->load(['vendor', 'items.product']),
                    'success' => true
                ], 201);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors(),
                'success' => false
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Cart not found',
                'success' => false
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error placing order: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error placing order',
                'success' => false
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
                'items.selectedAddons'
            ])
            ->where('user_id', $user->id)
            ->where('id', $orderId)
            ->firstOrFail();

            // Get status timeline
            $statusHistory = [
                [
                    'status' => 'pending',
                    'label' => 'Order Placed',
                    'description' => 'Your order has been received',
                    'timestamp' => $order->created_at,
                    'completed' => true
                ]
            ];

            if ($order->status === 'accepted') {
                $statusHistory[] = [
                    'status' => 'accepted',
                    'label' => 'Accepted',
                    'description' => 'Vendor has accepted your order',
                    'timestamp' => $order->updated_at,
                    'completed' => true
                ];
            } elseif (in_array($order->status, ['ready_for_pickup', 'completed'])) {
                $statusHistory[] = [
                    'status' => 'accepted',
                    'label' => 'Accepted',
                    'description' => 'Vendor has accepted your order',
                    'timestamp' => $order->updated_at,
                    'completed' => true
                ];
                $statusHistory[] = [
                    'status' => 'preparing',
                    'label' => 'Preparing',
                    'description' => 'Your food is being prepared',
                    'timestamp' => $order->updated_at,
                    'completed' => true
                ];
                $statusHistory[] = [
                    'status' => 'ready_for_pickup',
                    'label' => 'Ready for Pickup',
                    'description' => 'Your order is ready for pickup',
                    'timestamp' => $order->updated_at,
                    'completed' => true
                ];
            }

            if ($order->status === 'completed') {
                $statusHistory[] = [
                    'status' => 'completed',
                    'label' => 'Completed',
                    'description' => 'Order completed',
                    'timestamp' => $order->updated_at,
                    'completed' => true
                ];
            } elseif ($order->status === 'declined') {
                $statusHistory[] = [
                    'status' => 'declined',
                    'label' => 'Declined',
                    'description' => 'Order was declined by vendor',
                    'timestamp' => $order->updated_at,
                    'completed' => true
                ];
            }

            return response()->json([
                'order' => $order,
                'status_history' => $statusHistory,
                'success' => true
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Order not found',
                'success' => false
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error tracking order: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error tracking order',
                'success' => false
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
                'items.product:id,name,image_url'
            ])
            ->where('user_id', $user->id);

            // Apply filters
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
                'success' => true
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting order history: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error retrieving order history',
                'success' => false
            ], 500);
        }
    }

    /**
     * Get downloadable receipt
     */
    public function receipt(Request $request, $orderId)
    {
        try {
            $user = Auth::user();

            $order = Order::with([
                'vendor:id,brand_name,brand_image',
                'items.product:id,name,image_url',
                'items.selectedAddons'
            ])
            ->where('user_id', $user->id)
            ->where('id', $orderId)
            ->firstOrFail();

            if ($order->status !== 'ready_for_pickup' && $order->status !== 'completed') {
                return response()->json([
                    'message' => 'Receipt not available for this order status',
                    'success' => false
                ], 400);
            }

            // Calculate subtotals
            $itemsSubtotal = 0;
            $addonsSubtotal = 0;

            $formattedItems = $order->items->map(function ($item) use (&$itemsSubtotal, &$addonsSubtotal) {
                $basePrice = $item->unit_price * $item->quantity;
                $itemsSubtotal += $basePrice;

                $addons = $item->selected_addons ?? [];
                $addonTotal = collect($addons)->sum('price') * $item->quantity;
                $addonsSubtotal += $addonTotal;

                return [
                    'name' => $item->product->name,
                    'quantity' => $item->quantity,
                    'unit_price' => number_format($item->unit_price, 2),
                    'base_total' => number_format($basePrice, 2),
                    'addons' => collect($addons)->map(function ($addon) use ($item) {
                        return [
                            'name' => $addon['name'],
                            'price' => number_format($addon['price'], 2),
                            'total' => number_format($addon['price'] * $item->quantity, 2)
                        ];
                    }),
                    'addon_total' => number_format($addonTotal, 2),
                    'line_total' => number_format($item->total_price, 2)
                ];
            });

            // Generate receipt data
            $receipt = [
                // Header
                'receipt_id' => 'RCP-' . str_pad($order->id, 6, '0', STR_PAD_LEFT),
                'order_number' => $order->order_number,
                'order_date' => $order->created_at->format('F d, Y'),
                'order_time' => $order->created_at->format('g:i A'),

                // Vendor Info
                'vendor_name' => $order->vendor->brand_name,
                'vendor_logo' => $order->vendor->brand_image,

                // Customer Info
                'customer_name' => $user->name,
                'table_number' => $order->table_number,

                // Order Items
                'items' => $formattedItems,
                'item_count' => $order->items->sum('quantity'),

                // Pricing Breakdown
                'subtotal' => number_format($itemsSubtotal, 2),
                'addons_total' => number_format($addonsSubtotal, 2),
                'total_amount' => number_format($order->total_amount, 2),

                // Payment Info
                'payment_method' => $order->payment_method === 'qr_code' ? 'QR Code Payment' : 'Pay at Cashier',
                'payment_status' => $order->status === 'completed' ? 'Paid' : 'Pending',

                // Special Instructions
                'special_instructions' => $order->special_instructions,

                // Status
                'status' => ucfirst(str_replace('_', ' ', $order->status)),
                'completed_at' => $order->status === 'completed' ? $order->updated_at->format('F d, Y g:i A') : null,

                // Footer
                'footer_message' => 'Thank you for your order! 🍽️ Enjoy your meal!',
                'generated_at' => now()->format('F d, Y g:i A')
            ];

            return response()->json([
                'receipt' => $receipt,
                'success' => true
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Order not found',
                'success' => false
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error generating receipt: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error generating receipt',
                'success' => false
            ], 500);
        }
    }

    /**
     * Cancel order (if allowed)
     */
    public function cancel(Request $request, $orderId)
    {
        try {
            $user = Auth::user();

            $order = Order::where('user_id', $user->id)
                ->where('id', $orderId)
                ->firstOrFail();

            // Only allow cancellation if order is still pending
            if ($order->status !== 'pending') {
                return response()->json([
                    'message' => 'Order cannot be cancelled at this stage',
                    'success' => false
                ], 400);
            }

            $order->update([
                'status' => 'cancelled',
                'updated_at' => now()
            ]);

            // Broadcast status change
            event(new OrderStatusChanged($order->vendor, $order, $order->customer, 'pending', 'cancelled'));

            return response()->json([
                'message' => 'Order cancelled successfully',
                'success' => true
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Order not found',
                'success' => false
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error cancelling order: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error cancelling order',
                'success' => false
            ], 500);
        }
    }
}
