<template>
  <div class="relative">
    <button
      @click="toggleDropdown"
      class="relative p-2 text-gray-600 hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 rounded-lg transition-all duration-200"
    >
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
      </svg>

      <!-- Customer Notification Badge with Count -->
      <span
        v-if="unreadCount > 0"
        class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center animate-pulse font-medium"
      >
        {{ unreadCount > 99 ? '99+' : unreadCount }}
      </span>
    </button>

    <!-- Customer Order Status Dropdown - FIXED WIDTH FOR DESKTOP -->
    <div
      v-if="showDropdown"
      class="absolute right-0 mt-2 w-80 md:w-96 lg:w-[600px] bg-white rounded-lg shadow-lg border border-gray-200 z-50 max-h-96 overflow-hidden"
    >
      <!-- Header -->
      <div class="px-4 py-3 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-purple-50">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
            <span>📱</span>
            Order Status
          </h3>
          <button
            @click="deleteAll"
            class="text-xs text-red-600 hover:text-red-700 bg-red-50 px-2 py-1 rounded transition-colors"
          >
            Clear All
          </button>
        </div>
      </div>

      <!-- Customer Order Status List -->
      <div class="max-h-80 overflow-y-auto">
        <div v-if="orderNotifications.length === 0" class="px-4 py-8 text-center text-gray-500">
          <div class="text-4xl mb-2">📱</div>
          <p class="font-medium">No order updates yet</p>
          <p class="text-xs mt-1 text-gray-400">You'll see order status updates here</p>
        </div>

        <div
          v-for="notification in orderNotifications"
          :key="notification.id"
          :class="[
            'px-4 py-3 border-b border-gray-100 cursor-pointer hover:bg-blue-50 transition-colors',
            notification.is_read ? 'opacity-60' : 'bg-blue-25'
          ]"
          @click="markAsRead(notification)"
        >
          <div class="flex items-start justify-between">
            <div class="flex items-start flex-1">
              <div class="flex-shrink-0 mr-3">
                <!-- Order Status Icon -->
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-sm"
                     :class="getAlertClass(notification.type)">
                  {{ getAlertIcon(notification.type) }}
                </div>
              </div>
              <div class="flex-1">
                <p class="text-sm font-medium text-gray-900">{{ notification.title }}</p>
                <p class="text-sm text-gray-600 mt-1">{{ notification.message }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ formatTime(notification.created_at) }}</p>
              </div>
              <div v-if="!notification.is_read" class="flex-shrink-0 ml-2">
                <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
              </div>
            </div>
            <!-- Delete Button -->
            <button
              @click.stop="deleteNotification(notification.id)"
              class="ml-2 text-gray-400 hover:text-red-500 p-1 transition-colors"
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
  userId: {
    type: Number,
    required: true
  }
})

const showDropdown = ref(false)
const notifications = ref([])
const unreadCount = ref(0)

// Import axios globally configured with credentials
import axios from 'axios'

// Filter to show ONLY customer order status notifications
const orderNotifications = computed(() => {
  return (notifications.value || []).filter(n =>
    ['order_status', 'receipt_ready'].includes(n.type)
  )
})

const toggleDropdown = () => {
  showDropdown.value = !showDropdown.value
  if (showDropdown.value) {
    loadNotifications()
  }
}

const getAlertIcon = (type) => {
  // ✅ FIXED: Customer order status icons (NO 'completed' or 'preparing' - uses ready_for_pickup as final status)
  const icons = {
    'order_status': '📱',        // Order status update
    'receipt_ready': '🧾',        // Receipt available
    'accepted': '✅',             // Order accepted
    'ready_for_pickup': '🔔',     // Ready for pickup (FINAL STATUS)
    'cancelled': '❌',            // Order cancelled
  }
  return icons[type] || '📱'
}

const getAlertClass = (type) => {
  // ✅ FIXED: Customer order status colors (NO 'completed' or 'preparing')
  const classes = {
    'order_status': 'bg-blue-500',      // Order status update - blue
    'receipt_ready': 'bg-green-500',    // Receipt ready - green
    'accepted': 'bg-green-500',         // Accepted - green
    'ready_for_pickup': 'bg-orange-500', // Ready - orange (FINAL STATUS)
    'cancelled': 'bg-red-500',          // Cancelled - red
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
    const response = await axios.get('/api/customer/notifications?per_page=20')

    if (response.status === 200) {
      notifications.value = Array.isArray(response.data.notifications) ? response.data.notifications : []
      unreadCount.value = response.data.statistics?.unread_notifications || 0
    }
  } catch (error) {
    console.error('Failed to load customer notifications:', error)
  }
}

const markAsRead = async (notification) => {
  if (notification.is_read) {
    showDropdown.value = false
    return
  }

  try {
    // ✅ FIXED: Use session-based route with axios
    const response = await axios.post(`/api/customer/notifications/${notification.id}/read`)

    if (response.status === 200) {
      notification.is_read = true
      unreadCount.value = Math.max(0, unreadCount.value - 1)
      showDropdown.value = false
    }
  } catch (error) {
    console.error('Failed to mark as read:', error)
  }
}

const deleteNotification = async (notificationId) => {
  try {
    // ✅ FIXED: Use session-based route with axios
    const response = await axios.delete(`/api/customer/notifications/${notificationId}`)

    if (response.status === 200) {
      const notification = notifications.value.find(n => n.id === notificationId)
      if (notification && !notification.is_read) {
        unreadCount.value = Math.max(0, unreadCount.value - 1)
      }
      notifications.value = (notifications.value || []).filter(n => n.id !== notificationId)
    }
  } catch (error) {
    console.error('Failed to delete notification:', error)
  }
}

const deleteAll = async () => {
  if (!confirm('Clear all order status notifications?')) return

  try {
    // ✅ FIXED: Use session-based route with axios
    const response = await axios.delete('/api/customer/notifications')

    if (response.status === 200) {
      notifications.value = []
      unreadCount.value = 0
    }
  } catch (error) {
    console.error('Failed to delete all notifications:', error)
  }
}

// Real-time subscription for customer order status updates
const subscribeToNotifications = () => {
  if (window.Echo && props.userId) {
    try {
      window.Echo.private(`customer-orders.${props.userId}`)
        .listen('.OrderStatusChanged', (e) => {
          // ✅ FIXED: Only handle real vendor status changes for customers (NO 'preparing' or 'completed')
          const status = e.order?.status
          if (status && ['accepted', 'ready_for_pickup', 'cancelled'].includes(status)) {
            if (Array.isArray(notifications.value)) {
              notifications.value.unshift({
                id: Date.now(),
                type: 'order_status',
                title: getStatusTitle(status),
                message: e.message || 'Order status updated', // ✅ FIXED: Add null check for e.message
                data: e.order,
                is_read: false,
                created_at: new Date().toISOString()
              })
            } else {
              notifications.value = [{
                id: Date.now(),
                type: 'order_status',
                title: getStatusTitle(status),
                message: e.message || 'Order status updated', // ✅ FIXED: Add null check for e.message
                data: e.order,
                is_read: false,
                created_at: new Date().toISOString()
              }]
            }
            unreadCount.value++

            // Show browser notification
            if (Notification.permission === 'granted') {
              new Notification(getStatusTitle(status), {
                body: e.message || 'Order status updated', // ✅ FIXED: Add null check for e.message
                icon: '/fast-food.png',
                tag: `order-status-${status}`
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

const getStatusTitle = (status) => {
  // ✅ FIXED: Order status titles (NO 'completed' or 'preparing' - ready_for_pickup is final)
  const titles = {
    'accepted': 'Order Accepted ✅',
    'ready_for_pickup': 'Ready for Pickup 🔔',
    'cancelled': 'Order Cancelled ❌'
  }
  return titles[status] || 'Order Update'
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
  if (window.Echo && props.userId) {
    try {
      window.Echo.leave(`customer-orders.${props.userId}`)
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

/* Custom hover effects */
.hover\:bg-blue-50:hover {
  background-color: rgb(239 246 255);
}

/* Custom focus styles */
.focus\:ring-blue-500:focus {
  --tw-ring-color: rgb(59 130 246 / 0.5);
}
</style>
