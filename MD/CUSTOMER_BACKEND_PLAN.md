# 🍔 **CUSTOMER BACKEND IMPLEMENTATION PLAN**

## **Overview**
Complete the customer-facing backend for the multi-tenant food ordering platform, building upon the successful vendor backend we've already implemented.

---

## 🎯 **CUSTOMER BACKEND REQUIREMENTS**

### **Core Customer Features**
1. **Menu Browsing**: View vendor menus and products
2. **Shopping Cart**: Multi-vendor cart management
3. **Order Placement**: Create and submit orders
4. **Payment Processing**: QR code payments and cash payments
5. **Order Tracking**: Real-time order status updates
6. **Customer Notifications**: Order updates and alerts
7. **Profile Management**: Customer account and history

---

# **PHASE 1: CUSTOMER ORDER MANAGEMENT SYSTEM**

## **1.1 Customer Order Controller**
**File**: `app/Http/Controllers/Customer/OrderController.php`

### **API Endpoints Needed**:
```php
// Order Management
GET    /api/customer/orders                - List customer orders
GET    /api/customer/orders/{order}        - Get specific order details
POST   /api/customer/orders                - Create new order
PUT    /api/customer/orders/{order}/cancel - Cancel order (if allowed)
GET    /api/customer/orders/track/{order}  - Track order status
GET    /api/customer/orders/history        - Order history with filters
```

### **Features to Implement**:
- **Order Creation**: Multi-vendor order processing
- **Order Status Tracking**: Real-time status updates via Pusher
- **Order Cancellation**: Customer cancellation with conditions
- **Order History**: Complete order history with filtering
- **Payment Integration**: QR code and cash payment processing
- **Receipt Generation**: Downloadable order receipts

### **Business Logic**:
- Validate table number and payment method
- Calculate totals across multiple vendors
- Process payment proof uploads for QR payments
- Send order confirmation notifications
- Real-time order status updates

---

## **1.2 Customer Cart Controller**
**File**: `app/Http/Controllers/Customer/CartController.php`

### **API Endpoints Needed**:
```php
// Cart Management
GET    /api/customer/cart                 - Get customer cart
POST   /api/customer/cart/items           - Add item to cart
PUT    /api/customer/cart/items/{item}    - Update cart item
DELETE /api/customer/cart/items/{item}    - Remove item from cart
POST   /api/customer/cart/checkout        - Process checkout
DELETE /api/customer/cart/clear           - Clear entire cart
POST   /api/customer/cart/merge           - Merge vendor carts
```

### **Features to Implement**:
- **Multi-vendor Cart**: Separate carts per vendor
- **Cart Item Management**: Add, update, remove items
- **Addon Management**: Add/remove addons to products
- **Checkout Process**: Order submission with payment details
- **Cart Validation**: Stock checks and availability
- **Session Persistence**: Save cart between visits

### **Business Logic**:
- Validate product availability and stock
- Calculate item totals with addons
- Handle vendor-specific cart separation
- Process checkout with payment methods
- Send order notifications to vendors

---

# **PHASE 2: CUSTOMER MENU & PRODUCT SYSTEM**

## **2.1 Customer Menu Controller**
**File**: `app/Http/Controllers/Customer/MenuController.php`

### **API Endpoints Needed**:
```php
// Menu Browsing
GET    /api/customer/menu/vendors         - List active vendors
GET    /api/customer/menu/vendors/{vendor} - Get vendor menu
GET    /api/customer/menu/products        - Search products across vendors
GET    /api/customer/menu/categories      - Get all categories
GET    /api/customer/menu/products/{product} - Get product details
POST   /api/customer/menu/products/{product}/quick-add - Quick add to cart
```

### **Features to Implement**:
- **Vendor Listing**: Show active vendors with basic info
- **Menu Display**: Product listing with categories and addons
- **Product Search**: Search across all vendors
- **Category Filtering**: Filter by product categories
- **Quick Add**: Fast add-to-cart functionality
- **Product Details**: Complete product information

### **Business Logic**:
- Filter vendors by active status
- Include only active products in menus
- Calculate product availability
- Handle product search across vendors
- Cache frequently accessed menus

---

# **PHASE 3: CUSTOMER PAYMENT SYSTEM**

## **3.1 Customer Payment Controller**
**File**: `app/Http/Controllers/Customer/PaymentController.php`

### **API Endpoints Needed**:
```php
// Payment Processing
POST   /api/customer/payments/validate    - Validate payment method
POST   /api/customer/payments/upload-proof - Upload payment proof (QR payments)
GET    /api/customer/payments/methods     - Get available payment methods
POST   /api/customer/payments/process     - Process payment
GET    /api/customer/payments/history     - Payment history
```

### **Features to Implement**:
- **Payment Method Selection**: QR code or cash payment
- **Payment Proof Upload**: Image upload for QR payments
- **Payment Validation**: Validate payment details
- **Payment Processing**: Process payment confirmation
- **Payment History**: Track payment records

### **Business Logic**:
- Validate payment method availability
- Handle QR code payment processing
- Process payment proof uploads
- Validate uploaded proof images
- Send payment confirmations

---

# **PHASE 4: CUSTOMER NOTIFICATION SYSTEM**

## **4.1 Customer Notification Controller**
**File**: `app/Http/Controllers/Customer/NotificationController.php`

### **API Endpoints Needed**:
```php
// Customer Notifications
GET    /api/customer/notifications        - List customer notifications
GET    /api/customer/notifications/count  - Get unread count
PUT    /api/customer/notifications/{id}/read - Mark as read
PUT    /api/customer/notifications/read-all - Mark all as read
GET    /api/customer/notifications/recent - Recent notifications
```

### **Features to Implement**:
- **Order Notifications**: Status updates, ready notifications
- **Payment Notifications**: Payment confirmations
- **System Notifications**: General announcements
- **Read/Unread Tracking**: Notification status management
- **Real-time Updates**: Pusher integration for live updates

### **Business Logic**:
- Send order status change notifications
- Notify when orders are ready for pickup
- Send payment confirmation messages
- Handle notification read/unread status
- Clean up old notifications

---

# **PHASE 5: CUSTOMER PROFILE SYSTEM**

## **5.1 Customer Profile Controller**
**File**: `app/Http/Controllers/Customer/ProfileController.php`

### **API Endpoints Needed**:
```php
// Customer Profile
GET    /api/customer/profile              - Get customer profile
PUT    /api/customer/profile              - Update profile
GET    /api/customer/profile/statistics   - Get usage statistics
GET    /api/customer/profile/favorites    - Get favorite vendors
POST   /api/customer/profile/favorites    - Add/remove favorites
```

### **Features to Implement**:
- **Profile Management**: View and update customer info
- **Order Statistics**: Total orders, spending, etc.
- **Favorite Vendors**: Mark preferred vendors
- **Order History**: Complete order tracking
- **Preferences**: Dietary preferences, etc.

### **Business Logic**:
- Update customer profile information
- Calculate customer statistics
- Manage favorite vendor relationships
- Track order patterns and preferences
- Provide personalized recommendations

---

# **PHASE 6: CUSTOMER EVENTS & BROADCASTING**

## **6.1 Customer Events**
**Files**: 
- `app/Events/OrderPlaced.php`
- `app/Events/PaymentReceived.php`
- `app/Events/OrderStatusUpdated.php`

### **Real-time Events**:
```php
// Order Events
Event: OrderPlaced
- Broadcast to: customer-orders.{customer_id}
- Data: order details, vendor info, total

Event: OrderStatusUpdated  
- Broadcast to: customer-orders.{customer_id}
- Data: new status, estimated time, vendor message

Event: PaymentReceived
- Broadcast to: customer-orders.{customer_id}
- Data: payment confirmation, receipt info
```

---

# **PHASE 7: CUSTOMER ROUTES**

## **7.1 API Routes**
**File**: `routes/web.php` (Customer API section)

```php
// Customer API Routes
Route::prefix('api/customer')->name('api.customer.')->group(function () {
    // Orders
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [CustomerOrderController::class, 'index']);
        Route::get('/{order}', [CustomerOrderController::class, 'show']);
        Route::post('/', [CustomerOrderController::class, 'store']);
        Route::put('/{order}/cancel', [CustomerOrderController::class, 'cancel']);
        Route::get('/track/{order}', [CustomerOrderController::class, 'track']);
        Route::get('/history', [CustomerOrderController::class, 'history']);
    });

    // Cart
    Route::prefix('cart')->name('cart.')->group(function () {
        Route::get('/', [CustomerCartController::class, 'index']);
        Route::post('/items', [CustomerCartController::class, 'store']);
        Route::put('/items/{item}', [CustomerCartController::class, 'update']);
        Route::delete('/items/{item}', [CustomerCartController::class, 'destroy']);
        Route::post('/checkout', [CustomerCartController::class, 'checkout']);
        Route::delete('/clear', [CustomerCartController::class, 'clear']);
    });

    // Menu
    Route::prefix('menu')->name('menu.')->group(function () {
        Route::get('/vendors', [CustomerMenuController::class, 'vendors']);
        Route::get('/vendors/{vendor}', [CustomerMenuController::class, 'vendorMenu']);
        Route::get('/products', [CustomerMenuController::class, 'searchProducts']);
        Route::get('/categories', [CustomerMenuController::class, 'categories']);
        Route::get('/products/{product}', [CustomerMenuController::class, 'productDetails']);
    });

    // Payments
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::post('/validate', [CustomerPaymentController::class, 'validate']);
        Route::post('/upload-proof', [CustomerPaymentController::class, 'uploadProof']);
        Route::get('/methods', [CustomerPaymentController::class, 'methods']);
        Route::post('/process', [CustomerPaymentController::class, 'process']);
        Route::get('/history', [CustomerPaymentController::class, 'history']);
    });

    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [CustomerNotificationController::class, 'index']);
        Route::get('/count', [CustomerNotificationController::class, 'count']);
        Route::put('/{notification}/read', [CustomerNotificationController::class, 'markAsRead']);
        Route::put('/read-all', [CustomerNotificationController::class, 'markAllAsRead']);
    });

    // Profile
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [CustomerProfileController::class, 'show']);
        Route::put('/', [CustomerProfileController::class, 'update']);
        Route::get('/statistics', [CustomerProfileController::class, 'statistics']);
        Route::get('/favorites', [CustomerProfileController::class, 'favorites']);
    });
});
```

---

# **PHASE 8: CUSTOMER MODELS & RELATIONSHIPS**

## **8.1 Customer Models Needed**

### **8.1.1 Customer Model Enhancement**
**File**: `app/Models/User.php` (Customer-specific methods)

```php
// Customer-specific methods
public function vendorCarts(): HasMany
public function orders(): HasMany  
public function notifications(): HasMany
public function favoriteVendors(): BelongsToMany
public function getOrderStatistics(): array
```

### **8.1.2 Cart Model Enhancement**
**File**: `app/Models/Cart.php` (already exists, needs customer methods)

```php
// Customer cart methods
public function vendor(): BelongsTo
public function customer(): BelongsTo
public function items(): HasMany
public function calculateTotal(): float
public function addProduct(): bool
public function removeProduct(): bool
```

---

# **IMPLEMENTATION PRIORITY**

## **Phase 1: Core Customer Functions (Week 1)**
1. **Customer Cart Controller** - Essential for ordering
2. **Customer Order Controller** - Order placement and tracking
3. **Customer Menu Controller** - Browse vendor menus
4. **Customer Routes** - API endpoint setup

## **Phase 2: Payment & Notifications (Week 2)**
1. **Customer Payment Controller** - Payment processing
2. **Customer Notification Controller** - Order updates
3. **Customer Events** - Real-time broadcasting
4. **Payment Integration** - QR code and cash payments

## **Phase 3: Profile & Enhancement (Week 3)**
1. **Customer Profile Controller** - Account management
2. **Customer Model Enhancement** - Business logic
3. **Cart Model Enhancement** - Cart management
4. **Testing & Integration** - End-to-end testing

---

# **CUSTOMER BACKEND FEATURES**

## **✅ Multi-Vendor Cart Management**
- Separate carts for each vendor
- Merge carts during checkout
- Individual vendor pricing
- Separate order processing per vendor

## **✅ Real-time Order Tracking**
- Live order status updates via Pusher
- Estimated preparation times
- Ready for pickup notifications
- Order completion confirmations

## **✅ Payment Integration**
- QR code payment processing
- Payment proof upload for verification
- Cash payment processing
- Payment confirmation system

## **✅ Customer Notifications**
- Order status change alerts
- Payment confirmation messages
- Order ready notifications
- System announcements

## **✅ Menu Browsing & Search**
- Search products across all vendors
- Category filtering
- Product availability checking
- Quick add-to-cart functionality

## **✅ Order Management**
- View complete order history
- Track current orders
- Cancel orders (when allowed)
- Download order receipts

---

# **INTEGRATION WITH EXISTING SYSTEMS**

## **✅ Vendor Integration**
- Customer orders → Vendor order notifications
- Real-time updates via shared Pusher events
- Vendor QR codes → Customer payment processing
- Order status changes → Customer notifications

## **✅ Superadmin Integration**
- Customer registration oversight
- Order analytics across all customers
- Payment tracking and reporting
- System-wide notification management

## **✅ Database Integration**
- Shared order and product tables
- Multi-tenant vendor relationships
- Customer-vendor interaction tracking
- Payment and notification persistence

---

# **SUCCESS CRITERIA**

### **Functional Requirements**
- [ ] Customers can browse vendor menus
- [ ] Multi-vendor cart management works
- [ ] Order placement and tracking functions
- [ ] Payment processing (QR + cash) works
- [ ] Real-time notifications delivered
- [ ] Customer profile management complete

### **Technical Requirements**
- [ ] All API endpoints return proper HTTP status codes
- [ ] Real-time Pusher notifications work within 2 seconds
- [ ] Payment proof uploads complete within 10 seconds
- [ ] Cart persistence works across sessions
- [ ] Database queries are optimized

### **Integration Requirements**
- [ ] Vendor notifications trigger customer updates
- [ ] Order status changes broadcast to customers
- [ ] Payment processing connects to vendor systems
- [ ] Multi-vendor orders process correctly
- [ ] Real-time updates work across all user types

---

This plan will complete the full customer-facing backend, working seamlessly with the vendor backend we've already built!
