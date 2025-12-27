<template>
  <VendorLayout>
    <div class="min-h-screen bg-white">
      <!-- Header -->
      <div class="bg-white border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between">
          <h1 class="text-xl font-bold text-gray-900">Order Management</h1>
          <div class="text-sm text-gray-500">
            Manage incoming orders and view order history
          </div>
        </div>
      </div>

      <!-- Tab Navigation -->
      <div class="bg-white border-b border-gray-200">
        <div class="px-6">
          <nav class="flex space-x-8">
            <button
              @click="activeTab = 'incoming'"
              :class="[
                'py-4 px-1 border-b-2 font-medium text-sm',
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
                'py-4 px-1 border-b-2 font-medium text-sm',
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

      <!-- Statistics Summary -->
      <div class="bg-gray-50 px-6 py-4">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 max-w-4xl mx-auto">
          <div class="text-center">
            <div class="text-2xl font-bold text-yellow-600">{{ stats.pending_orders || 0 }}</div>
            <div class="text-sm text-gray-600">Pending</div>
          </div>

          <div class="text-center">
            <div class="text-2xl font-bold text-blue-600">{{ stats.accepted_orders || 0 }}</div>
            <div class="text-sm text-gray-600">Accepted</div>
          </div>

          <div class="text-center">
            <div class="text-2xl font-bold text-green-600">{{ stats.ready_for_pickup_orders || 0 }}</div>
            <div class="text-sm text-gray-600">Ready</div>
          </div>

          <div class="text-center">
            <div class="text-2xl font-bold text-gray-600">{{ stats.today_orders || 0 }}</div>
            <div class="text-sm text-gray-600">Today</div>
          </div>
        </div>
      </div>

      <!-- Tab Content -->
      <div class="flex-1">
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
    </div>
  </VendorLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import VendorLayout from '@/layouts/vendor/VendorLayout.vue'
import IncomingOrders from './IncomingOrders.vue'
import OrderHistory from './OrderHistory.vue'

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

onMounted(async () => {
  await loadStats()
})
</script>
