# Vendor Backend Alignment Audit

## Executive Summary
This document provides a strict 1-to-1 alignment audit between backend and frontend for the Vendor role, scanning ALL backend layers (controllers, events, notifications, routes, models).

---

## 1. Backend Scanning Checklist

### Controllers Scanned
| Controller | Scanned | Frontend Coverage |
|-----------|---------|-------------------|
| OrderController | ✅ | ✅ Complete |
| ProductController | ✅ | ✅ Complete |
| AddonController | ✅ | ⚠️ Missing: stats endpoint |
| AnalyticsController | ✅ | ✅ Complete |
| QrController | ✅ | ✅ Complete |
| NotificationController | ✅ | ❌ **NOT IMPLEMENTED** |

### Events Scanned
| Event | Channel | Frontend Listens |
|-------|---------|------------------|
| OrderReceived | `vendor-orders.{id}` | ❌ **NOT IMPLEMENTED** |
| OrderStatusChanged | `vendor-orders.{id}` | ❌ **NOT IMPLEMENTED** |

### Models Scanned
| Model | Scopes Used | Frontend Aware |
|-------|-------------|----------------|
| Order | forVendor, byStatus | ✅ |
| Product | forVendor | ✅ |
| Addon | - | ✅ |
| Notification | forVendor | ❌ **NOT USED** |
| Vendor | - | ✅ |

---

## 2. Vendor Feature Completeness Matrix

### ✅ COMPLETE - Frontend Matches Backend
| Feature | Backend Endpoint | Frontend Page/Component |
|---------|-----------------|------------------------|
| Dashboard Stats | `/orders/stats` | Dashboard.vue |
| Order List | `/orders` | IncomingOrders.vue, OrderHistory.vue |
| Order Detail | `/orders/{id}` | OrderDetailModal.vue |
| Accept Order | `/orders/{id}/accept` | IncomingOrders.vue |
| Decline Order | `/orders/{id}/decline` | IncomingOrders.vue |
| Mark Ready | `/orders/{id}/ready` | OrderHistory.vue |
| Delete Orders | `/orders/batch` | OrderHistory.vue |
| Product CRUD | `/products` | Products.vue, ProductFormModal.vue |
| Product Toggle | `/products/{id}/toggle-status` | Products.vue |
| Addon CRUD | `/products/{id}/addons`, `/addons/{id}` | AddonManagement.vue |
| Addon Toggle | `/addons/{id}/toggle` | AddonManagement.vue |
| Analytics | `/analytics/*` | Analytics.vue |
| QR CRUD | `/qr` | QrCode.vue |
| QR Stats | `/qr/stats` | QrCode.vue ✅ (VALID - QR-specific stats) |

### ❌ MISSING - Backend Exists, Frontend NOT Implemented
| Feature | Backend Endpoint | Required Frontend |
|---------|-----------------|-------------------|
| **Notifications List** | `/notifications` | NotificationsPage.vue |
| **Unread Count** | `/notifications/count` | NotificationBell.vue |
| **Recent Notifications** | `/notifications/recent` | Dashboard widget |
| **Mark Read** | `/notifications/{id}/read` | Notification component |
| **Mark All Read** | `/notifications/mark-all-read` | Notifications page |
| **Delete Notification** | `/notifications/{id}` | Notifications page |
| **Real-time Orders** | OrderReceived event | Pusher listener |
| **Real-time Status** | OrderStatusChanged event | Pusher listener |
| **Bulk Product Toggle** | `/products/bulk` | Products.vue bulk actions |
| **Bulk Addon Toggle** | `/addons/bulk` | AddonManagement bulk |
| **Addon Statistics** | `/products/{id}/addons/stats` | AddonManagement stats |
| **Product Categories** | `/products/categories` | Products.vue filter |

---

## 3. Real-time & Notification Validation Plan

### Current Backend Setup
```php
// OrderReceived Event - broadcasts when new order is placed
class OrderReceived implements ShouldBroadcast {
    public function broadcastOn(): array {
        return [new PrivateChannel('vendor-orders.' . $this->vendor->id)];
    }
    public function broadcastAs(): string { return 'OrderReceived'; }
}

// OrderStatusChanged Event - broadcasts when status changes
class OrderStatusChanged implements ShouldBroadcast {
    public function broadcastOn(): array {
        return [
            new PrivateChannel('vendor-orders.' . $this->vendor->id),
            new PrivateChannel('customer-orders.' . $this->customer->id),
        ];
    }
    public function broadcastAs(): string { return 'OrderStatusChanged'; }
}
```

### Required Frontend Implementation

#### Step 1: Install Laravel Echo & Pusher
```bash
npm install laravel-echo pusher-js
```

#### Step 2: Configure Echo (resources/js/echo.js)
```javascript
import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

window.Pusher = Pusher
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true
})
```

#### Step 3: Create Composable (resources/js/composables/useVendorRealtime.ts)
```typescript
import { onMounted, onUnmounted, ref } from 'vue'

export function useVendorRealtime(vendorId: number) {
  const newOrderCount = ref(0)
  
  onMounted(() => {
    window.Echo.private(`vendor-orders.${vendorId}`)
      .listen('OrderReceived', (e) => {
        newOrderCount.value++
        // Show notification toast
        // Play sound
        // Refresh orders list
      })
      .listen('OrderStatusChanged', (e) => {
        // Update order in list
      })
  })
  
  onUnmounted(() => {
    window.Echo.leave(`vendor-orders.${vendorId}`)
  })
  
  return { newOrderCount }
}
```

---

## 4. QR Analytics Decision

### Analysis
| Endpoint | Purpose | Unique Data |
|----------|---------|-------------|
| `/qr/stats` | QR-specific payment stats | QR orders count, QR revenue |
| `/analytics/*` | General sales/order analytics | Total sales, best sellers, order metrics |

### Decision: **KEEP QR Stats** ✅
- QR stats provide **specific payment method analytics** (how many orders used QR vs cash)
- General analytics provide **overall business performance**
- These are complementary, NOT redundant
- No removal necessary

---

## 5. Implementation Priority

### HIGH Priority (Critical Missing Features)
1. **Notification System** - Backend fully implemented, frontend missing
2. **Real-time Updates** - Events exist, no Pusher listener

### MEDIUM Priority (Enhancement)
3. **Bulk Operations** - For managing multiple products/addons
4. **Product Categories Dropdown** - Uses existing endpoint

### LOW Priority (Nice-to-have)
5. **Addon Statistics** - Per-product addon analytics

---

## 6. Files to Create

### New Files Required
```
resources/js/pages/vendor/Notifications.vue          # Full notifications page
resources/js/components/vendor/NotificationBell.vue  # Header notification icon
resources/js/composables/useVendorRealtime.ts        # Pusher composable
resources/js/echo.js                                 # Echo configuration
```

### Files to Update
```
resources/js/layouts/vendor/VendorLayout.vue         # Add notification bell
resources/js/pages/vendor/Dashboard.vue              # Add recent notifications widget
resources/js/pages/vendor/Orders.vue                 # Add real-time listener
resources/js/pages/vendor/Products.vue               # Add bulk actions
routes/web.php                                       # Add /vendor/notifications route
```

---

## 7. Backend API Coverage Summary

| Category | Total Endpoints | Implemented | Missing | Coverage |
|----------|-----------------|-------------|---------|----------|
| Orders | 7 | 7 | 0 | 100% |
| Products | 6 | 5 | 1 (bulk) | 83% |
| Addons | 7 | 5 | 2 (bulk, stats) | 71% |
| QR | 9 | 9 | 0 | 100% |
| Analytics | 5 | 5 | 0 | 100% |
| Notifications | 11 | 0 | 11 | **0%** |
| **TOTAL** | 45 | 31 | 14 | **69%** |

---

## 8. Immediate Action Items

### Do NOT Remove
- ❌ QR Stats section in QrCode.vue (it's valid and uses real backend endpoint)

### Must Implement
1. Create `Notifications.vue` page with full CRUD
2. Create `NotificationBell.vue` component for header
3. Add notification route to VendorLayout sidebar
4. Set up Pusher/Echo for real-time order updates
5. Add recent notifications widget to Dashboard

### Optional Enhancements
6. Add bulk product/addon toggle functionality
7. Add product categories endpoint usage
8. Add addon statistics display

---

## Conclusion

The frontend covers **69%** of backend vendor features. The most critical gap is the **Notification System** (11 endpoints, 0% coverage) and **Real-time Events** (2 events, 0% coverage). QR analytics should be **kept** as it provides unique payment method insights separate from general analytics.
