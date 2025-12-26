# CUSTOMER UI HARDCODED MOCKUP - Based on Actual Code

## 🎨 Actual Design System (From Code)
**Colors**: Primary Orange `#FF6B35`, Background `#F5F5F5`, Text `#1A1A1A`, Borders `#E0E0E0`
**Layout**: Desktop header + Mobile bottom navigation
**Components**: Cards `bg-white rounded-xl shadow-sm border border-[#E0E0E0]`

---

## 🏠 HOME PAGE (Home.vue)

### Main Home Page
```
┌─────────────────────────────────────────┐
│  🍽️ 4Rodz Food Court           [🛒 Cart]│  ← Desktop Header
├─────────────────────────────────────────┤
│                                         │
│  Welcome to 4Rodz Food Court 🍽️         │
│  Browse our vendors and order your      │
│  favorite food                          │
│                                         │
│  🔍 [Search vendors...]                 │
│                                         │
│  ┌─────────────┬─────────────┬─────────┐ │
│  │ 🍔 Mike's   │ 🍜 Ramen    │ 🍕 Pizza│ │
│  │ Burger      │ House       │ Corner  │ │
│  │ 12 products │ 8 products  │ 15 prod │ │
│  │ [Browse →]  │ [Browse →]  │[Browse→]│ │
│  └─────────────┴─────────────┴─────────┘ │
│                                         │
│  ┌─────────────┬─────────────┬─────────┐ │
│  │ 🥗 Salad    │ 🍰 Desserts │ ☕ Coffee│ │
│  │ Express     │ Corner      │ Station │ │
│  │ 6 products  │ 10 products │ 5 prod  │ │
│  │ [Browse →]  │ [Browse →]  │[Browse→]│ │
│  └─────────────┴─────────────┴─────────┘ │
└─────────────────────────────────────────┘
```

**Search Bar**: `w-full pl-12 pr-4 py-3 rounded-xl border border-[#E0E0E0] bg-white focus:outline-none focus:ring-2 focus:ring-[#FF6B35] focus:border-transparent`

**Vendor Card**: `bg-white rounded-xl shadow-sm border border-[#E0E0E0] overflow-hidden hover:shadow-md hover:border-[#FF6B35]`
- **Image**: `h-40 bg-[#F5F5F5] overflow-hidden`
- **Content**: `p-4`
- **Name**: `font-semibold text-lg text-[#1A1A1A] group-hover:text-[#FF6B35]`
- **Rating**: `text-sm text-gray-500`
- **Arrow**: `ChevronRight class="w-5 h-5 text-gray-400 group-hover:text-[#FF6B35]"`

---

## 🍔 VENDOR MENU (VendorMenu.vue)

### Vendor Menu Page
```
┌─────────────────────────────────────────┐
│  [← Back] 🍔 Mike's Burger    [🛒 Cart] │  ← Desktop Header
├─────────────────────────────────────────┤
│                                         │
│  [🍔 Image] Mike's Burger               │
│  12 products available                  │
│                                         │
│  [All] [Burgers] [Sides] [Drinks]       │
│                                         │
│  ┌─────────────┬─────────────┬─────────┐ │
│  │   [Image]   │   [Image]   │ [Image] │ │
│  │ Mike's Spec │ Cheeseburger│ Crispy  │ │
│  │ Burger      │             │ Fries   │ │
│  │ ₱150.00     │ ₱120.00     │ ₱80.00  │ │
│  │ [Add →]     │ [Add →]     │[Add →]  │ │
│  └─────────────┴─────────────┴─────────┘ │
│                                         │
│  ┌─────────────┬─────────────┬─────────┐ │
│  │   [Image]   │   [Image]   │ [Image] │ │
│  │ Chicken     │ Fish Fillet │ Coke    │ │
│  │ Sandwich    │ Combo       │         │ │
│  │ ₱180.00     │ ₱220.00     │ ₱50.00  │ │
│  │ [Add →]     │ [Add →]     │[Add →]  │ │
│  └─────────────┴─────────────┴─────────┘ │
└─────────────────────────────────────────┘
```

**Back Button**: `inline-flex items-center gap-2 text-gray-500 hover:text-[#FF6B35]`
**Vendor Info**: `flex items-center gap-4`
**Category Filters**: `px-4 py-2 rounded-full whitespace-nowrap`
- Active: `bg-[#FF6B35] text-white`
- Inactive: `bg-white text-[#1A1A1A] border border-[#E0E0E0] hover:border-[#FF6B35]`

**Product Card**: `bg-white rounded-xl shadow-sm border border-[#E0E0E0] overflow-hidden`
- **Image**: `h-36 bg-[#F5F5F5]`
- **Out of Stock Badge**: `absolute top-2 right-2 bg-red-500 text-white text-xs px-2 py-1 rounded`
- **Content**: `p-4`
- **Name**: `font-medium text-[#1A1A1A]`
- **Category**: `text-sm text-gray-500`
- **Price**: `font-bold text-[#FF6B35]`
- **Add Button**: `flex items-center gap-1 px-3 py-2 bg-[#FF6B35] text-white rounded-lg hover:bg-orange-600`

### Add to Cart Modal
```
┌─────────────────────────────────────────┐
│  Add to Cart                    [✕]     │
├─────────────────────────────────────────┤
│  ┌─────────┬─────────────────────────┐   │
│  │[Image]  │ Mike's Special Burger   │   │
│  │         │ ₱150.00                 │   │
│  └─────────┴─────────────────────────┘   │
│                                         │
│  🥩 COOKING LEVEL                      │
│  ○ Rare ○ Medium ✅ Well-done         │
│                                         │
│  🧀 ADD-ONS                            │
│  ☑️ Extra Cheese (+₱20)                │
│  ☑️ Bacon (+₱30)                       │
│  ❌ Avocado (+₱25)                     │
│                                         │
│  🔢 QUANTITY                           │
│  [-] [1] [+]                           │
│                                         │
│  💰 TOTAL: ₱200.00                     │
│                                         │
│  [Add to Cart - ₱200.00]               │
└─────────────────────────────────────────┘
```

**Modal**: `bg-white w-full md:w-[480px] md:rounded-xl rounded-t-xl max-h-[90vh] overflow-y-auto`
**Header**: `sticky top-0 bg-white border-b border-[#E0E0E0] p-4 flex items-center justify-between`
**Product Info**: `flex gap-4`
**Add-ons**: `w-full flex items-center justify-between p-3 rounded-lg border`
- Selected: `border-[#FF6B35] bg-orange-50`
- Unselected: `border-[#E0E0E0] hover:border-[#FF6B35]`
**Quantity**: `flex items-center gap-4`
**Add Button**: `w-full py-3 bg-[#FF6B35] text-white font-medium rounded-xl hover:bg-orange-600`

---

## 🛒 CART PAGE (Cart.vue)

### Cart View
```
┌─────────────────────────────────────────┐
│  Your Cart                               │
├─────────────────────────────────────────┤
│  ┌─────────────────────────────────────┐ │
│  │ [🍔 Image] Mike's Burger            │ │
│  │ Table 5 • 2 items                   │ │
│  │ ─────────────────────────────────   │ │
│  │ Total: ₱530.00                      │ │
│  │                                     │ │
│  │ [Edit Order] [Checkout →]           │ │
│  └─────────────────────────────────────┘ │
│                                         │
│  ┌─────────────────────────────────────┐ │
│  │ [🍜 Image] Ramen House              │ │
│  │ Table 3 • 1 item                    │ │
│  │ ─────────────────────────────────   │ │
│  │ Total: ₱280.00                      │ │
│  │                                     │ │
│  │ [Edit Order] [Checkout →]           │ │
│  └─────────────────────────────────────┘ │
└─────────────────────────────────────────┘
```

**Cart Container**: `max-w-lg mx-auto px-4 py-6`
**Vendor Cart**: `bg-white rounded-2xl shadow-sm border border-[#E0E0E0] overflow-hidden`
**Vendor Header**: `flex items-center gap-3 p-4 border-b border-[#E0E0E0]`
**Summary**: `p-4 bg-[#F5F5F5]`
**Total**: `text-xl font-bold text-[#FF6B35]`
**Buttons**: 
- Edit: `flex-1 py-3 bg-white border border-[#E0E0E0] text-[#1A1A1A] rounded-xl hover:bg-gray-50`
- Checkout: `flex-1 py-3 bg-[#FF6B35] text-white rounded-xl hover:bg-orange-600`

### Edit Cart Modal
```
┌─────────────────────────────────────────┐
│  Edit Order                     [✕]     │
├─────────────────────────────────────────┤
│  ┌─────────────────────────────────────┐ │
│  │ [🍔 Image] Mike's Special Burger    │ │
│  │ ₱200.00                             │ │
│  │ + Extra Cheese + Bacon               │ │
│  │                                     │ │
│  │ ┌─────────┬─────────┬─────────────┐ │ │
│  │ │    [-]  │   [ 2 ] │      [+]    │ │ │
│  │ └─────────┴─────────┴─────────────┘ │ │
│  │                                     │ │
│  │ 🗑️ Remove Item                      │ │
│  └─────────────────────────────────────┘ │
│                                         │
│  Total: ₱400.00                         │
│                                         │
│  [Save & Back to Cart]                  │
└─────────────────────────────────────────┘
```

**Modal**: `bg-white w-full md:w-[450px] md:rounded-xl rounded-t-xl max-h-[85vh] overflow-hidden`
**Quantity Controls**: `flex items-center gap-2 bg-[#F5F5F5] rounded-full p-1`
**Quantity Button**: `w-8 h-8 rounded-full bg-white flex items-center justify-center shadow-sm`

### Checkout Modal - Step 1: Payment Method
```
┌─────────────────────────────────────────┐
│  Payment Method                 [✕]     │
├─────────────────────────────────────────┤
│  How would you like to pay?             │
│                                         │
│  ┌─────────────────────────────────────┐ │
│  │ 💵 Pay at Cashier                   │ │
│  │ Pay cash when you pick up           │ │
│  │                                     │ │
│  │ [→]                                 │ │
│  └─────────────────────────────────────┘ │
│                                         │
│  ┌─────────────────────────────────────┐ │
│  │ 📱 QR Code Payment                  │ │
│  │ Scan QR and upload proof            │ │
│  │                                     │ │
│  │ [→]                                 │ │
│  └─────────────────────────────────────┘ │
│                                         │
│  💰 Total: ₱530.00                      │
└─────────────────────────────────────────┘
```

### Checkout Modal - Step 2: QR Payment
```
┌─────────────────────────────────────────┐
│  ← Payment Method  Scan & Pay   [✕]     │
├─────────────────────────────────────────┤
│  Scan QR code to pay                    │
│                                         │
│  ┌─────────────────────────────────────┐ │
│  │                                     │ │
│  │        [QR CODE IMAGE]              │ │
│  │                                     │ │
│  └─────────────────────────────────────┘ │
│                                         │
│  💰 ₱530.00                             │
│                                         │
│  Upload Payment Screenshot              │
│  ┌─────────────────────────────────────┐ │
│  │     [📷 Tap to upload]              │ │
│  └─────────────────────────────────────┘ │
│                                         │
│  [Continue →]                           │
└─────────────────────────────────────────┘
```

### Checkout Modal - Step 3: Order Details
```
┌─────────────────────────────────────────┐
│  ← Scan & Pay    Order Details   [✕]    │
├─────────────────────────────────────────┤
│  Table Number *                         │
│  [T5                                 ]  │
│                                         │
│  Special Instructions                   │
│  [No onions, extra sauce...]           │
│                                         │
│  Order Summary                         │
│  Mike's Special Burger x2      ₱400    │
│  Crispy Fries x1              ₱80     │
│  Soft Drink x1               ₱50      │
│  ───────────────────────────           │
│  Total                        ₱530    │
│                                         │
│  [Place Order 🚀]                       │
└─────────────────────────────────────────┘
```

**Input Fields**: `w-full px-4 py-3 border border-[#E0E0E0] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#FF6B35]`
**Submit Button**: `w-full py-3 bg-[#FF6B35] text-white font-medium rounded-xl hover:bg-orange-600`

---

## 👤 PROFILE PAGE (Profile.vue)

### Profile View
```
┌─────────────────────────────────────────┐
│  Profile                                 │
├─────────────────────────────────────────┤
│  ┌─────────────────────────────────────┐ │
│  │ 👤 [📷] John Doe                    │ │
│  │ 📧 john@example.com                 │ │
│  └─────────────────────────────────────┘ │
│                                         │
│  🔐 CHANGE PASSWORD                     │
│  Current Password                       │
│  [••••••••••••] [👁️ Show]               │
│                                         │
│  New Password                           │
│  [••••••••••••] [👁️ Show]               │
│                                         │
│  Confirm New Password                   │
│  [••••••••••••] [👁️ Show]               │
│                                         │
│  [Update Password]                      │
│                                         │
│  [Logout]                               │
│                                         │
│  ⚠️ DANGER ZONE                         │
│  Delete Account                         │
│  [Delete Account]                       │
└─────────────────────────────────────────┘
```

**Container**: `max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-6`
**Profile Card**: `bg-white rounded-xl shadow-sm border border-[#E0E0E0] p-6 mb-6`
**Avatar**: `w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center border-2 border-[#FF6B35]`
**Password Fields**: `w-full px-4 py-2 border border-[#E0E0E0] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#FF6B35] pr-10`
**Password Toggle**: `absolute right-3 top-1/2 -translate-y-1/2 text-gray-400`
**Buttons**: `w-full py-3 bg-[#FF6B35] text-white font-medium rounded-lg hover:bg-orange-600`
**Logout**: `w-full py-3 bg-white border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50`
**Danger Zone**: `bg-red-50 rounded-xl border border-red-200 p-6`

### Delete Account Modal
```
┌─────────────────────────────────────────┐
│  ⚠️ Delete Account?                     │
├─────────────────────────────────────────┤
│                                         │
│  This action cannot be undone.          │
│  This will permanently delete your      │
│  account and remove all your data.      │
│                                         │
│  Type DELETE to confirm                 │
│  [DELETE                              ] │
│                                         │
│  [Cancel] [Delete]                      │
└─────────────────────────────────────────┘
```

**Modal**: `bg-white w-full max-w-md rounded-2xl overflow-hidden shadow-xl`
**Header**: `bg-red-500 text-white p-6 text-center`
**Content**: `p-6`
**Input**: `w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500`
**Buttons**: 
- Cancel: `flex-1 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200`
- Delete: `flex-1 py-3 bg-red-500 text-white rounded-xl hover:bg-red-600`

---

## 🔔 NOTIFICATIONS PAGE (Notifications.vue)

### Notifications View
```
┌─────────────────────────────────────────┐
│  Order Updates                           │
├─────────────────────────────────────────┤
│  ┌─────────────────────────────────────┐ │
│  │ 🎉 Order #12345 is ready!           │ │
│  │ Mike's Burger • Table 5             │ │
│  │ 2 minutes ago                       │ │
│  │ [View Receipt] [Download]           │ │
│  └─────────────────────────────────────┘ │
│                                         │
│  ┌─────────────────────────────────────┐ │
│  │ 👨‍🍳 Order #12344 being prepared      │ │
│  │ Mike's Burger • Table 3             │ │
│  │ 5 minutes ago                       │ │
│  │ [View Receipt] [Download]           │ │
│  └─────────────────────────────────────┘ │
│                                         │
│  ┌─────────────────────────────────────┐ │
│  │ ✅ Order #12343 completed           │ │
│  │ Mike's Burger • Table 1             │ │
│  │ 10 minutes ago                      │ │
│  │ [View Receipt] [Download]           │ │
│  └─────────────────────────────────────┘ │
└─────────────────────────────────────────┘
```

**Container**: `max-w-lg mx-auto px-4 py-6`
**Notification Card**: `rounded-xl shadow-sm border-2 overflow-hidden transition-all`
- **Order Ready**: `bg-green-50 border-green-400`
- **Preparing**: `bg-yellow-50 border-yellow-400`
- **Completed**: `bg-gray-50 border-gray-300`
**Icon**: `w-12 h-12 rounded-xl flex items-center justify-center`
**Content**: `p-4`
**Buttons**:
- View Receipt: `flex items-center gap-1 px-3 py-1.5 bg-white text-[#1A1A1A] rounded-lg hover:bg-gray-100 text-sm border border-gray-200`
- Download: `flex items-center gap-1 px-3 py-1.5 bg-[#FF6B35] text-white rounded-lg hover:bg-orange-600 text-sm`

### Receipt Modal
```
┌─────────────────────────────────────────┐
│  🏪 Mike's Burger              [✕]      │
├─────────────────────────────────────────┤
│          Order #12345                   │
│                                         │
│          Table Number                   │
│              5                          │
│                                         │
│  Order Items                            │
│  ┌─────────────────────────────────────┐ │
│  │ 2x Mike's Special Burger    ₱400    │ │
│  │ + Extra Cheese + Bacon              │ │
│  │ 1x Crispy Fries           ₱80      │ │
│  └─────────────────────────────────────┘ │
│                                         │
│  ───────────────────────────────────    │
│              Total                      │
│            ₱530.00                      │
│                                         │
│  Thank you for your order!              │
│  Dec 26, 2025                           │
│                                         │
│  [Download Receipt] [Close]             │
└─────────────────────────────────────────┘
```

**Modal**: `bg-white w-full max-w-sm rounded-2xl overflow-hidden shadow-xl`
**Header**: `bg-[#FF6B35] text-white p-6 text-center`
**Content**: `p-5`
**Table Info**: `text-center mb-5 pb-4 border-b border-dashed border-gray-300`
**Items**: `bg-gray-50 rounded-lg p-3`
**Quantity Badge**: `w-6 h-6 bg-[#FF6B35] text-white text-xs rounded-full flex items-center justify-center font-bold`
**Buttons**: `flex gap-3`
- Download: `flex-1 py-3 bg-[#FF6B35] text-white rounded-xl hover:bg-orange-600`
- Close: `flex-1 py-3 bg-gray-100 text-[#1A1A1A] rounded-xl hover:bg-gray-200`

---

## 🎨 LAYOUT STRUCTURE

### Desktop Layout (CustomerLayout.vue)
```
┌─────────────────────────────────────────┐
│ [4Rodz Logo] 4Rodz Food Court  [🛒 Cart]│  ← Desktop Header
│ [Home] [Cart] [Notifications] [Profile] │
├─────────────────────────────────────────┤
│                                         │
│              Page Content               │
│                                         │
└─────────────────────────────────────────┘
```

**Header**: `hidden md:block bg-white shadow-sm border-b border-[#E0E0E0] sticky top-0 z-50`
**Logo**: `flex items-center gap-2`
**Nav Items**: `flex items-center gap-2 px-3 py-2 rounded-lg transition-colors`
- Active: `text-[#FF6B35] bg-orange-50`
- Inactive: `text-[#1A1A1A] hover:text-[#FF6B35]`

### Mobile Layout
```
┌─────────────────────────────────────────┐
│ [4Rodz] 4Rodz                  [🛒 Cart]│  ← Mobile Header
├─────────────────────────────────────────┤
│                                         │
│            Page Content                 │
│                                         │
│ ┌─────────────────────────────────────┐ │
│ │ [🏠] [🛒] [🔔] [👤]                  │ │  ← Bottom Navigation
│ │ Home Cart Alerts Profile            │ │
│ └─────────────────────────────────────┘ │
└─────────────────────────────────────────┘
```

**Mobile Header**: `md:hidden bg-white shadow-sm border-b border-[#E0E0E0] sticky top-0 z-50`
**Bottom Nav**: `md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-[#E0E0E0] z-50`
**Nav Items**: `flex flex-col items-center gap-1 p-2`
- Active: `text-[#FF6B35]`
- Inactive: `text-gray-500`

---

## ✅ ACTUAL FEATURES SUMMARY

### ✅ Implemented Features (From Real Code)
- [x] Home page with vendor browsing and search
- [x] Vendor menu pages with product listing
- [x] Add to cart modal with customization
- [x] Shopping cart with vendor separation
- [x] Multi-step checkout process (method → QR/details → confirmation)
- [x] Profile management with password change
- [x] Order notifications with receipt viewing
- [x] Desktop header + mobile bottom navigation
- [x] Responsive design with Tailwind CSS
- [x] Real data structures and form handling

### 🎯 Exact Implementation Details
- **Colors**: `#FF6B35` primary, `#F5F5F5` background, `#1A1A1A` text, `#E0E0E0` borders
- **Cards**: `bg-white rounded-xl shadow-sm border border-[#E0E0E0]`
- **Buttons**: `bg-[#FF6B35] text-white font-medium rounded-xl hover:bg-orange-600`
- **Inputs**: `border border-[#E0E0E0] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#FF6B35]`
- **Layout**: Desktop header + Mobile bottom nav
- **Modals**: `bg-white w-full md:w-[450px] md:rounded-xl rounded-t-xl`
- **Typography**: Instrument Sans with proper font weights

This mockup reflects the actual hardcoded customer UI with all real features and exact styling patterns from the Laravel + Inertia.js application.
