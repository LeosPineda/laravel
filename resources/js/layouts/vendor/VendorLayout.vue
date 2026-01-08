<template>
  <div class="min-h-screen bg-[#F5F5F5] flex flex-col">
    <!-- Header -->
    <header class="bg-white border-b border-[#E0E0E0] sticky top-0 z-50">
      <div class="px-4 sm:px-6">
        <div class="flex justify-between items-center h-16">
          <!-- Logo & Brand -->
          <div class="flex items-center gap-8">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 bg-[#FF6B35] rounded-xl flex items-center justify-center">
                <span class="text-white text-lg">🍔</span>
              </div>
              <span class="text-lg font-bold text-[#1A1A1A] hidden sm:block">Food Court Vendor</span>
            </div>

            <!-- Desktop Navigation -->
            <nav class="hidden md:flex items-center gap-1">
              <Link
                href="/vendor/dashboard"
                :class="[
                  $page.url.startsWith('/vendor/dashboard')
                    ? 'text-white bg-[#FF6B35]'
                    : 'text-[#1A1A1A]/70 hover:text-[#1A1A1A] hover:bg-[#F5F5F5]',
                  'px-4 py-2 text-sm font-medium rounded-lg transition-colors'
                ]"
              >
                Dashboard
              </Link>
              <Link
                href="/vendor/orders"
                :class="[
                  $page.url.startsWith('/vendor/orders')
                    ? 'text-white bg-[#FF6B35]'
                    : 'text-[#1A1A1A]/70 hover:text-[#1A1A1A] hover:bg-[#F5F5F5]',
                  'px-4 py-2 text-sm font-medium rounded-lg transition-colors'
                ]"
              >
                Orders
              </Link>
              <Link
                href="/vendor/products"
                :class="[
                  $page.url.startsWith('/vendor/products')
                    ? 'text-white bg-[#FF6B35]'
                    : 'text-[#1A1A1A]/70 hover:text-[#1A1A1A] hover:bg-[#F5F5F5]',
                  'px-4 py-2 text-sm font-medium rounded-lg transition-colors'
                ]"
              >
                Products
              </Link>
              <Link
                href="/vendor/analytics"
                :class="[
                  $page.url.startsWith('/vendor/analytics')
                    ? 'text-white bg-[#FF6B35]'
                    : 'text-[#1A1A1A]/70 hover:text-[#1A1A1A] hover:bg-[#F5F5F5]',
                  'px-4 py-2 text-sm font-medium rounded-lg transition-colors'
                ]"
              >
                Analytics
              </Link>
              <Link
                href="/vendor/qr"
                :class="[
                  $page.url.startsWith('/vendor/qr')
                    ? 'text-white bg-[#FF6B35]'
                    : 'text-[#1A1A1A]/70 hover:text-[#1A1A1A] hover:bg-[#F5F5F5]',
                  'px-4 py-2 text-sm font-medium rounded-lg transition-colors'
                ]"
              >
                QR Code
              </Link>
            </nav>
          </div>

          <!-- Right side -->
          <div class="flex items-center gap-4">
            <!-- Sound toggle button -->
            <button
              @click="toggleSound"
              class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-[#1A1A1A]/70 hover:text-[#1A1A1A] hover:bg-[#F5F5F5] rounded-lg transition-colors"
              :title="isSoundEnabled ? 'Sound ON' : 'Sound OFF'"
            >
              <svg v-if="isSoundEnabled" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
              </svg>
              <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2" />
              </svg>
              <span class="hidden sm:inline">{{ isSoundEnabled ? 'Sound ON' : 'Sound OFF' }}</span>
            </button>

            <!-- Logout -->
            <button
              @click="logout"
              class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-[#1A1A1A]/70 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
              </svg>
              <span class="hidden sm:inline">Logout</span>
            </button>
          </div>
        </div>
      </div>
    </header>

    <!-- Mobile Navigation -->
    <div class="md:hidden border-t border-[#E0E0E0] bg-white">
      <div class="px-4 sm:px-6">
        <nav class="flex justify-between py-3 overflow-x-auto">
          <Link
            href="/vendor/dashboard"
            class="flex flex-col items-center py-2 px-2 hover:bg-[#F5F5F5] rounded-lg transition-colors min-w-0 flex-1"
          >
            <span class="text-xl">🏠</span>
            <span
              class="text-xs mt-1 font-medium truncate"
              :class="$page.url.startsWith('/vendor/dashboard') ? 'text-[#FF6B35]' : 'text-[#1A1A1A]/60'"
            >
              Dashboard
            </span>
          </Link>

          <Link
            href="/vendor/orders"
            class="flex flex-col items-center py-2 px-2 hover:bg-[#F5F5F5] rounded-lg transition-colors min-w-0 flex-1"
          >
            <span class="text-xl">📦</span>
            <span
              class="text-xs mt-1 font-medium truncate"
              :class="$page.url.startsWith('/vendor/orders') ? 'text-[#FF6B35]' : 'text-[#1A1A1A]/60'"
            >
              Orders
            </span>
          </Link>

          <Link
            href="/vendor/products"
            class="flex flex-col items-center py-2 px-2 hover:bg-[#F5F5F5] rounded-lg transition-colors min-w-0 flex-1"
          >
            <span class="text-xl">🍔</span>
            <span
              class="text-xs mt-1 font-medium truncate"
              :class="$page.url.startsWith('/vendor/products') ? 'text-[#FF6B35]' : 'text-[#1A1A1A]/60'"
            >
              Products
            </span>
          </Link>

          <Link
            href="/vendor/analytics"
            class="flex flex-col items-center py-2 px-2 hover:bg-[#F5F5F5] rounded-lg transition-colors min-w-0 flex-1"
          >
            <span class="text-xl">📊</span>
            <span
              class="text-xs mt-1 font-medium truncate"
              :class="$page.url.startsWith('/vendor/analytics') ? 'text-[#FF6B35]' : 'text-[#1A1A1A]/60'"
            >
              Analytics
            </span>
          </Link>

          <Link
            href="/vendor/qr"
            class="flex flex-col items-center py-2 px-2 hover:bg-[#F5F5F5] rounded-lg transition-colors min-w-0 flex-1"
          >
            <span class="text-xl">📱</span>
            <span
              class="text-xs mt-1 font-medium truncate"
              :class="$page.url.startsWith('/vendor/qr') ? 'text-[#FF6B35]' : 'text-[#1A1A1A]/60'"
            >
              QR Code
            </span>
          </Link>
        </nav>
      </div>
    </div>

    <!-- Main Content - Scrollable -->
    <main class="flex-1 px-4 sm:px-6 lg:px-8 py-4 sm:py-6 overflow-y-auto">
      <div class="max-w-7xl mx-auto">
        <slot />
      </div>
    </main>

    <!-- Toast Notifications -->
    <ToastContainer />
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { usePage } from '@inertiajs/vue3'
import { useToast } from '@/composables/useToast'
import { useSound } from '@/composables/useSound'
import ToastContainer from '@/components/ui/ToastContainer.vue'

const page = usePage()
const { newOrder: toastNewOrder, vendorError: toastVendorError } = useToast()
const { isSoundEnabled, toggleSound } = useSound()
const vendorId = ref(null)

const logout = () => {
  router.post('/logout')
}

// VENDOR REAL-TIME TOAST NOTIFICATIONS - SINGLE SOURCE OF TRUTH
const setupVendorToastNotifications = () => {
  const user = page.props.auth?.user
  vendorId.value = user?.vendor?.id || null

  if (!vendorId.value || !window.Echo) {
    return
  }

  try {
    // Listen on vendor-toasts channel for all order notifications
    const toastChannel = window.Echo.private(`vendor-toasts.${vendorId.value}`)

    // Listen on vendor-orders channel for order list updates
    const ordersChannel = window.Echo.private(`vendor-orders.${vendorId.value}`)

    // Toast notifications (new orders and cancellations)
    toastChannel.listen('.VendorNewOrder', (e) => {
      toastNewOrder(`📦 New order #${e.order?.order_number || ''} received!`, 30000)
    })
    .listen('.OrderStatusChanged', (e) => {
      // Handle customer cancellations with error toast (plays sound)
      if (e.order?.new_status === 'cancelled' || e.order?.status === 'cancelled') {
        toastVendorError(`❌ Order #${e.order?.order_number || ''} was cancelled by customer`, 20000)
      }
    })
    .error((error) => {
      // Silent error handling
    })

    // Order list updates (accept, ready, etc.)
    ordersChannel.listen('.OrderStatusChanged', (e) => {
      // FIXED: Auto-refresh order page when status changes occur
      if (page.url.includes('/vendor/orders') || page.url.includes('/vendor/incoming-orders')) {
        router.reload()
      }
    })
    .error((error) => {
      // Silent error handling
    })

  } catch (error) {
    // Silent error handling - don't log to console
  }
}

const cleanupVendorChannel = () => {
  if (vendorId.value && window.Echo) {
    try {
      window.Echo.leave(`vendor-toasts.${vendorId.value}`)
      window.Echo.leave(`vendor-orders.${vendorId.value}`)
    } catch (error) {
      // Silent error handling - don't log to console
    }
  }
}

onMounted(() => {
  setupVendorToastNotifications()
})

onUnmounted(() => {
  cleanupVendorChannel()
})
</script>
