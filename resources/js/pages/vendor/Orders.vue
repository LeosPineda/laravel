<template>
  <VendorLayout>
    <div class="bg-white">
      <!-- Header -->
      <div class="bg-white border-b border-gray-200 px-4 py-3">
        <div class="flex items-center justify-between">
          <h1 class="text-xl font-bold text-gray-900">Order Management</h1>
          <div class="text-sm text-gray-500">
            Manage incoming orders and view order history
          </div>
        </div>
      </div>

      <!-- Tab Navigation -->
      <div class="bg-white border-b border-gray-200">
        <div class="px-4">
          <nav class="flex space-x-8">
            <button
              @click="activeTab = 'incoming'"
              :class="[
                'py-3 px-1 border-b-2 font-medium text-sm',
                activeTab === 'incoming'
                  ? 'border-orange-500 text-orange-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
              ]"
            >
              📦 Incoming Orders
              <span v-if="stats.pending_orders" class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                {{ stats.pending_orders }}
              </span>
            </button>

            <button
              @click="activeTab = 'history'"
              :class="[
                'py-3 px-1 border-b-2 font-medium text-sm',
                activeTab === 'history'
                  ? 'border-orange-500 text-orange-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
              ]"
            >
              📋 Order History
            </button>
          </nav>
        </div>
      </div>

      <!-- Statistics and Filters Section -->
      <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <!-- Stats -->
          <div class="flex gap-6">
            <div class="text-center">
              <div class="text-lg font-bold text-yellow-600">{{ stats.pending_orders || 0 }}</div>
              <div class="text-xs text-gray-600">Pending</div>
            </div>
            <div class="text-center">
              <div class="text-lg font-bold text-blue-600">{{ stats.accepted_orders || 0 }}</div>
              <div class="text-xs text-gray-600">Accepted</div>
            </div>
            <div class="text-center">
              <div class="text-lg font-bold text-green-600">{{ stats.completed_orders || 0 }}</div>
              <div class="text-xs text-gray-600">Completed</div>
            </div>
            <div class="text-center">
              <div class="text-lg font-bold text-gray-600">{{ stats.today_orders || 0 }}</div>
              <div class="text-xs text-gray-600">Today</div>
            </div>
          </div>

          <!-- Filters for Incoming Orders -->
          <div v-if="activeTab === 'incoming'" class="flex items-center gap-4">
            <div class="flex items-center gap-2">
              <label class="text-sm text-gray-600">Sort by:</label>
              <select
                v-model="incomingFilters.sortBy"
                @change="handleIncomingFiltersChange"
                class="px-3 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-2 focus:ring-orange-500"
              >
                <option value="created_at">Time Received</option>
                <option value="updated_at">Time Updated</option>
                <option value="total_amount">Amount</option>
              </select>
              <button
                @click="incomingFilters.sortOrder = incomingFilters.sortOrder === 'asc' ? 'desc' : 'asc'"
                class="px-2 py-1 border border-gray-300 rounded text-sm hover:bg-gray-100"
              >
                {{ incomingFilters.sortOrder === 'asc' ? '↑' : '↓' }}
              </button>
            </div>
            <div class="flex items-center gap-2">
              <label class="text-sm text-gray-600">Search:</label>
              <input
                v-model="incomingFilters.search"
                @input="debouncedIncomingSearch"
                type="text"
                placeholder="Order number, table..."
                class="px-3 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 w-48"
              />
            </div>
          </div>

          <!-- Filters for Order History -->
          <div v-if="activeTab === 'history'" class="flex items-center gap-4">
            <div class="flex items-center gap-2">
              <label class="text-sm text-gray-600">Status:</label>
              <select
                v-model="historyFilters.status"
                @change="handleHistoryFiltersChange"
                class="px-3 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-2 focus:ring-orange-500"
              >
                <option value="">All</option>
                <option value="cancelled">Declined</option>
                <option value="ready_for_pickup">Completed</option>
              </select>
            </div>
            <div class="flex items-center gap-2">
              <label class="text-sm text-gray-600">Search:</label>
              <input
                v-model="historyFilters.search"
                @input="debouncedHistorySearch"
                type="text"
                placeholder="Order number, table..."
                class="px-3 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 w-48"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Tab Content -->
      <div class="flex-1">
        <IncomingOrders
          v-if="activeTab === 'incoming'"
          ref="incomingOrdersRef"
          :filters="incomingFilters"
          @orders-updated="handleOrdersUpdated"
        />
        <OrderHistory
          v-if="activeTab === 'history'"
          ref="orderHistoryRef"
          :filters="historyFilters"
          @orders-updated="handleOrdersUpdated"
        />
      </div>
    </div>
  </VendorLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { usePage } from '@inertiajs/vue3'
import VendorLayout from '@/layouts/vendor/VendorLayout.vue'
import IncomingOrders from './IncomingOrders.vue'
import OrderHistory from './OrderHistory.vue'

const page = usePage()
const vendorId = ref(null)

const activeTab = ref('incoming')
const incomingOrdersRef = ref(null)
const orderHistoryRef = ref(null)

const stats = ref({
  pending_orders: 0,
  accepted_orders: 0,
  ready_for_pickup_orders: 0,
  completed_orders: 0,
  today_orders: 0
})

const incomingFilters = ref({
  sortBy: 'created_at',
  sortOrder: 'desc',
  search: ''
})

const historyFilters = ref({
  status: '',
  search: ''
})

let incomingSearchTimeout = null
let historySearchTimeout = null

const loadStats = async () => {
  try {
    const response = await fetch('/api/vendor/orders/stats', {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json'
      }
    })

    if (response.ok) {
      const data = await response.json()
      stats.value = data
    }
  } catch (error) {
    console.error('Error loading stats:', error)
  }
}

const handleOrdersUpdated = async () => {
  // Refresh stats when orders change
  await loadStats()

  // Refresh both tabs if they exist
  if (incomingOrdersRef.value?.loadOrders) {
    incomingOrdersRef.value.loadOrders()
  }
  if (orderHistoryRef.value?.loadOrders) {
    orderHistoryRef.value.loadOrders()
  }
}

const handleIncomingFiltersChange = () => {
  if (incomingOrdersRef.value?.loadOrders) {
    incomingOrdersRef.value.loadOrders()
  }
}

const handleHistoryFiltersChange = () => {
  if (orderHistoryRef.value?.loadOrders) {
    orderHistoryRef.value.loadOrders()
  }
}

const debouncedIncomingSearch = () => {
  clearTimeout(incomingSearchTimeout)
  incomingSearchTimeout = setTimeout(() => {
    handleIncomingFiltersChange()
  }, 300)
}

const debouncedHistorySearch = () => {
  clearTimeout(historySearchTimeout)
  historySearchTimeout = setTimeout(() => {
    handleHistoryFiltersChange()
  }, 300)
}

// Real-time subscription for stats bar updates
const subscribeToChannel = () => {
  if (window.Echo && vendorId.value) {
    window.Echo.private(`vendor-orders.${vendorId.value}`)
      .listen('.OrderReceived', () => {
        console.log('Orders: New order received, refreshing stats')
        loadStats()
      })
      .listen('.OrderStatusChanged', () => {
        console.log('Orders: Order status changed, refreshing stats')
        loadStats()
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

  await loadStats()

  // Subscribe to real-time updates
  subscribeToChannel()
})

onUnmounted(() => {
  unsubscribeFromChannel()
})
</script>
