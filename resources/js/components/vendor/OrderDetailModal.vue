<template>
  <Teleport to="body">
    <div
      v-if="isOpen"
      class="fixed inset-0 z-50 overflow-y-auto"
      aria-labelledby="modal-title"
      role="dialog"
      aria-modal="true"
    >
      <!-- Backdrop -->
      <div class="fixed inset-0 bg-black/50" @click="close"></div>

      <!-- Modal Panel -->
      <div class="flex min-h-full items-center justify-center p-4">
        <div
          class="relative w-full max-w-2xl transform overflow-hidden rounded-lg bg-white shadow-xl"
          @click.stop
        >
          <!-- Loading State -->
          <div v-if="loading" class="p-8 text-center">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-orange-500 mx-auto"></div>
            <p class="text-gray-500 mt-4">Loading order details...</p>
          </div>

          <!-- Error State -->
          <div v-else-if="error" class="p-8 text-center">
            <div class="text-red-500 text-4xl mb-4">⚠</div>
            <p class="text-red-600 mb-4">{{ error }}</p>
            <button @click="close" class="px-4 py-2 bg-gray-100 rounded-lg hover:bg-gray-200">
              Close
            </button>
          </div>

          <!-- Order Details -->
          <template v-else-if="order">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200">
              <div class="flex items-center justify-between">
                <div>
                  <h3 class="text-xl font-semibold text-gray-900">Order #{{ order.order_number }}</h3>
                  <p class="text-sm text-gray-500 mt-1">
                    {{ formatDateTime(order.created_at) }}
                  </p>
                </div>
                <div class="flex items-center gap-3">
                  <span
                    :class="[
                      'px-3 py-1 rounded-full text-sm font-medium',
                      statusBadge[order.status]
                    ]"
                  >
                    {{ statusLabels[order.status] }}
                  </span>
                  <button @click="close" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>

            <!-- Content -->
            <div class="max-h-[70vh] overflow-y-auto">
              <!-- Order Information -->
              <div class="px-6 py-4 border-b border-gray-100">
                <div class="grid grid-cols-2 gap-4 text-sm">
                  <div>
                    <span class="font-medium text-gray-700">Customer:</span>
                    <p class="text-gray-900">{{ order.customer?.name || 'N/A' }}</p>
                  </div>
                  <div v-if="order.table_number">
                    <span class="font-medium text-gray-700">Table Number:</span>
                    <p class="text-gray-900">{{ order.table_number }}</p>
                  </div>
                  <div>
                    <span class="font-medium text-gray-700">Payment Method:</span>
                    <p class="text-gray-900">{{ paymentMethodLabels[order.payment_method] }}</p>
                  </div>
                  <div>
                    <span class="font-medium text-gray-700">Total Amount:</span>
                    <p class="text-gray-900 font-semibold">₱{{ parseFloat(order.total_amount).toFixed(2) }}</p>
                  </div>
                </div>
              </div>

              <!-- Special Instructions -->
              <div v-if="order.special_instructions" class="px-6 py-4 border-b border-gray-100">
                <h4 class="font-medium text-gray-700 mb-2">Special Instructions</h4>
                <p class="text-gray-900 bg-gray-50 p-3 rounded-lg">{{ order.special_instructions }}</p>
              </div>

              <!-- Order Items -->
              <div class="px-6 py-4 border-b border-gray-100">
                <h4 class="font-medium text-gray-700 mb-4">Order Items ({{ order.items?.length || 0 }})</h4>
                <div class="space-y-4">
                  <div
                    v-for="item in order.items"
                    :key="item.id"
                    class="border border-gray-200 rounded-lg p-4"
                  >
                    <div class="flex items-start justify-between">
                      <div class="flex-1">
                        <h5 class="font-medium text-gray-900">{{ item.product?.name || 'Product' }}</h5>
                        <p class="text-sm text-gray-600 mt-1">
                          {{ item.quantity }} × ₱{{ parseFloat(item.unit_price).toFixed(2) }}
                        </p>

                        <!-- Addons Section -->
                        <div v-if="item.selected_addons && item.selected_addons.length" class="mt-3">
                          <p class="text-sm font-medium text-gray-700 mb-2">Add-ons:</p>
                          <div class="space-y-2">
                            <div
                              v-for="addon in item.selected_addons"
                              :key="addon.name"
                              class="flex justify-between items-center text-sm"
                            >
                              <span class="text-gray-600">{{ addon.name }}</span>
                              <div class="text-right">
                                <span class="text-gray-500">₱{{ parseFloat(addon.price).toFixed(2) }} × {{ item.quantity }}</span>
                                <span class="font-medium text-gray-900 ml-2">₱{{ (parseFloat(addon.price) * item.quantity).toFixed(2) }}</span>
                              </div>
                            </div>
                          </div>
                          <div class="mt-2 pt-2 border-t border-gray-200">
                            <div class="flex justify-between items-center text-sm font-medium">
                              <span class="text-gray-700">Add-on subtotal:</span>
                              <span class="text-gray-900">₱{{ getAddonSubtotal(item).toFixed(2) }}</span>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="text-right ml-4">
                        <p class="font-semibold text-gray-900">₱{{ parseFloat(item.total_price).toFixed(2) }}</p>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Order Total Summary -->
                <div v-if="order.items?.length" class="mt-6 p-4 bg-gray-50 rounded-lg">
                  <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                      <span class="text-gray-600">Items total:</span>
                      <span class="text-gray-900">₱{{ getItemsTotal().toFixed(2) }}</span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-gray-600">Add-ons total:</span>
                      <span class="text-gray-900">₱{{ getAddonsTotal().toFixed(2) }}</span>
                    </div>
                    <div class="flex justify-between font-semibold text-lg border-t border-gray-200 pt-2">
                      <span class="text-gray-900">Total:</span>
                      <span class="text-gray-900">₱{{ parseFloat(order.total_amount).toFixed(2) }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Payment Proof (only for QR code payments) -->
              <div v-if="order.payment_method === 'qr_code'" class="px-6 py-4 border-b border-gray-100">
                <h4 class="font-medium text-gray-700 mb-3">Payment Proof</h4>
                <div v-if="order.payment_proof_url" class="border border-gray-200 rounded-lg overflow-hidden">
                  <img
                    :src="getImageUrl(order.payment_proof_url)"
                    alt="Payment Proof"
                    class="w-full h-48 object-cover cursor-pointer hover:opacity-90"
                    @click="showPaymentProof = true"
                  />
                  <div class="p-3 bg-gray-50 text-center">
                    <button @click="showPaymentProof = true" class="text-sm text-orange-600 hover:text-orange-700">
                      View Full Size
                    </button>
                  </div>
                </div>
                <div v-else class="text-gray-500 text-sm">
                  No payment proof uploaded
                </div>
              </div>

              <!-- Completion Info (only for completed orders) -->
              <div v-if="order.completed_at" class="px-6 py-4 border-b border-gray-100">
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                  <h4 class="font-medium text-green-800 mb-1">Order Completed</h4>
                  <p class="text-sm text-green-700">
                    Completed at: {{ formatDateTime(order.completed_at) }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Footer Actions -->
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
              <!-- Pending Orders -->
              <div v-if="order.status === 'pending'" class="space-y-3">
                <div class="flex gap-3">
                  <button
                    @click="$emit('decline', order)"
                    :disabled="processing"
                    class="flex-1 px-4 py-2 border border-red-300 text-red-700 rounded-lg hover:bg-red-50 disabled:opacity-50"
                  >
                    Decline Order
                  </button>
                  <button
                    @click="$emit('accept', order)"
                    :disabled="processing"
                    class="flex-1 px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 disabled:opacity-50"
                  >
                    {{ processing ? 'Processing...' : 'Accept Order' }}
                  </button>
                </div>
              </div>

              <!-- Accepted Orders -->
              <div v-else-if="order.status === 'accepted'" class="space-y-3">
                <div class="flex gap-3">
                  <button
                    @click="close"
                    class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50"
                  >
                    Close
                  </button>
                  <button
                    @click="$emit('markReady', order)"
                    :disabled="processing"
                    class="flex-1 px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 disabled:opacity-50"
                  >
                    {{ processing ? 'Processing...' : 'Mark Ready for Pickup' }}
                  </button>
                </div>
              </div>

              <!-- Completed Orders -->
              <div v-else-if="order.status === 'ready_for_pickup'" class="text-center">
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                  <p class="text-green-800 font-medium">Order Ready for Pickup</p>
                  <p class="text-sm text-green-700 mt-1">Customer has been notified</p>
                </div>
                <button
                  @click="close"
                  class="w-full mt-3 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50"
                >
                  Close
                </button>
              </div>

              <!-- Cancelled Orders -->
              <div v-else-if="order.status === 'cancelled'" class="text-center">
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                  <p class="text-red-800 font-medium">Order Cancelled</p>
                  <p class="text-sm text-red-700 mt-1">This order will not be processed</p>
                </div>
                <button
                  @click="close"
                  class="w-full mt-3 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50"
                >
                  Close
                </button>
              </div>

              <!-- Default -->
              <button
                v-else
                @click="close"
                class="w-full px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50"
              >
                Close
              </button>
            </div>
          </template>
        </div>
      </div>
    </div>

    <!-- Payment Proof Lightbox -->
    <div
      v-if="showPaymentProof && order?.payment_proof_url"
      class="fixed inset-0 z-[60] bg-black/75 flex items-center justify-center p-4"
      @click="showPaymentProof = false"
    >
      <img
        :src="getImageUrl(order.payment_proof_url)"
        alt="Payment Proof"
        class="max-w-full max-h-full object-contain rounded-lg"
        @click.stop
      />
      <button
        @click="showPaymentProof = false"
        class="absolute top-4 right-4 text-white text-2xl hover:text-gray-300 w-10 h-10 bg-black/50 rounded-full flex items-center justify-center"
      >
        ×
      </button>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  isOpen: { type: Boolean, default: false },
  orderId: { type: [Number, String], default: null },
  processing: { type: Boolean, default: false }
})

const emit = defineEmits(['close', 'accept', 'decline', 'markReady'])

const order = ref(null)
const loading = ref(false)
const error = ref('')
const showPaymentProof = ref(false)

// Status styling and labels
const statusBadge = {
  pending: 'bg-yellow-100 text-yellow-800',
  accepted: 'bg-blue-100 text-blue-800',
  ready_for_pickup: 'bg-green-100 text-green-800',
  cancelled: 'bg-red-100 text-red-800'
}

const statusLabels = {
  pending: 'Pending',
  accepted: 'Accepted',
  ready_for_pickup: 'Ready for Pickup',
  cancelled: 'Cancelled'
}

const paymentMethodLabels = {
  qr_code: 'QR Code',
  cash: 'Cash'
}

// Calculate addon subtotal for an order item (multiplied by quantity)
const getAddonSubtotal = (item) => {
  if (!item.selected_addons || !item.selected_addons.length) return 0
  const totalAddonPrice = item.selected_addons.reduce((sum, addon) => {
    return sum + parseFloat(addon.price || 0)
  }, 0)
  return totalAddonPrice * item.quantity
}

// Calculate total for all addons across all items
const getAddonsTotal = () => {
  if (!order.value?.items) return 0
  return order.value.items.reduce((total, item) => {
    return total + getAddonSubtotal(item)
  }, 0)
}

// Calculate total for all items (base prices only)
const getItemsTotal = () => {
  if (!order.value?.items) return 0
  return order.value.items.reduce((total, item) => {
    const basePrice = parseFloat(item.unit_price || 0) * item.quantity
    return total + basePrice
  }, 0)
}

const formatDateTime = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleString('en-PH', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getImageUrl = (url) => {
  if (!url) return null
  if (url.startsWith('http')) return url
  return `/storage/${url}`
}

const loadOrder = async () => {
  if (!props.orderId) return

  loading.value = true
  error.value = ''
  order.value = null

  try {
    const response = await fetch(`/api/vendor/orders/${props.orderId}`, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json'
      }
    })

    if (response.ok) {
      const data = await response.json()
      order.value = data.order
    } else {
      const errorData = await response.json()
      error.value = errorData.error || 'Failed to load order details'
    }
  } catch (e) {
    console.error('Error loading order:', e)
    error.value = 'Failed to load order details'
  } finally {
    loading.value = false
  }
}

const close = () => {
  showPaymentProof.value = false
  emit('close')
}

watch(() => props.orderId, (newId) => {
  if (newId && props.isOpen) loadOrder()
})

watch(() => props.isOpen, (isOpen) => {
  if (isOpen && props.orderId) {
    loadOrder()
  } else {
    order.value = null
    error.value = ''
    showPaymentProof.value = false
  }
})
</script>
