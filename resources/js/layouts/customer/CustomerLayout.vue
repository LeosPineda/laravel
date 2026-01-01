<template>
  <div class="min-h-screen bg-[#F5F5F5]">
    <!-- Header - MATCHED TO VENDOR STYLING -->
    <header class="bg-white border-b border-[#E0E0E0] sticky top-0 z-50">
      <div class="px-4 sm:px-6">
        <div class="flex justify-between items-center h-16">
          <!-- Logo & Brand -->
          <div class="flex items-center gap-3 lg:gap-4">
            <div class="flex items-center gap-2">
              <!-- Changed to orange like vendor -->
              <div class="w-9 h-9 bg-[#FF6B35] rounded-xl flex items-center justify-center">
                <span class="text-white text-lg">🍴</span>
              </div>
              <!-- Changed text color to match vendor -->
              <span class="text-lg font-bold text-[#1A1A1A] hidden sm:block">Food Court Customer</span>
            </div>

            <!-- Desktop Navigation - MATCHED TO VENDOR -->
            <nav class="hidden lg:flex items-center gap-1">
              <Link
                href="/customer/browse"
                :class="[
                  $page.url.startsWith('/customer/browse')
                    ? 'text-white bg-[#FF6B35]' // Orange like vendor
                    : 'text-[#1A1A1A]/70 hover:text-[#1A1A1A] hover:bg-[#F5F5F5]', // Dark text like vendor
                  'px-4 py-2 text-sm font-medium rounded-lg transition-colors'
                ]"
              >
                <span class="flex items-center gap-2">
                  <span>🔍</span>
                  Browse
                </span>
              </Link>
              <Link
                href="/customer/cart"
                :class="[
                  $page.url.startsWith('/customer/cart')
                    ? 'text-white bg-[#FF6B35]' // Orange like vendor
                    : 'text-[#1A1A1A]/70 hover:text-[#1A1A1A] hover:bg-[#F5F5F5]', // Dark text like vendor
                  'px-4 py-2 text-sm font-medium rounded-lg transition-colors relative'
                ]"
              >
                <span class="flex items-center gap-2">
                  <span>🛒</span>
                  Cart
                  <!-- Cart Badge -->
                  <span
                    v-if="cartItemCount > 0"
                    class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"
                  >
                    {{ cartItemCount > 99 ? '99+' : cartItemCount }}
                  </span>
                </span>
              </Link>

              <Link
                href="/customer/profile"
                :class="[
                  $page.url.startsWith('/customer/profile')
                    ? 'text-white bg-[#FF6B35]' // Orange like vendor
                    : 'text-[#1A1A1A]/70 hover:text-[#1A1A1A] hover:bg-[#F5F5F5]', // Dark text like vendor
                  'px-4 py-2 text-sm font-medium rounded-lg transition-colors'
                ]"
              >
                <span class="flex items-center gap-2">
                  <span>👤</span>
                  Profile
                </span>
              </Link>
            </nav>
          </div>

          <!-- Right side - MATCHED TO VENDOR -->
          <div class="flex items-center gap-4">
            <!-- 🔔 Customer Notification Bell -->
            <CustomerNotificationBell v-if="user?.id" :user-id="user.id" />

            <!-- Logout Button - MATCHED TO VENDOR -->
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

    <!-- FIXED: Tablet Navigation (768px - 1023px) - NON-OVERLAPPING -->
    <div class="lg:hidden md:block hidden border-t border-[#E0E0E0] bg-white">
      <div class="px-4 sm:px-6">
        <nav class="flex justify-between py-3">
          <Link
            href="/customer/browse"
            class="flex flex-col items-center py-2 px-3 hover:bg-[#F5F5F5] rounded-lg transition-colors"
          >
            <span class="text-xl">🔍</span>
            <span
              class="text-xs mt-1 font-medium"
              :class="$page.url.startsWith('/customer/browse') ? 'text-[#FF6B35]' : 'text-[#1A1A1A]/60'"
            >
              Browse
            </span>
          </Link>

          <Link
            href="/customer/cart"
            class="flex flex-col items-center py-2 px-3 hover:bg-[#F5F5F5] rounded-lg transition-colors relative"
          >
            <span class="text-xl">🛒</span>
            <span
              class="text-xs mt-1 font-medium"
              :class="$page.url.startsWith('/customer/cart') ? 'text-[#FF6B35]' : 'text-[#1A1A1A]/60'"
            >
              Cart
            </span>
            <!-- Cart Badge for Tablet -->
            <span
              v-if="cartItemCount > 0"
              class="absolute top-0 right-0 -mt-1 -mr-1 bg-red-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center"
            >
              {{ cartItemCount > 9 ? '9+' : cartItemCount }}
            </span>
          </Link>



          <Link
            href="/customer/profile"
            class="flex flex-col items-center py-2 px-3 hover:bg-[#F5F5F5] rounded-lg transition-colors"
          >
            <span class="text-xl">👤</span>
            <span
              class="text-xs mt-1 font-medium"
              :class="$page.url.startsWith('/customer/profile') ? 'text-[#FF6B35]' : 'text-[#1A1A1A]/60'"
            >
              Profile
            </span>
          </Link>
        </nav>
      </div>
    </div>

    <!-- FIXED: Mobile Navigation (< 768px) - NON-OVERLAPPING -->
    <div class="md:hidden border-t border-[#E0E0E0] bg-white">
      <div class="px-4 sm:px-6">
        <nav class="flex justify-between py-2">
          <Link
            href="/customer/browse"
            class="flex flex-col items-center py-3 px-2 hover:bg-[#F5F5F5] rounded-lg transition-colors"
          >
            <span class="text-2xl">🔍</span>
            <span
              class="text-xs mt-1 font-medium"
              :class="$page.url.startsWith('/customer/browse') ? 'text-[#FF6B35]' : 'text-[#1A1A1A]/60'"
            >
              Browse
            </span>
          </Link>

          <Link
            href="/customer/cart"
            class="flex flex-col items-center py-3 px-2 hover:bg-[#F5F5F5] rounded-lg transition-colors relative"
          >
            <span class="text-2xl">🛒</span>
            <span
              class="text-xs mt-1 font-medium"
              :class="$page.url.startsWith('/customer/cart') ? 'text-[#FF6B35]' : 'text-[#1A1A1A]/60'"
            >
              Cart
            </span>
            <!-- Cart Badge for Mobile -->
            <span
              v-if="cartItemCount > 0"
              class="absolute top-1 right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"
            >
              {{ cartItemCount > 9 ? '9+' : cartItemCount }}
            </span>
          </Link>



          <Link
            href="/customer/profile"
            class="flex flex-col items-center py-3 px-2 hover:bg-[#F5F5F5] rounded-lg transition-colors"
          >
            <span class="text-2xl">👤</span>
            <span
              class="text-xs mt-1 font-medium"
              :class="$page.url.startsWith('/customer/profile') ? 'text-[#FF6B35]' : 'text-[#1A1A1A]/60'"
            >
              Profile
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
import { ref, onMounted } from 'vue'
import ToastContainer from '@/components/ui/ToastContainer.vue'
// 🔔 Import Customer NotificationBell component
import CustomerNotificationBell from '@/components/customer/CustomerNotificationBell.vue'
// 🛒 Import cart composable
import { useCart } from '@/composables/useCart'

const page = usePage()
const user = page.props.auth?.user
const { cartItemCount } = useCart()

const logout = () => {
  router.post('/logout')
}

// Request notification permission on mount
onMounted(() => {
  if ('Notification' in window && Notification.permission === 'default') {
    Notification.requestPermission()
  }
})
</script>

<style scoped>
/* Smooth transitions for all interactive elements */
* {
  transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}

/* Custom animations */
@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Badge animations */
.badge-pulse {
  animation: badge-pulse 1.5s ease-in-out infinite;
}

@keyframes badge-pulse {
  0% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.1);
  }
  100% {
    transform: scale(1);
  }
}
</style>
