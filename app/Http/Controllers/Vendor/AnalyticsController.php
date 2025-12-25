<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    /**
     * Get complete dashboard analytics data.
     */
    public function dashboard(): JsonResponse
    {
        try {
            $vendor = $this->getCurrentVendor();
            if (!$vendor) {
                return response()->json(['error' => 'Vendor not found'], 404);
            }

            $data = [
                'sales' => $this->getSalesData($vendor->id),
                'best_sellers' => $this->getBestSellers($vendor->id),
                'orders' => $this->getOrderMetrics($vendor->id),
                'revenue' => $this->getRevenueData($vendor->id),
                'profit' => $this->getProfitData($vendor->id),
                'trends' => $this->getTrendsData($vendor->id),
            ];

            return response()->json($data);

        } catch (\Exception $e) {
            Log::error('Error fetching dashboard analytics', [
                'vendor_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to fetch analytics data'], 500);
        }
    }

    /**
     * Get sales data for specified period.
     */
    public function getSales(Request $request): JsonResponse
    {
        try {
            $vendor = $this->getCurrentVendor();
            if (!$vendor) {
                return response()->json(['error' => 'Vendor not found'], 404);
            }

            $period = $request->query('period', 'week');
            $startDate = $this->getStartDate($period);

            $sales = Order::forVendor($vendor->id)
                ->byStatus('completed')
                ->where('created_at', '>=', $startDate)
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('COUNT(*) as order_count'),
                    DB::raw('SUM(total_amount) as total_sales')
                )
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            return response()->json([
                'period' => $period,
                'sales' => $sales,
                'total_sales' => $sales->sum('total_sales'),
                'total_orders' => $sales->sum('order_count'),
                'average_order' => $sales->avg('total_sales') ?? 0,
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching sales data', [
                'vendor_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to fetch sales data'], 500);
        }
    }

    /**
     * Get best selling products.
     */
    public function getBestSellers(Request $request): JsonResponse
    {
        try {
            $vendor = $this->getCurrentVendor();
            if (!$vendor) {
                return response()->json(['error' => 'Vendor not found'], 404);
            }

            $period = $request->query('period', 'week');
            $limit = min($request->query('limit', 10), 50);
            $startDate = $this->getStartDate($period);

            $bestSellers = OrderItem::whereHas('order', function ($query) use ($vendor, $startDate) {
                    $query->forVendor($vendor->id)
                          ->byStatus('completed')
                          ->where('created_at', '>=', $startDate);
                })
                ->with('product')
                ->select(
                    'product_id',
                    DB::raw('SUM(quantity) as total_quantity'),
                    DB::raw('SUM(total_price) as total_revenue')
                )
                ->groupBy('product_id')
                ->orderBy('total_quantity', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'period' => $period,
                'best_sellers' => $bestSellers,
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching best sellers', [
                'vendor_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to fetch best sellers'], 500);
        }
    }

    /**
     * Get order metrics and volume data.
     */
    public function getOrderMetrics(Request $request): JsonResponse
    {
        try {
            $vendor = $this->getCurrentVendor();
            if (!$vendor) {
                return response()->json(['error' => 'Vendor not found'], 404);
            }

            $period = $request->query('period', 'week');
            $startDate = $this->getStartDate($period);

            $metrics = [
                'total_orders' => Order::forVendor($vendor->id)->count(),
                'completed_orders' => Order::forVendor($vendor->id)->byStatus('completed')->count(),
                'pending_orders' => Order::forVendor($vendor->id)->byStatus('pending')->count(),
                'accepted_orders' => Order::forVendor($vendor->id)->byStatus('accepted')->count(),
                'cancelled_orders' => Order::forVendor($vendor->id)->byStatus('cancelled')->count(),
                'period_orders' => Order::forVendor($vendor->id)->where('created_at', '>=', $startDate)->count(),
                'conversion_rate' => $this->calculateConversionRate($vendor->id, $startDate),
            ];

            return response()->json([
                'period' => $period,
                'metrics' => $metrics,
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching order metrics', [
                'vendor_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to fetch order metrics'], 500);
        }
    }

    /**
     * Get revenue data and analysis.
     */
    public function getRevenue(Request $request): JsonResponse
    {
        try {
            $vendor = $this->getCurrentVendor();
            if (!$vendor) {
                return response()->json(['error' => 'Vendor not found'], 404);
            }

            $period = $request->query('period', 'week');
            $startDate = $this->getStartDate($period);

            $revenueData = [
                'total_revenue' => Order::forVendor($vendor->id)->byStatus('completed')->sum('total_amount'),
                'period_revenue' => Order::forVendor($vendor->id)
                    ->byStatus('completed')
                    ->where('created_at', '>=', $startDate)
                    ->sum('total_amount'),
                'average_order_value' => Order::forVendor($vendor->id)->byStatus('completed')->avg('total_amount') ?? 0,
                'daily_revenue' => $this->getDailyRevenue($vendor->id, $startDate),
                'revenue_growth' => $this->getRevenueGrowth($vendor->id, $period),
            ];

            return response()->json([
                'period' => $period,
                'revenue' => $revenueData,
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching revenue data', [
                'vendor_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to fetch revenue data'], 500);
        }
    }

    /**
     * Get profit data (Revenue - Rent).
     */
    public function getProfit(Request $request): JsonResponse
    {
        try {
            $vendor = $this->getCurrentVendor();
            if (!$vendor) {
                return response()->json(['error' => 'Vendor not found'], 404);
            }

            $period = $request->query('period', 'week');
            $startDate = $this->getStartDate($period);
            $rentCost = 3000; // ₱3000 rent per vendor

            $totalRevenue = Order::forVendor($vendor->id)
                ->byStatus('completed')
                ->where('created_at', '>=', $startDate)
                ->sum('total_amount');

            $profitData = [
                'total_revenue' => $totalRevenue,
                'rent_cost' => $rentCost,
                'net_profit' => $totalRevenue - $rentCost,
                'profit_margin' => $totalRevenue > 0 ? (($totalRevenue - $rentCost) / $totalRevenue) * 100 : 0,
                'profit_breakdown' => $this->getProfitBreakdown($vendor->id, $rentCost),
            ];

            return response()->json([
                'period' => $period,
                'profit' => $profitData,
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching profit data', [
                'vendor_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to fetch profit data'], 500);
        }
    }

    /**
     * Get custom date range analytics.
     */
    public function getDateRange(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
            ]);

            $vendor = $this->getCurrentVendor();
            if (!$vendor) {
                return response()->json(['error' => 'Vendor not found'], 404);
            }

            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();

            $data = [
                'sales' => $this->getCustomRangeSales($vendor->id, $startDate, $endDate),
                'orders' => $this->getCustomRangeOrders($vendor->id, $startDate, $endDate),
                'best_sellers' => $this->getCustomRangeBestSellers($vendor->id, $startDate, $endDate),
                'revenue' => $this->getCustomRangeRevenue($vendor->id, $startDate, $endDate),
            ];

            return response()->json([
                'start_date' => $startDate->toISOString(),
                'end_date' => $endDate->toISOString(),
                'data' => $data,
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching date range analytics', [
                'vendor_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to fetch date range data'], 500);
        }
    }

    /**
     * Get the current authenticated vendor.
     */
    private function getCurrentVendor(): ?\App\Models\Vendor
    {
        $user = Auth::user();
        return $user?->vendor ?? null;
    }

    /**
     * Get start date based on period.
     */
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

    /**
     * Get sales data for dashboard.
     */
    private function getSalesData(int $vendorId): array
    {
        $today = Carbon::today();
        $weekStart = Carbon::now()->startOfWeek();
        $monthStart = Carbon::now()->startOfMonth();

        return [
            'today' => Order::forVendor($vendorId)
                ->byStatus('completed')
                ->whereDate('created_at', $today)
                ->sum('total_amount'),
            'this_week' => Order::forVendor($vendorId)
                ->byStatus('completed')
                ->where('created_at', '>=', $weekStart)
                ->sum('total_amount'),
            'this_month' => Order::forVendor($vendorId)
                ->byStatus('completed')
                ->where('created_at', '>=', $monthStart)
                ->sum('total_amount'),
        ];
    }

    /**
     * Get best sellers for dashboard.
     */
    private function getBestSellers(int $vendorId): array
    {
        return OrderItem::whereHas('order', function ($query) use ($vendorId) {
                $query->forVendor($vendorId)->byStatus('completed');
            })
            ->with('product')
            ->select(
                'product_id',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(total_price) as total_revenue')
            )
            ->groupBy('product_id')
            ->orderBy('total_quantity', 'desc')
            ->limit(5)
            ->get()
            ->toArray();
    }

    /**
     * Get order metrics for dashboard.
     */
    private function getOrderMetrics(int $vendorId): array
    {
        return [
            'today_orders' => Order::forVendor($vendorId)
                ->whereDate('created_at', Carbon::today())
                ->count(),
            'this_week_orders' => Order::forVendor($vendorId)
                ->where('created_at', '>=', Carbon::now()->startOfWeek())
                ->count(),
            'pending_orders' => Order::forVendor($vendorId)->byStatus('pending')->count(),
            'completed_orders' => Order::forVendor($vendorId)->byStatus('completed')->count(),
        ];
    }

    /**
     * Get revenue data for dashboard.
     */
    private function getRevenueData(int $vendorId): array
    {
        return [
            'total_revenue' => Order::forVendor($vendorId)->byStatus('completed')->sum('total_amount'),
            'average_order' => Order::forVendor($vendorId)->byStatus('completed')->avg('total_amount') ?? 0,
        ];
    }

    /**
     * Get profit data for dashboard.
     */
    private function getProfitData(int $vendorId): array
    {
        $rentCost = 3000;
        $totalRevenue = Order::forVendor($vendorId)->byStatus('completed')->sum('total_amount');

        return [
            'total_revenue' => $totalRevenue,
            'rent_cost' => $rentCost,
            'net_profit' => $totalRevenue - $rentCost,
        ];
    }

    /**
     * Get trends data for dashboard.
     */
    private function getTrendsData(int $vendorId): array
    {
        $currentWeek = Order::forVendor($vendorId)
            ->byStatus('completed')
            ->where('created_at', '>=', Carbon::now()->startOfWeek())
            ->sum('total_amount');

        $lastWeek = Order::forVendor($vendorId)
            ->byStatus('completed')
            ->whereBetween('created_at', [
                Carbon::now()->startOfWeek()->subWeek(),
                Carbon::now()->startOfWeek()
            ])
            ->sum('total_amount');

        return [
            'week_over_week_growth' => $lastWeek > 0 ? (($currentWeek - $lastWeek) / $lastWeek) * 100 : 0,
            'trend_direction' => $currentWeek >= $lastWeek ? 'up' : 'down',
        ];
    }

    /**
     * Calculate conversion rate.
     */
    private function calculateConversionRate(int $vendorId, Carbon $startDate): float
    {
        $totalOrders = Order::forVendor($vendorId)->where('created_at', '>=', $startDate)->count();
        $completedOrders = Order::forVendor($vendorId)
            ->byStatus('completed')
            ->where('created_at', '>=', $startDate)
            ->count();

        return $totalOrders > 0 ? ($completedOrders / $totalOrders) * 100 : 0;
    }

    /**
     * Get daily revenue breakdown.
     */
    private function getDailyRevenue(int $vendorId, Carbon $startDate): array
    {
        return Order::forVendor($vendorId)
            ->byStatus('completed')
            ->where('created_at', '>=', $startDate)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_amount) as revenue')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('revenue', 'date')
            ->toArray();
    }

    /**
     * Get revenue growth percentage.
     */
    private function getRevenueGrowth(int $vendorId, string $period): float
    {
        $currentPeriod = match ($period) {
            'week' => [
                'start' => Carbon::now()->startOfWeek(),
                'end' => Carbon::now()->endOfWeek()
            ],
            'month' => [
                'start' => Carbon::now()->startOfMonth(),
                'end' => Carbon::now()->endOfMonth()
            ],
            default => [
                'start' => Carbon::now()->startOfWeek(),
                'end' => Carbon::now()->endOfWeek()
            ]
        };

        $previousPeriod = match ($period) {
            'week' => [
                'start' => Carbon::now()->startOfWeek()->subWeek(),
                'end' => Carbon::now()->endOfWeek()->subWeek()
            ],
            'month' => [
                'start' => Carbon::now()->startOfMonth()->subMonth(),
                'end' => Carbon::now()->endOfMonth()->subMonth()
            ],
            default => [
                'start' => Carbon::now()->startOfWeek()->subWeek(),
                'end' => Carbon::now()->endOfWeek()->subWeek()
            ]
        };

        $currentRevenue = Order::forVendor($vendorId)
            ->byStatus('completed')
            ->whereBetween('created_at', [$currentPeriod['start'], $currentPeriod['end']])
            ->sum('total_amount');

        $previousRevenue = Order::forVendor($vendorId)
