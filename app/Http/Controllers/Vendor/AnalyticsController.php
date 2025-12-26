<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    /**
     * Get sales data for specified period.
     */
    public function sales(Request $request): JsonResponse
    {
        $vendor = Auth::user()?->vendor;
        if (!$vendor) {
            return response()->json(['error' => 'Vendor not found'], 404);
        }

        $period = $request->query('period', 'week');
        $startDate = $this->getStartDate($period);

        $totalSales = Order::forVendor($vendor->id)
            ->byStatus('completed')
            ->where('created_at', '>=', $startDate)
            ->sum('total_amount');

        $totalOrders = Order::forVendor($vendor->id)
            ->byStatus('completed')
            ->where('created_at', '>=', $startDate)
            ->count();

        return response()->json([
            'period' => $period,
            'total_sales' => $totalSales,
            'total_orders' => $totalOrders,
            'average_order' => $totalOrders > 0 ? $totalSales / $totalOrders : 0,
        ]);
    }

    /**
     * Get best selling products.
     */
    public function bestSellers(Request $request): JsonResponse
    {
        $vendor = Auth::user()?->vendor;
        if (!$vendor) {
            return response()->json(['error' => 'Vendor not found'], 404);
        }

        $limit = min($request->query('limit', 10), 50);

        $bestSellers = OrderItem::whereHas('order', function ($query) use ($vendor) {
                $query->forVendor($vendor->id)->byStatus('completed');
            })
            ->with('product:id,name,price,image_url')
            ->select('product_id', DB::raw('SUM(quantity) as total_sold'))
            ->groupBy('product_id')
            ->orderBy('total_sold', 'desc')
            ->limit($limit)
            ->get();

        return response()->json(['best_sellers' => $bestSellers]);
    }

    /**
     * Get order metrics.
     */
    public function orderMetrics(): JsonResponse
    {
        $vendor = Auth::user()?->vendor;
        if (!$vendor) {
            return response()->json(['error' => 'Vendor not found'], 404);
        }

        return response()->json([
            'pending' => Order::forVendor($vendor->id)->byStatus('pending')->count(),
            'accepted' => Order::forVendor($vendor->id)->byStatus('accepted')->count(),
            'completed' => Order::forVendor($vendor->id)->byStatus('completed')->count(),
            'cancelled' => Order::forVendor($vendor->id)->byStatus('cancelled')->count(),
            'today' => Order::forVendor($vendor->id)->whereDate('created_at', today())->count(),
        ]);
    }

    /**
     * Get revenue data.
     */
    public function revenue(Request $request): JsonResponse
    {
        $vendor = Auth::user()?->vendor;
        if (!$vendor) {
            return response()->json(['error' => 'Vendor not found'], 404);
        }

        $period = $request->query('period', 'week');
        $startDate = $this->getStartDate($period);

        $periodRevenue = Order::forVendor($vendor->id)
            ->byStatus('completed')
            ->where('created_at', '>=', $startDate)
            ->sum('total_amount');

        $totalRevenue = Order::forVendor($vendor->id)
            ->byStatus('completed')
            ->sum('total_amount');

        return response()->json([
            'period' => $period,
            'period_revenue' => $periodRevenue,
            'total_revenue' => $totalRevenue,
        ]);
    }

    /**
     * Get profit data (Revenue - Rent).
     */
    public function profit(): JsonResponse
    {
        $vendor = Auth::user()?->vendor;
        if (!$vendor) {
            return response()->json(['error' => 'Vendor not found'], 404);
        }

        $rentCost = 3000;
        $totalRevenue = Order::forVendor($vendor->id)
            ->byStatus('completed')
            ->sum('total_amount');

        return response()->json([
            'total_revenue' => $totalRevenue,
            'rent_cost' => $rentCost,
            'net_profit' => $totalRevenue - $rentCost,
        ]);
    }

    private function getStartDate(string $period): Carbon
    {
        return match ($period) {
            'today' => Carbon::today(),
            'week' => Carbon::now()->startOfWeek(),
            'month' => Carbon::now()->startOfMonth(),
            'year' => Carbon::now()->startOfYear(),
            default => Carbon::now()->startOfWeek(),
        };
    }
}
