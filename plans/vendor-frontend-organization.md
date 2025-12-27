# Vendor Frontend File Organization Plan

**Date:** December 27, 2025  
**Purpose:** Complete vendor-side frontend file structure and organization

---

## 📋 VENDOR FRONTEND ARCHITECTURE

### Core Vendor Features
1. **Dashboard** - Overview and quick actions
2. **Order Management** - Incoming orders, history, status updates
3. **Product Management** - Menu items, categories, addons
4. **Analytics** - Sales metrics, performance tracking
5. **QR Code Management** - Generate and manage QR codes
6. **Notifications** - Order alerts and system notifications
7. **Settings** - Profile and account management

---

## 📁 COMPLETE VENDOR FILE STRUCTURE

### Layout Directory
```
resources/js/pages/vendor/
└── layout/
    └── VendorLayout.vue (✅ EXISTS - Fix wrapper issues in Dashboard.vue & OrderHistory.vue)
```

### Dashboard Module
```
resources/js/pages/vendor/dashboard/
├── Dashboard.vue (⚠️ FIX: Add VendorLayout wrapper)
├── components/
│   ├── StatsCard.vue (Reusable dashboard stats)
│   ├── QuickActions.vue (Action buttons grid)
│   ├── RecentOrders.vue (Latest orders preview)
│   └── NotificationBell.vue (Header notification icon)
└── composables/
    └── useDashboardStats.ts (Dashboard data management)
```

### Orders Management Module
```
resources/js/pages/vendor/orders/
├── Index.vue (Main orders list - incoming orders)
├── History.vue (⚠️ FIX: Add VendorLayout wrapper)
├── Details.vue (Individual order detail view)
├── components/
│   ├── OrderCard.vue (Individual order display)
│   ├── OrderActions.vue (Accept/Decline/Ready buttons)
│   ├── OrderStatusBadge.vue (Status indicators)
│   ├── OrderFilters.vue (Status/date filters)
│   └── BatchActions.vue (Bulk operations)
└── composables/
    ├── useOrders.ts (Orders data management)
    └── useOrderActions.ts (Order status operations)
```

### Products Management Module
```
resources/js/pages/vendor/products/
├── Index.vue (✅ COMPLETE - Product listing with CRUD)
├── Create.vue (New product creation form)
├── Edit.vue (Product editing form)
├── Categories.vue (Product category management)
├── components/
│   ├── ProductCard.vue (Product display card)
│   ├── ProductForm.vue (Create/Edit form)
│   ├── ProductFilters.vue (Search/category/status filters)
│   ├── ProductGrid.vue (Responsive product grid)
│   ├── AddonManager.vue (Product addon management)
│   ├── ImageUploader.vue (Product image upload)
│   ├── StockManager.vue (Stock quantity management)
│   └── BulkActions.vue (Bulk product operations)
├── composables/
│   ├── useProducts.ts (Products CRUD operations)
│   ├── useCategories.ts (Category management)
│   └── useAddons.ts (Addon management)
└── types/
    └── product.ts (Product-related TypeScript types)
```

### Analytics Module
```
resources/js/pages/vendor/analytics/
├── Index.vue (✅ COMPLETE - Analytics dashboard)
├── components/
│   ├── SalesChart.vue (Sales over time chart)
│   ├── OrderMetrics.vue (Order status breakdown)
│   ├── RevenueBreakdown.vue (Revenue analysis)
│   ├── BestSellers.vue (Top selling products)
│   ├── PeriodSelector.vue (Date range picker)
│   ├── MetricCard.vue (Reusable metric display)
│   └── ChartLegend.vue (Chart legend component)
├── composables/
│   ├── useAnalytics.ts (Analytics data management)
│   ├── useSalesData.ts (Sales chart data)
│   └── useMetrics.ts (Dashboard metrics)
└── types/
    └── analytics.ts (Analytics-related types)
```

### QR Code Management Module
```
resources/js/pages/vendor/qr/
├── Index.vue (✅ COMPLETE - QR code management)
├── components/
│   ├── QrPreview.vue (QR code preview display)
│   ├── QrUploader.vue (QR code image upload)
│   ├── QrSettings.vue (QR code configuration)
│   ├── MobileNumberInput.vue (QR mobile number field)
│   └── QrDownload.vue (QR code download functionality)
├── composables/
│   └── useQrCode.ts (QR code management)
└── types/
    └── qr.ts (QR code-related types)
```

### Notifications Module
```
resources/js/pages/vendor/notifications/
├── Index.vue (Notifications list and management)
├── components/
│   ├── NotificationCard.vue (Individual notification display)
│   ├── NotificationFilters.vue (Filter notifications)
│   ├── NotificationActions.vue (Mark read/delete actions)
│   └── NotificationBell.vue (Header notification badge)
├── composables/
│   └── useNotifications.ts (Notification management)
└── types/
    └── notification.ts (Notification-related types)
```

### Settings Module
```
resources/js/pages/vendor/settings/
├── Profile.vue (Vendor profile management)
├── Account.vue (Account settings)
├── Business.vue (Business information)
├── components/
│   ├── ProfileForm.vue (Profile editing form)
│   ├── BusinessForm.vue (Business details form)
│   ├── LogoUploader.vue (Brand logo upload)
│   └── PasswordForm.vue (Password change form)
└── composables/
    └── useVendorSettings.ts (Settings management)
```

---

## 🔧 COMPONENT REUSABILITY STRATEGY

### Shared Components Across Vendor Module
```
resources/js/components/vendor/
├── forms/
│   ├── BaseForm.vue (Reusable form wrapper)
│   ├── InputField.vue (Consistent input styling)
│   ├── SelectField.vue (Dropdown component)
│   ├── TextareaField.vue (Multi-line input)
│   └── FileUpload.vue (File upload component)
├── layout/
│   ├── PageHeader.vue (Consistent page headers)
│   ├── PageFooter.vue (Page footer if needed)
│   └── Breadcrumbs.vue (Navigation breadcrumbs)
├── ui/
│   ├── Button.vue (Styled button component)
│   ├── Badge.vue (Status/pill badges)
│   ├── Modal.vue (Reusable modal dialog)
│   ├── Dropdown.vue (Dropdown menu)
│   ├── LoadingSpinner.vue (Loading states)
│   └── EmptyState.vue (Empty data states)
└── tables/
    ├── BaseTable.vue (Reusable table wrapper)
    ├── TablePagination.vue (Pagination controls)
    └── TableFilters.vue (Table filtering)
```

---

## 📱 VENDOR LAYOUT STRUCTURE

### VendorLayout.vue Requirements
```
resources/js/layouts/vendor/VendorLayout.vue
├── Header
│   ├── Logo/Brand
│   ├── Page Title
│   ├── Notification Bell (real-time count)
│   └── User Menu
├── Sidebar Navigation
│   ├── Dashboard
│   ├── Orders (with pending count badge)
│   ├── Products
│   ├── Analytics
│   ├── QR Code
│   ├── Notifications
│   └── Settings
├── Main Content Area
│   └── Page content slot
└── Footer
    └── System info/version
```

### Responsive Design Considerations
- **Desktop**: Full sidebar navigation
- **Tablet**: Collapsible sidebar
- **Mobile**: Bottom navigation bar or hamburger menu

---

## 🎨 STYLING AND DESIGN SYSTEM

### Tailwind CSS Classes Structure
```css
/* Vendor-specific color scheme */
.vendor-primary { @apply bg-orange-500 text-white; }
.vendor-secondary { @apply bg-orange-50 text-orange-700; }
.vendor-accent { @apply border-orange-200 hover:bg-orange-50; }

/* Status colors */
.status-pending { @apply bg-yellow-100 text-yellow-800; }
.status-accepted { @apply bg-blue-100 text-blue-800; }
.status-ready { @apply bg-green-100 text-green-800; }
.status-cancelled { @apply bg-red-100 text-red-800; }

/* Component spacing */
.vendor-section { @apply p-6 mb-6; }
.vendor-card { @apply bg-white rounded-xl border border-gray-200 p-6; }
.vendor-button { @apply px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600; }
```

---

## 🔄 STATE MANAGEMENT STRATEGY

### Composables for State Management
```
resources/js/composables/vendor/
├── useAuth.ts (Vendor authentication state)
├── useOrders.ts (Orders state management)
├── useProducts.ts (Products state management)
├── useAnalytics.ts (Analytics data state)
├── useNotifications.ts (Notifications state)
└── useSettings.ts (Vendor settings state)
```

### Real-time Updates
- **Orders**: WebSocket connection for live order updates
- **Notifications**: Real-time notification count updates
- **Analytics**: Auto-refresh analytics data

---

## 📡 API INTEGRATION PATTERNS

### HTTP Client Configuration
```typescript
// api/client.ts
const vendorClient = axios.create({
  baseURL: '/api/vendor',
  headers: {
    'Authorization': `Bearer ${localStorage.getItem('token')}`,
    'Content-Type': 'application/json'
  }
});

// API service modules
export const orderService = {
  getOrders: (params) => vendorClient.get('/orders', { params }),
  acceptOrder: (id) => vendorClient.patch(`/orders/${id}/accept`),
  declineOrder: (id) => vendorClient.patch(`/orders/${id}/decline`),
  markReady: (id) => vendorClient.patch(`/orders/${id}/ready`)
};

export const productService = {
  getProducts: (params) => vendorClient.get('/products', { params }),
  createProduct: (data) => vendorClient.post('/products', data),
  updateProduct: (id, data) => vendorClient.put(`/products/${id}`, data),
  deleteProduct: (id) => vendorClient.delete(`/products/${id}`)
};
```

---

## 🔍 CURRENT STATE ANALYSIS

### ✅ Completed Vendor Pages
1. **Dashboard.vue** - Statistics display (needs layout fix)
2. **Products.vue** - Full CRUD functionality
3. **Analytics.vue** - Complete analytics dashboard
4. **QrCode.vue** - QR code management

### ⚠️ Issues to Fix
1. **Dashboard.vue** - Missing VendorLayout wrapper
2. **OrderHistory.vue** - Missing VendorLayout wrapper

### ❌ Missing Vendor Pages
1. Incoming orders management (real-time)
2. Order details modal/view
3. Product creation form
4. Product editing form
5. Vendor settings/profile
6. Notification management
7. Category management

---

## 🎯 IMPLEMENTATION ROADMAP

### Phase 1: Critical Fixes (1-2 days)
1. Fix VendorLayout wrapper in Dashboard.vue
2. Fix VendorLayout wrapper in OrderHistory.vue
3. Test all existing vendor pages

### Phase 2: Core Missing Features (1 week)
1. Create incoming orders management page
2. Add product creation/editing forms
3. Implement vendor settings pages
4. Create notification management

### Phase 3: Enhanced Features (1 week)
1. Add real-time order updates
2. Implement advanced analytics charts
3. Add bulk operations
4. Mobile optimization

### Phase 4: Polish & Testing (2-3 days)
1. UI/UX improvements
2. Performance optimization
3. Error handling
4. Testing across devices

---

## 📊 ESTIMATED DEVELOPMENT TIME

| Feature Category | Estimated Files | Development Time |
|------------------|-----------------|------------------|
| Layout Fixes | 2 files | 1 hour |
| Orders Management | 8 files | 3-4 days |
| Products Enhancement | 10 files | 2-3 days |
| Settings & Profile | 6 files | 1-2 days |
| Components & Composables | 15 files | 2-3 days |
| **Total** | **~41 files** | **8-12 days** |

---

## 🔧 TECHNICAL SPECIFICATIONS

### Dependencies
- Vue.js 3 (Composition API)
- Inertia.js (Client-side routing)
- Tailwind CSS (Styling)
- Chart.js or similar (Analytics charts)
- Axios (HTTP client)

### Browser Support
- Modern browsers (Chrome, Firefox, Safari, Edge)
- Mobile responsive design
- Progressive Web App capabilities

### Performance Considerations
- Lazy loading for large data sets
- Image optimization for product photos
- Efficient state management
- API response caching

This vendor frontend organization plan provides a comprehensive structure for building a complete vendor management interface for the QR code restaurant ordering system.
