# Vendor UI Refactor Plan
**Date:** December 27, 2025

---

## SCOPE SUMMARY

| Task | Action | Risk |
|------|--------|------|
| QR Statistics | Remove from QrCode.vue | Low |
| Notification Bell | Remove component | Low |
| Notifications Page | Remove page + route | Low |
| Toast System | Create composable | Low |
| Sound Alert | Add to toast | Low |
| Responsiveness | Fix all vendor pages | Medium |

---

## PHASE 1: Remove Redundant Features

### 1.1 Remove QR Statistics (Duplicates Analytics)
**File:** `resources/js/pages/vendor/QrCode.vue`
- Remove statistics section (already in Analytics.vue)
- Keep only: QR upload, preview, mobile number

### 1.2 Remove Notification System
**Files to modify:**
| File | Action |
|------|--------|
| `VendorLayout.vue` | Remove `<NotificationBell />` import & usage |
| `routes/web.php` | Remove `/vendor/notifications` route |

**Files to delete:**
| File | Reason |
|------|--------|
| `NotificationBell.vue` | Replaced by toast |
| `Notifications.vue` | No longer needed |

**Backend routes to keep:** (for potential future use)
- Keep `/api/vendor/notifications/*` - doesn't hurt, can be used later

---

## PHASE 2: Toast + Sound System

### 2.1 Create Toast Composable
**File:** `resources/js/composables/useToast.ts`
```typescript
// No dependencies - pure Vue
export function useToast() {
  const toasts = ref([])
  
  const show = (message, type = 'info') => {
    // Add toast, auto-remove after 5s
  }
  
  return { toasts, show }
}
```

### 2.2 Create Toast Container Component
**File:** `resources/js/components/ui/ToastContainer.vue`
- Renders toasts from composable
- Position: bottom-right
- Auto-dismiss after 5s
- Click to dismiss

### 2.3 Add Sound Alert
**File:** `public/sounds/new-order.mp3`
- Short notification sound
- Plays on new order via Echo event

### 2.4 Integration Points
| File | Integration |
|------|-------------|
| `VendorLayout.vue` | Add `<ToastContainer />` |
| `IncomingOrders.vue` | On `.OrderReceived` → show toast + play sound |

---

## PHASE 3: Mobile Responsiveness

### 3.1 Priority Order (Most Used First)
1. **IncomingOrders.vue** - Primary vendor screen
2. **OrderHistory.vue** - Order management
3. **Products.vue** - Product management
4. **Dashboard.vue** - Stats overview
5. **QrCode.vue** - QR management
6. **Analytics.vue** - Detailed analytics

### 3.2 Responsive Patterns to Apply
| Pattern | Where |
|---------|-------|
| Stack on mobile, grid on desktop | Product cards, order cards |
| Collapsible sidebar | VendorLayout navigation |
| Bottom sheet modals | ProductFormModal, OrderDetailModal |
| Touch-friendly buttons | All interactive elements (min 44px) |
| Horizontal scroll tables | Order items, analytics data |

### 3.3 Breakpoints
```css
/* Mobile first */
@media (min-width: 640px) { /* sm */ }
@media (min-width: 768px) { /* md */ }
@media (min-width: 1024px) { /* lg */ }
```

---

## IMPLEMENTATION ORDER

```
Day 1: Remove redundant features
├── Remove NotificationBell from VendorLayout
├── Remove Notifications route
├── Remove QR statistics section
└── Build & verify

Day 2: Toast + Sound
├── Create useToast composable
├── Create ToastContainer component
├── Add sound file
├── Integrate with Echo in IncomingOrders
└── Build & verify

Day 3-4: Responsiveness
├── VendorLayout (sidebar)
├── IncomingOrders
├── OrderHistory
├── Products
├── Dashboard
├── QrCode
├── Analytics
└── Test on mobile devices
```

---

## FALLBACK: No Real-Time

If WebSocket fails, system still works:
- **IncomingOrders:** 30-second polling fetches new orders
- **Toast:** Only shows when Echo event fires (no toast = still functional)
- **Sound:** Only plays with toast
- **Manual refresh:** Always available

**Toast is UI feedback only - NOT business logic**

---

## FILES AFFECTED (Final List)

**Delete:**
- `resources/js/components/vendor/NotificationBell.vue`
- `resources/js/pages/vendor/Notifications.vue`

**Modify:**
- `resources/js/layouts/vendor/VendorLayout.vue` - Remove bell, add toast
- `resources/js/pages/vendor/QrCode.vue` - Remove stats
- `resources/js/pages/vendor/IncomingOrders.vue` - Add toast trigger
- `routes/web.php` - Remove notification route

**Create:**
- `resources/js/composables/useToast.ts`
- `resources/js/components/ui/ToastContainer.vue`
- `public/sounds/new-order.mp3`

---

## VERIFICATION CHECKLIST

- [ ] No notification bell in header
- [ ] No /vendor/notifications route
- [ ] No QR statistics (use Analytics instead)
- [ ] Toast appears on new order (when Echo connected)
- [ ] Sound plays with toast
- [ ] All pages work on 375px width (mobile)
- [ ] All pages work on 1920px width (desktop)
- [ ] Orders still work without real-time
