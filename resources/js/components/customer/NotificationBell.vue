<template>
  <div class="relative">
    <button
      @click="toggleDropdown"
      class="relative p-2 text-gray-600 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 rounded-lg"
      :class="{ 'bg-gray-100': showDropdown }"
    >
      <svg
        class="w-6 h-6"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
        xmlns="http://www.w3.org/2000/svg"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M15 17h5l-5 5l-5-5h5v-6a1 1 0 011-1h10a1 1 0 011 1v4h2a2 2 0 002-2v-2a4 4 0 00-4-4H9a4 4 0 00-4 4v2a2 2 0 002 2z"
        />
      </svg>

      <span
        v-if="unreadCount > 0"
        class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center animate-pulse"
      >
        {{ unreadCount > 99 ? '99+' : unreadCount }}
      </span>
    </button>

    <div
      v-if="showDropdown"
      class="absolute right-0 mt-2 w-96 bg-white rounded-lg shadow-lg border border-gray-200 z-50"
    >
      <div class="p-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-900">Notifications</h3>
          <button
            @click="markAllAsRead"
            class="text-sm text-orange-600 hover:text-orange-700 font-medium"
            :disabled="unreadCount === 0"
            :class="{ 'opacity-50 cursor-not-allowed': unreadCount === 0 }"
          >
            Mark all read
          </button>
        </div>
      </div>

      <div class="max-h-96 overflow-y-auto">
        <div v-if="loading" class="p-4 text-center text-gray-500">
          <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-orange-500"></div>
          <span class="ml-2">Loading notifications...</span>
        </div>

        <div v-else-if="notifications.length === 0" class="p-4 text-center text-gray-500">
          <svg class="mx-auto h-12 w-12 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2 2v-5m16 0h-2M4 13h2m13-8V4a1 1 0 00-1-1H9a1 1 0 00-1 1v1m4 0V4a1 1 0 00-1-1H9a1 1 0 00-1 1v1" />
          </svg>
          <p>No notifications yet</p>
          <p class="text-sm">When you get notifications, they'll appear here</p>
        </div>

        <div v-else>
          <div
            v-for="notification in notifications"
            :key="notification.id"
            @click="markAsRead(notification.id)"
            class="p-4 border-b border-gray-100 hover:bg-gray-50 cursor-pointer transition-colors duration-200"
            :class="{ 'bg-blue-50': !notification.read_at }"
          >
            <div class="flex items-start space-x-3">
              <div class="flex-shrink-0">
                <div
                  class="w-2 h-2 rounded-full mt-2"
                  :class="notification.read_at ? 'bg-gray-300' : 'bg-orange-500'"
                ></div>
              </div>

              <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between">
                  <p class="text-sm font-medium text-gray-900 truncate">
                    {{ notification.title }}
                  </p>
                  <p class="text-xs text-gray-500 whitespace-nowrap ml-2">
                    {{ formatTime(notification.created_at) }}
                  </p>
                </div>

                <p class="text-sm text-gray-600 mt-1 line-clamp-2">
                  {{ notification.message }}
                </p>

                <div v-if="notification.data && notification.data.order_id" class="mt-2">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    Order #{{ notification.data.order_number }}
                  </span>
                </div>

                <div v-if="notification.data && notification.data.status" class="mt-2">
                  <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                    :class="getStatusClass(notification.data.status)"
                  >
                    {{ formatStatus(notification.data.status) }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div v-if="hasMore && notifications.length > 0" class="p-4 border-t border-gray-200">
        <button
          @click="loadMore"
          :disabled="loadingMore"
          class="w-full text-sm text-orange-600 hover:text-orange-700 font-medium disabled:opacity-50"
        >
          {{ loadingMore ? 'Loading...' : 'Load more' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'
import { usePage } from '@inertiajs/vue3'
import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

// Make Pusher available globally for Laravel Echo
window.Pusher = Pusher

const props = defineProps({
  maxNotifications: {
    type: Number,
    default: 50
  }
})

const notifications = ref([])
const loading = ref(false)
const loadingMore = ref(false)
const showDropdown = ref(false)
const hasMore = ref(false)
const page = ref(1)
const unreadCount = ref(0)

const echo = ref(null)

// Computed
const totalNotifications = computed(() => notifications.value.length)

// Methods
const formatTime = (dateString) => {
  const date = new Date(dateString)
  const now = new Date()
  const diff = now - date

  if (diff < 60000) return 'Just now'
  if (diff < 3600000) return `${Math.floor(diff / 60000)}m ago`
  if (diff < 86400000) return `${Math.floor(diff / 3600000)}h ago`
  if (diff < 604800000) return `${Math.floor(diff / 86400000)}d ago`

  return date.toLocaleDateString()
}

const formatStatus = (status) => {
  return status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const getStatusClass = (status) => {
  const classes = {
    'pending': 'bg-yellow-100 text-yellow-800',
    'confirmed': 'bg-blue-100 text-blue-800',
    'preparing': 'bg-purple-100 text-purple-800',
    'ready_for_pickup': 'bg-green-100 text-green-800',
    'completed': 'bg-gray-100 text-gray-800',
    'cancelled': 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const fetchNotifications = async (reset = false) => {
  if (reset) {
    page.value = 1
    notifications.value = []
  }

  const currentLoading = reset ? loading : loadingMore
  currentLoading.value = true

  try {
    const response = await fetch(`/api/customer/notifications?page=${page.value}&limit=${props.maxNotifications}`)

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`)
    }

    const data = await response.json()

    if (reset) {
      notifications.value = data.notifications || []
    } else {
      notifications.value.push(...(data.notifications || []))
    }

    unreadCount.value = data.unread_count || 0
    hasMore.value = data.has_more || false

  } catch (error) {
    console.error('Failed to fetch notifications:', error)
  } finally {
    currentLoading.value = false
  }
}

const markAsRead = async (notificationId) => {
  try {
    const response = await fetch(`/api/customer/notifications/${notificationId}/read`, {
      method: 'POST'
    })

    if (response.ok) {
      const notification = notifications.value.find(n => n.id === notificationId)
      if (notification && !notification.read_at) {
        notification.read_at = new Date().toISOString()
        unreadCount.value = Math.max(0, unreadCount.value - 1)
      }
    }
  } catch (error) {
    console.error('Failed to mark notification as read:', error)
  }
}

const markAllAsRead = async () => {
  if (unreadCount.value === 0) return

  try {
    const response = await fetch('/api/customer/notifications/mark-all-read', {
      method: 'POST'
    })

    if (response.ok) {
      notifications.value.forEach(notification => {
        if (!notification.read_at) {
          notification.read_at = new Date().toISOString()
        }
      })
      unreadCount.value = 0
    }
  } catch (error) {
    console.error('Failed to mark all notifications as read:', error)
  }
}

const loadMore = () => {
  if (!hasMore.value || loadingMore.value) return

  page.value += 1
  fetchNotifications()
}

const toggleDropdown = async () => {
  showDropdown.value = !showDropdown.value

  if (showDropdown.value && notifications.value.length === 0) {
    await fetchNotifications(true)
  }
}

// Real-time notification handling
const initializeRealtime = () => {
  try {
    echo.value = new Echo({
      broadcaster: 'pusher',
      key: import.meta.env.VITE_PUSHER_APP_KEY,
      cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
      forceTLS: true
    })

    // Listen for customer-specific notifications
    echo.value.private(`customer.${usePage().props.auth.user.id}`)
      .listen('.NewNotification', (e) => {
        // Add new notification to the top of the list
        notifications.value.unshift({
          ...e.notification,
          created_at: new Date().toISOString()
        })

        // Update unread count
        if (!e.notification.read_at) {
          unreadCount.value += 1
        }

        // Show browser notification if permission granted
        if (Notification.permission === 'granted') {
          new Notification(e.notification.title, {
            body: e.notification.message,
            icon: '/images/logo.png',
            tag: `notification-${e.notification.id}`
          })
        }
      })
      .listen('.OrderStatusChanged', (e) => {
        // Handle order status changes
        const orderId = e.order?.id
        if (orderId) {
          // Find and update any existing notifications for this order
          const existingNotification = notifications.value.find(n =>
            n.data?.order_id === orderId
          )

          if (existingNotification) {
            existingNotification.message = `Order #${e.order.order_number} status changed to ${formatStatus(e.status)}`
            existingNotification.data.status = e.status
            existingNotification.updated_at = new Date().toISOString()
          }
        }
      })

  } catch (error) {
    console.error('Failed to initialize real-time notifications:', error)
  }
}

const requestNotificationPermission = async () => {
  if ('Notification' in window && Notification.permission === 'default') {
    await Notification.requestPermission()
  }
}

// Event listeners
const handleClickOutside = (event) => {
  if (!event.target.closest('.notification-bell')) {
    showDropdown.value = false
  }
}

const handleEscape = (event) => {
  if (event.key === 'Escape') {
    showDropdown.value = false
  }
}

// Lifecycle
onMounted(async () => {
  // Add click outside and escape key listeners
  document.addEventListener('click', handleClickOutside)
  document.addEventListener('keydown', handleEscape)

  // Request notification permission
  await requestNotificationPermission()

  // Initialize real-time notifications
  initializeRealtime()

  // Fetch initial notifications
  await fetchNotifications(true)
})

onUnmounted(() => {
  // Clean up event listeners
  document.removeEventListener('click', handleClickOutside)
  document.removeEventListener('keydown', handleEscape)

  // Disconnect from real-time service
  if (echo.value) {
    echo.value.disconnect()
  }
})
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.notification-bell {
  position: relative;
}
</style>
