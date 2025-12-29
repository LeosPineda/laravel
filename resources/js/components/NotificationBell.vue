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
        class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"
      >
        {{ unreadCount > 99 ? '99+' : unreadCount }}
      </span>
    </button>

    <!-- Notification Dropdown -->
    <div
      v-if="showDropdown"
      class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50"
    >
      <!-- Header -->
      <div class="px-4 py-3 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-900">Notifications</h3>
          <button
            @click="markAllAsRead"
            class="text-sm text-orange-600 hover:text-orange-700"
          >
            Mark all as read
          </button>
        </div>
      </div>

      <!-- Notification List -->
      <div class="max-h-96 overflow-y-auto">
        <div v-if="notifications.length === 0" class="px-4 py-8 text-center text-gray-500">
          <div class="text-4xl mb-2">🔔</div>
          <p>No notifications yet</p>
        </div>

        <div
          v-for="notification in notifications"
          :key="notification.id"
          @click="markAsRead(notification)"
          :class="[
            'px-4 py-3 border-b border-gray-100 cursor-pointer hover:bg-gray-50',
            notification.is_read ? 'opacity-60' : 'bg-blue-50'
          ]"
        >
          <div class="flex items-start">
            <div class="flex-shrink-0">
              <!-- Notification Icon -->
              <div class="w-8 h-8 rounded-full flex items-center justify-center text-white"
                   :class="getIconClass(notification.type)">
                {{ getIcon(notification.type) }}
              </div>
            </div>
            <div class="ml-3 flex-1">
              <p class="text-sm font-medium text-gray-900">{{ notification.title }}</p>
              <p class="text-sm text-gray-600">{{ notification.message }}</p>
              <p class="text-xs text-gray-400 mt-1">{{ formatTime(notification.created_at) }}</p>
            </div>
            <div v-if="!notification.is_read" class="flex-shrink-0">
              <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="px-4 py-3 border-t border-gray-200">
        <a href="/vendor/notifications" class="text-sm text-orange-600 hover:text-orange-700">
          View all notifications
        </a>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  vendorId: {
    type: Number,
    required: true
  }
})

const showDropdown = ref(false)
const notifications = ref([])
const unreadCount = ref(0)

const toggleDropdown = () => {
  showDropdown.value = !showDropdown.value
  if (showDropdown.value) {
    loadNotifications()
  }
}

const getIcon = (type) => {
  const icons = {
    'order': '🛒',
    'order_declined': '❌',
    'order_accepted': '✅',
    'order_ready': '🔔',
    'system': '⚙️'
  }
  return icons[type] || '🔔'
}

const getIconClass = (type) => {
  const classes = {
    'order': 'bg-green-500',
    'order_declined': 'bg-red-500',
    'order_accepted': 'bg-blue-500',
    'order_ready': 'bg-orange-500',
    'system': 'bg-gray-500'
  }
  return classes[type] || 'bg-gray-500'
}

const formatTime = (dateString) => {
  if (!dateString) return ''
  return new Date(dateString).toLocaleTimeString('en-PH', {
    hour: '2-digit',
    minute: '2-digit'
  })
}

const loadNotifications = async () => {
  try {
    const response = await fetch(`/api/vendor/notifications?per_page=10`, {
      headers: {
        'Content-Type': 'application/json'
      }
    })

    if (response.ok) {
      const data = await response.json()
      notifications.value = data.notifications || []
      unreadCount.value = data.statistics?.unread_notifications || 0
    }
  } catch (error) {
    console.error('Failed to load notifications:', error)
  }
}

const markAsRead = async (notification) => {
  if (notification.is_read) return

  try {
    const response = await fetch(`/api/vendor/notifications/${notification.id}/read`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      }
    })

    if (response.ok) {
      notification.is_read = true
      unreadCount.value = Math.max(0, unreadCount.value - 1)
    }
  } catch (error) {
    console.error('Failed to mark as read:', error)
  }
}

const markAllAsRead = async () => {
  try {
    const response = await fetch('/api/vendor/notifications/mark-all-read', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      }
    })

    if (response.ok) {
      notifications.value.forEach(n => n.is_read = true)
      unreadCount.value = 0
    }
  } catch (error) {
    console.error('Failed to mark all as read:', error)
  }
}

// Real-time subscription
const subscribeToNotifications = () => {
  if (window.Echo && props.vendorId) {
    window.Echo.private(`vendor-notifications.${props.vendorId}`)
      .listen('.NewNotification', (e) => {
        // Add new notification to top of list
        notifications.value.unshift(e.notification)
        unreadCount.value++

        // Show browser notification if permission granted
        if (Notification.permission === 'granted') {
          new Notification(e.notification.title, {
            body: e.notification.message,
            icon: '/fast-food.png'
          })
        }
      })
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
    window.Echo.leave(`vendor-notifications.${props.vendorId}`)
  }
})
</script>
