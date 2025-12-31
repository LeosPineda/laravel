<template>
  <div class="min-h-screen bg-[#F5F5F5]">
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
            <!-- 🔔 FIXED: Now vendor relationship is loaded properly -->
            <NotificationBell
              v-if="user?.vendor?.id"
              :vendor-id="user.vendor.id"
            />

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
        <nav class="flex justify-between py-3">
          <Link
            href="/vendor/dashboard"
            class="flex flex-col items-center py-2 px-3 hover:bg-[#F5F5F5] rounded-lg transition-colors"
          >
            <span class="text-xl">🏠</span>
            <span
              class="text-xs mt-1 font-medium"
              :class="$page.url.startsWith('/vendor/dashboard') ? 'text-[#FF6B35]' : 'text-[#1A1A1A]/60'"
            >
              Dashboard
            </span>
          </Link>

          <Link
            href="/vendor/orders"
            class="flex flex-col items-center py-2 px-3 hover:bg-[#F5F5F5] rounded-lg transition-colors"
          >
            <span class="text-xl">📦</span>
            <span
              class="text-xs mt-1 font-medium"
              :class="$page.url.startsWith('/vendor/orders') ? 'text-[#FF6B35]' : 'text-[#1A1A1A]/60'"
            >
              Orders
            </span>
          </Link>

          <Link
            href="/vendor/products"
            class="flex flex-col items-center py-2 px-3 hover:bg-[#F5F5F5] rounded-lg transition-colors"
          >
            <span class="text-xl">🍔</span>
            <span
              class="text-xs mt-1 font-medium"
              :class="$page.url.startsWith('/vendor/products') ? 'text-[#FF6B35]' : 'text-[#1A1A1A]/60'"
            >
              Products
            </span>
          </Link>

          <Link
            href="/vendor/analytics"
            class="flex flex-col items-center py-2 px-3 hover:bg-[#F5F5F5] rounded-lg transition-colors"
          >
            <span class="text-xl">📊</span>
            <span
              class="text-xs mt-1 font-medium"
              :class="$page.url.startsWith('/vendor/analytics') ? 'text-[#FF6B35]' : 'text-[#1A1A1A]/60'"
            >
              Analytics
            </span>
          </Link>

          <Link
            href="/vendor/qr"
            class="flex flex-col items-center py-2 px-3 hover:bg-[#F5F5F5] rounded-lg transition-colors"
          >
            <span class="text-xl">📱</span>
            <span
              class="text-xs mt-1 font-medium"
              :class="$page.url.startsWith('/vendor/qr') ? 'text-[#FF6B35]' : 'text-[#1A1A1A]/60'"
            >
              QR Code
            </span>
          </Link>
        </nav>
      </div>
    </div>

    <!-- Main Content -->
    <main class="flex-1 px-4 sm:px-6 py-4">
      <slot />
    </main>

    <!-- Toast Notifications -->
    <ToastContainer />
  </div>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import { usePage } from '@inertiajs/vue3'
import ToastContainer from '@/components/ui/ToastContainer.vue'
// 🔔 Import NotificationBell component
import NotificationBell from '@/components/NotificationBell.vue'

const page = usePage()
const user = page.props.auth?.user

const logout = () => {
  router.post('/logout')
}
</script>

<style scoped>
/* No additional styles needed - using Tailwind CSS classes */
</style>
