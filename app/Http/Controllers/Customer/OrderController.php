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
use App\Services\ReceiptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function __construct(
        protected ReceiptService $receiptService
    ) {}

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
            ->where('customer_id', $user->id)
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
            ->where('customer_id', $user->id)
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
                'payment_proof' => 'nullable|file|image|mimes:jpeg,png,jpg,gif|max:5120'
            ]);

            $user = Auth::user();

            $cart = Cart::where('user_id', $user->id)
                ->where('vendor_id', $validated['vendor_id'])
                ->firstOrFail();

            $cartItems = $cart->items()->with('product')->get();

            if ($cartItems->isEmpty()) {
                return response()->json([
                    'message' => 'Cart is empty',
                    'success' => false
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
                        'total_price' => $itemTotal
                    ];
                }

                $paymentProofUrl = null;
                if ($validated['payment_method'] === 'qr_code' && $request->hasFile('payment_proof')) {
                    $paymentProofUrl = $request->file('payment_proof')->store('payment-proofs', 'public');
                }

                $orderNumber = 'ORD-' . str_pad(Order::max('id') + 1, 6, '0', STR_PAD_LEFT);

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
                    'updated_at' => now()
                ]);

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

                $cart->clear();

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
            ->where('customer_id', $user->id)
            ->where('id', $orderId)
            ->firstOrFail();

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
     * Generate and download PDF receipt for an order
     */
    public function downloadReceipt(Request $request, $orderId)
    {
        try {
            $order = Order::whereHas('customer', function ($query) {
                    $query->where('id', auth()->id());
                })
                ->findOrFail($orderId);

            return $this->receiptService->downloadReceipt($order, 'customer');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Order not found',
                'success' => false
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error generating customer receipt: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error generating receipt',
                'success' => false
            ], 500);
        }
    }

    /**
     * Generate and stream PDF receipt for an order
     */
    public function streamReceipt(Request $request, $orderId)
    {
        try {
            $order = Order::whereHas('customer', function ($query) {
                    $query->where('id', auth()->id());
                })
                ->findOrFail($orderId);

            return $this->receiptService->streamReceipt($order, 'customer');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Order not found',
                'success' => false
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error streaming customer receipt: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error generating receipt',
                'success' => false
            ], 500);
        }
    }

    /**
     * Get downloadable receipt (legacy JSON format) - DEPRECATED
     */
    public function receipt(Request $request, $orderId)
    {
        try {
            $order = Order::whereHas('customer', function ($query) {
                    $query->where('id', auth()->id());
                })
                ->findOrFail($orderId);

            if ($order->status !== 'ready_for_pickup' && $order->status !== 'completed') {
                return response()->json([
                    'message' => 'Receipt not available for this order status',
                    'success' => false
                ], 400);
            }

            return response()->json([
                'message' => 'Use PDF download endpoint instead',
                'order' => $order,
                'success' => true
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Order not found',
                'success' => false
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error getting receipt: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error retrieving receipt',
                'success' => false
            ], 500);
        }
    }

    /**
     * Cancel an order
     */
    public function cancel(Request $request, $orderId)
    {
        try {
            $user = Auth::user();

            $order = Order::where('customer_id', $user->id)
                ->where('id', $orderId)
                ->firstOrFail();

            if ($order->status !== 'pending') {
                return response()->json([
                    'message' => 'Only pending orders can be cancelled',
                    'success' => false
                ], 400);
            }

            $order->update(['status' => 'cancelled']);

            return response()->json([
                'message' => 'Order cancelled successfully',
                'order' => $order,
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
