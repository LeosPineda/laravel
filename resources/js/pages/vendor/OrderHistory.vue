<template>
  <div class="min-h-screen bg-white">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200 px-6 py-4">
      <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold text-gray-900">Order History</h1>
        <div class="flex gap-2">
          <button
            @click="refreshOrders"
            class="px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200"
          >
            Refresh
          </button>
        </div>
      </div>
    </div>

    <!-- Content -->
    <div class="p-6">
      <div class="max-w-4xl mx-auto">
        <!-- Filters -->
        <div class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
          <div class="flex gap-4">
            <!-- Status Filter - FIXED: Removed 'completed' status -->
            <select
              v-model="selectedStatus"
              @change="loadOrders"
              class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
            >
              <option value="">All Status</option>
              <option value="accepted">Accepted</option>
              <option value="ready_for_pickup">Ready for Pickup</option>
              <option value="cancelled">Cancelled</option>
            </select>

            <!-- Search -->
            <input
              v-model="searchQuery"
              @input="debouncedSearch"
              type="text"
              placeholder="Search by order number or table..."
              class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
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
            class="bg-white rounded-xl border border-gray-200 p-6 hover:shadow-lg transition-shadow"
          >
            <!-- Order Header -->
            <div class="flex items-center justify-between mb-4">
              <div class="flex items-center gap-4">
                <div class="text-lg font-bold text-gray-900">#{{ order.order_number }}</div>
                <span
                  :class="[
                    'px-3 py-1 rounded-full text-sm font-medium',
                    getStatusColor(order.status)
                  ]"
                >
                  {{ getStatusText(order.status) }}
                </span>
                <span class="text-gray-500">Table {{ order.table_number }}</span>
                <span class="text-gray-500">{{ formatTime(order.created_at) }}</span>
              </div>

              <div class="flex items-center gap-2">
                <span class="text-lg font-bold text-orange-600">₱{{ order.total_amount.toFixed(2) }}</span>
                <button
                  @click="viewOrderDetails(order)"
                  class="px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm"
                >
                  View Details
                </button>
              </div>
            </div>

            <!-- Action Buttons Based on Status -->
            <div class="flex gap-2">
              <!-- Accepted Orders -->
              <template v-if="order.status === 'accepted'">
                <button
                  @click="markReady(order)"
                  :disabled="processingOrder === order.id"
                  class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 disabled:opacity-50"
                >
                  {{ processingOrder === order.id ? 'Marking Ready...' : 'Mark Ready' }}
                </button>
              </template>

              <!-- Ready Orders -->
              <template v-else-if="order.status === 'ready_for_pickup'">
                <button
                  @click="downloadReceipt(order)"
                  class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600"
                >
                  Download Receipt
                </button>
              </template>

              <!-- Delete for cancelled orders only -->
              <template v-else-if="order.status === 'cancelled'">
                <button
                  @click="deleteOrder(order)"
                  class="px-4 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200"
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
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const orders = ref([])
const loading = ref(false)
const processingOrder = ref(null)
const selectedStatus = ref('')
const searchQuery = ref('')

const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 20,
  total: 0
})

let searchTimeout = null

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

const formatTime = (dateString) => {
  return new Date(dateString).toLocaleTimeString()
}

const loadOrders = async () => {
  loading.value = true
  try {
    const params = new URLSearchParams({
      page: pagination.value.current_page,
      per_page: pagination.value.per_page,
      status: selectedStatus.value || 'accepted,ready_for_pickup,cancelled' // Exclude pending
    })

    if (searchQuery.value) params.append('search', searchQuery.value)

    const response = await fetch(`/api/vendor/orders?${params}`, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json'
      }
    })

    if (response.ok) {
      const data = await response.json()
      orders.value = data.orders
      pagination.value = data.pagination
    }
  } catch (error) {
    console.error('Error loading orders:', error)
  } finally {
    loading.value = false
  }
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

const viewOrderDetails = async (order) => {
  try {
    const response = await fetch(`/api/vendor/orders/${order.id}`, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json'
      }
    })

    if (response.ok) {
      const data = await response.json()
      // TODO: Open modal with order details
      alert(`Order #${order.order_number} details:\n${data.order.items.map(item => `${item.quantity}x ${item.product?.name}`).join('\n')}`)
    }
  } catch (error) {
    console.error('Error loading order details:', error)
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
      await loadOrders()
      alert('Order marked as ready successfully!')
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

const downloadReceipt = async (order) => {
  try {
    const response = await fetch(`/api/customer/orders/${order.id}/receipt`, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json'
      }
    })

    if (response.ok) {
      const data = await response.json()
      // Open receipt in new window or download
      const receiptWindow = window.open('', '_blank')
      receiptWindow.document.write(data.receipt_html)
      receiptWindow.document.close()
    } else {
      const error = await response.json()
      alert(error.error || 'Failed to download receipt')
    }
  } catch (error) {
    console.error('Error downloading receipt:', error)
    alert('Failed to download receipt')
  }
}

const deleteOrder = async (order) => {
  if (!confirm(`Are you sure you want to delete order #${order.order_number}?`)) return

  try {
    const response = await fetch(`/api/vendor/orders/${order.id}`, {
      method: 'DELETE',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json'
      }
    })

    if (response.ok) {
      await loadOrders()
      alert('Order deleted successfully!')
    } else {
      const error = await response.json()
      alert(error.error || 'Failed to delete order')
    }
  } catch (error) {
    console.error('Error deleting order:', error)
    alert('Failed to delete order')
  }
}

onMounted(async () => {
  await loadOrders()
})
</script>
