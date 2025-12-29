<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-50">
      <div class="px-4 sm:px-6">
        <div class="flex justify-between items-center h-16">
          <!-- Logo & Brand -->
          <div class="flex items-center gap-8">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-orange-500 rounded-xl flex items-center justify-center">
                <span class="text-white text-lg">🍔</span>
              </div>
              <span class="text-xl font-bold text-gray-900 hidden sm:block">4Rodz Food Court</span>
            </div>

            <!-- Desktop Navigation -->
            <nav class="hidden md:flex items-center gap-1">
              <Link
                href="/customer/menu"
                :class="[
                  $page.url.startsWith('/customer/menu')
                    ? 'text-orange-500 bg-orange-50'
                    : 'text-gray-700 hover:text-gray-900 hover:bg-gray-50',
                  'px-4 py-2 text-sm font-medium rounded-lg transition-colors'
                ]"
              >
                <span class="mr-2">🏪</span>Browse
              </Link>
              <Link
                href="/customer/cart"
                :class="[
                  $page.url.startsWith('/customer/cart')
                    ? 'text-orange-500 bg-orange-50'
                    : 'text-gray-700 hover:text-gray-900 hover:bg-gray-50',
                  'px-4 py-2 text-sm font-medium rounded-lg transition-colors relative'
                ]"
              >
                <span class="mr-2">🛒</span>Cart
                <span
                  v-if="cartCount > 0"
                  class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center"
                >
                  {{ cartCount > 99 ? '99+' : cartCount }}
                </span>
              </Link>
              <Link
                href="/customer/notifications"
                :class="[
                  $page.url.startsWith('/customer/notifications')
                    ? 'text-orange-500 bg-orange-50'
                    : 'text-gray-700 hover:text-gray-900 hover:bg-gray-50',
                  'px-4 py-2 text-sm font-medium rounded-lg transition-colors relative'
                ]"
              >
                <span class="mr-2">🔔</span>Notifications
                <span
                  v-if="notificationCount > 0"
                  class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center"
                >
                  {{ notificationCount > 99 ? '99+' : notificationCount }}
                </span>
              </Link>
              <Link
                href="/customer/profile"
                :class="[
                  $page.url.startsWith('/customer/profile')
                    ? 'text-orange-500 bg-orange-50'
                    : 'text-gray-700 hover:text-gray-900 hover:bg-gray-50',
                  'px-4 py-2 text-sm font-medium rounded-lg transition-colors'
                ]"
              >
                <span class="mr-2">👤</span>Profile
              </Link>
            </nav>
          </div>

          <!-- Right side -->
          <div class="flex items-center gap-4">
            <!-- Cart -->
            <Link
              href="/customer/cart"
              class="relative p-2 text-gray-600 hover:text-gray-900 focus:outline-none"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.5 1.5M7 13l1.5 1.5M17 17a2 2 0 100 4 2 2 0 000-4zM9 17a2 2 0 100 4 2 2 0 000-4z" />
              </svg>
              <span
                v-if="cartCount > 0"
                class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-orange-500 rounded-full"
              >
                {{ cartCount > 99 ? '99+' : cartCount }}
              </span>
            </Link>

            <!-- Notifications -->
            <Link
              href="/customer/notifications"
              class="relative p-2 text-gray-600 hover:text-gray-900 focus:outline-none"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
              </svg>
              <span
                v-if="notificationCount > 0"
                class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-500 rounded-full"
              >
                {{ notificationCount > 99 ? '99+' : notificationCount }}
              </span>
            </Link>

            <!-- User Menu -->
            <div class="relative">
              <button class="flex items-center gap-2 p-2 text-gray-700 hover:text-gray-900 focus:outline-none">
                <div class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center">
                  <span class="text-orange-600 text-sm font-medium">
                    {{ userInitials }}
                  </span>
                </div>
                <svg class="w-4 h-4 hidden sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>
    </header>

    <!-- Mobile Navigation -->
    <div class="md:hidden border-t border-gray-200 bg-white">
      <div class="px-4 sm:px-6">
        <nav class="flex justify-between py-3">
          <Link
            href="/customer/menu"
            class="flex flex-col items-center py-2 px-3 hover:bg-gray-50 rounded-lg transition-colors"
          >
            <span class="text-xl">🏪</span>
            <span
              class="text-xs mt-1 font-medium"
              :class="$page.url.startsWith('/customer/menu') ? 'text-orange-500' : 'text-gray-600'"
            >
              Browse
            </span>
          </Link>

          <Link
            href="/customer/cart"
            class="flex flex-col items-center py-2 px-3 hover:bg-gray-50 rounded-lg transition-colors relative"
          >
            <div class="relative">
              <span class="text-xl">🛒</span>
              <span
                v-if="cartCount > 0"
                class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center"
              >
                {{ cartCount > 9 ? '9+' : cartCount }}
              </span>
            </div>
            <span
              class="text-xs mt-1 font-medium"
              :class="$page.url.startsWith('/customer/cart') ? 'text-orange-500' : 'text-gray-600'"
            >
              Cart
            </span>
          </Link>

          <Link
            href="/customer/notifications"
            class="flex flex-col items-center py-2 px-3 hover:bg-gray-50 rounded-lg transition-colors relative"
          >
            <div class="relative">
              <span class="text-xl">🔔</span>
              <span
                v-if="notificationCount > 0"
                class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center"
              >
                {{ notificationCount > 9 ? '9+' : notificationCount }}
              </span>
            </div>
            <span
              class="text-xs mt-1 font-medium"
              :class="$page.url.startsWith('/customer/notifications') ? 'text-orange-500' : 'text-gray-600'"
            >
              Alerts
            </span>
          </Link>

          <Link
            href="/customer/profile"
            class="flex flex-col items-center py-2 px-3 hover:bg-gray-50 rounded-lg transition-colors"
          >
            <span class="text-xl">👤</span>
            <span
              class="text-xs mt-1 font-medium"
              :class="$page.url.startsWith('/customer/profile') ? 'text-orange-500' : 'text-gray-600'"
            >
              Profile
            </span>
          </Link>
        </nav>
      </div>
    </div>

    <!-- Main Content -->
    <main class="flex-1">
      <slot />
    </main>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()
const user = page.props.auth?.user

// Props for cart and notification counts
defineProps({
  cartCount: {
    type: Number,
    default: 0
  },
  notificationCount: {
    type: Number,
    default: 0
  }
})

// Get user initials for avatar
const userInitials = computed(() => {
  if (!user?.name) return 'U'
  return user.name
    .split(' ')
    .map(n => n.charAt(0))
    .join('')
    .toUpperCase()
    .slice(0, 2)
})
</script>

<style scoped>
/* No additional styles needed - using Tailwind CSS classes */
</style>
