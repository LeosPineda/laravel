<template>
  <div class="flex min-h-screen bg-gray-50">
    <!-- Desktop Sidebar -->
    <div class="hidden md:flex md:w-64 md:flex-col">
      <div class="flex flex-col flex-grow pt-5 overflow-y-auto bg-white border-r border-gray-200">
        <!-- Logo -->
        <div class="flex items-center flex-shrink-0 px-4">
          <div class="w-10 h-10 rounded-lg bg-orange-500 flex items-center justify-center">
            <span class="text-white font-bold">🍔</span>
          </div>
          <span class="ml-3 text-lg font-semibold text-gray-900">Vendor Portal</span>
        </div>

        <!-- Navigation -->
        <div class="mt-8 flex-grow flex flex-col">
          <nav class="flex-1 px-3 space-y-2">
            <Link href="/vendor/dashboard"
                  :class="[$page.url.startsWith('/vendor/dashboard')
                          ? 'bg-orange-50 text-orange-700 border-l-4 border-orange-500'
                          : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 border-l-4 border-transparent',
                          'group flex items-center px-4 py-3 text-base font-medium rounded-lg transition-all']">
              <span class="mr-4 text-xl">🏠</span>
              Dashboard
            </Link>

            <Link href="/vendor/orders"
                  :class="[$page.url.startsWith('/vendor/orders')
                          ? 'bg-orange-50 text-orange-700 border-l-4 border-orange-500'
                          : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 border-l-4 border-transparent',
                          'group flex items-center px-4 py-3 text-base font-medium rounded-lg transition-all']">
              <span class="mr-4 text-xl">📦</span>
              Orders
            </Link>

            <Link href="/vendor/products"
                  :class="[$page.url.startsWith('/vendor/products')
                          ? 'bg-orange-50 text-orange-700 border-l-4 border-orange-500'
                          : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 border-l-4 border-transparent',
                          'group flex items-center px-4 py-3 text-base font-medium rounded-lg transition-all']">
              <span class="mr-4 text-xl">🍔</span>
              Products
            </Link>

            <Link href="/vendor/analytics"
                  :class="[$page.url.startsWith('/vendor/analytics')
                          ? 'bg-orange-50 text-orange-700 border-l-4 border-orange-500'
                          : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 border-l-4 border-transparent',
                          'group flex items-center px-4 py-3 text-base font-medium rounded-lg transition-all']">
              <span class="mr-4 text-xl">📊</span>
              Analytics
            </Link>

            <Link href="/vendor/qr"
                  :class="[$page.url.startsWith('/vendor/qr')
                          ? 'bg-orange-50 text-orange-700 border-l-4 border-orange-500'
                          : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 border-l-4 border-transparent',
                          'group flex items-center px-4 py-3 text-base font-medium rounded-lg transition-all']">
              <span class="mr-4 text-xl">📱</span>
              QR Code
            </Link>
          </nav>

          <!-- Logout -->
          <div class="flex-shrink-0 border-t border-gray-200 p-4">
            <button @click="logout"
                    class="w-full flex items-center px-4 py-3 text-base font-medium text-red-600 hover:bg-red-50 rounded-lg transition-all">
              <span class="mr-4 text-xl">🚪</span>
              Logout
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="flex flex-col flex-1 overflow-hidden">
      <!-- Mobile Header -->
      <div class="md:hidden bg-white border-b border-gray-200 px-4 py-3">
        <div class="flex items-center gap-3">
          <div class="w-8 h-8 rounded-lg bg-orange-500 flex items-center justify-center">
            <span class="text-white text-sm">🍔</span>
          </div>
          <span class="font-semibold text-gray-900">Vendor Portal</span>
        </div>
      </div>

      <!-- Page Content -->
      <main class="flex-1 overflow-y-auto">
        <slot />
      </main>

      <!-- Mobile Bottom Navigation -->
      <div class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 z-50">
        <div class="grid grid-cols-5 py-2">
          <Link href="/vendor/dashboard" class="flex flex-col items-center py-2">
            <span class="text-sm">🏠</span>
            <span class="text-xs mt-1" :class="$page.url.startsWith('/vendor/dashboard') ? 'text-orange-600' : 'text-gray-500'">Home</span>
          </Link>
          <Link href="/vendor/orders" class="flex flex-col items-center py-2">
            <span class="text-sm">📦</span>
            <span class="text-xs mt-1" :class="$page.url.startsWith('/vendor/orders') ? 'text-orange-600' : 'text-gray-500'">Orders</span>
          </Link>
          <Link href="/vendor/products" class="flex flex-col items-center py-2">
            <span class="text-sm">🍔</span>
            <span class="text-xs mt-1" :class="$page.url.startsWith('/vendor/products') ? 'text-orange-600' : 'text-gray-500'">Products</span>
          </Link>
          <Link href="/vendor/analytics" class="flex flex-col items-center py-2">
            <span class="text-sm">📊</span>
            <span class="text-xs mt-1" :class="$page.url.startsWith('/vendor/analytics') ? 'text-orange-600' : 'text-gray-500'">Analytics</span>
          </Link>
          <Link href="/vendor/qr" class="flex flex-col items-center py-2">
            <span class="text-sm">📱</span>
            <span class="text-xs mt-1" :class="$page.url.startsWith('/vendor/qr') ? 'text-orange-600' : 'text-gray-500'">QR</span>
          </Link>
        </div>
      </div>
    </div>

    <!-- Toast Notifications -->
    <ToastContainer />
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import ToastContainer from '@/components/ui/ToastContainer.vue'

const logout = async () => {
  try {
    await fetch('/api/logout', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json'
      }
    })
    localStorage.removeItem('token')
    window.location.href = '/login'
  } catch (error) {
    console.error('Logout error:', error)
  }
}
</script>

<style scoped>
/* Add padding to main content to account for mobile bottom nav - mobile only */
@media (max-width: 767px) {
  main {
    padding-bottom: 4rem;
  }
}

/* Desktop: no extra padding needed */
@media (min-width: 768px) {
  main {
    padding-bottom: 0;
  }
}
</style>
