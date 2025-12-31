<template>
  <div class="relative">
    <button
      @click="toggleDropdown"
      class="relative p-2 text-gray-600 hover:text-gray-900 focus:outline-none"
    >
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
      </svg>

      <!-- Red Badge with Count -->
      <span
        v-if="unreadCount > 0"
        class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center animate-pulse"
      >
        {{ unreadCount > 99 ? '99+' : unreadCount }}
      </span>
    </button>

    <!-- Order Alert Dropdown -->
    <div
      v-if="showDropdown"
      class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50"
    >
      <!-- Header -->
      <div class="px-4 py-3 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-900">Order Alerts</h3>
          <button
            @click="deleteAll"
            class="text-xs text-red-600 hover:text-red-700 bg-red-50 px-2 py-1 rounded"
          >
            Clear All
          </button>
        </div>
      </div>

      <!-- Order Alert List -->
      <div class="max-h-80 overflow-y-auto">
        <div v-if="orderNotifications.length === 0" class="px-4 py-8 text-center text-gray-500">
          <div class="text-4xl mb-2">🔔</div>
          <p>No order alerts yet</p>
          <p class="text-xs mt-1 text-gray-400">You'll see new orders and declines here</p>
        </div>

        <div
          v-for="notification in orderNotifications"
          :key="notification.id"
          :class="[
            'px-4 py-3 border-b border-gray-100',
            notification.is_read ? 'opacity-60' : 'bg-orange-50'
          ]"
        >
          <div class="flex items-start justify-between">
            <div class="flex items-start flex-1" @click="markAsRead(notification)">
              <div class="flex-shrink-0 mr-3">
                <!-- Order Alert Icon -->
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-sm"
                     :class="getAlertClass(notification.type)">
                  {{ getAlertIcon(notification.type) }}
                </div>
              </div>
              <div class="flex-1">
                <p class="text-sm font-medium text-gray-900">{{ notification.title }}</p>
                <p class="text-sm text-gray-600">{{ notification.message }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ formatTime(notification.created_at) }}</p>
              </div>
              <div v-if="!notification.is_read" class="flex-shrink-0">
                <div class="w-2 h-2 bg-orange-500 rounded-full"></div>
              </div>
            </div>
            <!-- Delete Button -->
            <button
              @click.stop="deleteNotification(notification.id)"
              class="ml-2 text-gray-400 hover:text-red-500 p-1"
              title="Delete notification"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'

const props = defineProps({
  vendorId: {
    type: Number,
    required: true
  }
})

const showDropdown = ref(false)
const notifications = ref([])
const unreadCount = ref(0)

// Import axios globally configured with credentials
import axios from 'axios'

// Filter to show ONLY these two specific order notifications
const orderNotifications = computed(() => {
  return notifications.value.filter(n =>
    ['order', 'order_declined'].includes(n.type)
  )
})

const toggleDropdown = () => {
  showDropdown.value = !showDropdown.value
  if (showDropdown.value) {
    loadNotifications()
  }
}

const getAlertIcon = (type) => {
  // Only TWO states as requested
  const icons = {
    'order': '🛒',        // New order from customer
    'order_declined': '❌', // Customer declined/cancelled order
  }
  return icons[type] || '🔔'
}

const getAlertClass = (type) => {
  // Only TWO states as requested
  const classes = {
    'order': 'bg-green-500',      // New order - green
    'order_declined': 'bg-red-500', // Cancelled - red
  }
  return classes[type] || 'bg-gray-500'
}

const formatTime = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  const now = new Date()
  const diffMs = now - date
  const diffMins = Math.floor(diffMs / 60000)

  if (diffMins < 1) return 'Just now'
  if (diffMins < 60) return `${diffMins}m ago`
  if (diffMins < 1440) return `${Math.floor(diffMins / 60)}h ago`

  return date.toLocaleDateString('en-PH', {
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const loadNotifications = async () => {
  try {
    // ✅ FIXED: Use session-based route with axios (sends cookies automatically)
    const response = await axios.get('/vendor/api/notifications?per_page=20')

    if (response.status === 200) {
      notifications.value = response.data.notifications || []
      unreadCount.value = response.data.statistics?.unread_notifications || 0
    }
  } catch (error) {
    console.error('Failed to load notifications:', error)
  }
}

const markAsRead = async (notification) => {
  if (notification.is_read) return

  try {
    // ✅ FIXED: Use session-based route with axios
    const response = await axios.post(`/vendor/api/notifications/${notification.id}/read`)

    if (response.status === 200) {
      notification.is_read = true
      unreadCount.value = Math.max(0, unreadCount.value - 1)
    }
  } catch (error) {
    console.error('Failed to mark as read:', error)
  }
}

const deleteNotification = async (notificationId) => {
  try {
    // ✅ FIXED: Use session-based route with axios
    const response = await axios.delete(`/vendor/api/notifications/${notificationId}`)

    if (response.status === 200) {
      const notification = notifications.value.find(n => n.id === notificationId)
      if (notification && !notification.is_read) {
        unreadCount.value = Math.max(0, unreadCount.value - 1)
      }
      notifications.value = notifications.value.filter(n => n.id !== notificationId)
    }
  } catch (error) {
    console.error('Failed to delete notification:', error)
  }
}

const deleteAll = async () => {
  if (!confirm('Clear all order alerts?')) return

  try {
    // ✅ FIXED: Use session-based route with axios
    const response = await axios.delete('/vendor/api/notifications')

    if (response.status === 200) {
      notifications.value = []
      unreadCount.value = 0
    }
  } catch (error) {
    console.error('Failed to delete all notifications:', error)
  }
}

// Real-time subscription for ONLY two order states
// RE-ENABLED: Core functionality for vendor-customer system
const subscribeToNotifications = () => {
  if (window.Echo && props.vendorId) {
    try {
      window.Echo.private(`vendor-notifications.${props.vendorId}`)
        .listen('.OrderReceived', (e) => {
          // Add new order alert to top of list
          notifications.value.unshift({
            ...e.notification,
            type: 'order'  // New order from customer
          })
          unreadCount.value++

          // Show browser notification
          if (Notification.permission === 'granted') {
            new Notification('New Order! 🛒', {
              body: e.notification.message,
              icon: '/fast-food.png',
              tag: 'new-order'
            })
          }
        })
        .listen('.OrderStatusChanged', (e) => {
          // Only handle DECLINED orders
          const status = e.notification.data?.status
          if (status === 'declined') {
            notifications.value.unshift({
              ...e.notification,
              type: 'order_declined'  // Customer declined order
            })
            unreadCount.value++

            // Show browser notification
            if (Notification.permission === 'granted') {
              new Notification('Order Declined ❌', {
                body: e.notification.message,
                icon: '/fast-food.png',
                tag: 'order-declined'
              })
            }
          }
        })
    } catch (error) {
      console.error('Broadcasting connection error:', error)
      // Continue without real-time if broadcasting fails
    }
  }
}

onMounted(() => {
  loadNotifications()
  subscribeToNotifications()

  // Request notification permission
  if ('Notification' in window && Notification.permission === 'default') {
    Notification.requestPermission()
  }
})

onUnmounted(() => {
  if (window.Echo && props.vendorId) {
    try {
      window.Echo.leave(`vendor-notifications.${props.vendorId}`)
    } catch (error) {
      console.error('Error leaving broadcasting channel:', error)
    }
  }
})
</script>

<style scoped>
.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: .5;
  }
}
</style>
