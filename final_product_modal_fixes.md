# Final Product Modal Fixes Plan

## Issues Identified & Solutions

### 1. Product Modal Size
**Problem**: Modal is too large
**Solution**: Reduce height from `85vh/80vh` to `70vh` and max-width from `4xl` to `3xl`

### 2. Out of Stock Products
**Problem**: Out of stock products occupy space in product grid
**Solution**: Filter out products with `stock_quantity <= 0` in `filteredProducts`

### 3. Browser Spinner Controls
**Problem**: Number input field shows browser spinner controls (up/down arrows)
**Solution**: Add CSS to hide spinners while keeping manual typing capability:
```css
input[type="number"]::-webkit-outer-spin-button,
input[type="number"]::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
```

### 4. Image Preview Modal Behavior
**Problem**: Clicking X/empty space returns to vendor boxes instead of product modal
**Solution**: Fix event handling in `closeImagePreview` method

### 5. Vendor Box Image Clicks
**Problem**: All clicks on product box trigger "view details" instead of "order now"
**Solution**: Move click handler to product info section only, keep image for "order now"

## Implementation Order

1. **Modal Size & Stock Filtering** (ProductModalContainer.vue)
2. **Remove Spinner Controls** (ProductDetailModal.vue)
3. **Vendor Box Click Behavior** (ProductBox.vue)
4. **Image Preview Fix** (ProductDetailModal.vue)
5. **Testing**

## Expected Result

- Smaller, more manageable product modal
- Clean quantity input with only custom +/- buttons and manual typing
- Image clicks go directly to order (product details)
- Info clicks go to view details
- Proper modal navigation flow
- No out-of-stock products displayed

## Files to Modify

1. `resources/js/components/customer/ProductModalContainer.vue`
2. `resources/js/components/customer/ProductDetailModal.vue`
3. `resources/js/components/customer/ProductBox.vue`

Ready for implementation approval.
