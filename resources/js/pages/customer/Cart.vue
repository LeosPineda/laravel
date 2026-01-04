<template>
  <CustomerLayout>
    <!-- Header - Mobile App Style -->
    <div class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-10">
      <div class="px-4 py-4 sm:py-6 sm:max-w-7xl sm:mx-auto sm:px-6 lg:px-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Your Cart</h1>
            <p class="text-sm sm:text-base text-gray-600">Review your items</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Cart Content - Mobile First -->
    <div class="px-3 py-4 sm:max-w-4xl sm:mx-auto sm:px-6 lg:px-8 sm:py-8">
      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center min-h-[40vh]">
        <div class="text-center">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-orange-500 mx-auto mb-4"></div>
          <p class="text-gray-600">Loading cart...</p>
        </div>
      </div>

      <!-- Empty Cart -->
      <div v-else-if="vendorCarts.length === 0" class="text-center py-16">
        <div class="text-6xl mb-4">🛒</div>
        <h2 class="text-xl font-semibold text-gray-700 mb-2">Your cart is empty</h2>
        <p class="text-gray-500 mb-6">Start browsing and add some delicious items!</p>
        <Link
          href="/customer/browse"
          class="inline-flex items-center px-6 py-3 bg-orange-500 hover:bg-orange-600 text-white font-medium rounded-lg transition-colors"
        >
          Browse Vendors
        </Link>
      </div>

      <!-- Vendor Carts -->
      <div v-else class="space-y-6">
        <div
          v-for="vendorCart in vendorCarts"
          :key="vendorCart.vendor.id"
          class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden"
        >
          <!-- Vendor Header -->
          <div class="bg-gradient-to-r from-orange-50 to-red-50 px-4 py-3 border-b border-gray-200">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center shadow-sm">
                <span class="text-lg font-bold text-orange-600">
                  {{ getVendorInitials(vendorCart.vendor.brand_name) }}
                </span>
              </div>
              <div class="flex-1">
                <h3 class="font-semibold text-gray-900">{{ vendorCart.vendor.brand_name }}</h3>
                <p class="text-sm text-gray-500">{{ vendorCart.items.length }} item(s)</p>
              </div>
            </div>
          </div>

          <!-- Cart Items -->
          <div class="divide-y divide-gray-100">
            <div
              v-for="item in vendorCart.items"
              :key="item.id"
              class="p-4 flex items-center gap-4"
            >
              <!-- Product Image -->
              <div class="w-16 h-16 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                <img
                  v-if="item.product.image_url"
                  :src="getImageUrl(item.product.image_url)"
                  :alt="item.product.name"
                  class="w-full h-full object-cover"
                  @error="handleImageError"
                />
                <div v-else class="w-full h-full flex items-center justify-center text-2xl">
                  🍽️
                </div>
              </div>

              <!-- Item Details -->
              <div class="flex-1 min-w-0">
                <h4 class="font-medium text-gray-900 truncate">{{ item.product.name }}</h4>
                <p class="text-sm text-gray-500">
                  ₱{{ formatPrice(item.unit_price) }} × {{ item.quantity }}
                </p>
                <!-- Addons -->
                <div v-if="item.selected_addons && item.selected_addons.length > 0" class="mt-1">
                  <span class="text-xs text-orange-600">
                    + {{ item.selected_addons.length }} addon(s)
                  </span>
                </div>
              </div>

              <!-- Price & Actions -->
              <div class="flex items-center gap-3">
                <span class="font-semibold text-orange-600">
                  ₱{{ formatPrice(item.total_price) }}
                </span>

                <!-- Remove Button -->
                <button
                  @click="handleRemoveItem(item.id)"
                  :disabled="removingItem === item.id"
                  class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors"
                  title="Remove"
                >
                  <svg v-if="removingItem !== item.id" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                  <svg v-else class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                  </svg>
                </button>

                <!-- Edit Button -->
                <button
                  @click="handleEditItem(item)"
                  class="p-2 text-gray-400 hover:text-orange-500 hover:bg-orange-50 rounded-lg transition-colors"
                  title="Edit"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                </button>
              </div>
            </div>
          </div>

          <!-- Vendor Footer - Checkout Button -->
          <div class="px-4 py-4 bg-gray-50 border-t border-gray-200">
            <div class="flex items-center justify-between">
              <div>
                <span class="text-sm text-gray-500">Subtotal:</span>
                <span class="ml-2 text-lg font-bold text-gray-900">
                  ₱{{ formatPrice(getVendorTotal(vendorCart)) }}
                </span>
              </div>
              <button
                @click="handleProceedToCheckout(vendorCart)"
                class="px-6 py-2.5 bg-orange-500 hover:bg-orange-600 text-white font-medium rounded-lg transition-colors"
              >
                Proceed to Checkout
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Item Modal -->
    <EditItemModal
      v-if="editingItem"
      :item="editingItem"
      :is-open="!!editingItem"
      :available-addons="editingItemAddons"
      @close="editingItem = null"
      @save="handleSaveItem"
    />

    <!-- Checkout Modal -->
    <CheckoutModal
      v-if="checkoutVendor"
      :vendor-cart="checkoutVendor"
      :is-open="!!checkoutVendor"
      @close="checkoutVendor = null"
      @complete="handleOrderComplete"
    />
  </CustomerLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import CustomerLayout from '@/layouts/customer/CustomerLayout.vue'
import EditItemModal from '@/components/customer/EditItemModal.vue'
import CheckoutModal from '@/components/customer/CheckoutModal.vue'
import { useCart } from '@/composables/useCart'
import { useToast } from '@/composables/useToast'

// Cart composable
const { vendorCarts, loading, fetchCart, removeFromCart, updateCartItem } = useCart()
const { success, error } = useToast()

// Local state
const removingItem = ref(null)
const editingItem = ref(null)
const editingItemAddons = ref([])
const checkoutVendor = ref(null)

// Methods
const getVendorInitials = (name) => {
  if (!name) return '?'
  return name
    .split(' ')
    .map(word => word.charAt(0).toUpperCase())
    .join('')
    .substring(0, 2)
}

const formatPrice = (price) => {
  const num = typeof price === 'string' ? parseFloat(price) : price
  return num ? num.toFixed(2) : '0.00'
}

const getImageUrl = (imageUrl) => {
  if (!imageUrl) return ''
  if (imageUrl.startsWith('http://') || imageUrl.startsWith('https://')) {
    return imageUrl
  }
  if (imageUrl.startsWith('storage/')) {
    return `/${imageUrl}`
  }
  return `/storage/${imageUrl}`
}

const handleImageError = (event) => {
  event.target.style.display = 'none'
}

const getVendorTotal = (vendorCart) => {
  return vendorCart.items.reduce((total, item) => {
    return total + (item.total_price || item.unit_price * item.quantity)
  }, 0)
}

const handleRemoveItem = async (itemId) => {
  if (removingItem.value) return

  removingItem.value = itemId
  try {
    const result = await removeFromCart(itemId)
    if (result.success) {
      success('Item removed from cart')
    } else {
      error(result.message || 'Failed to remove item')
    }
  } catch (err) {
    console.error('Error removing item:', err)
    error('Failed to remove item')
  } finally {
    removingItem.value = null
  }
}

const handleEditItem = async (item) => {
  // Fetch available addons for this product
  try {
    const response = await fetch(`/api/customer/menu/products/${item.product_id}`)
    if (response.ok) {
      const data = await response.json()
      editingItemAddons.value = data.product?.addons || []
    } else {
      editingItemAddons.value = []
    }
  } catch (err) {
    console.error('Error fetching product addons:', err)
    editingItemAddons.value = []
  }

  editingItem.value = item
}

const handleSaveItem = async (updatedItem) => {
  try {
    // For now, we only update quantity (backend doesn't support addon updates yet)
    const result = await updateCartItem(updatedItem.id, updatedItem.quantity)

    if (result.success) {
      success('Item updated successfully')
      editingItem.value = null
    } else {
      error(result.message || 'Failed to update item')
    }
  } catch (err) {
    console.error('Error updating item:', err)
    error('Failed to update item')
  }
}

const handleProceedToCheckout = (vendorCart) => {
  checkoutVendor.value = vendorCart
}

const handleOrderComplete = async (orderData) => {
  success('Order placed successfully! 🎉')
  checkoutVendor.value = null

  // Refresh cart to remove ordered items
  await fetchCart()
}

// Lifecycle
onMounted(() => {
  fetchCart()
})
</script>

<style scoped>
/* Smooth transitions */
* {
  transition-property: color, background-color, border-color, opacity, box-shadow, transform;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}
</style>
