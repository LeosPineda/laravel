# Product Modal Fixes Plan

## Current Problems Identified

### 1. Product Modal Size Issue
- **Problem**: Product modal is too big
- **Current**: `h-[85vh] lg:h-[80vh] lg:max-w-4xl` in ProductModalContainer.vue
- **Solution**: Reduce height and max-width

### 2. Out of Stock Products Display
- **Problem**: Out of stock products are shown, occupying space
- **Current**: All products are displayed regardless of stock
- **Solution**: Filter out products with `stock_quantity <= 0`

### 3. Quantity Input Interface
- **Problem**: Input field has default browser spinner controls (up/down arrows), need to remove them
- **Current**: Has +/- buttons AND input field with browser spinners
- **Solution**: Remove browser spinner controls using CSS, keep custom +/- buttons and manual input

### 4. Image Preview Modal Behavior
- **Problem**: Clicking X or empty space in image preview returns to vendor boxes instead of product modal
- **Current**: `closeImagePreview` only sets `showImagePreview.value = false`
- **Solution**: Ensure proper event handling to stay in product modal context

### 5. Vendor Box Image Click Behavior
- **Problem**: Clicking vendor box images should always trigger "order now" (direct to product details)
- **Current**: Entire ProductBox component (line 4) has `@click="handleViewDetails"`, so all clicks trigger "view-details" instead of "order-now"
- **Solution**: Modify ProductBox component so image clicks trigger "order-now" event

## Implementation Plan

### Phase 1: Modal Size and Stock Filtering
1. Reduce ProductModalContainer modal size
2. Add stock filtering to filteredProducts computed property

### Phase 2: Image Preview Modal Fix
1. Fix closeImagePreview event handling
2. Ensure proper modal stack behavior

### Phase 3: Vendor Box Image Behavior
1. Modify ProductBox component to always use "order-now"
2. Test the complete flow

### Phase 4: Quantity Interface Review
1. Review current quantity input implementation
2. Ensure both +/- buttons and manual input work correctly

## Expected Files to Modify

1. `resources/js/components/customer/ProductModalContainer.vue`
   - Reduce modal size
   - Add stock filtering

2. `resources/js/components/customer/ProductDetailModal.vue`
   - Fix image preview modal behavior

3. `resources/js/components/customer/ProductBox.vue`
   - Modify image click behavior

## Success Criteria

- Product modal is smaller and more manageable
- Out of stock products are hidden
- Image preview closes properly to product modal
- Vendor box images always trigger order now
- Quantity input has both +/- buttons AND manual input capability
