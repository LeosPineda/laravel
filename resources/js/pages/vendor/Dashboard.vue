<template>
  <VendorLayout>
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
      <div class="px-4 sm:px-6 py-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
            <p class="text-gray-600 mt-1">Overview of your store performance</p>
          </div>
          <button
            @click="refreshStats"
            class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg flex items-center gap-2 transition-colors"
          >
            <svg class="w-5 h-5" :class="{ 'animate-spin': loadingStats }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <span class="hidden sm:inline">Refresh</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Dashboard Content -->
    <div class="px-4 sm:px-6 py-6">
        <!-- Quick Actions Only -->
        <div>
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h2>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <Link href="/vendor/orders" class="p-6 bg-gray-50 rounded-xl border border-gray-200 hover:bg-gray-100 text-center">
              <div class="text-3xl mb-3">📦</div>
              <p class="text-sm font-medium text-gray-900">Orders</p>
            </Link>
            <Link href="/vendor/products" class="p-6 bg-gray-50 rounded-xl border border-gray-200 hover:bg-gray-100 text-center">
              <div class="text-3xl mb-3">🍔</div>
              <p class="text-sm font-medium text-gray-900">Products</p>
            </Link>
            <Link href="/vendor/analytics" class="p-6 bg-gray-50 rounded-xl border border-gray-200 hover:bg-gray-100 text-center">
              <div class="text-3xl mb-3">📊</div>
              <p class="text-sm font-medium text-gray-900">Analytics</p>
            </Link>
            <Link href="/vendor/qr" class="p-6 bg-gray-50 rounded-xl border border-gray-200 hover:bg-gray-100 text-center">
              <div class="text-3xl mb-3">📱</div>
              <p class="text-sm font-medium text-gray-900">QR Code</p>
            </Link>
          </div>
        </div>
      </div>
  </VendorLayout>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import VendorLayout from '@/layouts/vendor/VendorLayout.vue'
import { ref, onMounted, onUnmounted } from 'vue'
import { apiGet } from '@/composables/useApi'

const page = usePage()
const vendorId = ref(null)

const loadingStats = ref(false)

const refreshStats = async () => {
  loadingStats.value = true
  // Optional: you can remove this entirely if you want
  loadingStats.value = false
}

// Note: Notifications are now handled by VendorLayout.vue (appears on all pages)
// Dashboard only handles stats refresh since it's the stats display page
onMounted(async () => {
  // Get vendor ID from user data
  const user = page.props.auth?.user
  vendorId.value = user?.vendor?.id || null
})
</script>
