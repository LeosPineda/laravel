<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Vendor;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $vendorCount = Vendor::count();
        $activeVendorCount = Vendor::where('is_active', true)->count();
        $customerCount = User::where('role', 'customer')->count();
        $rentPerVendor = 3000;
        $totalRent = $activeVendorCount * $rentPerVendor;

        // Get order statistics
        // Simple approach: ready_for_pickup = completed
        $totalOrders = Order::count();
        $completedOrders = Order::byStatus('ready_for_pickup')->count();
        $pendingOrders = Order::byStatus('pending')->count();
        $totalRevenue = Order::byStatus('ready_for_pickup')->sum('total_amount');

        // Today's stats
        $todayOrders = Order::whereDate('created_at', today())->count();
        $todayRevenue = Order::byStatus('ready_for_pickup')
            ->whereDate('created_at', today())
            ->sum('total_amount');

        // This month's stats
        $monthlyOrders = Order::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $monthlyRevenue = Order::byStatus('ready_for_pickup')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_amount');

        // Top vendors by revenue
        $topVendors = Vendor::with('user:id,name,email')
            ->withCount('orders')
            ->withSum(['orders as total_revenue' => function ($query) {
                $query->byStatus('ready_for_pickup');
            }], 'total_amount')
            ->orderByDesc('total_revenue')
            ->take(5)
            ->get()
            ->map(fn ($vendor) => [
                'id' => $vendor->id,
                'brand_name' => $vendor->brand_name,
                'brand_logo' => $vendor->brand_logo,
                'is_active' => $vendor->is_active,
                'user_name' => $vendor->user?->name,
                'user_email' => $vendor->user?->email,
                'total_orders' => $vendor->orders_count ?? 0,
                'total_revenue' => $vendor->total_revenue ?? 0,
                'net_profit' => ($vendor->total_revenue ?? 0) - $rentPerVendor,
            ]);

        // Recent orders
        $recentOrders = Order::with(['vendor:id,brand_name', 'user:id,name'])
            ->latest()
            ->take(10)
            ->get()
            ->map(fn ($order) => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'vendor_name' => $order->vendor?->brand_name,
                'customer_name' => $order->user?->name,
                'total_amount' => $order->total_amount,
                'status' => $order->status,
                'created_at' => $order->created_at->diffForHumans(),
            ]);

        return Inertia::render('superadmin/Dashboard', [
            'statistics' => [
                'vendor_count' => $vendorCount,
                'active_vendor_count' => $activeVendorCount,
                'customer_count' => $customerCount,
                'rent_per_vendor' => $rentPerVendor,
                'total_rent' => $totalRent,
                'total_orders' => $totalOrders,
                'completed_orders' => $completedOrders,
                'pending_orders' => $pendingOrders,
                'total_revenue' => $totalRevenue,
                'today_orders' => $todayOrders,
                'today_revenue' => $todayRevenue,
                'monthly_orders' => $monthlyOrders,
                'monthly_revenue' => $monthlyRevenue,
                'top_vendors' => $topVendors,
                'recent_orders' => $recentOrders,
            ],
        ]);
    }
}
