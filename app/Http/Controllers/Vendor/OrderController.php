<?php

namespace App\Http\Controllers\Vendor;

use App\Events\OrderStatusChanged;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Vendor;
use App\Models\User;
use App\Models\Notification;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /**
     * Display the vendor's orders with filtering.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $vendor = $this->getCurrentVendor();
            if (!$vendor) {
                return response()->json(['error' => 'Vendor not found'], 404);
            }

            $status = $request->query('status');
            $perPage = min($request->query('per_page', 20), 100);
            $search = $request->query('search');

            $query = Order::forVendor($vendor->id)
                ->with(['items.product', 'customer'])
                ->orderBy('created_at', 'desc');

            // Apply status filter
            if ($status && in_array($status, ['pending', 'accepted', 'ready_for_pickup', 'cancelled'])) {
                $query->byStatus($status);
            }

            // Apply search filter
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%")
                      ->orWhere('table_number', 'like', "%{$search}%");
                });
            }

            $orders = $query->paginate($perPage);

            return response()->json([
                'orders' => $orders->items(),
                'pagination' => [
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                    'per_page' => $orders->perPage(),
                    'total' => $orders->total(),
                ],
                'stats' => $this->getOrderStats($vendor->id)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching vendor orders', [
                'vendor_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to fetch orders'], 500);
        }
    }

    /**
     * Display a specific order with special instructions.
     */
    public function show(Order $order): JsonResponse
    {
        try {
            $vendor = $this->getCurrentVendor();
            if (!$vendor || $order->vendor_id !== $vendor->id) {
                return response()->json(['error' => 'Order not found'], 404);
            }

            // Load order items with product details and customer info
            $order->load([
                'items.product.addons',
                'customer:id,name,email'
            ]);

            // Add special_instructions to the response
            $orderData = $order->toArray();
            $orderData['special_instructions'] = $order->special_instructions;

            return response()->json(['order' => $orderData]);

        } catch (\Exception $e) {
            Log::error('Error fetching order details', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to fetch order details'], 500);
        }
    }

    /**
     * Accept an order.
     */
    public function accept(Request $request, Order $order): JsonResponse
    {
        try {
            $vendor = $this->getCurrentVendor();
            if (!$vendor || $order->vendor_id !== $vendor->id) {
                return response()->json(['error' => 'Order not found'], 404);
            }

            if (!$order->isPending()) {
                return response()->json(['error' => 'Order cannot be accepted'], 400);
            }

            $oldStatus = $order->status;

            DB::beginTransaction();

            try {
                $order->update([
                    'status' => 'accepted'
                ]);

                // Broadcast status change event
                event(new OrderStatusChanged($vendor, $order, $order->customer, $oldStatus, 'accepted'));

                // ✅ UPDATED: Combined "Accepted & Preparing" notification
                $customerNotification = Notification::create([
                    'user_id' => $order->customer_id,
                    'vendor_id' => $vendor->id,
                    'order_id' => $order->id,
                    'type' => 'order_status',
                    'title' => 'Order Accepted & Preparing 👨‍🍳',
                    'message' => "Your order #{$order->order_number} has been accepted and is now being prepared. Please wait for updates.",
                    'is_read' => false,
                    'created_at' => now(),
                ]);

                DB::commit();

                return response()->json([
                    'message' => 'Order accepted successfully',
                    'order' => $order->fresh(['items.product', 'customer'])
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Error accepting order', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to accept order'], 500);
        }
    }

    /**
     * Decline an order with reason.
     */
    public function decline(Request $request, Order $order): JsonResponse
    {
        try {
            $vendor = $this->getCurrentVendor();
            if (!$vendor || $order->vendor_id !== $vendor->id) {
                return response()->json(['error' => 'Order not found'], 404);
            }

            if (!$order->isPending()) {
                return response()->json(['error' => 'Order cannot be declined'], 400);
            }

            $oldStatus = $order->status;

            // Validate decline reason
            $request->validate([
                'decline_reason' => 'required|string|max:255'
            ]);

            $declineReason = $request->input('decline_reason');

            DB::beginTransaction();

            try {
                $order->update([
                    'status' => 'cancelled',
                    'decline_reason' => $declineReason
                ]);

                // Broadcast status change event
                event(new OrderStatusChanged($vendor, $order, $order->customer, $oldStatus, 'cancelled'));

                // Create customer notification with decline reason
                $declineReasonDisplay = $this->getDeclineReasonDisplay($declineReason);
                $customerNotification = Notification::create([
                    'user_id' => $order->customer_id,
                    'vendor_id' => $vendor->id,
                    'order_id' => $order->id,
                    'type' => 'order_status',
                    'title' => 'Order Cancelled ❌',
                    'message' => "Your order #{$order->order_number} has been declined. Reason: {$declineReasonDisplay}",
                    'is_read' => false,
                    'created_at' => now(),
                ]);

                DB::commit();

                return response()->json([
                    'message' => 'Order declined successfully',
                    'order' => $order->fresh(['items.product', 'customer'])
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Error declining order', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to decline order'], 500);
        }
    }

    /**
     * Mark order as ready for pickup (FINAL STATUS).
     * This is the completion point - ready_for_pickup = completed
     * NEW: Generates receipt and sends receipt notification to customer
     */
    public function markReady(Order $order): JsonResponse
    {
        try {
            $vendor = $this->getCurrentVendor();
            if (!$vendor || $order->vendor_id !== $vendor->id) {
                return response()->json(['error' => 'Order not found'], 404);
            }

            if (!$order->isAccepted()) {
                return response()->json(['error' => 'Order must be accepted first'], 400);
            }

            $oldStatus = $order->status;

            DB::beginTransaction();

            try {
                $order->update([
                    'status' => 'ready_for_pickup',
                    'completed_at' => now()
                ]);

                // Broadcast status change event
                event(new OrderStatusChanged($vendor, $order, $order->customer, $oldStatus, 'ready_for_pickup'));

                // Create order status notification for customer
                $customerNotification = Notification::create([
                    'user_id' => $order->customer_id,
                    'vendor_id' => $vendor->id,
                    'order_id' => $order->id,
                    'type' => 'order_status',
                    'title' => 'Ready for Pickup 🔔',
                    'message' => "Your order #{$order->order_number} is ready for pickup",
                    'is_read' => false,
                    'created_at' => now(),
                ]);

                // NEW: Generate receipt and send receipt notification
                $receiptUrl = $this->generateAndSaveReceipt($order);

                // Create receipt notification for customer
                $receiptNotification = Notification::create([
                    'user_id' => $order->customer_id,
                    'vendor_id' => $vendor->id,
                    'order_id' => $order->id,
                    'type' => 'receipt_ready', // This matches frontend filter!
                    'title' => 'Receipt Ready 🧾',
                    'message' => "Your receipt for order #{$order->order_number} is ready! You can download and print it.",
                    'is_read' => false,
                    'created_at' => now(),
                ]);

                DB::commit();

                return response()->json([
                    'message' => 'Order marked as ready and completed',
                    'order' => $order->fresh(['items.product', 'customer'])
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Error marking order as ready', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to mark order as ready'], 500);
        }
    }

    /**
     * Generate receipt and save URL to order record
     * NEW: Helper method for receipt generation and storage
     */
    private function generateAndSaveReceipt(Order $order): string
    {
        try {
            // Generate PDF receipt
            $pdf = Pdf::loadView('receipts.customer', compact('order'));

            // Create unique filename
            $fileName = "receipt-{$order->order_number}-" . time() . ".pdf";

            // Save PDF to storage (public disk for easy access)
            $filePath = "receipts/{$fileName}";
            $pdf->save(storage_path("app/public/{$filePath}"));

            // Update order with receipt URL
            $order->update([
                'receipt_url' => $filePath
            ]);

            return $filePath;

        } catch (\Exception $e) {
            Log::error('Error generating receipt', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            // Return empty string if generation fails
            return '';
        }
    }

    /**
     * Delete a single order.
     */
    public function destroy(Order $order): JsonResponse
    {
        try {
            $vendor = $this->getCurrentVendor();
            if (!$vendor || $order->vendor_id !== $vendor->id) {
                return response()->json(['error' => 'Order not found'], 404);
            }

            // Only allow deletion of non-pending orders
            if ($order->isPending()) {
                return response()->json(['error' => 'Cannot delete pending orders'], 400);
            }

            DB::beginTransaction();

            try {
                $order->delete();

                DB::commit();

                return response()->json([
                    'message' => 'Order deleted successfully'
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Error deleting order', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to delete order'], 500);
        }
    }

    /**
     * Batch delete orders.
     */
    public function batchDelete(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'order_ids' => 'required|array|min:1',
                'order_ids.*' => 'integer|exists:orders,id'
            ]);

            $vendor = $this->getCurrentVendor();
            if (!$vendor) {
                return response()->json(['error' => 'Vendor not found'], 404);
            }

            $orderIds = $request->input('order_ids');

            // Only allow deletion of non-pending orders
            $deletableOrders = Order::forVendor($vendor->id)
                ->whereIn('id', $orderIds)
                ->where('status', '!=', 'pending')
                ->get();

            if ($deletableOrders->isEmpty()) {
                return response()->json(['error' => 'No deletable orders found'], 400);
            }

            DB::beginTransaction();

            try {
                $deletedCount = Order::whereIn('id', $deletableOrders->pluck('id'))
                    ->delete();

                DB::commit();

                return response()->json([
                    'message' => "{$deletedCount} orders deleted successfully",
                    'deleted_count' => $deletedCount
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Error batch deleting orders', [
                'order_ids' => $request->input('order_ids', []),
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to delete orders'], 500);
        }
    }

    /**
     * Get order statistics.
     */
    public function stats(): JsonResponse
    {
        try {
            $vendor = $this->getCurrentVendor();
            if (!$vendor) {
                return response()->json(['error' => 'Vendor not found'], 404);
            }

            return response()->json($this->getOrderStats($vendor->id));

        } catch (\Exception $e) {
            Log::error('Error fetching order stats', [
                'vendor_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to fetch order statistics'], 500);
        }
    }

    /**
     * Generate and download PDF receipt for an order.
     */
    public function downloadReceipt(Request $request, $orderId)
    {
        try {
            $vendor = $this->getCurrentVendor();
            if (!$vendor) {
                return response()->json(['error' => 'Vendor not found'], 404);
            }

            $order = Order::with(['vendor:id,brand_name', 'items.product:id,name,price'])
                ->where('vendor_id', $vendor->id)
                ->where('id', $orderId)
                ->whereIn('status', ['ready_for_pickup', 'completed'])
                ->firstOrFail();

            $pdf = Pdf::loadView('receipts.customer', compact('order'));

            $fileName = "receipt-{$order->order_number}.pdf";
            return $pdf->download($fileName);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Order not found or receipt not available',
                'success' => false
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error generating vendor receipt: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error generating receipt',
                'success' => false
            ], 500);
        }
    }

    /**
     * Generate and stream PDF receipt for an order.
     */
    public function streamReceipt(Request $request, $orderId)
    {
        try {
            $vendor = $this->getCurrentVendor();
            if (!$vendor) {
                return response()->json(['error' => 'Vendor not found'], 404);
            }

            $order = Order::with(['vendor:id,brand_name', 'items.product:id,name,price'])
                ->where('vendor_id', $vendor->id)
                ->where('id', $orderId)
                ->whereIn('status', ['ready_for_pickup', 'completed'])
                ->firstOrFail();

            $pdf = Pdf::loadView('receipts.customer', compact('order'));

            $fileName = "receipt-{$order->order_number}.pdf";
            return $pdf->stream($fileName);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Order not found or receipt not available',
                'success' => false
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error streaming vendor receipt: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error generating receipt',
                'success' => false
            ], 500);
        }
    }

    /**
     * Get the current authenticated vendor.
     */
    private function getCurrentVendor(): ?Vendor
    {
        $user = Auth::user();

        if (!$user) {
            return null;
        }

        // Check if user is a vendor and has a vendor relationship
        if ($user->role === 'vendor' && $user->vendor) {
            return $user->vendor;
        }

        return null;
    }

    /**
     * Calculate order statistics for a vendor.
     */
    private function getOrderStats(int $vendorId): array
    {
        return [
            'total_orders' => Order::forVendor($vendorId)->count(),
            'pending_orders' => Order::forVendor($vendorId)->byStatus('pending')->count(),
            'accepted_orders' => Order::forVendor($vendorId)->byStatus('accepted')->count(),
            'completed_orders' => Order::forVendor($vendorId)->byStatus('ready_for_pickup')->count(),
            'cancelled_orders' => Order::forVendor($vendorId)->byStatus('cancelled')->count(),
            'today_orders' => Order::forVendor($vendorId)
                ->whereDate('created_at', today())
                ->count(),
            'today_revenue' => Order::forVendor($vendorId)
                ->byStatus('ready_for_pickup')
                ->whereDate('created_at', today())
                ->sum('total_amount'),
            'this_week_orders' => Order::forVendor($vendorId)
                ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->count(),
            'this_month_orders' => Order::forVendor($vendorId)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];
    }

    /**
     * Get decline reason display for customers.
     * UPDATED: Handle single pre-written reason
     */
    private function getDeclineReasonDisplay(string $reason): string
    {
        // Check if it's the pre-written reason
        if ($reason === 'cannot_prepare') {
            return 'Cannot prepare the order at the moment';
        }

        // Custom reason (user entered text)
        return $reason;
    }
