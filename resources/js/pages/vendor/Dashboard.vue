<template>
  <VendorLayout>
    <div class="bg-white">
      <!-- Header -->
      <div class="bg-white border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-orange-500 flex items-center justify-center">
              <span class="text-white font-bold">🍔</span>
            </div>
            <h1 class="text-xl font-bold text-gray-900">Dashboard</h1>
          </div>
          <button
            @click="refreshStats"
            class="p-2 text-gray-600 hover:text-gray-900 flex items-center gap-2"
          >
            <svg class="w-5 h-5" :class="{ 'animate-spin': loadingStats }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Refresh
          </button>
        </div>
      </div>

      <!-- Dashboard Content -->
      <div class="p-6">
        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
          <!-- Today's Orders -->
          <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-lg bg-orange-50 flex items-center justify-center">
                <span class="text-orange-500">📦</span>
              </div>
              <div>
                <p class="text-2xl font-bold text-gray-900">{{ stats.today_orders || 0 }}</p>
                <p class="text-sm text-gray-500">Today's Orders</p>
              </div>
            </div>
          </div>

          <!-- Today's Revenue -->
          <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center">
                <span class="text-green-500">💰</span>
              </div>
              <div>
                <p class="text-2xl font-bold text-gray-900">₱{{ formatNumber(stats.today_revenue) }}</p>
                <p class="text-sm text-gray-500">Today's Revenue</p>
              </div>
            </div>
          </div>

          <!-- Pending Orders - Clickable -->
          <Link
            href="/vendor/orders"
            class="bg-white rounded-xl border-2 border-yellow-300 p-6 hover:shadow-lg transition-shadow cursor-pointer"
          >
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-lg bg-yellow-50 flex items-center justify-center">
                <span class="text-yellow-500">⏳</span>
              </div>
              <div>
                <p class="text-2xl font-bold text-yellow-600">{{ stats.pending_orders || 0 }}</p>
                <p class="text-sm text-gray-500">Pending Orders</p>
              </div>
            </div>
            <div v-if="stats.pending_orders > 0" class="mt-2 text-xs text-yellow-600">
              Click to view →
            </div>
          </Link>

          <!-- Total Orders -->
          <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center">
                <span class="text-blue-500">📊</span>
              </div>
              <div>
                <p class="text-2xl font-bold text-gray-900">{{ stats.total_orders || 0 }}</p>
                <p class="text-sm text-gray-500">Total Orders</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Second Row Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
          <!-- Accepted Orders -->
          <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center">
                <span class="text-blue-500">✓</span>
              </div>
              <div>
                <p class="text-2xl font-bold text-blue-600">{{ stats.accepted_orders || 0 }}</p>
                <p class="text-sm text-gray-500">In Progress</p>
              </div>
            </div>
          </div>

          <!-- Completed Orders -->
          <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center">
                <span class="text-green-500">✅</span>
              </div>
              <div>
                <p class="text-2xl font-bold text-green-600">{{ stats.completed_orders || 0 }}</p>
                <p class="text-sm text-gray-500">Completed</p>
              </div>
            </div>
          </div>

          <!-- This Week Orders -->
          <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-lg bg-purple-50 flex items-center justify-center">
                <span class="text-purple-500">📅</span>
              </div>
              <div>
                <p class="text-2xl font-bold text-purple-600">{{ stats.this_week_orders || 0 }}</p>
                <p class="text-sm text-gray-500">This Week</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Pending Orders Quick View -->
        <div v-if="pendingOrders.length > 0" class="bg-white rounded-xl border-2 border-yellow-200 p-6 mb-8">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
              <span class="text-yellow-500">⏳</span> Pending Orders
              <span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded-full text-sm">
                {{ pendingOrders.length }}
              </span>
            </h2>
            <Link href="/vendor/orders" class="text-orange-600 hover:text-orange-700 text-sm">
              View All →
            </Link>
          </div>

          <div class="space-y-3">
            <div
              v-for="order in pendingOrders.slice(0, 3)"
              :key="order.id"
              class="flex items-center justify-between p-4 bg-yellow-50 rounded-lg"
            >
              <div class="flex items-center gap-4">
                <div>
                  <p class="font-medium text-gray-900">#{{ order.order_number }}</p>
                  <p class="text-sm text-gray-500">Table {{ order.table_number || 'N/A' }}</p>
                </div>
                <div class="text-sm text-gray-500">
                  {{ formatTime(order.created_at) }}
                </div>
              </div>
              <div class="flex items-center gap-3">
                <span class="font-bold text-orange-600">₱{{ parseFloat(order.total_amount).toFixed(2) }}</span>
                <Link
                  href="/vendor/orders"
                  class="px-3 py-1 bg-orange-500 text-white rounded-lg hover:bg-orange-600 text-sm"
                >
                  Review
                </Link>
              </div>
            </div>
          </div>

          <div v-if="pendingOrders.length > 3" class="mt-3 text-center">
            <Link href="/vendor/orders" class="text-sm text-orange-600 hover:text-orange-700">
              +{{ pendingOrders.length - 3 }} more pending orders
            </Link>
          </div>
        </div>

        <!-- Quick Actions -->
        <div>
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h2>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <Link href="/vendor/orders" class="p-4 bg-gray-50 rounded-xl border border-gray-200 hover:bg-gray-100 text-center">
              <div class="text-2xl mb-2">📦</div>
              <p class="text-sm font-medium text-gray-900">Manage Orders</p>
            </Link>
            <Link href="/vendor/products" class="p-4 bg-gray-50 rounded-xl border border-gray-200 hover:bg-gray-100 text-center">
              <div class="text-2xl mb-2">🍔</div>
              <p class="text-sm font-medium text-gray-900">Products</p>
            </Link>
            <Link href="/vendor/analytics" class="p-4 bg-gray-50 rounded-xl border border-gray-200 hover:bg-gray-100 text-center">
              <div class="text-2xl mb-2">📊</div>
              <p class="text-sm font-medium text-gray-900">Analytics</p>
            </Link>
            <Link href="/vendor/qr" class="p-4 bg-gray-50 rounded-xl border border-gray-200 hover:bg-gray-100 text-center">
              <div class="text-2xl mb-2">📱</div>
              <p class="text-sm font-medium text-gray-900">QR Code</p>
            </Link>
          </div>
        </div>
      </div>
    </div>
  </VendorLayout>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import VendorLayout from '@/layouts/vendor/VendorLayout.vue'
import { ref, onMounted, onUnmounted } from 'vue'

const page = usePage()
const vendorId = ref(null)

const stats = ref({
  today_orders: 0,
  today_revenue: 0,
  pending_orders: 0,
  accepted_orders: 0,
  completed_orders: 0,
  total_orders: 0,
  this_week_orders: 0,
  this_month_orders: 0
})

const pendingOrders = ref([])
const loadingStats = ref(false)

const formatNumber = (num) => {
  return (num || 0).toLocaleString()
}

const formatTime = (dateString) => {
  if (!dateString) return ''
  return new Date(dateString).toLocaleTimeString('en-PH', {
    hour: '2-digit',
    minute: '2-digit'
  })
}

const loadStats = async () => {
  loadingStats.value = true
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
    console.error('Error fetching stats:', error)
  } finally {
    loadingStats.value = false
  }
}

const loadPendingOrders = async () => {
  try {
    const response = await fetch('/api/vendor/orders?status=pending&per_page=5', {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json'
      }
    })

    if (response.ok) {
      const data = await response.json()
      pendingOrders.value = data.orders || []
    }
  } catch (error) {
    console.error('Error fetching pending orders:', error)
  }
}

const refreshStats = async () => {
  await Promise.all([loadStats(), loadPendingOrders()])
}

// Real-time subscription for stats updates
const subscribeToChannel = () => {
  if (window.Echo && vendorId.value) {
    window.Echo.private(`vendor-orders.${vendorId.value}`)
      .listen('.OrderReceived', () => {
        console.log('Dashboard: New order received, refreshing stats')
        refreshStats()
      })
      .listen('.OrderStatusChanged', () => {
        console.log('Dashboard: Order status changed, refreshing stats')
        refreshStats()
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

  await refreshStats()

  // Subscribe to real-time updates
  subscribeToChannel()
})

onUnmounted(() => {
  unsubscribeFromChannel()
})
</script>
