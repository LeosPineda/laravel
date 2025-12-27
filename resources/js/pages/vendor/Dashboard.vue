<template>
  <VendorLayout>
    <div class="min-h-screen bg-white">
      <!-- Header -->
      <div class="bg-white border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-orange-500 flex items-center justify-center">
              <span class="text-white font-bold">🍔</span>
            </div>
            <h1 class="text-xl font-bold text-gray-900">Dashboard</h1>
          </div>
          <button class="p-2 text-gray-600 hover:text-gray-900">
            <span class="text-lg">🔔</span>
          </button>
        </div>
      </div>

      <!-- Dashboard Stats -->
      <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
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
              <div class="w-10 h-10 rounded-lg bg-orange-50 flex items-center justify-center">
                <span class="text-orange-500">💰</span>
              </div>
              <div>
                <p class="text-2xl font-bold text-gray-900">₱{{ (stats.today_revenue || 0).toLocaleString() }}</p>
                <p class="text-sm text-gray-500">Today's Revenue</p>
              </div>
            </div>
          </div>

          <!-- Pending Orders -->
          <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-lg bg-orange-50 flex items-center justify-center">
                <span class="text-orange-500">⏳</span>
              </div>
              <div>
                <p class="text-2xl font-bold text-gray-900">{{ stats.pending_orders || 0 }}</p>
                <p class="text-sm text-gray-500">Pending Orders</p>
              </div>
            </div>
          </div>

          <!-- Total Orders -->
          <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-lg bg-orange-50 flex items-center justify-center">
                <span class="text-orange-500">📊</span>
              </div>
              <div>
                <p class="text-2xl font-bold text-gray-900">{{ stats.total_orders || 0 }}</p>
                <p class="text-sm text-gray-500">Total Orders</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="mt-8">
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
import { Link } from '@inertiajs/vue3'
import VendorLayout from '@/layouts/vendor/VendorLayout.vue'
import { ref, onMounted } from 'vue'

const stats = ref({
  today_orders: 0,
  today_revenue: 0,
  pending_orders: 0,
  total_orders: 0
})

onMounted(async () => {
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
  }
})
</script>
