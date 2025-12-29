<template>
  <CustomerLayout>
    <div class="min-h-screen bg-white">
      <!-- Header -->
      <div class="bg-white border-b border-gray-200 px-4 py-6">
        <div class="max-w-7xl mx-auto">
          <div class="flex items-center justify-between">
            <div>
              <h1 class="text-2xl font-bold text-gray-900">Shopping Cart</h1>
              <p class="text-gray-600 mt-1">
                {{ cartCount }} items • {{ cartByVendor.length }} vendor{{ cartByVendor.length !== 1 ? 's' : '' }}
              </p>
            </div>

            <!-- Table Number Input -->
            <div class="flex items-center gap-2">
              <label class="text-sm font-medium text-gray-700">Table:</label>
              <input
                v-model="tableNumber"
                type="text"
                placeholder="Enter table number"
                class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500 w-24"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Content -->
      <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Loading State -->
        <div v-if="loading" class="space-y-6">
          <div
            v-for="n in 3"
            :key="n"
            class="bg-white border border-gray-200 rounded-lg p-6 animate-pulse"
          >
            <div class="h-6 bg-gray-200 rounded mb-4 w-1/3"></div>
            <div class="space-y-4">
              <div
                v-for="m in 2"
                :key="m"
                class="flex items-center gap-4"
              >
                <div class="w-16 h-16 bg-gray-200 rounded-lg"></div>
                <div class="flex-1">
                  <div class="h-4 bg-gray-200 rounded mb-2"></div>
                  <div class="h-3 bg-gray-200 rounded w-2/3"></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty Cart -->
        <div v-else-if="cart.length === 0" class="text-center py-16">
          <div class="text-6xl mb-4">🛒</div>
          <h3 class="text-xl font-semibold text-gray-900 mb-2">Your cart is empty</h3>
          <p class="text-gray-600 mb-6">Add some delicious food to get started!</p>
          <Link
            href="/customer/menu"
            class="inline-flex items-center px-4 py-2 bg-orange-500 text-white font-medium rounded-lg hover:bg-orange-600 transition-colors"
          >
            <span class="mr-2">🏪</span>Browse Vendors
          </Link>
        </div>

        <!-- Cart Content -->
        <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <!-- Cart Items -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Vendor Sections -->
            <div
              v-for="vendorGroup in cartByVendor"
              :key="vendorGroup.vendor.id"
              class="bg-white border border-gray-200 rounded-lg overflow-hidden"
            >
              <!-- Vendor Header -->
              <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                      <span class="text-orange-600 font-medium">{{ vendorGroup.vendor.brand_name.charAt(0) }}</span>
                    </div>
                    <div>
                      <h3 class="font-semibold text-gray-900">{{ vendorGroup.vendor.brand_name }}</h3>
                      <p class="text-sm text-gray-600">{{ vendorGroup.items.length }} item{{ vendorGroup.items.length !== 1 ? 's' : '' }}</p>
                    </div>
                  </div>
                  <button
                    @click="clearVendorCart(vendorGroup.vendor.id)"
                    class="text-sm text-red-600 hover:text-red-700 font-medium"
                  >
                    Clear All
                  </button>
                </div>
              </div>

              <!-- Vendor Items -->
              <div class="p-6">
                <div class="space-y-6">
                  <div
                    v-for="item in vendorGroup.items"
                    :key="item.id"
                    class="border border-gray-200 rounded-lg p-4"
                  >
                    <!-- Item Header -->
                    <div class="flex items-start gap-4">
                      <!-- Product Image -->
                      <div class="w-16 h-16 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                        <img
                          v-if="item.product.image_url"
                          :src="item.product.image_url"
                          :alt="item.product.name"
                          class="w-full h-full object-cover"
                        />
                        <div v-else class="w-full h-full flex items-center justify-center">
                          <span class="text-lg">🍽️</span>
                        </div>
                      </div>

                      <!-- Product Details -->
                      <div class="flex-1 min-w-0">
                        <h4 class="font-semibold text-gray-900">{{ item.product.name }}</h4>
                        <p class="text-orange-600 font-semibold">₱{{ item.product.price }}</p>

                        <!-- Quantity Controls -->
                        <div class="flex items-center gap-2 mt-2">
                          <button
                            @click="updateQuantity(item.id, item.quantity - 1)"
                            class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center transition-colors"
                            :disabled="item.quantity <= 1"
                          >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                            </svg>
                          </button>
                          <span class="w-8 text-center font-medium">{{ item.quantity }}</span>
                          <button
                            @click="updateQuantity(item.id, item.quantity + 1)"
                            class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center transition-colors"
                          >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                          </button>
                        </div>
                      </div>

                      <!-- Item Total -->
                      <div class="text-right">
                        <p class="font-semibold text-gray-900">₱{{ (item.quantity * item.product.price).toFixed(2) }}</p>
                        <button
                          @click="removeItem(item.id)"
                          class="text-xs text-red-600 hover:text-red-700 mt-2"
                        >
                          Remove
                        </button>
                      </div>
                    </div>

                    <!-- Edit Order Button -->
                    <div class="mt-4 pt-4 border-t border-gray-200">
                      <button
                        @click="editOrder(item)"
                        class="text-sm text-orange-600 hover:text-orange-700 font-medium flex items-center gap-1"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Edit Order
                      </button>
                    </div>
                  </div>
                </div>

                <!-- Vendor Total -->
                <div class="mt-6 pt-4 border-t border-gray-200">
                  <div class="flex justify-between items-center">
                    <span class="font-medium text-gray-900">{{ vendorGroup.vendor.brand_name }} Total:</span>
                    <span class="font-bold text-orange-600 text-lg">₱{{ vendorGroup.total.toFixed(2) }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Order Summary -->
          <div class="lg:col-span-1">
            <div class="sticky top-6">
              <div class="bg-white border border-gray-200 rounded-lg p-6">
                <h3 class="font-semibold text-gray-900 mb-4">Order Summary</h3>

                <!-- Table Number Display -->
                <div v-if="tableNumber" class="mb-4 p-3 bg-orange-50 rounded-lg">
                  <div class="flex items-center gap-2">
                    <span class="text-sm font-medium text-orange-700">Table: {{ tableNumber }}</span>
                  </div>
                </div>

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
                <div class="space-y-3">
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

                  <Link
                    href="/customer/menu"
                    class="w-full py-2 text-center text-orange-600 font-medium hover:text-orange-700 hover:bg-orange-50 rounded-lg transition-colors border border-orange-200"
                  >
                    Continue Shopping
                  </Link>
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
        </div>
      </div>

      <!-- Order Edit Modal -->
      <OrderEditModal
        v-if="editingItem"
        :item="editingItem"
        @close="editingItem = null"
        @save="saveOrderEdit"
      />

      <!-- Payment Modal -->
      <PaymentModal
        v-if="showPaymentModal"
        :vendor-groups="cartByVendor"
        :table-number="tableNumber"
        @close="showPaymentModal = false"
        @process-payment="processPayment"
      />
    </div>
  </CustomerLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import CustomerLayout from '@/layouts/customer/CustomerLayout.vue'
import OrderEditModal from '@/components/customer/OrderEditModal.vue'
import PaymentModal from '@/components/customer/PaymentModal.vue'
import { useCart } from '@/composables/useCart'

const {
  cart,
  loading,
  cartCount,
  cartByVendor,
  cartTotal,
  fetchCart,
  updateCartItem,
  removeFromCart,
  clearCart
} = useCart()

// Local state
const tableNumber = ref('')
const isProcessing = ref(false)
const editingItem = ref(null)
const showPaymentModal = ref(false)

// Event handlers
const updateQuantity = async (cartItemId, quantity) => {
  await updateCartItem(cartItemId, quantity)
}

const removeItem = async (cartItemId) => {
  await removeFromCart(cartItemId)
}

const clearVendorCart = async (vendorId) => {
  if (confirm('Remove all items from this vendor?')) {
    await clearCart(vendorId)
  }
}

const editOrder = (item) => {
  editingItem.value = item
}

const saveOrderEdit = async (updatedItem) => {
  // TODO: Implement order editing with addons and special instructions
  console.log('Saving order edit:', updatedItem)
  editingItem.value = null
}

const proceedToCheckout = () => {
  showPaymentModal.value = true
}

const processPayment = async (paymentData) => {
  isProcessing.value = true

  try {
    // TODO: Implement payment processing
    console.log('Processing payment:', paymentData)

    // This would integrate with the payment system
    // and send order requests to vendors
  } finally {
    isProcessing.value = false
    showPaymentModal.value = false
  }
}

// Initialize
onMounted(() => {
  fetchCart()
})
</script>

<style scoped>
/* No additional styles needed - using Tailwind CSS classes */
</style>
