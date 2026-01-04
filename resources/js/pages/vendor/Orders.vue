<template>
  <VendorLayout>
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
      <div class="px-4 sm:px-6 py-6">
        <h1 class="text-2xl font-bold text-gray-900">Order Management</h1>
        <p class="text-gray-600 mt-1">Manage incoming orders and view order history</p>
      </div>
    </div>

    <!-- Tab Navigation -->
    <div class="bg-white border-b border-gray-200">
      <div class="px-4 sm:px-6">
          <nav class="flex space-x-12">
            <button
              @click="activeTab = 'incoming'"
              :class="[
                'py-5 px-4 border-b-2 font-medium text-base',
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
                'py-5 px-4 border-b-2 font-medium text-base',
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

      <!-- Statistics and Search Section -->
      <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <!-- Stats - Left side -->
          <div class="flex gap-8">
            <div class="text-center">
              <div class="text-xl font-bold text-yellow-600">{{ stats.pending_orders || 0 }}</div>
              <div class="text-sm text-gray-600">Pending</div>
            </div>
            <div class="text-center">
              <div class="text-xl font-bold text-blue-600">{{ stats.accepted_orders || 0 }}</div>
              <div class="text-sm text-gray-600">Accepted</div>
            </div>
            <div class="text-center">
              <div class="text-xl font-bold text-green-600">{{ stats.completed_orders || 0 }}</div>
              <div class="text-sm text-gray-600">Completed</div>
            </div>
            <div class="text-center">
              <div class="text-xl font-bold text-gray-600">{{ stats.today_orders || 0 }}</div>
              <div class="text-sm text-gray-600">Today</div>
            </div>
          </div>

          <!-- Empty space on right for balance -->
          <div></div>
        </div>
      </div>

      <!-- Tab Content -->
      <div class="flex-1 px-4 sm:px-6 py-6">
        <IncomingOrders
          v-if="activeTab === 'incoming'"
          ref="incomingOrdersRef"
          @orders-updated="handleOrdersUpdated"
        />
        <OrderHistory
          v-if="activeTab === 'history'"
          ref="orderHistoryRef"
          @orders-updated="handleOrdersUpdated"
        />
      </div>
  </VendorLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { usePage } from '@inertiajs/vue3'
import VendorLayout from '@/layouts/vendor/VendorLayout.vue'
import IncomingOrders from './IncomingOrders.vue'
import OrderHistory from './OrderHistory.vue'
import { apiGet } from '@/composables/useApi'

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

const loadStats = async () => {
  try {
    const response = await apiGet('/api/vendor/orders/stats')

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

// Real-time subscription for stats bar updates
const subscribeToChannel = () => {
  if (window.Echo && vendorId.value) {
    console.log('🔔 Orders: Subscribing to vendor-orders channel for vendor:', vendorId.value)

    window.Echo.private(`vendor-orders.${vendorId.value}`)
      // FIXED: Event broadcasts as 'VendorNewOrder' not 'OrderReceived'
      .listen('.VendorNewOrder', () => {
        console.log('🛒 Orders: New order received, refreshing stats')
        loadStats()
      })
      .listen('.OrderStatusChanged', () => {
        console.log('📦 Orders: Order status changed, refreshing stats')
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
