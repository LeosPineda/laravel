# Vendor Backend Implementation Plan

## Overview
This document outlines the complete backend implementation plan for the multi-tenant food ordering web app vendor system.

## Vendor System Architecture

### Core Features & Roles

#### 1. Order Management System
**Role**: Handle incoming customer orders and manage order lifecycle

**Features**:
- Real-time order notifications
- Order acceptance/decline with 5-second undo
- Order status tracking (pending → accepted → ready for pickup)
- Order history management
- Batch deletion of completed orders
- Receipt generation and download
- Payment proof viewing

**Backend Requirements**:
- Order Controller with accept/decline/complete operations
- Real-time Pusher events for order notifications
- Order status tracking
- Receipt generation system
- File handling for payment proofs

#### 2. Notification System
**Role**: Real-time communication between customers and vendors

**Features**:
- In-app notifications for new orders
- Order status change notifications
- Real-time alerts using Laravel Echo + Pusher
- Browser notifications (future enhancement)
- Audio alerts for new orders

**Backend Requirements**:
- Notification model and database structure
- Pusher configuration and channels
- Real-time event broadcasting
- Notification persistence and retrieval

#### 3. Product Management System
**Role**: Manage vendor's food products and add-ons

**Features**:
- Product CRUD operations (Create, Read, Update, Delete)
- Add-on management (Name, Price)
- Category assignment
- Stock management
- Product image handling
- Price management

**Backend Requirements**:
- Product Controller with full CRUD
- Addon Controller for add-on management
- Category system integration
- Image upload handling
- Validation rules

#### 4. Analytics Dashboard
**Role**: Provide sales insights and performance metrics

**Features**:
- Total sales tracking (day/week/month)
- Best selling products analysis
- Order volume metrics
- Revenue calculations
- Performance comparisons
- Net profit calculations (Revenue - Rent)

**Backend Requirements**:
- Analytics Controller with date-based filtering
- Aggregation queries for metrics
- Performance calculation logic
- Data export functionality

#### 5. QR Code Management
**Role**: Handle payment QR codes for GCash integration

**Features**:
- QR code image upload
- QR code storage and retrieval
- Payment method association
- QR code display for customers

**Backend Requirements**:
- QR Controller for upload/management
- File storage handling
- Image validation and processing
- URL generation for customer access

#### 6. Authentication & Authorization
**Role**: Secure vendor access and role management

**Features**:
- Vendor login/logout
- Password management
- Session handling
- Role-based access control
- Deactivation/activation by superadmin

**Backend Requirements**:
- Existing Laravel Fortify integration
- Vendor middleware for route protection
- Session management
- Password reset functionality

## Database Schema Analysis

### Existing Tables
```sql
-- Users (vendors and customers)
users (id, name, email, password, role, created_at, updated_at)

-- Vendors (vendor-specific data)
vendors (id, user_id, brand_name, brand_logo, is_active, created_at, updated_at)

-- Products (vendor's food items)
products (id, vendor_id, name, price, stock, category, image_url, created_at, updated_at)

-- Addons (additional items for products)
addons (id, product_id, name, price, created_at, updated_at)

-- Orders (customer orders)
orders (id, customer_id, vendor_id, order_number, status, total_amount, payment_method, payment_proof_url, table_number, special_instructions, created_at, updated_at)

-- Order Items (individual products in orders)
order_items (id, order_id, product_id, quantity, unit_price, total_price, selected_addons, created_at, updated_at)

-- Notifications
notifications (id, user_id, type, message, data, is_read, created_at)

-- Carts (customer shopping carts)
carts (id, customer_id, vendor_id, product_id, quantity, selected_addons, created_at, updated_at)
```

## API Endpoints Structure

### Order Management Endpoints
```
GET  /vendor/orders                    - Get vendor's orders (with status filter)
GET  /vendor/orders/{id}               - Get specific order details
PUT  /vendor/orders/{id}/accept        - Accept an order
PUT  /vendor/orders/{id}/decline       - Decline an order
PUT  /vendor/orders/{id}/ready         - Mark order as ready for pickup
GET  /vendor/orders/{id}/receipt       - Generate and download receipt
DELETE /vendor/orders/batch-delete     - Batch delete selected orders
GET  /vendor/orders/stats              - Get order statistics
```

### Product Management Endpoints
```
GET    /vendor/products                - Get vendor's products
POST   /vendor/products                - Create new product
GET    /vendor/products/{id}           - Get specific product
PUT    /vendor/products/{id}           - Update product
DELETE /vendor/products/{id}           - Delete product
GET    /vendor/products/{id}/addons    - Get product's addons
POST   /vendor/products/{id}/addons    - Add addon to product
PUT    /vendor/products/{id}/addons/{addonId} - Update addon
DELETE /vendor/products/{id}/addons/{addonId} - Delete addon
POST   /vendor/products/upload-image   - Upload product image
```

### Analytics Endpoints
```
GET /vendor/analytics/sales           - Sales data (day/week/month)
GET /vendor/analytics/best-sellers    - Best selling products
GET /vendor/analytics/orders          - Order volume metrics
GET /vendor/analytics/revenue         - Revenue calculations
GET /vendor/analytics/profit          - Net profit calculations
```

### QR Code Management Endpoints
```
GET  /vendor/qr                        - Get current QR code
POST /vendor/qr/upload                - Upload new QR code
PUT  /vendor/qr                       - Update QR code
DELETE /vendor/qr                     - Remove QR code
```

### Notification Endpoints
```
GET /vendor/notifications             - Get vendor notifications
PUT /vendor/notifications/{id}/read   - Mark notification as read
PUT /vendor/notifications/read-all    - Mark all notifications as read
```

## Real-time Features Implementation

### Pusher Configuration
```php
// config/broadcasting.php
'pusher' => [
    'key' => 'd7844fc467464fad6f63',
    'secret' => '0cc84702eff4731d5823',
    'app_id' => '2073677',
    'options' => [
        'cluster' => 'ap1',
    ],
],
```

### Laravel Echo Channels
```javascript
// Order Notifications Channel
Pusher.channel('vendor-orders.' + vendorId)
    .listen('OrderReceived', (e) => {
        // Handle new order notification
    });

// Order Status Updates
Pusher.channel('vendor-orders.' + vendorId)
    .listen('OrderStatusChanged', (e) => {
        // Handle order status change
    });
```

### Event Classes
```php
// OrderReceived Event
class OrderReceived extends Event
{
    use SerializesModels;
    
    public $vendorId;
    public $order;
    
    public function broadcastOn()
    {
        return new PrivateChannel('vendor-orders.' . $this->vendorId);
    }
}

// OrderStatusChanged Event
class OrderStatusChanged extends Event
{
    use SerializesModels;
    
    public $vendorId;
    public $orderId;
    public $status;
    
    public function broadcastOn()
    {
        return new PrivateChannel('vendor-orders.' . $this->vendorId);
    }
}
```

## File Management System

### Image Storage Structure
```
storage/app/public/
├── vendor-logos/         # Vendor brand images
├── product-images/       # Product photos
├── qr-codes/            # Payment QR codes
└── payment-proofs/      # Customer payment proofs
```

### File Validation Rules
```php
// Product Images
'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'

// QR Codes
'qr_code' => 'required|image|mimes:jpeg,png,jpg,gif|max:1024'

// Vendor Logos
'logo' => 'required|image|mimes:jpeg,png,jpg,gif|max:1024'
```

## Security Considerations

### Authentication Middleware
- Vendor role verification
- Session timeout handling
- CSRF protection
- Rate limiting for order operations

### Data Validation
- Input sanitization
- Business rule validation (e.g., stock availability)
- Payment method validation
- Order status transitions

### File Security
- Secure file uploads
- Virus scanning (future enhancement)
- File type validation
- Access control for private files

## Performance Optimizations

### Database Indexes
```sql
-- Order queries optimization
CREATE INDEX idx_orders_vendor_status ON orders(vendor_id, status);
CREATE INDEX idx_orders_created_at ON orders(created_at);

-- Product queries optimization
CREATE INDEX idx_products_vendor ON products(vendor_id);
CREATE INDEX idx_products_category ON products(category);

-- Notification queries optimization
CREATE INDEX idx_notifications_user_read ON notifications(user_id, is_read);
```

### Caching Strategy
- Product data caching
- Analytics data caching (hourly/daily)
- QR code URL caching
- Session data optimization

## Error Handling & Logging

### Error Categories
- Order processing errors
- Payment validation errors
- File upload errors
- Real-time connection errors

### Logging Strategy
- Order status changes
- Failed payment attempts
- System errors
- User actions audit trail

## Testing Strategy

### Unit Tests
- Order logic testing
- Product CRUD operations
- Analytics calculations
- File upload handling

### Feature Tests
- Order acceptance/decline workflow
- Real-time notifications
- Product management UI
- Analytics dashboard

### Integration Tests
- Customer order flow
- Vendor notification system
- Payment processing
- File upload/download

## Implementation Phases

### Phase 1: Core Order Management
1. Order Controller implementation
2. Basic order CRUD operations
3. Order status management
4. Simple notifications

### Phase 2: Real-time Features
1. Pusher configuration
2. Laravel Echo integration
3. Real-time order notifications
4. Order status updates

### Phase 3: Product Management
1. Product CRUD operations
2. Addon management
3. Category system
4. Image upload handling

### Phase 4: Analytics & QR
1. Analytics calculations
2. QR code management
3. Report generation
4. Performance optimization

### Phase 5: Testing & Polish
1. Comprehensive testing
2. Performance tuning
3. Security audit
4. Documentation

## Success Metrics

### Functional Requirements
- [ ] Orders can be accepted/declined with undo functionality
- [ ] Real-time notifications work reliably
- [ ] Product management is fully functional
- [ ] Analytics provide accurate data
- [ ] QR code system works for payments

### Performance Requirements
- [ ] Order notifications appear within 2 seconds
- [ ] Product updates reflect immediately
- [ ] Analytics load within 3 seconds
- [ ] File uploads complete within 10 seconds

### Quality Requirements
- [ ] All API endpoints return appropriate HTTP status codes
- [ ] Error messages are user-friendly
- [ ] System handles concurrent orders properly
- [ ] Data validation prevents invalid operations

---

This plan provides a comprehensive roadmap for implementing the vendor backend system, ensuring all features are properly planned and implemented according to the requirements.
