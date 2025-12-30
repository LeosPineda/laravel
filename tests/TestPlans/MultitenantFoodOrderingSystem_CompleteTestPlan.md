# Multitenant Food Ordering System - Comprehensive Test Plan

## System Overview
This is a multitenant food ordering web application with three user roles:
- **Superadmin**: Manages vendors, hardcoded credentials
- **Vendors**: Manage products, orders, QR codes
- **Customers**: Browse, order, pay for food

## Test Environment Setup
```bash
# Prerequisites
php artisan migrate:fresh --seed
php artisan storage:link
npm install
npm run build

# Start services
php artisan serve --port=8000
npm run dev
```

## 1. SUPERADMIN FUNCTIONALITY TESTS

### 1.1 Authentication & Authorization
**Test Case SA-001: Superadmin Login with Hardcoded Credentials**
- **Precondition**: .env contains SUPERADMIN_EMAIL and SUPERADMIN_PASSWORD
- **Steps**:
  1. Navigate to login page
  2. Enter hardcoded superadmin credentials
  3. Submit form
- **Expected Result**: Successfully logged in as superadmin, redirected to superadmin dashboard
- **Edge Cases**:
  - Wrong password
  - Empty credentials
  - Non-existent email
  - Expired session

**Test Case SA-002: Superadmin Role Protection**
- **Steps**:
  1. Attempt to access superadmin routes as vendor
  2. Attempt to access superadmin routes as customer
- **Expected Result**: Access denied, redirected to appropriate page

### 1.2 Vendor Account Management
**Test Case SA-003: Create Vendor Account**
- **Steps**:
  1. Login as superadmin
  2. Navigate to vendor creation form
  3. Fill vendor details (name, email, password, brand name)
  4. Submit form
- **Expected Result**: 
  - Vendor user created with 'vendor' role
  - Vendor profile created
  - Vendor credentials provided to superadmin
  - Email notification sent to vendor (if implemented)
- **Edge Cases**:
  - Duplicate email
  - Invalid email format
  - Weak password
  - Missing required fields

**Test Case SA-004: Vendor Account Management**
- **Steps**:
  1. View list of all vendors
  2. Test vendor activation/deactivation
  3. Test vendor deletion
  4. Test vendor credential updates
- **Expected Result**: 
  - All operations work correctly
  - Status changes reflect in database
  - Notifications sent to affected vendors

### 1.3 Superadmin Dashboard & Analytics
**Test Case SA-005: Analytics Dashboard**
- **Precondition**: Multiple vendors with orders
- **Steps**:
  1. Access superadmin dashboard
  2. View system-wide analytics
- **Expected Result**: 
  - Total vendors count
  - Total customers count
  - Total orders across all vendors
  - Revenue summary
  - Active/inactive vendor counts

## 2. VENDOR FUNCTIONALITY TESTS

### 2.1 Vendor Authentication
**Test Case V-001: Vendor Login**
- **Steps**:
  1. Use vendor credentials provided by superadmin
  2. Login to vendor dashboard
- **Expected Result**: Successfully logged in, redirected to vendor dashboard

**Test Case V-002: Vendor Role Protection**
- **Steps**:
  1. Attempt to access vendor routes as customer
  2. Attempt to access vendor routes as superadmin
- **Expected Result**: Access denied, appropriate error messages

### 2.2 Product Management
**Test Case V-003: Create Product**
- **Steps**:
  1. Login as vendor
  2. Navigate to product creation
  3. Fill product details (name, price, category, image, stock)
  4. Upload product image
  5. Submit form
- **Expected Result**: 
  - Product created with vendor_id
  - Image uploaded to storage
  - Product appears in vendor's product list
- **Edge Cases**:
  - Invalid image format
  - Image too large
  - Negative price
  - Invalid category
  - Zero stock quantity

**Test Case V-004: Product CRUD Operations**
- **Steps**:
  1. Edit existing product
  2. Deactivate/activate product
  3. Delete product
- **Expected Result**: All operations work correctly, changes reflect immediately

**Test Case V-005: Product Visibility**
- **Steps**:
  1. Create active products
  2. Create inactive products
  3. Check customer view
- **Expected Result**: Only active products visible to customers

### 2.3 QR Code & Mobile Number Management
**Test Case V-006: Upload QR Code**
- **Steps**:
  1. Navigate to QR settings
  2. Upload QR code image
  3. Save settings
- **Expected Result**: 
  - QR image uploaded and saved
  - Image accessible via URL
  - QR code visible to customers

**Test Case V-007: Update Mobile Number**
- **Steps**:
  1. Navigate to contact settings
  2. Update mobile number
  3. Save settings
- **Expected Result**: Mobile number updated and displayed to customers

### 2.4 Order Management
**Test Case V-008: View Orders**
- **Steps**:
  1. Access order management page
  2. View list of orders
- **Expected Result**: 
  - Orders filtered by vendor
  - Order details visible (customer, items, total, status)
  - Order items and addons displayed correctly

**Test Case V-009: Accept Order**
- **Steps**:
  1. Select pending order
  2. Accept order
- **Expected Result**: 
  - Order status changed to 'accepted'
  - Customer notified via Pusher
  - Order moved to accepted list

**Test Case V-010: Decline Order**
- **Steps**:
  1. Select pending order
  2. Decline order with reason
- **Expected Result**: 
  - Order status changed to 'cancelled'
  - Customer notified
  - Order removed from active orders

**Test Case V-011: Mark Ready for Pickup**
- **Steps**:
  1. Select accepted order
  2. Mark as ready for pickup
  3. Upload receipt (if required)
- **Expected Result**: 
  - Order status changed to 'ready_for_pickup'
  - Receipt generated with Dompdf
  - Customer notified with receipt link

### 2.5 Vendor Analytics
**Test Case V-012: View Vendor Analytics**
- **Steps**:
  1. Access analytics dashboard
  2. View different time periods
- **Expected Result**: 
  - Total orders
  - Revenue
  - Popular products
  - Order status breakdown
  - Daily/weekly/monthly trends

## 3. CUSTOMER FUNCTIONALITY TESTS

### 3.1 Customer Registration & Authentication
**Test Case C-001: Customer Registration**
- **Steps**:
  1. Navigate to registration page
  2. Fill registration form
  3. Submit
- **Expected Result**: 
  - Customer account created with 'customer' role
  - Auto-redirected to customer home
  - Welcome notification sent
- **Edge Cases**:
  - Duplicate email
  - Invalid email format
  - Weak password
  - Missing required fields

**Test Case C-002: Customer Login**
- **Steps**:
  1. Login with customer credentials
  2. Verify redirect to customer home
- **Expected Result**: Successfully logged in as customer

### 3.2 Product Browsing
**Test Case C-003: View Vendor Grid**
- **Steps**:
  1. Access customer home page
  2. View vendor boxes/cards
- **Expected Result**: 
  - All active vendors displayed
  - Vendor information visible (name, logo, QR)
  - Products grouped by vendor

**Test Case C-004: Browse Products by Vendor**
- **Steps**:
  1. Click on vendor card
  2. View vendor's products
- **Expected Result**: 
  - Only that vendor's products shown
  - Product details visible (name, price, image, stock)
  - Addons displayed if available

**Test Case C-005: Product Filtering**
- **Steps**:
  1. Filter products by category
  2. Search products
- **Expected Result**: Products filtered correctly, search results accurate

### 3.3 Cart Management
**Test Case C-006: Add to Cart**
- **Steps**:
  1. Select product
  2. Choose quantity and addons
  3. Add to cart
- **Expected Result**: 
  - Product added to vendor-specific cart
  - Cart count updated
  - Real-time cart update via Pusher

**Test Case C-007: Cart Summary**
- **Steps**:
  1. View cart summary
  2. Check vendor grouping
- **Expected Result**: 
  - Items grouped by vendor
  - Individual vendor totals calculated
  - Overall total displayed

**Test Case C-008: Cart Item Management**
- **Steps**:
  1. Update item quantity
  2. Remove item
  3. Clear cart
- **Expected Result**: 
  - Cart updates immediately
  - Totals recalculated
  - Real-time updates

### 3.4 Checkout Process
**Test Case C-009: Checkout Flow**
- **Steps**:
  1. Proceed to checkout
  2. Select payment method (QR/Cash)
  3. Enter table number and special instructions
  4. Confirm order
- **Expected Result**: 
  - Orders created per vendor
  - Order numbers generated
  - Vendors notified of new orders
  - Real-time notifications sent

**Test Case C-010: Payment Processing**
- **Steps**:
  1. Complete payment for vendor orders
  2. Upload payment proof (if applicable)
- **Expected Result**: 
  - Payment recorded
  - Order status updated
  - Vendor receives payment notification

### 3.5 Order Tracking
**Test Case C-011: View Order History**
- **Steps**:
  1. Access order history
  2. View past orders
- **Expected Result**: 
  - All customer orders displayed
  - Order status clearly shown
  - Order details accessible

**Test Case C-012: Order Status Updates**
- **Precondition**: Order placed and pending
- **Steps**:
  1. Wait for vendor response
  2. Check real-time notifications
- **Expected Result**: 
  - Real-time status updates via Pusher
  - Notifications for status changes
  - Receipt available when order completed

### 3.6 Receipt Management
**Test Case C-013: Download Receipt**
- **Precondition**: Order marked ready for pickup
- **Steps**:
  1. Access completed order
  2. Download receipt
- **Expected Result**: 
  - PDF receipt generated with Dompdf
  - Download works correctly
  - Receipt contains all order details

### 3.7 Profile Management
**Test Case C-014: Update Profile**
- **Steps**:
  1. Access profile settings
  2. Update personal information
  3. Save changes
- **Expected Result**: Profile updated successfully

## 4. REAL-TIME FEATURES TESTS

### 4.1 Pusher Integration
**Test Case RT-001: Real-time Notifications**
- **Steps**:
  1. Place order as customer
  2. Check vendor notifications
  3. Update order status as vendor
  4. Check customer notifications
- **Expected Result**: 
  - All notifications delivered in real-time
  - No manual refresh required
  - Proper notification content

**Test Case RT-002: Connection Recovery**
- **Steps**:
  1. Disconnect internet temporarily
  2. Reconnect
  3. Verify notifications resume
- **Expected Result**: Connection recovered automatically, no data loss

### 4.2 WebSocket Events
**Test Case RT-003: Order Events**
- **Steps**:
  1. Place order
  2. Monitor WebSocket events
- **Expected Result**: 
  - OrderCreated event fired
  - OrderReceived event for vendor
  - Proper event payload structure

## 5. MULTITENANCY TESTS

### 5.1 Data Isolation
**Test Case MT-001: Vendor Data Separation**
- **Steps**:
  1. Create multiple vendors
  2. Add products to each vendor
  3. Place orders for different vendors
- **Expected Result**: 
  - Each vendor sees only their own data
  - Products and orders properly isolated
  - No cross-vendor data leakage

**Test Case MT-002: Customer Cart Isolation**
- **Steps**:
  1. Add products from different vendors to cart
  2. Verify vendor grouping
- **Expected Result**: Cart properly grouped by vendor, no mixing

## 6. INTEGRATION TESTS

### 6.1 Image Upload & Storage
**Test Case INT-001: Image Upload Pipeline**
- **Steps**:
  1. Upload product image
  2. Upload vendor logo
  3. Upload QR code
- **Expected Result**: 
  - Images stored in correct directories
  - URLs generated correctly
  - Images display properly

### 6.2 PDF Generation
**Test Case INT-002: Receipt Generation**
- **Precondition**: Order marked ready for pickup
- **Steps**:
  1. Generate receipt
  2. Download and verify content
- **Expected Result**: 
  - PDF contains all order details
  - Formatting correct
  - Download successful

### 6.3 Database Consistency
**Test Case INT-003: Data Integrity**
- **Steps**:
  1. Perform various operations
  2. Check database constraints
  3. Verify relationships
- **Expected Result**: 
  - No orphaned records
  - Foreign key constraints satisfied
  - Data consistency maintained

## 7. PERFORMANCE TESTS

### 7.1 Load Testing
**Test Case PERF-001: Multiple Users**
- **Steps**:
  1. Simulate 10+ concurrent users
  2. Test browsing, cart operations
- **Expected Result**: System responds within acceptable time (<2 seconds)

**Test Case PERF-002: Large Product Catalog**
- **Steps**:
  1. Add 100+ products
  2. Test browsing and filtering
- **Expected Result**: Performance remains acceptable

### 7.2 Database Performance
**Test Case PERF-003: Query Optimization**
- **Steps**:
  1. Monitor database queries
  2. Check for N+1 queries
- **Expected Result**: Queries optimized, no performance bottlenecks

## 8. SECURITY TESTS

### 8.1 Authentication Security
**Test Case SEC-001: Password Security**
- **Steps**:
  1. Test password hashing
  2. Attempt SQL injection
  3. Test session security
- **Expected Result**: 
  - Passwords properly hashed
  - No SQL injection vulnerabilities
  - Secure session management

**Test Case SEC-002: Role-Based Access Control**
- **Steps**:
  1. Test unauthorized access attempts
  2. Verify role restrictions
- **Expected Result**: Proper access control, unauthorized access blocked

### 8.2 Input Validation
**Test Case SEC-003: Input Sanitization**
- **Steps**:
  1. Test with malicious input
  2. Test file upload security
- **Expected Result**: 
  - Input properly sanitized
  - File uploads secured
  - XSS protection active

## 9. MOBILE RESPONSIVENESS TESTS

### 9.1 Responsive Design
**Test Case MOB-001: Mobile Layout**
- **Steps**:
  1. Test on various screen sizes
  2. Test touch interactions
- **Expected Result**: 
  - Layout adapts correctly
  - Touch interactions work
  - Readable text sizes

**Test Case MOB-002: Mobile Navigation**
- **Steps**:
  1. Test mobile menu
  2. Test navigation flow
- **Expected Result**: Easy navigation on mobile devices

## 10. ERROR HANDLING TESTS

### 10.1 Error Scenarios
**Test Case ERR-001: Network Errors**
- **Steps**:
  1. Simulate network failures
  2. Test error recovery
- **Expected Result**: 
  - Graceful error handling
  - User-friendly error messages
  - Proper error logging

**Test Case ERR-002: Invalid Data**
- **Steps**:
  1. Submit invalid forms
  2. Test edge cases
- **Expected Result**: 
  - Proper validation messages
  - No system crashes
  - Data integrity maintained

## Test Execution Strategy

### Phase 1: Core Functionality (Priority 1)
1. Superadmin authentication and vendor management
2. Vendor product management and order handling
3. Customer registration, browsing, and ordering
4. Basic cart and checkout functionality

### Phase 2: Real-time Features (Priority 2)
1. Pusher notifications
2. WebSocket connections
3. Real-time cart updates

### Phase 3: Advanced Features (Priority 3)
1. Receipt generation
2. Analytics and reporting
3. Image uploads and storage

### Phase 4: Performance & Security (Priority 4)
1. Load testing
2. Security audit
3. Mobile responsiveness

## Test Data Setup

### Sample Vendors
- Pizza Palace (pizza category)
- Burger Hub (burger category)
- Sushi World (sushi category)

### Sample Products
Each vendor should have 5-10 products with various categories and addons

### Sample Customers
- Regular customers with different order histories
- Customers with various cart states

## Success Criteria
- All critical paths work without errors
- Real-time features function properly
- Multi-tenancy properly implemented
- Security measures in place
- Mobile responsive
- Performance acceptable under load

## Reporting
- Test execution results
- Bug reports with screenshots
- Performance metrics
- Security audit results
