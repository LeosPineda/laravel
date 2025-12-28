<template>
  <div class="bg-white">
    <!-- Tabs -->
    <div class="border-b border-gray-200 px-4">
      <div class="flex gap-6">
        <button
          @click="activeTab = 'pending'"
          :class="[
            'py-3 text-sm font-medium border-b-2 transition-colors',
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
            'py-3 text-sm font-medium border-b-2 transition-colors',
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
    <div class="p-4">
      <!-- Loading -->
      <div v-if="loading" class="text-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-orange-500 mx-auto"></div>
        <p class="text-gray-500 mt-4">Loading orders...</p>
      </div>

      <!-- Search Control - Stays on Top -->
      <div v-if="!loading" class="mb-4 p-3 bg-gray-50 rounded-lg border sticky top-0 z-10">
        <div class="flex items-center gap-4">
          <div class="flex items-center gap-2">
            <label class="text-sm text-gray-600">Search:</label>
            <input
              v-model="searchQuery"
              @input="debouncedSearch"
              type="text"
              placeholder="Order number, table..."
              class="px-3 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 w-48"
            />
          </div>
          <div class="text-sm text-gray-600 ml-auto">
            {{ allOrders.length }} orders
          </div>
        </div>
      </div>

      <!-- Pending Orders -->
      <div v-if="activeTab === 'pending'">
        <div v-if="pendingOrders.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="order in pendingOrders"
            :key="order.id"
            class="bg-white rounded-lg border-2 border-yellow-300 p-6"
          >
            <div class="flex items-start justify-between mb-4">
              <div>
                <div class="mb-2">
                  <span class="text-base font-bold text-gray-900">#{{ order.order_number?.replace('ORD-', '') }}</span>
                  <span class="ml-2 px-2 py-1 bg-yellow-100 text-yellow-700 rounded text-sm font-medium">New</span>
                </div>
                <p class="text-sm text-gray-500">Table {{ order.table_number || 'N/A' }}</p>
                <p class="text-sm text-gray-500">{{ formatTime(order.created_at) }}</p>
              </div>
              <span class="text-lg font-bold text-orange-600">₱{{ parseFloat(order.total_amount).toFixed(0) }}</span>
            </div>

            <div class="flex gap-3">
              <button
                @click="openOrderDetail(order)"
                class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200"
              >
                View Order
              </button>
              <button
                @click="declineOrder(order)"
                class="px-4 py-2 bg-red-100 text-red-600 rounded-lg text-sm hover:bg-red-200"
              >
                Decline
              </button>
              <button
                @click="acceptOrder(order)"
                class="flex-1 px-4 py-2 bg-green-500 text-white rounded-lg text-sm hover:bg-green-600"
              >
                Accept
              </button>
            </div>
          </div>
        </div>

        <div v-else class="text-center py-12">
          <div class="text-5xl mb-4">📭</div>
          <h3 class="text-lg font-medium text-gray-900 mb-2">No pending orders</h3>
          <p class="text-gray-500">New orders will appear here</p>
        </div>
      </div>

      <!-- Accepted Orders -->
      <div v-if="activeTab === 'accepted'">
        <div v-if="acceptedOrders.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="order in acceptedOrders"
            :key="order.id"
            class="bg-white rounded-lg border-2 border-blue-300 p-6"
          >
            <div class="flex items-start justify-between mb-4">
              <div>
                <div class="mb-2">
                  <span class="text-base font-bold text-gray-900">#{{ order.order_number?.replace('ORD-', '') }}</span>
                  <span class="ml-2 px-2 py-1 bg-blue-100 text-blue-700 rounded text-sm font-medium">Preparing</span>
                </div>
                <p class="text-sm text-gray-500">Table {{ order.table_number || 'N/A' }}</p>
                <p class="text-sm text-gray-500">{{ formatTime(order.created_at) }}</p>
              </div>
              <span class="text-lg font-bold text-orange-600">₱{{ parseFloat(order.total_amount).toFixed(0) }}</span>
            </div>

            <div class="flex gap-3">
              <button
                @click="openOrderDetail(order)"
                class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200"
              >
                View Order
              </button>
              <button
                @click="markReady(order)"
                class="flex-1 px-4 py-2 bg-blue-500 text-white rounded-lg text-sm hover:bg-blue-600"
              >
                Mark as Ready
              </button>
            </div>
          </div>
        </div>

        <div v-else class="text-center py-12">
          <div class="text-5xl mb-4">👨‍🍳</div>
          <h3 class="text-lg font-medium text-gray-900 mb-2">No orders being prepared</h3>
          <p class="text-gray-500">Accepted orders will appear here</p>
        </div>
      </div>
    </div>

    <!-- Modals -->
    <OrderDetailModal
      :is-open="showOrderModal"
      :order-id="selectedOrderId"
      @close="showOrderModal = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'
import OrderDetailModal from '@/components/vendor/OrderDetailModal.vue'
import { useToast } from '@/composables/useToast'

const props = defineProps({
  filters: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['ordersUpdated'])
const toast = useToast()
const page = usePage()

const vendorId = ref(null)
const allOrders = ref([])
const loading = ref(false)
const activeTab = ref('pending')

// Local filter state
const searchQuery = ref('')
let searchTimeout = null

// Modal
const showOrderModal = ref(false)
const selectedOrderId = ref(null)

// Computed
const pendingOrders = computed(() => {
  let filtered = allOrders.value.filter(o => o.status === 'pending')
  return applySearch(filtered)
})

const acceptedOrders = computed(() => {
  let filtered = allOrders.value.filter(o => o.status === 'accepted')
  return applySearch(filtered)
})

const pendingCount = computed(() => pendingOrders.value.length)
const acceptedCount = computed(() => acceptedOrders.value.length)

// Helper function to apply search only
const applySearch = (orders) => {
  let filtered = [...orders]

  // Apply search
  if (searchQuery.value) {
    const searchTerm = searchQuery.value.toLowerCase()
    filtered = filtered.filter(order =>
      order.order_number?.toLowerCase().includes(searchTerm) ||
      order.table_number?.toString().includes(searchTerm)
    )
  }

  return filtered
}

const formatTime = (dateString) => {
  if (!dateString) return ''
  return new Date(dateString).toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit' })
}

const loadOrders = async () => {
  try {
    loading.value = true
    const params = new URLSearchParams({
      per_page: '50'
    })

    const response = await fetch(`/api/vendor/orders?${params}`, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json'
      }
    })

    if (response.ok) {
      const data = await response.json()
      allOrders.value = (data.orders || []).filter(o =>
        o.status === 'pending' || o.status === 'accepted'
      )
    } else {
      toast.error('Failed to load orders')
    }
  } catch (error) {
    toast.error('Failed to load orders')
  } finally {
    loading.value = false
  }
}

const openOrderDetail = (order) => {
  selectedOrderId.value = order.id
  showOrderModal.value = true
}

const acceptOrder = async (order) => {
  try {
    const response = await fetch(`/api/vendor/orders/${order.id}/accept`, {
      method: 'PATCH',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json'
      }
    })

    if (response.ok) {
      toast.success(`Order #${order.order_number} accepted! Customer will be notified.`)
      await loadOrders()
      activeTab.value = 'accepted'
      emit('ordersUpdated')
    } else {
      toast.error('Failed to accept order')
    }
  } catch (error) {
    toast.error('Failed to accept order')
  }
}

const declineOrder = async (order) => {
  if (!confirm(`Decline order #${order.order_number}? The customer will be notified.`)) return

  try {
    const response = await fetch(`/api/vendor/orders/${order.id}/decline`, {
      method: 'PATCH',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json'
      }
    })

    if (response.ok) {
      toast.warning(`Order #${order.order_number} declined. Customer will be notified.`)
      await loadOrders()
      emit('ordersUpdated')
    } else {
      toast.error('Failed to decline order')
    }
  } catch (error) {
    toast.error('Failed to decline order')
  }
}

const markReady = async (order) => {
  try {
    const response = await fetch(`/api/vendor/orders/${order.id}/ready`, {
      method: 'PATCH',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json'
      }
    })

    if (response.ok) {
      toast.success(`Order #${order.order_number} is ready! Customer notified + receipt sent.`)
      await loadOrders()
      emit('ordersUpdated')
    } else {
      toast.error('Failed to mark order as ready')
    }
  } catch (error) {
    toast.error('Failed to mark order as ready')
  }
}

// Real-time
const subscribeToChannel = () => {
  if (window.Echo && vendorId.value) {
    window.Echo.private(`vendor-orders.${vendorId.value}`)
      .listen('.OrderReceived', (e) => {
        loadOrders()
        toast.success(`New Order #${e.order?.order_number}!`)
      })
      .listen('.OrderStatusChanged', (e) => {
        loadOrders()
      })
  }
}

const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    // Search is handled by computed properties, no need to reload
  }, 300)
}

onMounted(async () => {
  const user = page.props.auth?.user
  vendorId.value = user?.vendor?.id || null
  await loadOrders()
  subscribeToChannel()
})

onUnmounted(() => {
  if (window.Echo && vendorId.value) {
    window.Echo.leave(`vendor-orders.${vendorId.value}`)
  }
})
</script>
