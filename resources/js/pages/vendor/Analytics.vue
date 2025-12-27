<template>
  <VendorLayout>
    <div class="min-h-screen bg-white">
      <!-- Header -->
      <div class="bg-white border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between">
          <h1 class="text-xl font-bold text-gray-900">Analytics Dashboard</h1>
          <div class="flex gap-2">
            <select
              v-model="selectedPeriod"
              @change="loadAllData"
              class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
            >
              <option value="today">Today</option>
              <option value="week">This Week</option>
              <option value="month">This Month</option>
              <option value="year">This Year</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Analytics Content -->
      <div class="p-6">
        <div class="max-w-6xl mx-auto">
          <!-- Loading State -->
          <div v-if="loading" class="text-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-orange-500 mx-auto"></div>
            <p class="text-gray-500 mt-4">Loading analytics...</p>
          </div>

          <div v-else>
            <!-- Sales Overview -->
            <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
              <h2 class="text-lg font-semibold text-gray-900 mb-4">Sales Overview</h2>

              <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-orange-50 rounded-lg p-4">
                  <div class="text-2xl font-bold text-orange-600">₱{{ (sales.total_sales || 0).toLocaleString() }}</div>
                  <div class="text-sm text-orange-700">Total Sales ({{ selectedPeriod }})</div>
                </div>

                <div class="bg-blue-50 rounded-lg p-4">
                  <div class="text-2xl font-bold text-blue-600">{{ sales.total_orders || 0 }}</div>
                  <div class="text-sm text-blue-700">Total Orders ({{ selectedPeriod }})</div>
                </div>

                <div class="bg-green-50 rounded-lg p-4">
                  <div class="text-2xl font-bold text-green-600">₱{{ (sales.average_order || 0).toFixed(0) }}</div>
                  <div class="text-sm text-green-700">Average Order Value</div>
                </div>

                <div class="bg-purple-50 rounded-lg p-4">
                  <div class="text-2xl font-bold text-purple-600">₱{{ (revenue.total_revenue || 0).toLocaleString() }}</div>
                  <div class="text-sm text-purple-700">All Time Revenue</div>
                </div>
              </div>
            </div>

            <!-- Order Metrics -->
            <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
              <h2 class="text-lg font-semibold text-gray-900 mb-4">Order Status Metrics</h2>

              <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-yellow-50 rounded-lg p-4 text-center">
                  <div class="text-2xl font-bold text-yellow-600">{{ orderMetrics.pending || 0 }}</div>
                  <div class="text-sm text-yellow-700">Pending</div>
                </div>

                <div class="bg-blue-50 rounded-lg p-4 text-center">
                  <div class="text-2xl font-bold text-blue-600">{{ orderMetrics.accepted || 0 }}</div>
                  <div class="text-sm text-blue-700">Accepted</div>
                </div>

                <div class="bg-green-50 rounded-lg p-4 text-center">
                  <div class="text-2xl font-bold text-green-600">{{ orderMetrics.ready_for_pickup || 0 }}</div>
                  <div class="text-sm text-green-700">Ready</div>
                </div>

                <div class="bg-red-50 rounded-lg p-4 text-center">
                  <div class="text-2xl font-bold text-red-600">{{ orderMetrics.cancelled || 0 }}</div>
                  <div class="text-sm text-red-700">Cancelled</div>
                </div>
              </div>
            </div>

            <!-- Revenue Breakdown -->
            <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
              <h2 class="text-lg font-semibold text-gray-900 mb-4">Revenue Breakdown</h2>

              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-green-50 rounded-lg p-4">
                  <div class="text-2xl font-bold text-green-600">₱{{ (revenue.period_revenue || 0).toLocaleString() }}</div>
                  <div class="text-sm text-green-700">{{ selectedPeriod }} Revenue</div>
                </div>

                <div class="bg-blue-50 rounded-lg p-4">
                  <div class="text-2xl font-bold text-blue-600">₱{{ (profit.total_revenue || 0).toLocaleString() }}</div>
                  <div class="text-sm text-blue-700">Total Revenue</div>
                </div>

                <div class="bg-purple-50 rounded-lg p-4">
                  <div class="text-2xl font-bold text-purple-600">₱{{ (profit.net_profit || 0).toLocaleString() }}</div>
                  <div class="text-sm text-purple-700">Net Profit</div>
                </div>
              </div>

              <!-- Profit Details -->
              <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                <div class="grid grid-cols-2 gap-4 text-sm">
                  <div>
                    <span class="text-gray-600">Total Revenue:</span>
                    <span class="font-semibold ml-2">₱{{ (profit.total_revenue || 0).toLocaleString() }}</span>
                  </div>
                  <div>
                    <span class="text-gray-600">Rent Cost:</span>
                    <span class="font-semibold ml-2">₱{{ (profit.rent_cost || 0).toLocaleString() }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Best Selling Products -->
            <div class="bg-white rounded-xl border border-gray-200 p-6">
              <h2 class="text-lg font-semibold text-gray-900 mb-4">Best Selling Products</h2>

              <div v-if="bestSellers.length === 0" class="text-center py-8">
                <div class="text-4xl mb-4">📊</div>
                <p class="text-gray-500">No sales data available yet</p>
              </div>

              <div v-else class="space-y-3">
                <div
                  v-for="(product, index) in bestSellers"
                  :key="product.product_id"
                  class="flex items-center justify-between p-4 bg-gray-50 rounded-lg"
                >
                  <div class="flex items-center gap-4">
                    <div class="w-8 h-8 bg-orange-500 text-white rounded-full flex items-center justify-center font-bold">
                      {{ index + 1 }}
                    </div>
                    <div>
                      <div class="font-medium text-gray-900">{{ product.product?.name || 'Unknown Product' }}</div>
                      <div class="text-sm text-gray-500">₱{{ (product.product?.price || 0).toLocaleString() }}</div>
                    </div>
                  </div>

                  <div class="text-right">
                    <div class="text-lg font-bold text-gray-900">{{ product.total_sold || 0 }}</div>
                    <div class="text-sm text-gray-500">sold</div>
                  </div>
                </div>
              </div>
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

const selectedPeriod = ref('week')
const loading = ref(false)

const sales = ref({
  total_sales: 0,
  total_orders: 0,
  average_order: 0
})

const orderMetrics = ref({
  pending: 0,
  accepted: 0,
  ready_for_pickup: 0,
  cancelled: 0
})

const revenue = ref({
  period_revenue: 0,
  total_revenue: 0
})

const profit = ref({
  total_revenue: 0,
  rent_cost: 3000,
  net_profit: 0
})

const bestSellers = ref([])

const loadSales = async () => {
  try {
    const response = await fetch(`/api/vendor/analytics/sales?period=${selectedPeriod.value}`, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json'
      }
    })

    if (response.ok) {
      const data = await response.json()
      sales.value = data
    }
  } catch (error) {
    console.error('Error loading sales:', error)
  }
}

const loadOrderMetrics = async () => {
  try {
    const response = await fetch('/api/vendor/analytics/order-metrics', {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json'
      }
    })

    if (response.ok) {
      const data = await response.json()
      orderMetrics.value = data
    }
  } catch (error) {
    console.error('Error loading order metrics:', error)
  }
}

const loadRevenue = async () => {
  try {
    const response = await fetch(`/api/vendor/analytics/revenue?period=${selectedPeriod.value}`, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json'
      }
    })

    if (response.ok) {
      const data = await response.json()
      revenue.value = data
    }
  } catch (error) {
    console.error('Error loading revenue:', error)
  }
}

const loadProfit = async () => {
  try {
    const response = await fetch('/api/vendor/analytics/profit', {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json'
      }
    })

    if (response.ok) {
      const data = await response.json()
      profit.value = data
    }
  } catch (error) {
    console.error('Error loading profit:', error)
  }
}

const loadBestSellers = async () => {
  try {
    const response = await fetch('/api/vendor/analytics/best-sellers?limit=10', {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json'
      }
    })

    if (response.ok) {
      const data = await response.json()
      bestSellers.value = data.best_sellers || []
    }
  } catch (error) {
    console.error('Error loading best sellers:', error)
  }
}

const loadAllData = async () => {
  loading.value = true
  try {
    await Promise.all([
      loadSales(),
      loadOrderMetrics(),
      loadRevenue(),
      loadProfit(),
      loadBestSellers()
    ])
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  await loadAllData()
})
</script>
