<template>
  <CustomerLayout>
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
      <div class="px-4 sm:px-6 py-6">
        <h1 class="text-2xl font-bold text-gray-900">Your Cart</h1>
        <p class="text-gray-600 mt-1">Review your items</p>
      </div>
    </div>

    <!-- Cart Content -->
    <div class="px-4 sm:px-6 py-6">
      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center min-h-[40vh]">
        <div class="text-center">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-orange-500 mx-auto mb-4"></div>
          <p class="text-gray-600">Loading cart...</p>
        </div>
      </div>

      <!-- Empty Cart (no items and no pending orders) -->
      <div v-else-if="vendorCarts.length === 0 && pendingOrders.length === 0" class="text-center py-16">
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

      <div v-else class="space-y-6">
        <!-- PENDING ORDERS Section -->
        <div
          v-for="order in pendingOrders"
          :key="'order-' + order.id"
          class="bg-white rounded-2xl shadow-sm border-2 border-amber-300 overflow-hidden"
        >
          <!-- Order Header - Pending Status -->
          <div class="bg-gradient-to-r from-amber-50 to-yellow-50 px-4 py-3 border-b border-amber-200">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-amber-500 rounded-xl flex items-center justify-center shadow-sm">
                  <span class="text-lg">⏳</span>
                </div>
                <div>
                  <h3 class="font-semibold text-gray-900">{{ order.vendor?.brand_name }}</h3>
                  <div class="flex items-center gap-2">
                    <span class="text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full font-medium animate-pulse">
                      Waiting for vendor...
                    </span>
                    <span class="text-xs text-gray-500">Order #{{ order.order_number }}</span>
                  </div>
                </div>
              </div>
              <!-- Timer/Time since order -->
              <div class="text-right">
                <span class="text-sm text-gray-500">{{ formatTimeAgo(order.created_at) }}</span>
              </div>
            </div>
          </div>

          <!-- Order Items Summary -->
          <div class="px-4 py-3 bg-gray-50 divide-y divide-gray-100">
            <div
              v-for="item in order.items"
              :key="item.id"
              class="py-2 flex justify-between text-sm"
            >
              <span class="text-gray-700">{{ item.product?.name }} × {{ item.quantity }}</span>
              <span class="font-medium">₱{{ formatPrice(item.total_price) }}</span>
            </div>
          </div>

          <!-- Order Footer - Cancel Button -->
          <div class="px-4 py-4 border-t border-amber-200 bg-amber-50/50">
            <div class="flex items-center justify-between">
              <div>
                <span class="text-sm text-gray-500">Total:</span>
                <span class="ml-2 text-lg font-bold text-gray-900">
                  ₱{{ formatPrice(order.total_amount) }}
                </span>
              </div>
              <button
                @click="handleCancelOrder(order)"
                :disabled="cancellingOrder === order.id"
                class="px-5 py-2.5 bg-red-500 hover:bg-red-600 disabled:bg-gray-300 text-white font-medium rounded-lg transition-colors flex items-center gap-2"
              >
                <svg v-if="cancellingOrder === order.id" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                {{ cancellingOrder === order.id ? 'Cancelling...' : '❌ Cancel Order' }}
              </button>
            </div>
            <p class="text-xs text-amber-600 mt-2">
              💡 You can cancel while waiting for the vendor to accept
            </p>
          </div>
        </div>

        <!-- CART Items Section -->
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
              class="p-3 sm:p-4"
            >
              <!-- Mobile: Stack layout -->
              <div class="flex gap-3">
                <!-- Product Image -->
                <div class="w-14 h-14 sm:w-16 sm:h-16 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                  <img
                    v-if="item.product.image_url"
                    :src="getImageUrl(item.product.image_url)"
                    :alt="item.product.name"
                    class="w-full h-full object-cover"
                    @error="handleImageError"
                  />
                  <div v-else class="w-full h-full flex items-center justify-center text-xl sm:text-2xl">
                    🍽️
                  </div>
                </div>

                <!-- Item Details & Actions -->
                <div class="flex-1 min-w-0">
                  <!-- Name & Price Row -->
                  <div class="flex items-start justify-between gap-2">
                    <h4 class="font-medium text-gray-900 text-sm sm:text-base line-clamp-2">{{ item.product.name }}</h4>
                    <span class="font-semibold text-orange-600 text-sm sm:text-base whitespace-nowrap">
                      ₱{{ formatPrice(item.total_price) }}
                    </span>
                  </div>

                  <!-- Quantity & Addons -->
                  <p class="text-xs sm:text-sm text-gray-500 mt-0.5">
                    ₱{{ formatPrice(item.unit_price) }} × {{ item.quantity }}
                    <span v-if="item.selected_addons && item.selected_addons.length > 0" class="text-orange-600">
                      + {{ item.selected_addons.length }} addon(s)
                    </span>
                  </p>

                  <!-- Action Buttons Row -->
                  <div class="flex items-center gap-2 mt-2">
                    <button
                      @click="handleEditItem(item)"
                      class="flex items-center gap-1 px-2.5 py-1.5 text-xs sm:text-sm text-gray-600 hover:text-orange-600 bg-gray-100 hover:bg-orange-50 rounded-lg transition-colors"
                    >
                      <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                      <span>Edit</span>
                    </button>

                    <button
                      @click="handleRemoveItem(item.id)"
                      :disabled="removingItem === item.id"
                      class="flex items-center gap-1 px-2.5 py-1.5 text-xs sm:text-sm text-gray-600 hover:text-red-600 bg-gray-100 hover:bg-red-50 rounded-lg transition-colors disabled:opacity-50"
                    >
                      <svg v-if="removingItem !== item.id" class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                      <svg v-else class="w-3.5 h-3.5 sm:w-4 sm:h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                      </svg>
                      <span>{{ removingItem === item.id ? '...' : 'Remove' }}</span>
                    </button>
                  </div>
                </div>
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
import { Link, usePage } from '@inertiajs/vue3'
import CustomerLayout from '@/layouts/customer/CustomerLayout.vue'
import EditItemModal from '@/components/customer/EditItemModal.vue'
import CheckoutModal from '@/components/customer/CheckoutModal.vue'
import { useCart } from '@/composables/useCart'
import { useToast } from '@/composables/useToast'

// Cart composable
const { vendorCarts, loading, fetchCart, removeFromCart, updateCartItem } = useCart()
const { success, error } = useToast()

// Page props
const page = usePage()

// Local state
const removingItem = ref(null)
const editingItem = ref(null)
const editingItemAddons = ref([])
const checkoutVendor = ref(null)
const pendingOrders = ref([])
const cancellingOrder = ref(null)

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

const formatTimeAgo = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  const now = new Date()
  const diffMs = now - date
  const diffMins = Math.floor(diffMs / 60000)

  if (diffMins < 1) return 'Just now'
  if (diffMins < 60) return `${diffMins}m ago`
  return `${Math.floor(diffMins / 60)}h ago`
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
    // Pass addons to updateCartItem so it can merge with existing items
    const result = await updateCartItem(updatedItem.id, updatedItem.quantity, updatedItem.addons)

    if (result.success) {
      // Check if items were merged
      if (result.message?.includes('merged')) {
        success('Items merged! Total quantity updated.')
      } else {
        success('Item updated successfully')
      }
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
  success('Order sent! Waiting for vendor to accept... ⏳')
  checkoutVendor.value = null

  // Add order to pending orders list
  if (orderData?.order) {
    pendingOrders.value.unshift(orderData.order)
  }

  // Refresh cart (cart items for this vendor should be cleared)
  await fetchCart()
}

// Cancel pending order
const handleCancelOrder = async (order) => {
  if (cancellingOrder.value) return

  cancellingOrder.value = order.id

  try {
    const response = await fetch(`/api/customer/orders/${order.id}/cancel`, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      credentials: 'include'
    })

    if (response.ok) {
      success('Order cancelled. Items restored to cart.')

      // Remove from pending orders
      pendingOrders.value = pendingOrders.value.filter(o => o.id !== order.id)

      // Refresh cart to get restored items
      await fetchCart()
    } else {
      const errorData = await response.json()
      error(errorData.message || 'Failed to cancel order')
    }
  } catch (err) {
    console.error('Error cancelling order:', err)
    error('Failed to cancel order')
  } finally {
    cancellingOrder.value = null
  }
}

// Fetch pending orders
const fetchPendingOrders = async () => {
  try {
    const response = await fetch('/api/customer/orders?status=pending', {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      credentials: 'include'
    })

    if (response.ok) {
      const data = await response.json()
      pendingOrders.value = data.orders?.data || data.orders || []
    }
  } catch (err) {
    console.error('Error fetching pending orders:', err)
  }
}

// Lifecycle
onMounted(() => {
  fetchCart()
  fetchPendingOrders()
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
