# OrderController.php - Missing Code Fix

## Problem
The OrderController.php file is truncated and has syntax errors. The file ends with an incomplete line and missing closing methods.

## Missing Code to Complete the File

### Copy this code and paste it after line 540 (the incomplete line):

```php
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
}
```

## How to Fix:

1. **Open** `app/Http/Controllers/Vendor/OrderController.php`
2. **Find** the incomplete line at the end: `'message' => 'message'`
3. **Replace** the incomplete content with the complete code above
4. **Save** the file

## What This Completes:

- ✅ Fixes syntax errors
- ✅ Completes the `streamReceipt` method
- ✅ Adds all missing helper methods:
  - `getCurrentVendor()`
  - `getOrderStats()`
  - `getDeclineReasonDisplay()`
- ✅ Properly closes the class with `}`

## Expected Result:

After applying this fix, the OrderController.php file will be complete and the receipt workflow will be fully functional with:
- ✅ Order status notifications
- ✅ Receipt generation and storage
- ✅ Receipt notifications to customers
- ✅ Complete backend functionality
