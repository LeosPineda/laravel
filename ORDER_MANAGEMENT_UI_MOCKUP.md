# Order Management UI Mockup

## Overview
Simple two-state Order Management UI for vendors to handle customer orders efficiently.

## Design Principles
- **Two Separate States**: Incoming Orders OR Order History (not both at once)
- **Clean & Minimal**: Focus on essential actions only
- **Mobile Responsive**: Works on all devices
- **Clear Visual Hierarchy**: Easy to scan and understand

---

## STATE 1: INCOMING ORDERS
*Shows ONLY pending orders requiring vendor action*

### Layout Structure
```
┌─────────────────────────────────────────┐
│              ORDER MANAGEMENT           │
├─────────────────────────────────────────┤
│  📊 STATS:  [2 Incoming] [15 History]  │
├─────────────────────────────────────────┤
│                                         │
│  🔔 INCOMING ORDERS (2 new)            │
│  ┌─────────────────────────────────────┐ │
│  │ ⚪ #12345 • Table 5 • 2:30 PM        │ │
│  │ Items: 3 • 💳 Cashier • ₱450.00     │ │
│  │                                     │ │
│  │ [View Details] [Decline] [Accept]   │ │
│  └─────────────────────────────────────┘ │
│  ┌─────────────────────────────────────┐ │
│  │ ⚪ #12346 • Table 3 • 2:25 PM        │ │
│  │ Items: 1 • 📱 QR Code • ₱180.00     │ │
│  │                                     │ │
│  │ [View Details] [Decline] [Accept]   │ │
│  └─────────────────────────────────────┘ │
└─────────────────────────────────────────┘
```

### Incoming Order Card Details
- **Header**: Order number, Table number, Time with pulsing indicator
- **Summary**: Item count, Payment method badge, Total amount
- **Actions**: Three buttons (View Details, Decline, Accept)

### Order Details Modal
```
┌─────────────────────────────────────────┐
│  ORDER DETAILS              [✕ Close]  │
├─────────────────────────────────────────┤
│  Order #: 12345                         │
│  Table: 5                               │
│  Time: 2:30 PM                          │
│                                         │
│  ORDER ITEMS                            │
│  ┌─────────────────────────────────────┐ │
│  │ Cheeseburger x2                     │ │
│  │ + Extra Cheese                      │ │
│  │ ₱200.00                             │ │
│  │ ─────────────────────────────────   │ │
│  │ French Fries x1                     │ │
│  │ ₱80.00                              │ │
│  │ ─────────────────────────────────   │ │
│  │ Subtotal: ₱280.00                   │ │
│  └─────────────────────────────────────┘ │
│                                         │
│  PAYMENT INFO                           │
│  Method: 💵 Pay at Cashier              │
│  Total: ₱320.00                         │
│  [View Payment Proof]                   │
│                                         │
│  CUSTOMER INSTRUCTIONS                  │
│  ⚠️ Please make it well-done            │
│                                         │
│  [Close] [Decline] [Accept]             │
└─────────────────────────────────────────┘
```

---

## STATE 2: ORDER HISTORY
*Shows ONLY completed/accepted/declined orders*

### Layout Structure
```
┌─────────────────────────────────────────┐
│              ORDER MANAGEMENT           │
├─────────────────────────────────────────┤
│  📊 STATS:  [2 Incoming] [15 History]  │
├─────────────────────────────────────────┤
│                                         │
│  🧾 ORDER HISTORY (15 completed)       │
│  [Select All (15)] [🗑️ Delete Selected (3)] │
│  ┌─────────────────────────────────────┐ │
│  │ ☑️ #12344 • Table 2 • 2:15 PM       │ │
│  │ ₱320.00 • [View Receipt]            │ │
│  └─────────────────────────────────────┘ │
│  ┌─────────────────────────────────────┐ │
│  │ ☑️ #12343 • Table 1 • 2:00 PM       │ │
│  │ ₱280.00 • [View Receipt]            │ │
│  └─────────────────────────────────────┘ │
│  ┌─────────────────────────────────────┐ │
│  │ ☐ #12342 • Table 4 • 1:45 PM       │ │
│  │ ₱150.00 • [View Receipt]            │ │
│  └─────────────────────────────────────┘ │
└─────────────────────────────────────────┘
```

### Order History Actions
- **Select All Checkbox**: Toggle all orders
- **Delete Selected Button**: Appears when orders are selected
- **Individual Checkboxes**: For each order
- **View Receipt Button**: Shows order receipt

### Receipt Modal
```
┌─────────────────────────────────────────┐
│  🧾 ORDER RECEIPT      4Rodz Food Court │
├─────────────────────────────────────────┤
│                                         │
│  #12345                                 │
│  Table: 5                               │
│  12/25/2025 2:30 PM                     │
│                                         │
│  Cheeseburger x2 ................ ₱200  │
│  + Extra Cheese .................. ₱20  │
│  French Fries x1 ................. ₱80  │
│                                         │
│  TOTAL ............................ ₱300 │
│                                         │
│  Payment: 💵 Cash                       │
│  Status: ✅ Accepted                    │
│                                         │
│  Thank you for your order!              │
│  🍽️ Enjoy your meal! 🍽️                │
│                                         │
│  [Close] [📄 Print/Download]            │
└─────────────────────────────────────────┘
```

---

## INTERACTION FLOWS

### 1. Accept/Decline Flow (State 1 Only)
```
Incoming Order → Click Accept/Decline → 
Show Undo Toast (5 sec) → 
Action Confirmed OR User Clicks Undo
```

### 2. View Details Flow (State 1 Only)
```
Incoming Order → Click "View Details" → 
Order Details Modal → 
[Close] OR [Decline] OR [Accept]
```

### 3. Delete Orders Flow (State 2 Only)
```
Order History → Select Orders → 
[Delete Selected] → 
Confirm Dialog → Orders Deleted
```

---

## VISUAL DESIGN SPECIFICATIONS

### Color Scheme
- **Incoming Orders**: Yellow border (#FEF3C7), Yellow background (#FEF9E7)
- **Order History**: Gray border (#E5E7EB), White background
- **Primary Actions**: Green (#10B981), Red (#EF4444), Gray (#6B7280)
- **Payment Badges**: Blue for QR Code, Green for Cashier

### Typography
- **Headers**: Bold, 18px
- **Order Numbers**: Bold, 16px
- **Amounts**: Bold, Orange (#FF6B35)
- **Times**: Regular, Gray (#6B7280)

### Animations
- **Pulsing Indicator**: Yellow dot for new orders (2s infinite)
- **Undo Toast**: Slide up animation, countdown bar
- **Hover Effects**: Subtle shadows and color changes

### Mobile Responsiveness
- Stack buttons vertically on mobile
- Reduce padding and font sizes
- Touch-friendly button sizes (min 44px)

---

## KEY FEATURES

### ✅ Implemented
- [x] Two separate states (Incoming/History - never shown together)
- [x] 5-second undo functionality
- [x] Order details modal (State 1 only)
- [x] Receipt viewing (State 2 only)
- [x] Batch delete with select all (State 2 only)
- [x] Payment proof viewing
- [x] Special instructions display
- [x] Mobile responsive design

### 🎯 User Experience
- **State 1 Focus**: Quick decisions on incoming orders
- **State 2 Focus**: Efficient order history management
- **Clear Feedback**: Visual confirmations and undo option
- **No Confusion**: Each state shows only relevant information

### 📱 Mobile Optimization
- Touch-friendly interface
- Readable on small screens
- Swipe gestures for actions (future enhancement)

---

## TECHNICAL NOTES

### Data Structure
```typescript
interface Order {
    id: number;
    order_number: string;
    status: 'pending' | 'accepted' | 'declined' | 'completed';
    table_number: string | null;
    total_amount: number;
    payment_method: 'qr_code' | 'cashier';
    payment_proof_url: string | null;
    special_instructions: string | null;
    created_at: string;
    items: OrderItem[];
}
```

### State Management
- **State 1**: Show only `status === 'pending'` orders
- **State 2**: Show only `status !== 'pending'` orders
- **Switching**: Toggle between states with navigation or tabs
- **Undo actions**: 5-second timeout with clear option

### API Endpoints Needed
- `PUT /vendor/orders/{id}/accept`
- `PUT /vendor/orders/{id}/decline`
- `GET /vendor/orders/{id}/receipt`
- `DELETE /vendor/orders/batch-delete`

---

This mockup provides a clear, practical design with truly separate states. Each state shows only the relevant information for that context, making the interface simple and focused.
