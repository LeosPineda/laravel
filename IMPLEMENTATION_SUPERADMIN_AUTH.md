# Implementation Guide: Superadmin & Authentication

## Status: In Progress

---

## Completed ✅

### Phase 1: Database - Users Table Migration
- [x] Added `is_active` field to users table
- [x] Role enum: `customer`, `vendor`, `superadmin`

### Phase 2: Database - Vendors Table Migration
- [x] Updated with `brand_name`, `brand_image`, `qr_code_image`, `is_active`
- [x] One-to-one with User via `user_id`

### Phase 3: Models
- [x] User model - Simplified (no 2FA), added role methods
- [x] Vendor model - Created with relationships

### Phase 4: Database Seeder
- [x] SuperadminSeeder created
- [x] Superadmin account: `superadmin@foodcourt.com` / `SuperAdmin@123`

### Phase 5: Middleware
- [x] CheckRole middleware created
- [x] Registered in bootstrap/app.php

### Phase 6: Authentication
- [x] FortifyServiceProvider - Simplified (no 2FA), vendor active check
- [x] CreateNewUser - Always creates customers

### Phase 7: Superadmin Controllers
- [x] DashboardController - Statistics (vendor count, rent, top vendors)
- [x] VendorController - CRUD vendors, toggle active, delete

### Phase 8: Routes
- [x] Role-based dashboard redirect
- [x] Superadmin routes with middleware protection
- [x] Vendor/Customer placeholder routes

---

## In Progress 🔄

### Phase 9: Email Configuration

#### Step 9.1: Configure .env for Email (Mailtrap for Testing)

**File:** `.env`

```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@foodcourt.com"
MAIL_FROM_NAME="${APP_NAME}"
```

**For Production (Gmail/SMTP):**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@foodcourt.com"
MAIL_FROM_NAME="${APP_NAME}"
```

#### Step 9.2: Create Email Notifications

**Command:** `php artisan make:notification WelcomeCustomerNotification`

**Files to create:**
1. `WelcomeCustomerNotification` - For new customer registration
2. `WelcomeVendorNotification` - For vendor created by superadmin
3. `VendorDeactivatedNotification` - When superadmin deactivates vendor
4. `VendorActivatedNotification` - When superadmin activates vendor

---

### Phase 10: Frontend - Auth Pages

#### Design System

| Element | Color | Percentage | Usage |
|---------|-------|------------|-------|
| **White Background** | `#FFFFFF` | **60%** | Main page background, cards, header |
| **Light Gray** | `#F5F5F5` | **30%** | Category cards, sections, subtle BG |
| **Vibrant Orange** | `#FF6B35` | **10%** | Buttons, CTAs, badges, accents |
| **Supporting** | `#1A1A1A` | - | Text (high contrast) |
| **Borders** | `#E0E0E0` | - | Dividers, separation |

#### Auth Pages to Update:
1. **Login Page** (`resources/js/pages/auth/Login.vue`)
   - Email + Password only (no remember me)
   - Clean, modern design with orange CTA button

2. **Register Page** (`resources/js/pages/auth/Register.vue`)
   - Name + Email + Password
   - Creates customer account only

3. **Forgot Password** (`resources/js/pages/auth/ForgotPassword.vue`)
   - Email input → Sends reset link

4. **Reset Password** (`resources/js/pages/auth/ResetPassword.vue`)
   - New password form

---

### Phase 11: Frontend - Superadmin Dashboard

#### Files to Create:
1. `resources/js/pages/superadmin/Dashboard.vue`
2. `resources/js/pages/superadmin/Vendors/Index.vue`
3. `resources/js/pages/superadmin/Vendors/Create.vue`
4. `resources/js/layouts/superadmin/SuperadminLayout.vue`

#### Dashboard Features:
- Vendor count (each = ₱3,000 rent)
- Total rent calculation
- Top performing vendors table
- Net profit per vendor (Revenue - Rent)

#### Vendor Management:
- List all vendors with pagination
- Create new vendor (Name, Email, Password, Brand Name)
- Toggle activate/deactivate
- Delete vendor

---

## Next Steps

1. [ ] Set up email configuration in .env
2. [ ] Create email notifications (welcome, activation)
3. [ ] Update auth frontend (Login, Register, Forgot/Reset Password)
4. [ ] Create superadmin layout
5. [ ] Create superadmin dashboard page
6. [ ] Create vendor management pages

---

## Test Accounts

| Role | Email | Password |
|------|-------|----------|
| Superadmin | superadmin@foodcourt.com | SuperAdmin@123 |

---

## Routes Summary

| Route | Method | Description |
|-------|--------|-------------|
| `/` | GET | Redirect to login |
| `/login` | GET/POST | Login page (Fortify) |
| `/register` | GET/POST | Register page (Fortify) |
| `/forgot-password` | GET/POST | Forgot password (Fortify) |
| `/reset-password/{token}` | GET/POST | Reset password (Fortify) |
| `/dashboard` | GET | Redirects based on role |
| `/superadmin/dashboard` | GET | Superadmin dashboard |
| `/superadmin/vendors` | GET | List vendors |
| `/superadmin/vendors/create` | GET | Create vendor form |
| `/superadmin/vendors` | POST | Store vendor |
| `/superadmin/vendors/{id}/toggle-active` | PATCH | Toggle vendor active |
| `/superadmin/vendors/{id}` | DELETE | Delete vendor |
| `/vendor/dashboard` | GET | Vendor dashboard (placeholder) |
| `/customer/home` | GET | Customer home (placeholder) |
