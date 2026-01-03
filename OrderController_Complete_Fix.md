# OrderController.php - Complete Missing Code Fix

## Problem
The OrderController.php file is truncated and missing the closing brace for the class, causing syntax errors.

## Missing Code to Complete the File

### Copy this code and paste it at the end of your OrderController.php file:

```php

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

## Complete OrderController.php File Structure

Your final OrderController.php should look like this:

```php
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
    // ... all your methods (index, show, accept, decline, markReady, etc.) ...
    
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

## Instructions

1. **Open** `app/Http/Controllers/Vendor/OrderController.php`
2. **Find** the last method `getDeclineReasonDisplay()`
3. **Add** the closing brace `}` after the return statement
4. **Save** the file

## What This Fixes

- ✅ **Syntax Error**: Adds missing closing brace for the class
- ✅ **PHP Parse Error**: Resolves "unexpected token" errors
- ✅ **Wayfinder Generation**: Allows Laravel plugins to work properly
- ✅ **Build Process**: Enables frontend builds to complete successfully

## Expected Result

After applying this fix:
- PHP syntax errors resolved
- Wayfinder type generation works
- Frontend build completes successfully
- All decline reason functionality works properly
- Receipt workflow fully functional
