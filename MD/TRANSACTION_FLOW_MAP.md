# Transaction Flow & Feature Map

---

## Roles Overview

| Role | Description |
|------|-------------|
| **Customer** | End user who browses vendors, orders food, and pays |
| **Vendor** | Food stall owner who manages products and fulfills orders |
| **Superadmin** | System admin who manages vendors |

---

## Transaction Flow

```
Customer                          Vendor
   │                                │
   ├─── Browse Vendors ────────────►│
   │                                │
   ├─── View Menu ─────────────────►│
   │                                │
   ├─── Add to Cart                 │
   │                                │
   ├─── Select Payment Method       │
   │    (Cash or QR)                │
   │                                │
   ├─── Place Order ───────────────►│── Receive Order Notification
   │                                │
   │                                ├─── Accept/Decline Order
   │                                │
   │◄── Order Status Update ────────┤
   │                                │
   │                                ├─── Prepare Order
   │                                │
   │◄── "Ready for Pickup" ─────────┤
   │                                │
   ├─── Pick Up Order ─────────────►│── Mark Complete
   │                                │
   └─── View Receipt                │
```

---

## Vendor Features

| Feature | Description |
|---------|-------------|
| **Products** | Add, edit, delete menu items with prices and images |
| **Addons** | Attach optional extras to products (e.g., extra rice) |
| **Orders** | View, accept, decline, mark ready, complete orders |
| **Analytics** | View sales, revenue, profit, best sellers |
| **QR Upload** | Upload GCash/payment QR code for customers |
| **Notifications** | Real-time alerts for new orders |

---

## Customer Features

| Feature | Description |
|---------|-------------|
| **Browse Vendors** | See list of active food stalls |
| **View Menu** | See vendor products with categories |
| **Cart** | Multi-vendor cart with addon selection |
| **Place Order** | Choose table, payment method, add notes |
| **Order Tracking** | Real-time status updates |
| **Notifications** | Stored alerts for order status changes |
| **Receipt** | Downloadable order receipt |
| **QR Download** | Download vendor's payment QR code |

---

## Payment Methods

| Method | Flow |
|--------|------|
| **Cash** | Pay at cashier when order is ready |
| **QR Code** | Scan vendor's GCash QR, upload proof |

---

## Order Statuses

| Status | Meaning |
|--------|---------|
| `pending` | Order placed, waiting for vendor |
| `accepted` | Vendor accepted, preparing food |
| `ready_for_pickup` | Food ready, customer notified |
| `completed` | Customer picked up order |
| `cancelled` | Order cancelled by customer/vendor |

---

## Superadmin Features

| Feature | Description |
|---------|-------------|
| **Vendor Management** | Create, edit, activate/deactivate vendors |
| **Dashboard** | Overview of system statistics |
