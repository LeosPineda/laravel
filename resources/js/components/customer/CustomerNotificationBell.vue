<template>
  <div class="relative">
    <!-- Bell Button -->
    <button
      @click="toggleDropdown"
      class="relative p-2 text-gray-600 hover:text-[#FF6B35] focus:outline-none focus:ring-2 focus:ring-[#FF6B35]/30 rounded-xl transition-all duration-200 hover:bg-orange-50"
    >
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
      </svg>

      <!-- Badge -->
      <span
        v-if="unreadCount > 0"
        class="absolute -top-0.5 -right-0.5 bg-red-500 text-white text-xs rounded-full h-5 min-w-5 px-1 flex items-center justify-center font-bold shadow-sm"
      >
        {{ unreadCount > 99 ? '99+' : unreadCount }}
      </span>
    </button>

    <!-- DESKTOP: Dropdown Panel -->
    <Transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="opacity-0 translate-y-1"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 translate-y-1"
    >
      <div
        v-if="showDropdown"
        class="hidden sm:block absolute right-0 mt-2 w-96 bg-white rounded-2xl shadow-xl border border-gray-100 z-50 overflow-hidden"
      >
        <!-- Header -->
        <div class="px-4 py-3 border-b border-gray-100 bg-gradient-to-r from-orange-50 to-amber-50">
          <div class="flex items-center justify-between">
            <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
              <span class="text-xl">🔔</span>
              Order Updates
            </h3>
            <button
              v-if="orderNotifications.length > 0"
              @click="deleteAll"
              class="text-xs text-red-500 hover:text-red-600 hover:bg-red-50 px-2 py-1 rounded-lg transition-colors"
            >
              Clear All
            </button>
          </div>
        </div>

        <!-- Notifications List -->
        <div class="max-h-80 overflow-y-auto">
          <div v-if="orderNotifications.length === 0" class="px-4 py-10 text-center">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
              <span class="text-3xl">📭</span>
            </div>
            <p class="font-medium text-gray-900">No notifications yet</p>
            <p class="text-sm text-gray-500 mt-1">Order updates will appear here</p>
          </div>

          <div
            v-for="notification in orderNotifications"
            :key="notification.id"
            :class="[
              'px-4 py-3 border-b border-gray-50 cursor-pointer transition-colors',
              notification.is_read ? 'bg-white hover:bg-gray-50' : 'bg-orange-50/50 hover:bg-orange-50'
            ]"
            @click="markAsRead(notification)"
          >
            <div class="flex items-start gap-3">
              <!-- Status Icon -->
              <div
                class="w-10 h-10 rounded-full flex items-center justify-center text-white text-lg flex-shrink-0"
                :class="getAlertClass(notification.type)"
              >
                {{ getAlertIcon(notification.type) }}
              </div>

              <!-- Content -->
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                  <p class="text-sm font-semibold text-gray-900 truncate">{{ notification.title }}</p>
                  <div v-if="!notification.is_read" class="w-2 h-2 bg-orange-500 rounded-full flex-shrink-0"></div>
                </div>
                <p class="text-sm text-gray-600 mt-0.5 line-clamp-2">{{ notification.message }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ formatTime(notification.created_at) }}</p>
              </div>

              <!-- Delete Button -->
              <button
                @click.stop="deleteNotification(notification.id)"
                class="p-1.5 text-gray-300 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors flex-shrink-0"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>

    <!-- MOBILE: Full Screen Panel -->
    <Teleport to="body">
      <Transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div
          v-if="showDropdown"
          class="sm:hidden fixed inset-0 z-[60] bg-black/50"
          @click="showDropdown = false"
        >
          <!-- Slide-up Panel -->
          <div
            class="absolute bottom-0 left-0 right-0 bg-white rounded-t-3xl max-h-[85vh] flex flex-col"
            @click.stop
          >
            <!-- Drag Handle -->
            <div class="flex justify-center pt-3 pb-1">
              <div class="w-10 h-1 bg-gray-300 rounded-full"></div>
            </div>

            <!-- Header -->
            <div class="px-4 py-3 border-b border-gray-100 flex-shrink-0">
              <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                  <span class="text-xl">🔔</span>
                  Order Updates
                </h3>
                <div class="flex items-center gap-2">
                  <button
                    v-if="orderNotifications.length > 0"
                    @click="deleteAll"
                    class="text-sm text-red-500 hover:text-red-600 px-3 py-1.5 rounded-lg transition-colors"
                  >
                    Clear All
                  </button>
                  <button
                    @click="showDropdown = false"
                    class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full transition-colors"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>

            <!-- Notifications List -->
            <div class="flex-1 overflow-y-auto">
              <div v-if="orderNotifications.length === 0" class="px-4 py-12 text-center">
                <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                  <span class="text-4xl">📭</span>
                </div>
                <p class="font-semibold text-gray-900 text-lg">No notifications yet</p>
                <p class="text-gray-500 mt-1">Order updates will appear here</p>
              </div>

              <div
                v-for="notification in orderNotifications"
                :key="notification.id"
                :class="[
                  'px-4 py-4 border-b border-gray-100 active:bg-gray-50 transition-colors',
                  notification.is_read ? 'bg-white' : 'bg-orange-50/50'
                ]"
                @click="markAsRead(notification)"
              >
                <div class="flex items-start gap-3">
                  <!-- Status Icon -->
                  <div
                    class="w-12 h-12 rounded-full flex items-center justify-center text-white text-xl flex-shrink-0"
                    :class="getAlertClass(notification.type)"
                  >
                    {{ getAlertIcon(notification.type) }}
                  </div>

                  <!-- Content -->
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                      <p class="font-semibold text-gray-900">{{ notification.title }}</p>
                      <div v-if="!notification.is_read" class="w-2 h-2 bg-orange-500 rounded-full flex-shrink-0 animate-pulse"></div>
                    </div>
                    <p class="text-gray-600 mt-1">{{ notification.message }}</p>
                    <p class="text-sm text-gray-400 mt-2">{{ formatTime(notification.created_at) }}</p>
                  </div>

                  <!-- Delete Button -->
                  <button
                    @click.stop="deleteNotification(notification.id)"
                    class="p-2 text-gray-300 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors flex-shrink-0"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>

            <!-- Safe area padding -->
            <div class="h-6 flex-shrink-0"></div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import axios from 'axios'

const props = defineProps({
  userId: {
    type: Number,
    required: true
  }
})

const showDropdown = ref(false)
const notifications = ref([])
const unreadCount = ref(0)

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
  const icons = {
    'order_status': '📱',
    'receipt_ready': '🧾',
    'accepted': '✅',
    'ready_for_pickup': '🔔',
    'cancelled': '❌',
  }
  return icons[type] || '📱'
}

const getAlertClass = (type) => {
  const classes = {
    'order_status': 'bg-blue-500',
    'receipt_ready': 'bg-green-500',
    'accepted': 'bg-green-500',
    'ready_for_pickup': 'bg-orange-500',
    'cancelled': 'bg-red-500',
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
    const response = await axios.get('/api/customer/notifications?per_page=20')
    if (response.status === 200) {
      notifications.value = Array.isArray(response.data.notifications) ? response.data.notifications : []
      unreadCount.value = response.data.statistics?.unread_notifications || 0
    }
  } catch (error) {
    console.error('Failed to load notifications:', error)
  }
}

const markAsRead = async (notification) => {
  if (notification.is_read) {
    showDropdown.value = false
    return
  }

  try {
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
  if (!confirm('Clear all notifications?')) return

  try {
    const response = await axios.delete('/api/customer/notifications')
    if (response.status === 200) {
      notifications.value = []
      unreadCount.value = 0
    }
  } catch (error) {
    console.error('Failed to delete all:', error)
  }
}

// Real-time subscription
const subscribeToNotifications = () => {
  if (window.Echo && props.userId) {
    try {
      window.Echo.private(`customer-orders.${props.userId}`)
        .listen('.OrderStatusChanged', (e) => {
          const status = e.order?.status
          if (status && ['accepted', 'ready_for_pickup', 'cancelled'].includes(status)) {
            const newNotification = {
              id: Date.now(),
              type: 'order_status',
              title: getStatusTitle(status),
              message: e.message || 'Order status updated',
              data: e.order,
              is_read: false,
              created_at: new Date().toISOString()
            }

            if (Array.isArray(notifications.value)) {
              notifications.value.unshift(newNotification)
            } else {
              notifications.value = [newNotification]
            }
            unreadCount.value++

            // Browser notification
            if (Notification.permission === 'granted') {
              new Notification(getStatusTitle(status), {
                body: e.message || 'Order status updated',
                icon: '/fast-food.png',
                tag: `order-status-${status}`
              })
            }
          }
        })
    } catch (error) {
      console.error('Broadcasting error:', error)
    }
  }
}

const getStatusTitle = (status) => {
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

  if ('Notification' in window && Notification.permission === 'default') {
    Notification.requestPermission()
  }
})

onUnmounted(() => {
  if (window.Echo && props.userId) {
    try {
      window.Echo.leave(`customer-orders.${props.userId}`)
    } catch (error) {
      console.error('Error leaving channel:', error)
    }
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

/* Custom scrollbar */
.overflow-y-auto::-webkit-scrollbar {
  width: 4px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 2px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #d1d1d1;
  border-radius: 2px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #b1b1b1;
}
</style>
