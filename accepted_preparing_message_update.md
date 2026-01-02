# Update: Combine "Accepted" & "Preparing" Message

## Current Behavior
When vendor clicks "Accept":
```php
// Vendor/OrderController.php - accept() method
$customerNotification = Notification::create([
    'user_id' => $order->customer_id,
    'vendor_id' => $vendor->id,
    'order_id' => $order->id,
    'type' => 'order_status',
    'title' => 'Order Accepted ✅',
    'message' => "Your order #{$order->order_number} is now accepted",
    'is_read' => false,
    'created_at' => now(),
]);
```

## Proposed Change
When vendor clicks "Accept":
```php
// Updated accept() method
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
```

## Remove "Preparing" from OrderController::track()
```php
// Remove this entire section from Customer/OrderController.php track() method:
} elseif (in_array($order->status, ['ready_for_pickup', 'completed'])) {
    $statusHistory[] = [
        'status' => 'preparing',  // ← REMOVE THIS
        'label' => 'Preparing',
        'description' => 'Your food is being prepared',
        'timestamp' => $order->updated_at,
        'completed' => true
    ];
    // ...
}
```

## Updated Customer Flow
```
1. Order placed → "Order Placed"
2. Vendor accepts → "Order Accepted & Preparing 👨‍🍳" (message includes "please wait")
3. Vendor marks ready → "Ready for Pickup 🔔"
```

## Benefits
- ✅ No separate "preparing" status needed
- ✅ Clear communication that order is being actively prepared
- ✅ Customer knows to expect preparation time
- ✅ Simplified status progression
- ✅ No separate status history for "preparing"
