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
      <div
        class="fixed inset-0 bg-black/50 transition-opacity"
        @click="close"
      ></div>

      <!-- Modal Panel -->
      <div class="flex min-h-full items-center justify-center p-4">
        <div
          class="relative w-full max-w-2xl transform overflow-hidden rounded-2xl bg-white shadow-xl transition-all"
          @click.stop
        >
          <!-- Loading State -->
          <div v-if="loading" class="p-8 text-center">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-orange-500 mx-auto"></div>
            <p class="text-gray-500 mt-4">Loading order details...</p>
          </div>

          <!-- Error State -->
          <div v-else-if="error" class="p-8 text-center">
            <div class="text-4xl mb-4">❌</div>
            <p class="text-red-600 mb-4">{{ error }}</p>
            <button
              @click="close"
              class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200"
            >
              Close
            </button>
          </div>

          <!-- Order Details -->
          <div v-else-if="order">
            <!-- Header -->
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <h3 class="text-xl font-bold text-gray-900">
                    Order #{{ order.order_number }}
                  </h3>
                  <span
                    :class="[
                      'px-3 py-1 rounded-full text-sm font-medium',
                      statusColors[order.status] || 'bg-gray-100 text-gray-700'
                    ]"
                  >
                    {{ statusLabels[order.status] || order.status }}
                  </span>
                </div>
                <button
                  @click="close"
                  class="text-gray-400 hover:text-gray-600"
                >
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
            </div>

            <!-- Content -->
            <div class="p-6 max-h-[60vh] overflow-y-auto">
              <!-- Order Info -->
              <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                  <p class="text-sm text-gray-500">Table Number</p>
                  <p class="font-medium text-gray-900">{{ order.table_number || 'N/A' }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Payment Method</p>
                  <p class="font-medium text-gray-900">
                    {{ order.payment_method === 'qr_code' ? '📱 QR Code' : '💵 Cashier' }}
                  </p>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Order Date</p>
                  <p class="font-medium text-gray-900">{{ formatDate(order.created_at) }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Order Time</p>
                  <p class="font-medium text-gray-900">{{ formatTime(order.created_at) }}</p>
                </div>
              </div>

              <!-- Customer Info -->
              <div v-if="order.customer" class="mb-6 p-4 bg-gray-50 rounded-lg">
                <h4 class="text-sm font-medium text-gray-700 mb-2">Customer Information</h4>
                <p class="text-gray-900">{{ order.customer.name }}</p>
                <p class="text-sm text-gray-500">{{ order.customer.email }}</p>
              </div>

              <!-- Special Instructions -->
              <div v-if="order.special_instructions" class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <h4 class="text-sm font-medium text-yellow-800 mb-1">Special Instructions</h4>
                <p class="text-yellow-900">{{ order.special_instructions }}</p>
              </div>

              <!-- Order Items -->
              <div class="mb-6">
                <h4 class="text-sm font-medium text-gray-700 mb-3">Order Items</h4>
                <div class="space-y-3">
                  <div
                    v-for="item in order.items"
                    :key="item.id"
                    class="flex items-start justify-between p-3 bg-gray-50 rounded-lg"
                  >
                    <div class="flex items-start gap-3">
                      <!-- Product Image -->
                      <div class="w-12 h-12 bg-gray-200 rounded-lg flex items-center justify-center flex-shrink-0">
                        <img
                          v-if="item.product?.image_url"
                          :src="getImageUrl(item.product.image_url)"
                          :alt="item.product?.name"
                          class="w-full h-full object-cover rounded-lg"
                        />
                        <span v-else class="text-xl">🍔</span>
                      </div>

                      <div>
                        <p class="font-medium text-gray-900">
                          {{ item.quantity }}x {{ item.product?.name || 'Unknown Product' }}
                        </p>
                        <p class="text-sm text-gray-500">₱{{ item.price }} each</p>

                        <!-- Addons -->
                        <div v-if="item.addons && item.addons.length > 0" class="mt-1">
                          <p class="text-xs text-gray-400">Add-ons:</p>
                          <div class="flex flex-wrap gap-1 mt-1">
                            <span
                              v-for="addon in item.addons"
                              :key="addon.id"
                              class="px-2 py-0.5 bg-orange-100 text-orange-700 rounded text-xs"
                            >
                              {{ addon.name }} (+₱{{ addon.price }})
                            </span>
                          </div>
                        </div>
                      </div>
                    </div>

                    <div class="text-right">
                      <p class="font-semibold text-gray-900">
                        ₱{{ (item.price * item.quantity).toFixed(2) }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Total -->
              <div class="border-t border-gray-200 pt-4">
                <div class="flex justify-between items-center">
                  <span class="text-lg font-medium text-gray-700">Total Amount</span>
                  <span class="text-2xl font-bold text-orange-600">
                    ₱{{ parseFloat(order.total_amount).toFixed(2) }}
                  </span>
                </div>
              </div>

              <!-- Payment Proof -->
              <div v-if="order.payment_proof_url" class="mt-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                <h4 class="text-sm font-medium text-green-800 mb-2">Payment Proof</h4>
                <a
                  :href="order.payment_proof_url"
                  target="_blank"
                  class="text-green-600 hover:text-green-700 underline"
                >
                  View Payment Proof
                </a>
              </div>
            </div>

            <!-- Footer Actions -->
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
              <div class="flex justify-end gap-3">
                <!-- Pending Order Actions -->
                <template v-if="order.status === 'pending'">
                  <button
                    @click="$emit('decline', order)"
                    :disabled="processing"
                    class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 disabled:opacity-50"
                  >
                    Decline
                  </button>
                  <button
                    @click="$emit('accept', order)"
                    :disabled="processing"
                    class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 disabled:opacity-50"
                  >
                    {{ processing ? 'Processing...' : 'Accept Order' }}
                  </button>
                </template>

                <!-- Accepted Order Actions -->
                <template v-else-if="order.status === 'accepted'">
                  <button
                    @click="$emit('markReady', order)"
                    :disabled="processing"
                    class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 disabled:opacity-50"
                  >
                    {{ processing ? 'Processing...' : 'Mark Ready for Pickup' }}
                  </button>
                </template>

                <!-- Close Button -->
                <button
                  @click="close"
                  class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200"
                >
                  Close
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  orderId: {
    type: [Number, String],
    default: null
  },
  processing: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['close', 'accept', 'decline', 'markReady'])

const order = ref(null)
const loading = ref(false)
const error = ref('')

const statusColors = {
  pending: 'bg-yellow-100 text-yellow-700',
  accepted: 'bg-blue-100 text-blue-700',
  ready_for_pickup: 'bg-green-100 text-green-700',
  cancelled: 'bg-red-100 text-red-700'
}

const statusLabels = {
  pending: 'Pending',
  accepted: 'Accepted',
  ready_for_pickup: 'Ready for Pickup',
  cancelled: 'Cancelled'
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('en-PH', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const formatTime = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleTimeString('en-PH', {
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
  emit('close')
}

// Watch for orderId changes
watch(
  () => props.orderId,
  (newId) => {
    if (newId && props.isOpen) {
      loadOrder()
    }
  }
)

// Watch for modal open
watch(
  () => props.isOpen,
  (isOpen) => {
    if (isOpen && props.orderId) {
      loadOrder()
    } else {
      order.value = null
      error.value = ''
    }
  }
)
</script>
