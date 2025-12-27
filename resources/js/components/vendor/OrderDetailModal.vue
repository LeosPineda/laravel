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
          class="relative w-full max-w-lg transform overflow-hidden rounded-2xl bg-white shadow-xl"
          @click.stop
        >
          <!-- Loading State -->
          <div v-if="loading" class="p-8 text-center">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-orange-500 mx-auto"></div>
            <p class="text-gray-500 mt-4">Loading order...</p>
          </div>

          <!-- Error State -->
          <div v-else-if="error" class="p-8 text-center">
            <div class="text-4xl mb-4">❌</div>
            <p class="text-red-600 mb-4">{{ error }}</p>
            <button @click="close" class="px-4 py-2 bg-gray-100 rounded-lg">Close</button>
          </div>

          <!-- Order Details -->
          <template v-else-if="order">
            <!-- Header -->
            <div class="bg-orange-500 text-white px-6 py-4">
              <div class="flex items-center justify-between">
                <div>
                  <h3 class="text-lg font-bold">#{{ order.order_number }}</h3>
                  <p class="text-orange-100 text-sm">Table {{ order.table_number || 'N/A' }}</p>
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
                  <button @click="close" class="text-white/80 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>

            <!-- Content -->
            <div class="max-h-[65vh] overflow-y-auto">
              <!-- Quick Info Bar -->
              <div class="flex items-center justify-between px-6 py-3 bg-gray-50 border-b text-sm">
                <span class="text-gray-600">
                  {{ formatDateTime(order.created_at) }}
                </span>
                <span class="font-medium">
                  {{ order.payment_method === 'qr_code' ? '📱 QR Payment' : '💵 Cash' }}
                </span>
              </div>

              <!-- Special Instructions (prominent if exists) -->
              <div v-if="order.special_instructions" class="mx-6 mt-4 p-3 bg-yellow-50 border-l-4 border-yellow-400 rounded-r-lg">
                <p class="text-sm font-medium text-yellow-800">⚠️ Special Instructions</p>
                <p class="text-yellow-900">{{ order.special_instructions }}</p>
              </div>

              <!-- Order Items -->
              <div class="px-6 py-4">
                <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">Items</h4>
                <div class="space-y-3">
                  <div
                    v-for="item in order.items"
                    :key="item.id"
                    class="flex items-center gap-3"
                  >
                    <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center text-lg flex-shrink-0">
                      {{ item.quantity }}x
                    </div>
                    <div class="flex-1 min-w-0">
                      <p class="font-medium text-gray-900 truncate">{{ item.product?.name || 'Product' }}</p>
                      <div v-if="item.selected_addons && item.selected_addons.length" class="text-xs text-gray-500">
                        + {{ item.selected_addons.map(a => a.name).join(', ') }}
                      </div>
                    </div>
                    <div class="text-right font-semibold text-gray-900">
                      ₱{{ parseFloat(item.total_price).toFixed(2) }}
                    </div>
                  </div>
                </div>
              </div>

              <!-- Total -->
              <div class="mx-6 mb-4 p-4 bg-orange-50 rounded-xl">
                <div class="flex justify-between items-center">
                  <span class="text-gray-700 font-medium">Total</span>
                  <span class="text-2xl font-bold text-orange-600">₱{{ parseFloat(order.total_amount).toFixed(2) }}</span>
                </div>
              </div>

              <!-- Payment Proof Section (for QR payments) -->
              <div v-if="order.payment_method === 'qr_code'" class="mx-6 mb-4">
                <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-2">Payment Proof</h4>
                <div v-if="order.payment_proof_url" class="border rounded-xl overflow-hidden">
                  <img
                    :src="getImageUrl(order.payment_proof_url)"
                    alt="Payment Proof"
                    class="w-full max-h-48 object-contain bg-gray-100 cursor-pointer"
                    @click="showPaymentProof = true"
                  />
                  <div class="p-2 bg-gray-50 text-center">
                    <button @click="showPaymentProof = true" class="text-sm text-orange-600 hover:underline">
                      View Full Image
                    </button>
                  </div>
                </div>
                <div v-else class="p-4 bg-gray-100 rounded-xl text-center text-gray-500 text-sm">
                  No payment proof uploaded
                </div>
              </div>

              <!-- Customer Info (name only, no email for privacy) -->
              <div v-if="order.customer" class="mx-6 mb-4 text-sm text-gray-500">
                Customer: <span class="font-medium text-gray-700">{{ order.customer.name }}</span>
              </div>
            </div>

            <!-- Footer Actions -->
            <div class="px-6 py-4 border-t bg-gray-50">
              <!-- Pending -->
              <div v-if="order.status === 'pending'" class="flex gap-3">
                <button
                  @click="$emit('decline', order)"
                  :disabled="processing"
                  class="flex-1 px-4 py-3 bg-white border border-red-300 text-red-600 rounded-xl hover:bg-red-50 font-medium disabled:opacity-50"
                >
                  Decline
                </button>
                <button
                  @click="$emit('accept', order)"
                  :disabled="processing"
                  class="flex-1 px-4 py-3 bg-orange-500 text-white rounded-xl hover:bg-orange-600 font-medium disabled:opacity-50"
                >
                  {{ processing ? 'Accepting...' : '✓ Accept Order' }}
                </button>
              </div>

              <!-- Accepted -->
              <div v-else-if="order.status === 'accepted'" class="flex gap-3">
                <button
                  @click="close"
                  class="flex-1 px-4 py-3 bg-white border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 font-medium"
                >
                  Close
                </button>
                <button
                  @click="$emit('markReady', order)"
                  :disabled="processing"
                  class="flex-1 px-4 py-3 bg-green-500 text-white rounded-xl hover:bg-green-600 font-medium disabled:opacity-50"
                >
                  {{ processing ? 'Processing...' : '🍳 Mark Ready' }}
                </button>
              </div>

              <!-- Others -->
              <button
                v-else
                @click="close"
                class="w-full px-4 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 font-medium"
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
      class="fixed inset-0 z-[60] bg-black/90 flex items-center justify-center p-4"
      @click="showPaymentProof = false"
    >
      <img
        :src="getImageUrl(order.payment_proof_url)"
        alt="Payment Proof"
        class="max-w-full max-h-full object-contain"
        @click.stop
      />
      <button
        @click="showPaymentProof = false"
        class="absolute top-4 right-4 text-white text-3xl hover:text-gray-300"
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

const statusBadge = {
  pending: 'bg-yellow-400 text-yellow-900',
  accepted: 'bg-blue-400 text-blue-900',
  ready_for_pickup: 'bg-green-400 text-green-900',
  cancelled: 'bg-red-400 text-red-900'
}

const statusLabels = {
  pending: 'Pending',
  accepted: 'Preparing',
  ready_for_pickup: 'Ready',
  cancelled: 'Cancelled'
}

const formatDateTime = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('en-PH', { month: 'short', day: 'numeric' }) +
    ' • ' + date.toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit' })
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
      error.value = errorData.error || 'Failed to load order'
    }
  } catch (e) {
    console.error('Error loading order:', e)
    error.value = 'Failed to load order'
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
