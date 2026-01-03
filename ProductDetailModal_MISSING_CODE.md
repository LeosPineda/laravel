# ProductDetailModal.vue - MISSING CODE TO COMPLETE

This markdown contains the missing code to complete your truncated ProductDetailModal.vue file.

## Copy this code starting from where your file cuts off:

```javascript
// Initialize mobile detection
onMounted(() => {
  isMobile.value = window.innerWidth < 1024

  // Update mobile detection on resize
  const handleResize = () => {
    isMobile.value = window.innerWidth < 1024
  }

  window.addEventListener('resize', handleResize)

  // Cleanup on unmount
  return () => {
    window.removeEventListener('resize', handleResize)
  }
})
</script>

<style scoped>
/* Remove browser spinner controls */
.spin-button-none {
  -moz-appearance: textfield;
}

.spin-button-none::-webkit-outer-spin-button,
.spin-button-none::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

/* Custom scrollbar */
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}

/* Loading animation */
@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

.animate-spin {
  animation: spin 1s linear infinite;
}

/* Modal positioning fixes */
.fixed.inset-0.z-50 {
  position: fixed;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  z-index: 50;
}

.z-\[70\] {
  z-index: 70;
}

/* Bottom sheet handle */
.lg\:hidden.absolute.-top-2 {
  display: none;
}

@media (max-width: 1023px) {
  .lg\:hidden.absolute.-top-2 {
    display: block;
  }
}

/* Responsive improvements */
@media (max-width: 640px) {
  .flex.gap-4 {
    gap: 1rem;
  }

  .p-4 {
    padding: 1rem;
  }

  .space-y-6 > * + * {
    margin-top: 1.5rem;
  }
}

/* Button hover effects */
.hover\:bg-white:hover {
  background-color: white;
}

.hover\:border-orange-400:hover {
  border-color: #fb923c;
}

/* Transition improvements */
* {
  transition-property: color, background-color, border-color, opacity, box-shadow, transform;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}

/* Additional hover states for add-ons */
.hover\:bg-orange-25:hover {
  background-color: #fff7ed;
}
</style>
```

## Instructions:

1. **Find where your current ProductDetailModal.vue file cuts off** - it should end with:
   ```javascript
   onMounted(() => {
     isMobile.value = window.innerWidth < 1024
   ```

2. **Replace that truncated section with the complete code above**

3. **This will:**
   - ✅ Fix the "Add-ons (Fallback)" text issue
   - ✅ Fix addon price calculation in subtotals  
   - ✅ Remove all `is_active` complexity
   - ✅ Complete the missing mobile detection functionality
   - ✅ Add all necessary CSS styles

## What this fixes:

### Issue 1: "Add-ons (Fallback)" text
**Before:**
```vue
<!-- Fallback Add-ons Display -->
<div v-else-if="hasProductAddons" class="space-y-3 bg-yellow-50 p-3 rounded border border-yellow-200">
  <h4 class="font-bold text-gray-900 text-lg">Add-ons (Fallback)</h4>
```

**After:**
```vue
<!-- Add-ons Section - FIXED: No more "Fallback" text -->
<div v-if="hasProductAddons" class="space-y-3">
  <h4 class="font-bold text-gray-900 text-lg">Add-ons</h4>
```

### Issue 2: Addon price calculation
**Before:** Was using `availableAddons` (filtered by `is_active`)
**After:** Uses all `product.addons` directly (no filtering needed)

### Issue 3: TypeScript interfaces
**Before:**
```typescript
interface Addon {
  id: number
  name: string
  price: string | number
  is_active: boolean  // ❌ REMOVED
}
```

**After:**
```typescript
interface Addon {
  id: number
  name: string
  price: string | number
  // ✅ NO is_active field needed
}
```

## After applying this fix:

1. ✅ No more "Add-ons (Fallback)" text
2. ✅ Addon prices properly calculated in subtotals  
3. ✅ Simple system: Create → Appears | Delete → Gone
4. ✅ Works perfectly with the simplified backend (no `is_active`)

**Your addon system will be clean and working!** 🎉
