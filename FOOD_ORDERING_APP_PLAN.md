# Multi-Tenant Food Ordering Web App - Development Plan

## Project Overview
A mall-based food ordering web application where multiple vendors can sell food and customers can order from their tables. This is NOT a delivery app - customers order and pick up their food within the mall.

---

## Design System

| Element | Color | Percentage | Usage |
|---------|-------|------------|-------|
| **White Background** | `#FFFFFF` | **60%** | Main page background, cards, header |
| **Light Gray** | `#F5F5F5` | **30%** | Category cards, sections, subtle BG |
| **Vibrant Orange** | `#FF6B35` | **10%** | Buttons, CTAs, badges, accents |
| **Supporting** | `#1A1A1A` | - | Text (high contrast) |
| **Borders** | `#E0E0E0` | - | Dividers, separation |

---

## Technology Stack
- **Backend**: Laravel 12, PHP 8.3
- **Frontend**: Vue 3, Inertia.js v2, Tailwind CSS v4
- **Real-time**: Laravel Echo + Pusher
- **Testing**: Pest v4
- **Code Style**: Laravel Pint

### Pusher Configuration
```
app_id = "2073677"
key = "d7844fc467464fad6f63"
secret = "0cc84702eff4731d5823"
cluster = "ap1"
```

---

## User Roles

### 1. Superadmin
- Hardcoded credentials in `.env` file
- Cannot register through the app

### 2. Vendor
- Created by Superadmin only
- Can access Vendor Dashboard

### 3. Customer
- Registers through Sign Up page
- Can access Customer Dashboard

---

## Backend Development Plan

### Phase 1: Database Schema & Migrations

#### Users Table (All Accounts - Single Table)
Stores ALL user accounts: **1 Superadmin** + **Many Vendors** + **Many Customers**
- `id`
- `name`
- `email` (unique)
- `password`
- `role` enum: `superadmin`, `vendor`, `customer`
- `is_active` boolean (default: true) - For vendors: prevents login & hides from customer home
- `email_verified_at` (nullable)
- `created_at`, `updated_at`

**Account Distribution:**
- `role='superadmin'` → Only 1 (hardcoded in .env, created via seeder)
- `role='vendor'` → Many (created by superadmin)
- `role='customer'` → Many (self-registration via sign up)

#### Vendors Table (Vendor-Specific Data)
Stores vendor branding/business data. Each vendor user has exactly one record here.
- `id`
- `user_id` (FK to users, unique) - Links to user account with role='vendor'
- `brand_name` - Displayed on vendor box in customer home
- `brand_image` (local storage path) - Vendor logo/brand image
- `qr_code_image` (nullable, local storage path) - GCash/payment QR code
- `is_active` (default: true) - Controlled by superadmin
- `created_at`, `updated_at`

**Relationship**: User (role=vendor) hasOne Vendor, Vendor belongsTo User

#### Products Table (Modify Existing)
- `id`
- `vendor_id` (FK)
- `name`
- `price`
- `stock`
- `category` (fillable text)
- `image` (local storage path)
- `is_available` (default: true)
- `created_at`, `updated_at`

#### Addons Table (Modify Existing)
- `id`
- `product_id` (FK)
- `name`
- `price`
- `created_at`, `updated_at`

#### Carts Table (Modify Existing)
- `id`
- `customer_id` (FK to users)
- `vendor_id` (FK)
- `created_at`, `updated_at`

#### Cart Items Table (New)
- `id`
- `cart_id` (FK)
- `product_id` (FK)
- `quantity`
- `selected_addons` (JSON array of addon IDs)
- `created_at`, `updated_at`

#### Orders Table (Modify Existing)
- `id`
- `order_number` (unique, auto-generated)
- `customer_id` (FK to users)
- `vendor_id` (FK)
- `table_number`
- `payment_method` enum: `cashier`, `qr_code`
- `payment_proof_image` (nullable, local storage path)
- `special_instructions` (nullable text)
- `subtotal`
- `total`
- `status` enum: `pending`, `accepted`, `declined`, `ready`
- `created_at`, `updated_at`

#### Order Items Table (Modify Existing)
- `id`
- `order_id` (FK)
- `product_id` (FK)
- `product_name` (snapshot)
- `product_price` (snapshot)
- `quantity`
- `addons` (JSON - snapshot of selected addons with names and prices)
- `item_total`
- `created_at`, `updated_at`

#### Notifications Table (Modify Existing)
- `id`
- `user_id` (FK)
- `type` enum: `order_received`, `order_accepted`, `order_declined`, `order_ready`
- `title`
- `message`
- `data` (JSON - order_id, etc.)
- `is_read` (default: false)
- `created_at`, `updated_at`

#### Receipts Table (Modify Existing)
- `id`
- `order_id` (FK)
- `receipt_data` (JSON - full order snapshot)
- `generated_at`
- `created_at`, `updated_at`

---

### Phase 2: Authentication System

#### Superadmin Authentication
- Credentials hardcoded in `.env`:
  ```
  SUPERADMIN_EMAIL=superadmin@foodcourt.com
  SUPERADMIN_PASSWORD=superadmin_password
  ```
- Seeder to create superadmin account on first run
- Middleware to protect superadmin routes

#### Auth Pages (Simplified)
- **Sign In**: Email + Password only (no remember me)
- **Sign Up**: Email + Password + Name → Creates Customer account
- **Forgot Password**: Email input → Sends reset link
- **Reset Password**: Password change form (accessed via email link)

#### Email Templates (Must Work in Localhost)
1. **Welcome Email** - Sent to customers after registration
2. **Welcome Vendor Email** - Sent to vendors when account created by superadmin
3. **Password Reset Email** - Standard reset link
4. **Password Changed Confirmation** - Sent after password update
5. **Account Deactivated Email** - Sent to vendors when deactivated
6. **Account Activated Email** - Sent to vendors when reactivated

---

### Phase 3: Controllers & Routes

#### Auth Controllers
- `AuthController` - Login, Logout
- `RegisterController` - Customer registration only
- `PasswordResetController` - Forgot/Reset password
- `EmailVerificationController` - Email change verification

#### Superadmin Controllers
- `SuperadminDashboardController`
- `SuperadminVendorController` - CRUD vendors, activate/deactivate
- `SuperadminStatisticsController` - Analytics data

#### Vendor Controllers
- `VendorDashboardController`
- `VendorProductController` - CRUD products with addons
- `VendorOrderController` - Order management (accept/decline/ready)
- `VendorAnalyticsController` - Sales analytics
- `VendorBrandingController` - Brand image and name
- `VendorQRController` - QR code upload for payments
- `VendorNotificationController`

#### Customer Controllers
- `CustomerHomeController` - List vendors + View vendor products (combined - clicking vendor shows products in modal/drawer)
- `CustomerCartController` - Cart management + Checkout flow (checkout is a modal within cart, not separate page)
- `CustomerOrderController` - View order status
- `CustomerProfileController` - Profile management
- `CustomerNotificationController`

---

### Phase 4: API Routes Structure

```
/api/
├── auth/
│   ├── login
│   ├── register (customer only)
│   ├── logout
│   ├── forgot-password
│   └── reset-password
│
├── superadmin/ (protected by superadmin middleware)
│   ├── vendors/ (index, store, update, destroy, toggle-active)
│   └── statistics/
│
├── vendor/ (protected by vendor middleware)
│   ├── products/ (CRUD with addons)
│   ├── orders/ (index, accept, decline, ready, delete)
│   ├── analytics/
│   ├── branding/
│   ├── qr-code/
│   └── notifications/
│
└── customer/ (protected by customer middleware)
    ├── vendors/ (index, show - show returns vendor with products for modal)
    ├── cart/ (index, add, update, remove, clear, checkout - checkout is part of cart flow)
    ├── orders/ (index, show)
    ├── profile/
    └── notifications/
```

---

### Phase 5: Real-time Features (Laravel Echo + Pusher)

#### Broadcasting Events
1. `OrderPlaced` - Customer → Vendor (new order notification)
2. `OrderAccepted` - Vendor → Customer
3. `OrderDeclined` - Vendor → Customer
4. `OrderReady` - Vendor → Customer (with receipt)

#### Channels
- `private-vendor.{vendorId}` - Vendor receives order notifications
- `private-customer.{customerId}` - Customer receives order updates

---

### Phase 6: Services & Business Logic

#### VendorService
- Create vendor with user account
- Manage vendor status
- Calculate vendor statistics

#### OrderService
- Generate unique order numbers
- Calculate order totals
- Process order status changes
- Generate receipts

#### NotificationService
- Create in-app notifications
- Broadcast real-time events

#### StatisticsService
- Vendor count and rent calculation (3000 pesos per vendor)
- Top performing vendors
- Total revenue per vendor
- Net profit calculation (Revenue - Rent)

#### AnalyticsService (for Vendors)
- Total sales (day/week/month)
- Best selling products (day/week/month)
- Total orders received (day/week/month)

---

### Phase 7: Form Requests & Validation

#### Auth Requests
- `LoginRequest`
- `RegisterRequest`
- `ForgotPasswordRequest`
- `ResetPasswordRequest`

#### Superadmin Requests
- `CreateVendorRequest`
- `UpdateVendorRequest`

#### Vendor Requests
- `CreateProductRequest`
- `UpdateProductRequest`
- `UpdateBrandingRequest`
- `UpdateQRCodeRequest`
- `UpdateOrderStatusRequest`

#### Customer Requests
- `AddToCartRequest`
- `UpdateCartItemRequest`
- `CheckoutRequest`
- `UpdateProfileRequest`

---

### Phase 8: Middleware

- `SuperadminMiddleware` - Verify user is superadmin
- `VendorMiddleware` - Verify user is vendor and active
- `CustomerMiddleware` - Verify user is customer
- `ActiveVendorMiddleware` - Check vendor account is active

---

### Phase 9: File Storage

#### Local Storage Paths
```
storage/app/public/
├── vendors/
│   ├── brands/       # Vendor brand images
│   └── qr-codes/     # Payment QR codes
├── products/         # Product images
├── payments/         # Payment proof images
└── receipts/         # Generated receipt PDFs (optional)
```

---

## Frontend Development Plan (After Backend)

### Phase 10: Auth Pages
- Login Page
- Register Page (Customer)
- Forgot Password Page
- Reset Password Page

### Phase 11: Superadmin Dashboard
- Vendor Management (Create, List, Activate/Deactivate, Delete)
- Statistics Dashboard
- Logout

### Phase 12: Vendor Dashboard
- Notifications Panel
- Order Management
- Product Management (with Addons)
- Analytics Dashboard
- QR Code Upload
- Branding Settings
- Logout (Profile icon on mobile)

### Phase 13: Customer Dashboard
- **Home Page** (Single page with vendor boxes)
  - Shows vendor boxes with: Brand Image + Vendor Name
  - Search and filter vendors
  - Clicking vendor box → Opens **Vendor Products Modal**
  
- **Vendor Products Modal** (Compact grid - overlay on Home)
  - Header: Vendor name + Close button
  - Search bar + Category filter dropdown
  - Scrollable grid of products (Image, Name, Price only)
  - Clicking product → Opens **Product Detail Modal**
  
- **Product Detail Modal** (Stacked on top of Products Modal)
  - Large product image
  - Product name, price, category, stock
  - Addons list with checkboxes
  - Quantity selector (- / +)
  - Subtotal display
  - Add to Cart button
  
- **Cart Page** (Per-vendor cart sections)
  - Each vendor section shows items with qty/addons
  - Edit Order button (goes back to product detail modal)
  - Proceed to Checkout button → Opens **Checkout Modal**
  
- **Checkout Modal** (Overlay on Cart)
  - Payment method selection (Pay to Cashier / QR Code)
  - If QR Code: Shows vendor's QR image + Upload payment proof
  - Table number input
  - Special instructions textarea
  - Order summary with totals
  - "Send Order Now" button
  
- Notifications
- Profile Settings
- Logout (Profile icon on mobile)

---

## Responsiveness Requirements

### Mobile
- Hamburger menu navigation
- Customer: Logout moves to Profile section
- Vendor: Logout moves to Branding section
- Touch-friendly buttons and interactions
- Stacked layouts

### Tablet
- Adaptive grid layouts
- Side panel navigation where appropriate

### Laptop/Desktop
- Full navigation bar
- Multi-column layouts
- Hover states

---

## Key Features Checklist

### Authentication
- [ ] Superadmin hardcoded credentials
- [ ] Customer registration
- [ ] Login/Logout
- [ ] Forgot Password with email
- [ ] Reset Password
- [ ] Email templates (Welcome, Auth-related)

### Superadmin
- [ ] Create vendor accounts
- [ ] Delete vendor accounts
- [ ] Activate/Deactivate vendors
- [ ] View statistics (vendor count, rent, revenue, profit)
- [ ] Top performing vendors

### Vendor
- [ ] Receive order notifications (real-time)
- [ ] Order management (Accept/Decline with 5s undo)
- [ ] Mark orders as Ready
- [ ] Auto-send receipt when Ready
- [ ] Delete completed orders
- [ ] Product CRUD with addons
- [ ] Analytics (sales, orders, best sellers by day/week/month)
- [ ] Upload payment QR code
- [ ] Brand image and name

### Customer
- [ ] View active vendors
- [ ] Browse vendor products
- [ ] Add to cart (per-vendor carts)
- [ ] Edit cart items
- [ ] Checkout with payment method selection
- [ ] Upload payment proof (for QR payment)
- [ ] Enter table number
- [ ] Add special instructions
- [ ] Receive order status notifications (real-time)
- [ ] View/Download receipts
- [ ] Profile management (email/password change)

### Real-time
- [ ] Pusher integration
- [ ] Order placed notifications
- [ ] Order status updates
- [ ] Cart count updates

---

## Development Order

### Backend (Start Here)
1. Update migrations and run them
2. Create/update models with relationships
3. Set up authentication with email functionality
4. Create superadmin seeder
5. Implement middleware
6. Build controllers and form requests
7. Set up broadcasting and events
8. Create services for business logic
9. Write tests for each feature

### Frontend (After Backend Complete)
1. Auth pages
2. Superadmin dashboard
3. Vendor dashboard
4. Customer dashboard
5. Real-time integration
6. Responsiveness polish


---
## Notes
- Each user type has its own UI folder (no shared components)
- Proper navigation and pagination on all list views
- Search and filter functionality where applicable
- All images stored locally (not cloud storage)
- Email must work in localhost environment
