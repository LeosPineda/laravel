<template>
  <div class="bg-white">
    <!-- Tabs -->
    <div class="border-b border-gray-200 px-6">
      <div class="flex gap-6">
        <button
          @click="activeTab = 'pending'"
          :class="[
            'py-4 text-sm font-medium border-b-2 transition-colors',
            activeTab === 'pending'
              ? 'border-orange-500 text-orange-600'
              : 'border-transparent text-gray-500 hover:text-gray-700'
          ]"
        >
          🔔 Pending
          <span v-if="pendingCount > 0" class="ml-1 px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded-full text-xs">
            {{ pendingCount }}
          </span>
        </button>
        <button
          @click="activeTab = 'accepted'"
          :class="[
            'py-4 text-sm font-medium border-b-2 transition-colors',
            activeTab === 'accepted'
              ? 'border-orange-500 text-orange-600'
              : 'border-transparent text-gray-500 hover:text-gray-700'
          ]"
        >
          🍳 Preparing
          <span v-if="acceptedCount > 0" class="ml-1 px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full text-xs">
            {{ acceptedCount }}
          </span>
        </button>
      </div>
    </div>

    <!-- Content -->
    <div class="p-6">
      <div class="max-w-4xl mx-auto">
        <!-- Loading -->
        <div v-if="loading" class="text-center py-12">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-orange-500 mx-auto"></div>
          <p class="text-gray-500 mt-4">Loading orders...</p>
        </div>

        <!-- Pending Orders -->
        <div v-else-if="activeTab === 'pending'">
          <div v-if="pendingOrders.length > 0" class="space-y-4">
            <div
              v-for="order in pendingOrders"
              :key="order.id"
              class="bg-white rounded-xl border-2 border-yellow-300 p-5 hover:shadow-lg transition-shadow"
            >
              <div class="flex items-start justify-between mb-3">
                <div>
                  <div class="flex items-center gap-2">
                    <span class="text-lg font-bold text-gray-900">#{{ order.order_number }}</span>
                    <span class="px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded-full text-xs font-medium">New</span>
                  </div>
                  <p class="text-sm text-gray-500">Table {{ order.table_number || 'N/A' }} • {{ formatTime(order.created_at) }}</p>
                </div>
                <span class="text-lg font-bold text-orange-600">₱{{ parseFloat(order.total_amount).toFixed(2) }}</span>
              </div>

              <!-- Items Preview -->
              <div class="mb-3 text-sm text-gray-600">
                <span v-for="(item, idx) in order.items?.slice(0, 3)" :key="item.id">
                  {{ item.quantity }}x {{ item.product?.name }}<span v-if="idx < Math.min(order.items.length, 3) - 1">, </span>
                </span>
                <span v-if="order.items?.length > 3" class="text-gray-400">+{{ order.items.length - 3 }} more</span>
              </div>

              <!-- Special Instructions Alert -->
              <div v-if="order.special_instructions" class="mb-3 px-3 py-2 bg-yellow-50 border-l-4 border-yellow-400 text-sm text-yellow-800">
                ⚠️ {{ order.special_instructions }}
              </div>

              <!-- Actions -->
              <div class="flex gap-2">
                <button @click="openOrderDetail(order)" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm">
                  View Details
                </button>
                <button
                  @click="declineOrder(order)"
                  :disabled="processingOrder === order.id"
                  class="px-4 py-2 bg-white border border-red-300 text-red-600 rounded-lg hover:bg-red-50 text-sm disabled:opacity-50"
                >
                  Decline
                </button>
                <button
                  @click="acceptOrder(order)"
                  :disabled="processingOrder === order.id"
                  class="flex-1 px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 text-sm font-medium disabled:opacity-50"
                >
                  {{ processingOrder === order.id ? 'Accepting...' : '✓ Accept' }}
                </button>
              </div>
            </div>
          </div>

          <!-- Empty Pending -->
          <div v-else class="text-center py-12">
            <div class="text-5xl mb-4">📭</div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No pending orders</h3>
            <p class="text-gray-500">New orders will appear here</p>
          </div>
        </div>

        <!-- Accepted/Preparing Orders -->
        <div v-else-if="activeTab === 'accepted'">
          <div v-if="acceptedOrders.length > 0" class="space-y-4">
            <div
              v-for="order in acceptedOrders"
              :key="order.id"
              class="bg-white rounded-xl border-2 border-blue-300 p-5 hover:shadow-lg transition-shadow"
            >
              <div class="flex items-start justify-between mb-3">
                <div>
                  <div class="flex items-center gap-2">
                    <span class="text-lg font-bold text-gray-900">#{{ order.order_number }}</span>
                    <span class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full text-xs font-medium">Preparing</span>
                  </div>
                  <p class="text-sm text-gray-500">Table {{ order.table_number || 'N/A' }} • {{ formatTime(order.created_at) }}</p>
                </div>
                <span class="text-lg font-bold text-orange-600">₱{{ parseFloat(order.total_amount).toFixed(2) }}</span>
              </div>

              <!-- Items Preview -->
              <div class="mb-3 text-sm text-gray-600">
                <span v-for="(item, idx) in order.items?.slice(0, 3)" :key="item.id">
                  {{ item.quantity }}x {{ item.product?.name }}<span v-if="idx < Math.min(order.items.length, 3) - 1">, </span>
                </span>
                <span v-if="order.items?.length > 3" class="text-gray-400">+{{ order.items.length - 3 }} more</span>
              </div>

              <!-- Special Instructions -->
              <div v-if="order.special_instructions" class="mb-3 px-3 py-2 bg-yellow-50 border-l-4 border-yellow-400 text-sm text-yellow-800">
                ⚠️ {{ order.special_instructions }}
              </div>

              <!-- Actions -->
              <div class="flex gap-2">
                <button @click="openOrderDetail(order)" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm">
                  View Details
                </button>
                <button
                  @click="markReady(order)"
                  :disabled="processingOrder === order.id"
                  class="flex-1 px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 text-sm font-medium disabled:opacity-50"
                >
                  {{ processingOrder === order.id ? 'Processing...' : '🍳 Mark Ready for Pickup' }}
                </button>
              </div>
            </div>
          </div>

          <!-- Empty Accepted -->
          <div v-else class="text-center py-12">
            <div class="text-5xl mb-4">👨‍🍳</div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No orders being prepared</h3>
            <p class="text-gray-500">Accepted orders will appear here</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Order Detail Modal -->
    <OrderDetailModal
      :is-open="showOrderModal"
      :order-id="selectedOrderId"
      :processing="processingOrder !== null"
      @close="closeOrderModal"
      @accept="openAcceptModal"
      @decline="openDeclineModal"
      @markReady="handleMarkReady"
    />

    <!-- Accept Confirmation Modal -->
    <ConfirmModal
      :is-open="showAcceptModal"
      title="Accept Order"
      :message="`Accept order #${targetOrder?.order_number}? This will start preparing the order.`"
      confirm-text="Accept Order"
      :loading="processingOrder !== null"
      icon="✓"
      variant="warning"
      @confirm="confirmAccept"
      @cancel="showAcceptModal = false"
    />

    <!-- Decline Confirmation Modal -->
    <ConfirmModal
      :is-open="showDeclineModal"
      title="Decline Order"
      :message="`Are you sure you want to decline order #${targetOrder?.order_number}? The customer will be notified.`"
      confirm-text="Decline Order"
      :loading="processingOrder !== null"
      icon="✕"
      variant="danger"
      @confirm="confirmDecline"
      @cancel="showDeclineModal = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { usePage } from '@inertiajs/vue3'
import OrderDetailModal from '@/components/vendor/OrderDetailModal.vue'
import ConfirmModal from '@/components/ui/ConfirmModal.vue'
import { useToast } from '@/composables/useToast'

const emit = defineEmits(['ordersUpdated'])
const toast = useToast()
const page = usePage()

const vendorId = ref(null)
const allOrders = ref([])
const loading = ref(false)
const processingOrder = ref(null)
const activeTab = ref('pending')

// Order Detail Modal
const showOrderModal = ref(false)
const selectedOrderId = ref(null)

// Accept/Decline Modals
const showAcceptModal = ref(false)
const showDeclineModal = ref(false)
const targetOrder = ref(null)

// Computed
const pendingOrders = computed(() => allOrders.value.filter(o => o.status === 'pending'))
const acceptedOrders = computed(() => allOrders.value.filter(o => o.status === 'accepted'))
const pendingCount = computed(() => pendingOrders.value.length)
const acceptedCount = computed(() => acceptedOrders.value.length)

const formatTime = (dateString) => {
  if (!dateString) return ''
  return new Date(dateString).toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit' })
}

const loadOrders = async () => {
  loading.value = true
  try {
    // Load both pending and accepted
    const response = await fetch('/api/vendor/orders?per_page=50', {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json'
      }
    })

    if (response.ok) {
      const data = await response.json()
      // Filter to only show pending and accepted
      allOrders.value = (data.orders || []).filter(o =>
        o.status === 'pending' || o.status === 'accepted'
      )
    }
  } catch (error) {
    console.error('Error loading orders:', error)
  } finally {
    loading.value = false
  }
}

// Modal handlers
const openOrderDetail = (order) => {
  selectedOrderId.value = order.id
  showOrderModal.value = true
}

const closeOrderModal = () => {
  showOrderModal.value = false
  selectedOrderId.value = null
}

// Actions
const acceptOrder = async (order) => {
  processingOrder.value = order.id
  try {
    const response = await fetch(`/api/vendor/orders/${order.id}/accept`, {
      method: 'PATCH',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json'
      }
    })

    if (response.ok) {
      toast.success(`Order #${order.order_number} accepted!`)
      await loadOrders()
      // Switch to preparing tab to show the accepted order
      activeTab.value = 'accepted'
      emit('ordersUpdated')
    } else {
      const error = await response.json()
      toast.error(error.error || 'Failed to accept order')
    }
  } catch (error) {
    console.error('Error accepting order:', error)
    toast.error('Failed to accept order')
  } finally {
    processingOrder.value = null
  }
}

const declineOrder = async (order) => {
  if (!confirm(`Decline order #${order.order_number}?`)) return

  processingOrder.value = order.id
  try {
    const response = await fetch(`/api/vendor/orders/${order.id}/decline`, {
      method: 'PATCH',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json'
      }
    })

    if (response.ok) {
      toast.warning(`Order #${order.order_number} declined`)
      await loadOrders()
      emit('ordersUpdated')
    } else {
      const error = await response.json()
      toast.error(error.error || 'Failed to decline order')
    }
  } catch (error) {
    console.error('Error declining order:', error)
    toast.error('Failed to decline order')
  } finally {
    processingOrder.value = null
  }
}

const markReady = async (order) => {
  processingOrder.value = order.id
  try {
    const response = await fetch(`/api/vendor/orders/${order.id}/ready`, {
      method: 'PATCH',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json'
      }
    })

    if (response.ok) {
      toast.success(`Order #${order.order_number} is ready for pickup! 🎉`)
      await loadOrders()
      emit('ordersUpdated')
    } else {
      const error = await response.json()
      toast.error(error.error || 'Failed to mark order as ready')
    }
  } catch (error) {
    console.error('Error marking order ready:', error)
    toast.error('Failed to mark order as ready')
  } finally {
    processingOrder.value = null
  }
}

// Open confirmation modals
const openAcceptModal = (order) => {
  targetOrder.value = order
  showAcceptModal.value = true
  closeOrderModal()
}

const openDeclineModal = (order) => {
  targetOrder.value = order
  showDeclineModal.value = true
  closeOrderModal()
}

// Confirm actions
const confirmAccept = async () => {
  if (!targetOrder.value) return
  await acceptOrder(targetOrder.value)
  showAcceptModal.value = false
  targetOrder.value = null
}

const confirmDecline = async () => {
  if (!targetOrder.value) return
  processingOrder.value = targetOrder.value.id
  try {
    const response = await fetch(`/api/vendor/orders/${targetOrder.value.id}/decline`, {
      method: 'PATCH',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json'
      }
    })

    if (response.ok) {
      toast.warning(`Order #${targetOrder.value.order_number} declined`)
      await loadOrders()
      emit('ordersUpdated')
    } else {
      const error = await response.json()
      toast.error(error.error || 'Failed to decline order')
    }
  } catch (error) {
    console.error('Error declining order:', error)
    toast.error('Failed to decline order')
  } finally {
    processingOrder.value = null
    showDeclineModal.value = false
    targetOrder.value = null
  }
}

const handleMarkReady = async (order) => {
  await markReady(order)
  closeOrderModal()
}

// Real-time
const subscribeToChannel = () => {
  if (window.Echo && vendorId.value) {
    window.Echo.private(`vendor-orders.${vendorId.value}`)
      .listen('.OrderReceived', (e) => {
        console.log('New order received:', e)
        loadOrders()
        toast.newOrder(`New Order #${e.order?.order_number || 'N/A'}!`)
      })
      .listen('.OrderStatusChanged', (e) => {
        console.log('Order status changed:', e)
        loadOrders()
      })
  }
}

const unsubscribeFromChannel = () => {
  if (window.Echo && vendorId.value) {
    window.Echo.leave(`vendor-orders.${vendorId.value}`)
  }
}

onMounted(async () => {
  const user = page.props.auth?.user
  vendorId.value = user?.vendor?.id || null
  await loadOrders()
  subscribeToChannel()
})

onUnmounted(() => {
  unsubscribeFromChannel()
})

defineExpose({ loadOrders })
</script>
