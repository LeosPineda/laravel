<template>
  <div
    v-if="show"
    class="fixed inset-0 bg-black bg-opacity-50 z-50"
    @click="$emit('close')"
  >
    <div
      class="fixed right-0 top-0 h-full w-96 bg-white shadow-lg z-50 flex flex-col"
      @click.stop
    >
      <!-- Header -->
      <div class="flex items-center justify-between p-6 border-b border-gray-200">
        <h2 class="text-xl font-bold">Shopping Cart</h2>
        <button
          @click="$emit('close')"
          class="text-gray-500 hover:text-gray-700 p-2 rounded-lg hover:bg-gray-100"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Cart Content -->
      <div class="flex-1 overflow-y-auto">
        <!-- Loading State -->
        <div v-if="loading" class="p-6">
          <div class="space-y-4">
            <div
              v-for="n in 3"
              :key="n"
              class="flex items-center gap-4 animate-pulse"
            >
              <div class="w-16 h-16 bg-gray-200 rounded-lg"></div>
              <div class="flex-1">
                <div class="h-4 bg-gray-200 rounded mb-2"></div>
                <div class="h-3 bg-gray-200 rounded w-3/4"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty Cart -->
        <div v-else-if="cart.length === 0" class="flex flex-col items-center justify-center h-full p-6">
          <div class="text-6xl mb-4">🛒</div>
          <h3 class="text-lg font-semibold text-gray-900 mb-2">Your cart is empty</h3>
          <p class="text-gray-600 text-center">Add some delicious food to get started!</p>
        </div>

        <!-- Cart Items -->
        <div v-else>
          <!-- Group by Vendor -->
          <div
            v-for="vendorGroup in cartByVendor"
            :key="vendorGroup.vendor.id"
            class="border-b border-gray-200 last:border-b-0"
          >
            <!-- Vendor Header -->
            <div class="p-4 bg-gray-50 border-b border-gray-200">
              <div class="flex items-center justify-between">
                <h3 class="font-semibold text-gray-900">{{ vendorGroup.vendor.brand_name }}</h3>
                <button
                  @click="clearVendorCart(vendorGroup.vendor.id)"
                  class="text-sm text-red-600 hover:text-red-700 font-medium"
                >
                  Clear
                </button>
              </div>
            </div>

            <!-- Vendor Items -->
            <div>
              <CartItem
                v-for="item in vendorGroup.items"
                :key="item.id"
                :cart-item="item"
                @update-quantity="updateQuantity"
                @remove-item="removeItem"
              />
            </div>

            <!-- Vendor Total -->
            <div class="p-4 bg-gray-50">
              <div class="flex justify-between items-center">
                <span class="font-medium text-gray-900">{{ vendorGroup.vendor.brand_name }} Total:</span>
                <span class="font-bold text-orange-600">₱{{ vendorGroup.total.toFixed(2) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div v-if="cart.length > 0" class="border-t border-gray-200 p-6">
        <!-- Cart Summary -->
        <div class="space-y-2 mb-4">
          <div class="flex justify-between text-sm">
            <span class="text-gray-600">Items ({{ cartCount }})</span>
            <span class="font-medium">₱{{ cartTotal.toFixed(2) }}</span>
          </div>
          <div class="flex justify-between text-sm">
            <span class="text-gray-600">Delivery Fee</span>
            <span class="font-medium text-green-600">Free</span>
          </div>
          <div class="flex justify-between font-bold text-lg border-t pt-2">
            <span>Total</span>
            <span class="text-orange-600">₱{{ cartTotal.toFixed(2) }}</span>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="space-y-2">
          <button
            @click="proceedToCheckout"
            class="w-full py-3 bg-orange-500 text-white font-semibold rounded-lg hover:bg-orange-600 transition-colors"
            :disabled="isProcessing"
          >
            <span v-if="isProcessing" class="flex items-center justify-center gap-2">
              <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
              </svg>
              Processing...
            </span>
            <span v-else>Proceed to Checkout</span>
          </button>

          <button
            @click="clearAllCart"
            class="w-full py-2 text-red-600 font-medium hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors"
          >
            Clear All Cart
          </button>
        </div>

        <!-- Multi-vendor Notice -->
        <div v-if="cartByVendor.length > 1" class="mt-4 p-3 bg-blue-50 rounded-lg">
          <div class="flex items-start gap-2">
            <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="text-sm text-blue-800">
              <p class="font-medium">Multi-vendor Order</p>
              <p>You'll need to pay each vendor separately. Orders will be sent to each vendor individually.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import CartItem from './CartItem.vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  cart: {
    type: Array,
    default: () => []
  },
  cartByVendor: {
    type: Array,
    default: () => []
  },
  cartTotal: {
    type: Number,
    default: 0
  },
  cartCount: {
    type: Number,
    default: 0
  },
  loading: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['close', 'update-quantity', 'remove-item', 'clear-vendor', 'clear-all', 'checkout'])

// Local state
const isProcessing = ref(false)

// Event handlers
const updateQuantity = async (cartItemId, quantity) => {
  return await emit('update-quantity', cartItemId, quantity)
}

const removeItem = async (cartItemId) => {
  return await emit('remove-item', cartItemId)
}

const clearVendorCart = async (vendorId) => {
  if (confirm('Remove all items from this vendor?')) {
    await emit('clear-vendor', vendorId)
  }
}

const clearAllCart = async () => {
  if (confirm('Remove all items from your cart?')) {
    await emit('clear-all')
  }
}

const proceedToCheckout = async () => {
  isProcessing.value = true

  try {
    await emit('checkout')
  } finally {
    isProcessing.value = false
  }
}
</script>

<style scoped>
/* No additional styles needed - using Tailwind CSS classes */
</style>
