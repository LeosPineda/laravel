<template>
  <div class="relative">
    <!-- Bell Icon Button -->
    <button
      @click="toggleDropdown"
      class="relative p-2 text-gray-600 hover:text-gray-900 focus:outline-none"
    >
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
      </svg>

      <!-- Unread Badge -->
      <span
        v-if="unreadCount > 0"
        class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-500 rounded-full"
      >
        {{ unreadCount > 99 ? '99+' : unreadCount }}
      </span>
    </button>

    <!-- Dropdown -->
    <div
      v-if="showDropdown"
      class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-lg border border-gray-200 z-50"
    >
      <!-- Header -->
      <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200">
        <h3 class="font-semibold text-gray-900">Notifications</h3>
        <div class="flex items-center gap-2">
          <button
            v-if="unreadCount > 0"
            @click="markAllRead"
            class="text-xs text-orange-600 hover:text-orange-700"
          >
            Mark all read
          </button>
          <Link
            href="/vendor/notifications"
            class="text-xs text-gray-500 hover:text-gray-700"
            @click="showDropdown = false"
          >
            View all
          </Link>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="p-4 text-center">
        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-orange-500 mx-auto"></div>
      </div>

      <!-- Notifications List -->
      <div v-else-if="notifications.length > 0" class="max-h-96 overflow-y-auto">
        <div
          v-for="notification in notifications"
          :key="notification.id"
          @click="handleNotificationClick(notification)"
          :class="[
            'px-4 py-3 border-b border-gray-100 hover:bg-gray-50 cursor-pointer',
            !notification.is_read ? 'bg-orange-50' : ''
          ]"
        >
          <div class="flex items-start gap-3">
            <!-- Icon -->
            <div
              :class="[
                'w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0',
                getIconBg(notification.type)
              ]"
            >
              {{ getIcon(notification.type) }}
            </div>

            <!-- Content -->
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-900 truncate">
                {{ notification.title }}
              </p>
              <p class="text-xs text-gray-500 line-clamp-2">
                {{ notification.message }}
              </p>
              <p class="text-xs text-gray-400 mt-1">
                {{ notification.created_at }}
              </p>
            </div>

            <!-- Unread dot -->
            <div v-if="!notification.is_read" class="w-2 h-2 bg-orange-500 rounded-full flex-shrink-0"></div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="p-6 text-center">
        <div class="text-3xl mb-2">🔔</div>
        <p class="text-sm text-gray-500">No notifications yet</p>
      </div>

      <!-- Footer -->
      <div class="px-4 py-3 border-t border-gray-200 bg-gray-50 rounded-b-xl">
        <Link
          href="/vendor/notifications"
          class="block text-center text-sm text-orange-600 hover:text-orange-700 font-medium"
          @click="showDropdown = false"
        >
          See all notifications
        </Link>
      </div>
    </div>

    <!-- Backdrop -->
    <div
      v-if="showDropdown"
      class="fixed inset-0 z-40"
      @click="showDropdown = false"
    ></div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

const showDropdown = ref(false)
const loading = ref(false)
const notifications = ref([])
const unreadCount = ref(0)
const vendorId = ref(null)

let pollInterval = null

const getIcon = (type) => {
  const icons = {
    'order': '📦',
    'payment': '💰',
    'system': '⚙️',
    'general': '📢'
  }
  return icons[type] || '🔔'
}

const getIconBg = (type) => {
  const colors = {
    'order': 'bg-yellow-100',
    'payment': 'bg-green-100',
    'system': 'bg-blue-100',
    'general': 'bg-gray-100'
  }
  return colors[type] || 'bg-gray-100'
}

const toggleDropdown = () => {
  showDropdown.value = !showDropdown.value
  if (showDropdown.value) {
    loadRecentNotifications()
  }
}

const loadUnreadCount = async () => {
  try {
    const response = await fetch('/api/vendor/notifications/count', {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json'
      }
    })

    if (response.ok) {
      const data = await response.json()
      unreadCount.value = data.unread_count || 0
    }
  } catch (error) {
    console.error('Error loading notification count:', error)
  }
}

const loadRecentNotifications = async () => {
  loading.value = true
  try {
    const response = await fetch('/api/vendor/notifications/recent?limit=5', {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json'
      }
    })

    if (response.ok) {
      const data = await response.json()
      notifications.value = data.notifications || []
      unreadCount.value = data.unread_count || 0
    }
  } catch (error) {
    console.error('Error loading notifications:', error)
  } finally {
    loading.value = false
  }
}

const handleNotificationClick = async (notification) => {
  // Mark as read
  if (!notification.is_read) {
    try {
      await fetch(`/api/vendor/notifications/${notification.id}/read`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
          'Content-Type': 'application/json'
        }
      })
      notification.is_read = true
      unreadCount.value = Math.max(0, unreadCount.value - 1)
    } catch (error) {
      console.error('Error marking notification as read:', error)
    }
  }

  // Navigate if order-related
  if (notification.order) {
    showDropdown.value = false
    window.location.href = '/vendor/orders'
  }
}

const markAllRead = async () => {
  try {
    const response = await fetch('/api/vendor/notifications/mark-all-read', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json'
      }
    })

    if (response.ok) {
      notifications.value.forEach(n => n.is_read = true)
      unreadCount.value = 0
    }
  } catch (error) {
    console.error('Error marking all as read:', error)
  }
}

// Real-time subscription for instant badge updates
const subscribeToChannel = () => {
  if (window.Echo && vendorId.value) {
    window.Echo.private(`vendor-orders.${vendorId.value}`)
      .listen('.OrderReceived', (e) => {
        console.log('New order notification:', e)
        // Immediately increment badge count (optimistic update)
        unreadCount.value++
        // Then sync with backend to ensure accuracy
        loadUnreadCount()
        // Show browser notification if permitted
        if ('Notification' in window && Notification.permission === 'granted') {
          new Notification('New Order Received!', {
            body: e.message || `Order #${e.order?.order_number || 'N/A'}`,
            icon: '/fast-food.png'
          })
        }
      })
  }
}

const unsubscribeFromChannel = () => {
  if (window.Echo && vendorId.value) {
    window.Echo.leave(`vendor-orders.${vendorId.value}`)
  }
}

onMounted(() => {
  // Get vendor ID from page props
  const page = usePage()
  const user = page.props.auth?.user
  vendorId.value = user?.vendor?.id || null

  // Load initial count from backend (source of truth)
  loadUnreadCount()

  // Subscribe to real-time updates
  subscribeToChannel()

  // Request browser notification permission
  if ('Notification' in window && Notification.permission === 'default') {
    Notification.requestPermission()
  }

  // Fallback: Poll every 30 seconds in case real-time fails
  pollInterval = setInterval(loadUnreadCount, 30000)
})

onUnmounted(() => {
  if (pollInterval) {
    clearInterval(pollInterval)
  }
  unsubscribeFromChannel()
})

// Expose refresh method
defineExpose({
  refresh: loadUnreadCount
})
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
