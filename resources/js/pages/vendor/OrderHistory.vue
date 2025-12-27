<template>
  <VendorLayout>
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
              <!-- Status Filter -->
              <select
                v-model="selectedStatus"
                @change="loadOrders"
                class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
              >
                <option value="">All Status</option>
                <option value="accepted">Accepted</option>
                <option value="ready_for_pickup">Ready</option>
                <option value="completed">Completed</option>
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

                <!-- Delete for completed/cancelled -->
                <template v-else-if="['completed', 'cancelled'].includes(order.status)">
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
              {{ searchQuery || selectedStatus ? 'Try adjusting your filters' : 'No completed orders to display' }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </VendorLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import VendorLayout from '@/layouts/vendor/VendorLayout.vue'

const orders = ref([])
const loading = ref(false)
const processingOrder = ref(null)
const selectedStatus = ref('')
const searchQuery = ref('')

let searchTimeout = null

const getStatusColor = (status) => {
  const colors = {
    'accepted': 'bg-blue-100 text-blue-700',
    'ready_for_pickup': 'bg-green-100 text-green-700',
    'completed': 'bg-gray-100 text-gray-700',
    'cancelled': 'bg-red-100 text-red-700'
  }
  return colors[status] || 'bg-gray-100 text-gray-700'
}

const getStatusText = (status) => {
  const texts = {
    'accepted': 'Accepted',
    'ready_for_pickup': 'Ready',
    'completed': 'Completed',
    'cancelled': 'Cancelled'
  }
  return texts[status] || status
}

const formatTime = (dateString) => {
  return new Date(dateString).toLocaleTimeString()
}

const loadOrders = async () => {
  // TODO: Load non-pending orders
  console.log('Loading order history...')
}

const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    loadOrders()
  }, 300)
}

const refreshOrders = async () => {
  await loadOrders()
}

const viewOrderDetails = (order) => {
  console.log('Viewing order details:', order)
}

const markReady = async (order) => {
  console.log('Marking order ready:', order)
}

const downloadReceipt = (order) => {
  console.log('Downloading receipt for:', order)
}

const deleteOrder = (order) => {
  console.log('Deleting order:', order)
}

onMounted(() => {
  // TODO: Initialize component
})
</script>
