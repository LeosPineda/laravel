# "Preparing" Status Timing Clarification

## When "Preparing" Shows Up: NEVER on "Accept" Click

### Actual Flow Based on Code Analysis

**Order Status Flow:**
```
1. pending → (customer places order)
2. accepted → (vendor clicks "Accept")
3. ready_for_pickup → (vendor clicks "Mark Ready")
4. completed → (system or manual)
```

### Customer View Timeline

#### When Vendor Clicks "ACCEPT" 
**Customer Sees:**
- ❌ No "preparing" status
- ✅ Real-time notification: "Order Accepted ✅"
- 📱 Status history: "Order Placed" → "Accepted"

#### When Vendor Clicks "MARK READY"
**Customer Sees:**
- ✅ Real-time notification: "Ready for Pickup 🔔"  
- 📱 Status history: "Order Placed" → "Accepted" → **"Preparing"** → "Ready for Pickup"

### Code Evidence (OrderController::track)

```php
if ($order->status === 'accepted') {
    // Only shows "Order Placed" → "Accepted"
    // NO "preparing" here
} elseif (in_array($order->status, ['ready_for_pickup', 'completed'])) {
    // "preparing" ONLY shows when status is ready_for_pickup/completed
    $statusHistory[] = [
        'status' => 'preparing',  // ← ONLY shows here
        'label' => 'Preparing',
        // ...
    ];
}
```

### Summary
- **"Accept" click**: No "preparing" status shown
- **"Mark Ready" click**: "Preparing" appears retroactively in history display
- **Real-time notifications**: Never show "preparing" status
- **Visual flow**: "preparing" provides better UX for completed orders

The "preparing" status is a **display enhancement** that appears when viewing the complete order history, not as a real-time status update.
