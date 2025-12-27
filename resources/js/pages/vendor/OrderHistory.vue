<template>
  <div class="bg-white">
    <!-- Content -->
    <div class="p-6">
      <div class="max-w-4xl mx-auto">
        <!-- Filters -->
        <div class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
          <div class="flex gap-4 flex-wrap">
            <!-- Status Filter -->
            <select
              v-model="selectedStatus"
              @change="handleStatusChange"
              class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
            >
              <option value="">All Non-Pending</option>
              <option value="accepted">Accepted (In Progress)</option>
              <option value="ready_for_pickup">Ready for Pickup</option>
              <option value="cancelled">Cancelled</option>
            </select>

            <!-- Search -->
            <input
              v-model="searchQuery"
              @input="debouncedSearch"
              type="text"
              placeholder="Search by order number or table..."
              class="flex-1 min-w-48 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
            />
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="text-center py-12">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-orange-500 mx-auto"></div>
          <p class="text-gray-500 mt-4">Loading orders...</p>
        </div>

        <!-- Orders List -->
        <div v-else-if="orders.length > 0" class="space-y-4">
          <div
            v-for="order in orders"
            :key="order.id"
            :class="[
              'bg-white rounded-xl border-2 p-6 hover:shadow-lg transition-shadow',
              getBorderColor(order.status)
            ]"
          >
            <!-- Order Header -->
            <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
              <div class="flex items-center gap-4 flex-wrap">
                <div class="text-lg font-bold text-gray-900">#{{ order.order_number }}</div>
                <span
                  :class="[
                    'px-3 py-1 rounded-full text-sm font-medium',
                    getStatusColor(order.status)
                  ]"
                >
                  {{ getStatusText(order.status) }}
                </span>
                <span class="text-gray-500">Table {{ order.table_number || 'N/A' }}</span>
                <span class="text-gray-500">{{ formatDateTime(order.created_at) }}</span>
              </div>

              <div class="flex items-center gap-2">
                <span class="text-lg font-bold text-orange-600">₱{{ parseFloat(order.total_amount).toFixed(2) }}</span>
              </div>
            </div>

            <!-- Order Items Preview -->
            <div class="mb-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
                <div v-for="item in order.items?.slice(0, 3)" :key="item.id" class="flex justify-between">
                  <span class="text-gray-700">{{ item.quantity }}x {{ item.product?.name || 'Product' }}</span>
                  <span class="text-gray-600">₱{{ (parseFloat(item.price) * item.quantity).toFixed(2) }}</span>
                </div>
                <div v-if="order.items?.length > 3" class="text-gray-500 italic">
                  +{{ order.items.length - 3 }} more items...
                </div>
              </div>
            </div>

            <!-- Completed At for Ready Orders -->
            <div v-if="order.status === 'ready_for_pickup' && order.completed_at" class="mb-4">
              <span class="text-sm text-green-600">
                ✅ Completed at {{ formatDateTime(order.completed_at) }}
              </span>
            </div>

            <!-- Action Buttons Based on Status -->
            <div class="flex gap-2 flex-wrap">
              <button
                @click="openOrderDetail(order)"
                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200"
              >
                View Details
              </button>

              <!-- Accepted Orders -->
              <template v-if="order.status === 'accepted'">
                <button
                  @click="markReady(order)"
                  :disabled="processingOrder === order.id"
                  class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 disabled:opacity-50"
                >
                  {{ processingOrder === order.id ? 'Marking Ready...' : 'Mark Ready for Pickup' }}
                </button>
              </template>

              <!-- Ready Orders - Can be deleted -->
              <template v-if="order.status === 'ready_for_pickup' || order.status === 'cancelled'">
                <button
                  @click="deleteOrder(order)"
                  :disabled="processingOrder === order.id"
                  class="px-4 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 disabled:opacity-50"
                >
                  Delete
                </button>
              </template>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-12">
          <div class="text-6xl mb-4">📋</div>
          <h3 class="text-lg font-medium text-gray-900 mb-2">No orders found</h3>
          <p class="text-gray-500 mb-6">
            {{ searchQuery || selectedStatus ? 'Try adjusting your filters' : 'No orders to display' }}
          </p>
        </div>

        <!-- Pagination -->
        <div v-if="pagination.total > pagination.per_page" class="mt-6 flex items-center justify-between">
          <div class="text-sm text-gray-500">
            Showing {{ ((pagination.current_page - 1) * pagination.per_page) + 1 }} to
            {{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }} of
            {{ pagination.total }} results
          </div>
          <div class="flex gap-2">
            <button
              @click="changePage(pagination.current_page - 1)"
              :disabled="pagination.current_page <= 1"
              class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Previous
            </button>
            <button
              @click="changePage(pagination.current_page + 1)"
              :disabled="pagination.current_page >= pagination.last_page"
              class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Next
            </button>
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
      @markReady="handleMarkReadyFromModal"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { usePage } from '@inertiajs/vue3'
import OrderDetailModal from '@/components/vendor/OrderDetailModal.vue'

const emit = defineEmits(['ordersUpdated'])

// Get vendor ID for channel subscription
const page = usePage()
const vendorId = ref(null)

const orders = ref([])
const loading = ref(false)
const processingOrder = ref(null)
const selectedStatus = ref('')
const searchQuery = ref('')

// Modal state
const showOrderModal = ref(false)
const selectedOrderId = ref(null)

const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 20,
  total: 0
})

let searchTimeout = null

const getBorderColor = (status) => {
  const colors = {
    'accepted': 'border-blue-300',
    'ready_for_pickup': 'border-green-300',
    'cancelled': 'border-red-300'
  }
  return colors[status] || 'border-gray-200'
}

const getStatusColor = (status) => {
  const colors = {
    'accepted': 'bg-blue-100 text-blue-700',
    'ready_for_pickup': 'bg-green-100 text-green-700',
    'cancelled': 'bg-red-100 text-red-700'
  }
  return colors[status] || 'bg-gray-100 text-gray-700'
}

const getStatusText = (status) => {
  const texts = {
    'accepted': 'Accepted',
    'ready_for_pickup': 'Ready for Pickup',
    'cancelled': 'Cancelled'
  }
  return texts[status] || status
}

const formatDateTime = (dateString) => {
  if (!dateString) return ''
  return new Date(dateString).toLocaleString('en-PH', {
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const loadOrders = async () => {
  loading.value = true
  try {
    const params = new URLSearchParams({
      page: pagination.value.current_page.toString(),
      per_page: pagination.value.per_page.toString()
    })

    // If no specific status selected, don't send status param
    // The backend will return all orders, we'll filter out pending
    if (selectedStatus.value) {
      params.append('status', selectedStatus.value)
    }

    if (searchQuery.value) {
      params.append('search', searchQuery.value)
    }

    const response = await fetch(`/api/vendor/orders?${params}`, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json'
      }
    })

    if (response.ok) {
      const data = await response.json()
      // Filter out pending orders for history view
      let filteredOrders = data.orders || []
      if (!selectedStatus.value) {
        filteredOrders = filteredOrders.filter(order => order.status !== 'pending')
      }
      orders.value = filteredOrders
      pagination.value = data.pagination || pagination.value
    }
  } catch (error) {
    console.error('Error loading orders:', error)
  } finally {
    loading.value = false
  }
}

const handleStatusChange = () => {
  pagination.value.current_page = 1
  loadOrders()
}

const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    pagination.value.current_page = 1
    loadOrders()
  }, 300)
}

const changePage = (page) => {
  pagination.value.current_page = page
  loadOrders()
}

const refreshOrders = async () => {
  await loadOrders()
}

const openOrderDetail = (order) => {
  selectedOrderId.value = order.id
  showOrderModal.value = true
}

const closeOrderModal = () => {
  showOrderModal.value = false
  selectedOrderId.value = null
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
      await loadOrders()
      emit('ordersUpdated')
    } else {
      const error = await response.json()
      alert(error.error || 'Failed to mark order as ready')
    }
  } catch (error) {
    console.error('Error marking order ready:', error)
    alert('Failed to mark order as ready')
  } finally {
    processingOrder.value = null
  }
}

const deleteOrder = async (order) => {
  if (!confirm(`Are you sure you want to delete order #${order.order_number}?`)) return

  processingOrder.value = order.id
  try {
    // Use batch delete endpoint with single order
    const response = await fetch('/api/vendor/orders/batch', {
      method: 'DELETE',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ order_ids: [order.id] })
    })

    if (response.ok) {
      await loadOrders()
      emit('ordersUpdated')
    } else {
      const error = await response.json()
      alert(error.error || 'Failed to delete order')
    }
  } catch (error) {
    console.error('Error deleting order:', error)
    alert('Failed to delete order')
  } finally {
    processingOrder.value = null
  }
}

// Modal action handlers
const handleMarkReadyFromModal = async (order) => {
  await markReady(order)
  closeOrderModal()
}

// Real-time subscription
const subscribeToChannel = () => {
  if (window.Echo && vendorId.value) {
    window.Echo.private(`vendor-orders.${vendorId.value}`)
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
  // Get vendor ID from user data
  const user = page.props.auth?.user
  vendorId.value = user?.vendor?.id || null

  await loadOrders()

  // Subscribe to real-time updates
  subscribeToChannel()
})

onUnmounted(() => {
  unsubscribeFromChannel()
})

// Expose loadOrders for parent component to call
defineExpose({
  loadOrders
})
</script>
