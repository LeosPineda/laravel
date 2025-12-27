<?php

namespace App\Http\Controllers\Vendor;

use App\Events\OrderStatusChanged;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

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

            // Apply status filter - removed 'completed' status
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
     * Display a specific order.
     */
    public function show(Order $order): JsonResponse
    {
        try {
            $vendor = $this->getCurrentVendor();
            if (!$vendor || $order->vendor_id !== $vendor->id) {
                return response()->json(['error' => 'Order not found'], 404);
            }

            $order->load(['items.product.addons', 'customer']);

            return response()->json(['order' => $order]);

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
     * Decline an order.
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

            DB::beginTransaction();

            try {
                $order->update([
                    'status' => 'cancelled'
                ]);

                // Broadcast status change event
                event(new OrderStatusChanged($vendor, $order, $order->customer, $oldStatus, 'cancelled'));

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
                    'completed_at' => now() // Mark as completed immediately
                ]);

                // Broadcast status change event
                event(new OrderStatusChanged($vendor, $order, $order->customer, $oldStatus, 'ready_for_pickup'));

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
     * Get the current authenticated vendor.
     */
    private function getCurrentVendor(): ?Vendor
    {
        $user = Auth::user();
        return $user?->vendor ?? null;
    }

    /**
     * Calculate order statistics for a vendor.
     * Updated: completed orders = ready_for_pickup orders
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
}
