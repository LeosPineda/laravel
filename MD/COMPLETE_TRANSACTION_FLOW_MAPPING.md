# 🔄 **COMPLETE TRANSACTION FLOW MAPPING**
## **Customer ↔ Vendor Backend Transaction Flow Analysis**

### **Date**: December 26, 2025, 12:59 AM (Asia/Manila)
### **Scope**: Comprehensive scan of all backend files for vendor and customer systems

---

## 🎯 **TRANSACTION FLOW OVERVIEW**

The multi-tenant food ordering system implements a **complete real-time transaction flow** between customers and vendors through Laravel's event-driven architecture.

---

## 📊 **CORE DATA MODELS MAPPING**

### **Order System Architecture**
```
Order (Central Hub)
├── Customer (User) → Places orders
├── Vendor → Receives & processes orders  
├── OrderItem[] → Individual items with addons
└── Status Tracking → Real-time updates
```

### **Key Models Scanned**
- ✅ **Order.php** - Central order management with status workflows
- ✅ **OrderItem.php** - Order line items with addon support
- ✅ **Customer OrderController.php** - Customer-facing order operations
- ✅ **Vendor OrderController.php** - Vendor-facing order operations
- ✅ **OrderReceived.php** - Event for new order notifications
- ✅ **OrderStatusChanged.php** - Event for status update broadcasts

---

## 🔄 **COMPLETE TRANSACTION FLOW**

### **Phase 1: Customer Order Placement**

**1. Customer Browses Menu**
```
Customer → MenuController.php
├── GET /api/customer/menu/vendors → List active vendors
├── GET /api/customer/menu/vendors/{id} → View vendor menu
├── GET /api/customer/menu/products → Search products
└── POST /api/customer/menu/products/{id}/quick-add → Quick add to cart
```

**2. Customer Manages Cart**
```
Customer → CartController.php
├── GET /api/customer/cart → View multi-vendor cart
├── POST /api/customer/cart/items → Add item to cart
├── PUT /api/customer/cart/items/{id} → Update cart item
├── DELETE /api/customer/cart/items/{id} → Remove cart item
├── DELETE /api/customer/cart/clear → Clear vendor cart
└── GET /api/customer/cart/count → Get cart count
```

**3. Customer Places Order**
```
Customer → OrderController.php → store()
├── Validates cart items and payment method
├── Calculates total (products + addons)
├── Handles payment proof upload (QR code)
├── Creates Order record with status = 'pending'
├── Creates OrderItem records for each cart item
├── Clears customer cart
└── 🚨 TRIGGERS: OrderReceived Event
```

### **Phase 2: Real-time Vendor Notification**

**4. OrderReceived Event Broadcast**
```
OrderReceived.php Event
├── Channels: vendor-orders.{vendor_id}
├── Broadcasts to: All connected vendor devices
├── Data: Order details + customer info
└── Real-time alert: "New order received!"
```

**5. Vendor Receives Order**
```
Vendor → OrderController.php → index()
├── GET /api/vendor/orders → View incoming orders
├── Filters: status=pending, search, pagination
├── Loads: order items + customer details
└── Real-time updates via Pusher
```

### **Phase 3: Vendor Order Processing**

**6. Vendor Order Actions**
```
Vendor → OrderController.php
├── accept() → Status: pending → accepted
├── decline() → Status: pending → cancelled  
├── markReady() → Status: accepted → ready_for_pickup
├── complete() → Status: ready_for_pickup → completed
└── undo() → Revert recent action (5-second window)
```

**7. Status Change Broadcasting**
```
OrderStatusChanged.php Event
├── Channels: 
│   ├── vendor-orders.{vendor_id} → Vendor updates
│   └── customer-orders.{customer_id} → Customer updates
├── Data: Order status + timeline
└── 🚨 TRIGGERS: Real-time customer notifications
```

### **Phase 4: Customer Order Tracking**

**8. Customer Receives Updates**
```
Customer → OrderController.php → track()
├── GET /api/customer/orders/track/{id} → Real-time status
├── Returns: Order details + status timeline
├── Status flow: pending → accepted → ready → completed
└── Real-time via OrderStatusChanged events
```

**9. Order Completion**
```
Vendor → complete() → Status: ready_for_pickup → completed
├── Updates: completed_at timestamp
├── Broadcasts: OrderStatusChanged event
├── Customer receives: Completion notification
└── Customer can: Download receipt
```

**10. Receipt Generation**
```
Customer → OrderController.php → receipt()
├── GET /api/customer/orders/{id}/receipt
├── Generates: Detailed receipt with items + addons
├── Includes: Vendor info, table number, total
└── Available: When status = ready_for_pickup OR completed
```

---

## 🔄 **DETAILED TRANSACTION SEQUENCE**

### **Customer Order Journey**
```
1. Browse Vendors → 2. View Menu → 3. Add to Cart → 4. Checkout → 5. Place Order
     ↓                    ↓              ↓            ↓            ↓
6. Track Status → 7. Receive Updates → 8. Pickup Ready → 9. Download Receipt
     ↓                    ↓                   ↓              ↓
10. Complete Order ← 9. Mark Complete ← 8. Mark Ready ← 7. Vendor Accepts
```

### **Vendor Order Journey**
```
1. Receive Alert → 2. View Order → 3. Accept/Decline → 4. Prepare Food
     ↓                ↓               ↓                 ↓
5. Mark Ready → 6. Customer Notified → 7. Customer Picks Up → 8. Complete Order
     ↓                ↓                      ↓                    ↓
9. Order Complete ← 8. Mark Complete ← 7. Auto-complete ← 6. Customer Confirms
```

---

## 📱 **REAL-TIME EVENT FLOW**

### **Pusher Broadcasting Architecture**
```
Customer Places Order
        ↓
   OrderReceived Event
        ↓
   Vendor Notification
   (vendor-orders.{id})
        ↓
   Vendor Accepts/Declines
        ↓
  OrderStatusChanged Event
        ↓
   Customer Update
   (customer-orders.{id})
        ↓
   Vendor Updates
   (vendor-orders.{id})
```

### **Event Data Structure**
```php
// OrderReceived Event Data
[
    'vendor_id' => 1,
    'order' => [
        'id' => 123,
        'order_number' => 'ORD-000123',
        'status' => 'pending',
        'table_number' => 'T5',
        'items' => [...],
        'customer_info' => {...}
    ],
    'message' => 'New order received!'
]

// OrderStatusChanged Event Data  
[
    'vendor_id' => 1,
    'customer_id' => 456,
    'order' => [
        'id' => 123,
        'status' => 'accepted',
        'old_status' => 'pending',
        'new_status' => 'accepted'
    ],
    'message' => 'Order has been accepted!'
]
```

---

## 💰 **PAYMENT PROCESSING FLOW**

### **Payment Methods Supported**
```
1. Cash Payment (Pay to Cashier)
   └── Customer places order
   └── No payment proof required
   └── Vendor processes normally

2. QR Code Payment (GCash)
   └── Customer places order
   └── Uploads payment proof image
   └── Vendor verifies payment
   └── Processes order if verified
```

### **Payment Proof Handling**
```php
// Customer OrderController.php → store()
if ($validated['payment_method'] === 'qr_code' && $request->hasFile('payment_proof')) {
    $paymentProofUrl = $request->file('payment_proof')->store('payment-proofs', 'public');
    $order->payment_proof_url = $paymentProofUrl;
}
```

---

## 🗃️ **DATA PERSISTENCE LAYERS**

### **Order Status Lifecycle**
```
pending → accepted → ready_for_pickup → completed
    ↓         ↓              ↓               ↓
Placed    Processing    Ready to Pickup   Finished
```

### **Database Relationships**
```
orders
├── user_id → users (customer)
├── vendor_id → vendors  
├── status → Order status enum
├── total_amount → Calculated total
├── payment_method → 'cashier' | 'qr_code'
├── payment_proof_url → File path for QR payments
└── completed_at → Timestamp when completed

order_items
├── order_id → orders (belongsTo)
├── product_id → products
├── quantity → Item quantity
├── unit_price → Price per unit
├── total_price → Quantity × unit_price
└── selected_addons → JSON array of addons
```

---

## 🔐 **SECURITY & VALIDATION**

### **Multi-tenant Data Isolation**
```php
// Vendor OrderController.php → getCurrentVendor()
private function getCurrentVendor(): ?Vendor
{
    $user = Auth::user();
    return $user?->vendor ?? null; // Ensures vendor isolation
}

// Customer OrderController.php
$order = Order::where('user_id', $user->id)->where('id', $orderId)->firstOrFail();
```

### **Order Access Control**
- **Customers**: Can only view their own orders
- **Vendors**: Can only view orders for their own restaurant
- **Status Transitions**: Controlled business logic enforcement
- **Undo Functionality**: 5-second window for misclick prevention

---

## 📈 **ANALYTICS & REPORTING INTEGRATION**

### **Real-time Statistics**
```php
// Vendor OrderController.php → getOrderStats()
[
    'total_orders' => Order::forVendor($vendorId)->count(),
    'pending_orders' => Order::forVendor($vendorId)->byStatus('pending')->count(),
    'today_orders' => Order::forVendor($vendorId)->whereDate('created_at', today())->count(),
    'today_revenue' => Order::forVendor($vendorId)->byStatus('completed')->whereDate('created_at', today())->sum('total_amount'),
    'this_week_orders' => Order::forVendor($vendorId)->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
]
```

### **Profit Calculation Ready**
- **Revenue Tracking**: Real-time sales data
- **Rent Deduction**: ₱3000 rent per vendor (in AnalyticsController)
- **Net Profit**: Revenue - Rent calculation
- **Best Sellers**: Product performance analytics

---

## 🚨 **ERROR HANDLING & RECOVERY**

### **Transaction Safety**
```php
// Customer OrderController.php → store()
DB::beginTransaction();
try {
    // Create order and items
    // Clear cart
    // Broadcast events
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
    throw $e; // Ensures data consistency
}
```

### **Validation Layers**
- **Input Validation**: All API endpoints validate input
- **Business Logic**: Status transition validation
- **File Upload**: Image validation for payment proofs
- **Access Control**: Role-based access validation

---

## 🎯 **TRANSACTION FLOW SUMMARY**

### **Customer Actions**
1. **Browse** → View vendors and menus
2. **Cart** → Add items with addons
3. **Order** → Place order with payment
4. **Track** → Monitor real-time status
5. **Receive** → Get notifications and receipts

### **Vendor Actions**
1. **Alert** → Receive real-time order notifications
2. **Review** → View order details and customer info
3. **Process** → Accept/decline/prepare/complete orders
4. **Update** → Send real-time status updates
5. **Analytics** → Track performance and revenue

### **System Actions**
1. **Real-time** → Pusher broadcasting for instant updates
2. **Persistence** → Database transactions for data safety
3. **Validation** → Multi-layer input and business logic validation
4. **Isolation** → Multi-tenant vendor data separation
5. **Analytics** → Real-time statistics and profit tracking

---

## 🚀 **PRODUCTION-READY FEATURES**

### **✅ Complete Transaction Flow**
- Customer order placement with cart management
- Real-time vendor notifications via Pusher
- Vendor order processing with status updates
- Customer order tracking with timeline
- Receipt generation and download
- Payment processing (cash + QR code)

### **✅ Real-time Architecture**
- Event-driven broadcasting (OrderReceived, OrderStatusChanged)
- Multi-channel notifications (vendor + customer)
- Real-time status updates across all devices
- Instant order alerts and confirmations

### **✅ Data Integrity**
- Database transactions for order safety
- Multi-tenant vendor isolation
- Comprehensive validation layers
- Error handling and recovery mechanisms

**CONCLUSION: The backend implements a complete, production-ready transaction flow with real-time capabilities, comprehensive validation, and multi-tenant architecture suitable for a multi-vendor food ordering platform.** 🎉
