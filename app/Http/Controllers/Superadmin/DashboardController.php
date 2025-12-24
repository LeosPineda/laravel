<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $vendorCount = Vendor::count();
        $rentPerVendor = 3000;
        $totalRent = $vendorCount * $rentPerVendor;

        // Get vendors with user info (revenue/orders will be added later)
        $vendors = Vendor::with('user:id,name,email')
            ->where('is_active', true)
            ->take(5)
            ->get()
            ->map(fn ($vendor) => [
                'id' => $vendor->id,
                'brand_name' => $vendor->brand_name,
                'user_name' => $vendor->user?->name,
                'user_email' => $vendor->user?->email,
                // TODO: Add when Order model is created
                'total_revenue' => 0,
                'net_profit' => 0 - $rentPerVendor,
            ]);

        return Inertia::render('superadmin/Dashboard', [
            'statistics' => [
                'vendor_count' => $vendorCount,
                'rent_per_vendor' => $rentPerVendor,
                'total_rent' => $totalRent,
                'top_vendors' => $vendors,
            ],
        ]);
    }
}
