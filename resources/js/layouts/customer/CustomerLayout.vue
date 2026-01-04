<template>
  <div class="min-h-screen bg-[#F5F5F5]">
    <!-- Header - BIGGER & MORE VISIBLE -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-50 shadow-sm">
      <div class="px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16 lg:h-20">
          <!-- Logo & Brand -->
          <div class="flex items-center gap-4 lg:gap-6">
            <div class="flex items-center gap-3">
              <!-- Logo - Bigger -->
              <div class="w-10 h-10 lg:w-12 lg:h-12 bg-gradient-to-br from-[#FF6B35] to-[#FF8C5A] rounded-xl flex items-center justify-center shadow-md">
                <span class="text-white text-xl lg:text-2xl">🍴</span>
              </div>
              <!-- Brand Name - Bigger -->
              <span class="text-xl lg:text-2xl font-bold text-gray-900 hidden sm:block">Food Court</span>
            </div>

            <!-- Desktop Navigation - BIGGER -->
            <nav class="hidden lg:flex items-center gap-2 ml-8">
              <Link
                href="/customer/browse"
                :class="[
                  $page.url.startsWith('/customer/browse')
                    ? 'text-white bg-[#FF6B35] shadow-md'
                    : 'text-gray-700 hover:text-[#FF6B35] hover:bg-orange-50',
                  'px-5 py-2.5 text-base font-semibold rounded-xl transition-all'
                ]"
              >
                <span class="flex items-center gap-2">
                  <span class="text-lg">🔍</span>
                  Browse
                </span>
              </Link>
              <Link
                href="/customer/cart"
                :class="[
                  $page.url.startsWith('/customer/cart')
                    ? 'text-white bg-[#FF6B35] shadow-md'
                    : 'text-gray-700 hover:text-[#FF6B35] hover:bg-orange-50',
                  'px-5 py-2.5 text-base font-semibold rounded-xl transition-all relative'
                ]"
              >
                <span class="flex items-center gap-2">
                  <span class="text-lg">🛒</span>
                  Cart
                  <!-- Cart Badge - BIGGER -->
                  <span
                    v-if="cartItemCount > 0"
                    class="absolute -top-2 -right-2 bg-red-500 text-white text-sm font-bold rounded-full h-6 min-w-6 px-1 flex items-center justify-center shadow-md"
                  >
                    {{ cartItemCount > 99 ? '99+' : cartItemCount }}
                  </span>
                </span>
              </Link>

              <Link
                href="/customer/profile"
                :class="[
                  $page.url.startsWith('/customer/profile')
                    ? 'text-white bg-[#FF6B35] shadow-md'
                    : 'text-gray-700 hover:text-[#FF6B35] hover:bg-orange-50',
                  'px-5 py-2.5 text-base font-semibold rounded-xl transition-all'
                ]"
              >
                <span class="flex items-center gap-2">
                  <span class="text-lg">👤</span>
                  Profile
                </span>
              </Link>
            </nav>
          </div>

          <!-- Right side - BIGGER -->
          <div class="flex items-center gap-3 lg:gap-4">
            <!-- 🔔 Customer Notification Bell -->
            <CustomerNotificationBell v-if="user?.id" :user-id="user.id" />

            <!-- Logout Button - BIGGER -->
            <button
              @click="logout"
              class="flex items-center gap-2 px-4 py-2.5 text-base font-medium text-gray-600 hover:text-red-600 hover:bg-red-50 rounded-xl transition-colors"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

    <!-- Main Content - Full width on desktop, proper padding -->
    <main class="flex-1 px-4 sm:px-6 lg:px-8 xl:px-12 py-4 sm:py-6 pb-24 md:pb-6">
      <slot />
    </main>

    <!-- MOBILE: Fixed Bottom Navigation Bar (like native app) -->
    <nav class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-[#E0E0E0] z-50 safe-area-bottom">
      <div class="flex justify-around items-center h-16">
        <Link
          href="/customer/browse"
          class="flex flex-col items-center justify-center flex-1 py-2 active:scale-95 transition-transform"
        >
          <span
            class="text-2xl"
            :class="$page.url.startsWith('/customer/browse') ? '' : 'grayscale opacity-60'"
          >🔍</span>
          <span
            class="text-xs mt-0.5 font-medium"
            :class="$page.url.startsWith('/customer/browse') ? 'text-[#FF6B35]' : 'text-[#1A1A1A]/50'"
          >
            Browse
          </span>
        </Link>

        <Link
          href="/customer/cart"
          class="flex flex-col items-center justify-center flex-1 py-2 relative active:scale-95 transition-transform"
        >
          <span
            class="text-2xl"
            :class="$page.url.startsWith('/customer/cart') ? '' : 'grayscale opacity-60'"
          >🛒</span>
          <span
            class="text-xs mt-0.5 font-medium"
            :class="$page.url.startsWith('/customer/cart') ? 'text-[#FF6B35]' : 'text-[#1A1A1A]/50'"
          >
            Cart
          </span>
          <!-- Cart Badge -->
          <span
            v-if="cartItemCount > 0"
            class="absolute top-1 left-1/2 ml-2 bg-red-500 text-white text-xs rounded-full h-5 min-w-5 px-1 flex items-center justify-center font-bold"
          >
            {{ cartItemCount > 99 ? '99+' : cartItemCount }}
          </span>
        </Link>

        <Link
          href="/customer/profile"
          class="flex flex-col items-center justify-center flex-1 py-2 active:scale-95 transition-transform"
        >
          <span
            class="text-2xl"
            :class="$page.url.startsWith('/customer/profile') ? '' : 'grayscale opacity-60'"
          >👤</span>
          <span
            class="text-xs mt-0.5 font-medium"
            :class="$page.url.startsWith('/customer/profile') ? 'text-[#FF6B35]' : 'text-[#1A1A1A]/50'"
          >
            Profile
          </span>
        </Link>
      </div>
    </nav>

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
const { cartCount: cartItemCount, fetchCart } = useCart()

const logout = () => {
  router.post('/logout')
}

// Fetch cart on mount + request notification permission
onMounted(() => {
  fetchCart()
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
