<template>
  <CustomerLayout>
    <div class="min-h-screen bg-white">
      <!-- Header -->
      <div class="bg-white border-b border-gray-200 px-4 py-6">
        <div class="max-w-7xl mx-auto">
          <div class="flex items-center justify-between">
            <div>
              <h1 class="text-2xl font-bold text-gray-900">Notifications</h1>
              <p class="text-gray-600 mt-1">Stay updated with your orders and vendor communications</p>
            </div>
            <div class="flex items-center gap-4">
              <span class="text-sm text-gray-600">
                {{ unreadCount }} unread
              </span>
              <button
                @click="markAllAsRead"
                class="px-4 py-2 text-sm font-medium text-orange-600 hover:text-orange-700 border border-orange-200 rounded-lg hover:bg-orange-50"
                :disabled="markingAllRead"
              >
                {{ markingAllRead ? 'Marking...' : 'Mark all read' }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Content -->
      <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Filter Tabs -->
        <div class="mb-6 border-b border-gray-200">
          <nav class="flex space-x-8">
            <button
              @click="activeFilter = 'all'"
              :class="[
                activeFilter === 'all'
                  ? 'border-orange-500 text-orange-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm'
              ]"
            >
              All Notifications
            </button>
            <button
              @click="activeFilter = 'unread'"
              :class="[
                activeFilter === 'unread'
                  ? 'border-orange-500 text-orange-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm'
              ]"
            >
              Unread
              <span v-if="unreadCount > 0" class="ml-1 bg-red-100 text-red-600 py-0.5 px-2 rounded-full text-xs">
                {{ unreadCount }}
              </span>
            </button>
            <button
              @click="activeFilter = 'orders'"
              :class="[
                activeFilter === 'orders'
                  ? 'border-orange-500 text-orange-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm'
              ]"
            >
              Orders
            </button>
          </nav>
        </div>

        <!-- Notifications List -->
        <div v-if="loading" class="space-y-4">
          <div
            v-for="n in 5"
            :key="n"
            class="bg-white border border-gray-200 rounded-lg p-6 animate-pulse"
          >
            <div class="flex items-start gap-4">
              <div class="w-10 h-10 bg-gray-200 rounded-full"></div>
              <div class="flex-1">
                <div class="h-4 bg-gray-200 rounded mb-2 w-3/4"></div>
                <div class="h-3 bg-gray-200 rounded w-1/2"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else-if="filteredNotifications.length === 0" class="text-center py-16">
          <div class="text-6xl mb-4">🔔</div>
          <h3 class="text-xl font-semibold text-gray-900 mb-2">
            {{ activeFilter === 'unread' ? 'No unread notifications' : 'No notifications yet' }}
          </h3>
          <p class="text-gray-600">
            {{ activeFilter === 'unread'
              ? 'You\'re all caught up!'
              : 'When you place orders or receive updates, they\'ll appear here.' }}
          </p>
        </div>

        <!-- Notifications List -->
        <div v-else class="space-y-4">
          <div
            v-for="notification in filteredNotifications"
            :key="notification.id"
            class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow"
            :class="{ 'bg-orange-50 border-orange-200': !notification.is_read }"
          >
            <div class="flex items-start gap-4">
              <!-- Notification Icon -->
              <div class="flex-shrink-0">
                <div class="w-10 h-10 rounded-full flex items-center justify-center"
                     :class="getNotificationIconClass(notification.type)">
                  <span class="text-lg">{{ getNotificationIcon(notification.type) }}</span>
                </div>
              </div>

              <!-- Notification Content -->
              <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between">
                  <div class="flex-1">
                    <h3 class="text-sm font-semibold text-gray-900 mb-1">
                      {{ notification.title }}
                    </h3>
                    <p class="text-sm text-gray-600 mb-2">
                      {{ notification.message }}
                    </p>
                    <div class="flex items-center gap-4 text-xs text-gray-500">
                      <span>{{ formatTime(notification.created_at) }}</span>
                      <span v-if="notification.vendor" class="flex items-center gap-1">
                        <span class="w-3 h-3 bg-gray-300 rounded-full"></span>
                        {{ notification.vendor.brand_name }}
                      </span>
                      <span v-if="notification.order" class="flex items-center gap-1">
                        <span class="w-3 h-3 bg-blue-300 rounded-full"></span>
                        Order #{{ notification.order.id }}
                      </span>
                    </div>
                  </div>

                  <!-- Action Buttons -->
                  <div class="flex items-center gap-2 ml-4">
                    <button
                      v-if="!notification.is_read"
                      @click="markAsRead(notification.id)"
                      class="text-xs text-orange-600 hover:text-orange-700 font-medium"
                      :disabled="notification.marking"
                    >
                      {{ notification.marking ? 'Marking...' : 'Mark read' }}
                    </button>
                    <button
                      @click="deleteNotification(notification.id)"
                      class="text-xs text-gray-400 hover:text-red-500"
                      :disabled="notification.deleting"
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
        </div>
      </div>
    </div>
  </CustomerLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import CustomerLayout from '@/layouts/customer/CustomerLayout.vue'
import { useNotifications } from '@/composables/useNotifications'

const {
  notifications,
  unreadCount,
  loading,
  fetchNotifications,
  markAsRead,
  markAllAsRead,
  deleteNotification
} = useNotifications()

// Local state
const activeFilter = ref('all')
const markingAllRead = ref(false)

// Computed
const filteredNotifications = computed(() => {
  let filtered = notifications.value

  switch (activeFilter.value) {
    case 'unread':
      filtered = filtered.filter(n => !n.is_read)
      break
    case 'orders':
      filtered = filtered.filter(n => n.type === 'order')
      break
  }

  return filtered
})

// Methods
const getNotificationIcon = (type) => {
  const icons = {
    order: '📋',
    payment: '💳',
    status: '📊',
    vendor: '🏪',
    system: '⚙️'
  }
  return icons[type] || '📢'
}

const getNotificationIconClass = (type) => {
  const classes = {
    order: 'bg-blue-100 text-blue-600',
    payment: 'bg-green-100 text-green-600',
    status: 'bg-yellow-100 text-yellow-600',
    vendor: 'bg-purple-100 text-purple-600',
    system: 'bg-gray-100 text-gray-600'
  }
  return classes[type] || 'bg-gray-100 text-gray-600'
}

const formatTime = (timestamp) => {
  const date = new Date(timestamp)
  const now = new Date()
  const diff = now - date

  const minutes = Math.floor(diff / 60000)
  const hours = Math.floor(diff / 3600000)
  const days = Math.floor(diff / 86400000)

  if (minutes < 1) return 'Just now'
  if (minutes < 60) return `${minutes}m ago`
  if (hours < 24) return `${hours}h ago`
  if (days < 7) return `${days}d ago`

  return date.toLocaleDateString()
}

// Initialize
onMounted(() => {
  fetchNotifications()
})
</script>

<style scoped>
/* No additional styles needed - using Tailwind CSS classes */
</style>
