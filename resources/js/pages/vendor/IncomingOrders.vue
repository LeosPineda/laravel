<template>
  <VendorLayout>
    <div class="min-h-screen bg-white">
      <!-- Header -->
      <div class="bg-white border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between">
          <h1 class="text-xl font-bold text-gray-900">Incoming Orders</h1>
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
          <!-- Search -->
          <div class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
            <input
              v-model="searchQuery"
              @input="debouncedSearch"
              type="text"
              placeholder="Search by order number or table..."
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
            />
          </div>

          <!-- Loading State -->
          <div v-if="loading" class="text-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-orange-500 mx-auto"></div>
            <p class="text-gray-500 mt-4">Loading orders...</p>
          </div>

          <!-- Orders List -->
          <div v-else-if="incomingOrders.length > 0" class="space-y-4">
            <div
              v-for="order in incomingOrders"
              :key="order.id"
              class="bg-white rounded-xl border-2 border-yellow-300 p-6 hover:shadow-lg transition-shadow"
            >
              <!-- Order Header -->
              <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-4">
                  <div class="text-lg font-bold text-gray-900">#{{ order.order_number }}</div>
                  <span class="px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-700">
                    Pending
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

              <!-- Order Items -->
              <div class="mb-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
                  <div v-for="item in order.items" :key="item.id" class="flex justify-between">
                    <span>{{ item.quantity }}x {{ item.product?.name }}</span>
                    <span class="text-gray-600">₱{{ (item.price * item.quantity).toFixed(2) }}</span>
                  </div>
                </div>
              </div>

              <!-- Payment Info -->
              <div class="mb-4">
                <span class="text-sm text-gray-500">
                  Payment: {{ order.payment_method === 'qr_code' ? '📱 QR Code' : '💵 Cashier' }}
                  <span v-if="order.special_instructions"> • Special: {{ order.special_instructions }}</span>
                </span>
              </div>

              <!-- Action Buttons -->
              <div class="flex gap-2">
                <button
                  @click="acceptOrder(order)"
                  :disabled="processingOrder === order.id"
                  class="flex-1 px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 disabled:opacity-50"
                >
                  {{ processingOrder === order.id ? 'Accepting...' : 'Accept' }}
                </button>
                <button
                  @click="declineOrder(order)"
                  :disabled="processingOrder === order.id"
                  class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 disabled:opacity-50"
                >
                  Decline
                </button>
              </div>
            </div>
          </div>

          <!-- Empty State -->
          <div v-else class="text-center py-12">
            <div class="text-6xl mb-4">📦</div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No incoming orders</h3>
            <p class="text-gray-500 mb-6">
              {{ searchQuery ? 'Try adjusting your search' : 'No pending orders to display' }}
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
  </VendorLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import VendorLayout from '@/layouts/vendor/VendorLayout.vue'

const incomingOrders = ref([])
const loading = ref(false)
const processingOrder = ref(null)
const searchQuery = ref('')

const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 20,
  total: 0
})

let searchTimeout = null

const formatTime = (dateString) => {
  return new Date(dateString).toLocaleTimeString()
}

const loadOrders = async () => {
  loading.value = true
  try {
    const params = new URLSearchParams({
      page: pagination.value.current_page,
      per_page: pagination.value.per_page,
      status: 'pending'
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
      incomingOrders.value = data.orders
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
      await loadOrders()
      alert('Order accepted successfully!')
    } else {
      const error = await response.json()
      alert(error.error || 'Failed to accept order')
    }
  } catch (error) {
    console.error('Error accepting order:', error)
    alert('Failed to accept order')
  } finally {
    processingOrder.value = null
  }
}

const declineOrder = async (order) => {
  if (!confirm(`Are you sure you want to decline order #${order.order_number}?`)) return

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
      await loadOrders()
      alert('Order declined successfully!')
    } else {
      const error = await response.json()
      alert(error.error || 'Failed to decline order')
    }
  } catch (error) {
    console.error('Error declining order:', error)
    alert('Failed to decline order')
  } finally {
    processingOrder.value = null
  }
}

onMounted(async () => {
  await loadOrders()
})
</script>
