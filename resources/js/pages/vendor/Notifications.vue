<template>
  <VendorLayout>
    <div class="min-h-screen bg-white">
      <!-- Header -->
      <div class="bg-white border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between">
          <h1 class="text-xl font-bold text-gray-900">Notifications</h1>
          <div class="flex gap-2">
            <button
              v-if="stats.unread_notifications > 0"
              @click="markAllAsRead"
              class="px-3 py-2 text-sm text-orange-600 hover:text-orange-700"
            >
              Mark all as read
            </button>
            <button
              @click="loadNotifications"
              class="px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 flex items-center gap-2"
            >
              <svg class="w-4 h-4" :class="{ 'animate-spin': loading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
              Refresh
            </button>
          </div>
        </div>
      </div>

      <!-- Stats Bar -->
      <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
        <div class="flex gap-6 text-sm">
          <div>
            <span class="text-gray-500">Total:</span>
            <span class="ml-1 font-medium text-gray-900">{{ stats.total_notifications || 0 }}</span>
          </div>
          <div>
            <span class="text-gray-500">Unread:</span>
            <span class="ml-1 font-medium text-orange-600">{{ stats.unread_notifications || 0 }}</span>
          </div>
          <div>
            <span class="text-gray-500">Today:</span>
            <span class="ml-1 font-medium text-gray-900">{{ stats.today_notifications || 0 }}</span>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex gap-4 flex-wrap">
          <!-- Type Filter -->
          <select
            v-model="filters.type"
            @change="handleFilterChange"
            class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 text-sm"
          >
            <option value="">All Types</option>
            <option value="order">Orders</option>
            <option value="payment">Payments</option>
            <option value="system">System</option>
            <option value="general">General</option>
          </select>

          <!-- Status Filter -->
          <select
            v-model="filters.status"
            @change="handleFilterChange"
            class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 text-sm"
          >
            <option value="all">All</option>
            <option value="unread">Unread</option>
            <option value="read">Read</option>
          </select>

          <!-- Search -->
          <input
            v-model="filters.search"
            @input="debouncedSearch"
            type="text"
            placeholder="Search notifications..."
            class="flex-1 min-w-48 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 text-sm"
          />
        </div>
      </div>

      <!-- Content -->
      <div class="p-6">
        <div class="max-w-4xl mx-auto">
          <!-- Loading State -->
          <div v-if="loading" class="text-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-orange-500 mx-auto"></div>
            <p class="text-gray-500 mt-4">Loading notifications...</p>
          </div>

          <!-- Notifications List -->
          <div v-else-if="notifications.length > 0" class="space-y-3">
            <div
              v-for="notification in notifications"
              :key="notification.id"
              :class="[
                'bg-white rounded-xl border p-4 hover:shadow-md transition-shadow',
                !notification.is_read ? 'border-orange-200 bg-orange-50' : 'border-gray-200'
              ]"
            >
              <div class="flex items-start gap-4">
                <!-- Icon -->
                <div
                  :class="[
                    'w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0',
                    getIconBg(notification.type)
                  ]"
                >
                  {{ getIcon(notification.type) }}
                </div>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                  <div class="flex items-start justify-between gap-4">
                    <div>
                      <p class="font-medium text-gray-900">{{ notification.title }}</p>
                      <p class="text-sm text-gray-600 mt-1">{{ notification.message }}</p>

                      <!-- Order Link -->
                      <div v-if="notification.order" class="mt-2">
                        <Link
                          href="/vendor/orders"
                          class="text-sm text-orange-600 hover:text-orange-700"
                        >
                          View Order #{{ notification.order.order_number }} →
                        </Link>
                      </div>

                      <p class="text-xs text-gray-400 mt-2">{{ formatDate(notification.created_at) }}</p>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-2 flex-shrink-0">
                      <button
                        v-if="!notification.is_read"
                        @click="markAsRead(notification)"
                        class="p-1 text-orange-600 hover:text-orange-700"
                        title="Mark as read"
                      >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                      </button>
                      <button
                        @click="deleteNotification(notification)"
                        class="p-1 text-gray-400 hover:text-red-600"
                        title="Delete"
                      >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Empty State -->
          <div v-else class="text-center py-12">
            <div class="text-6xl mb-4">🔔</div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No notifications</h3>
            <p class="text-gray-500">
              {{ hasFilters ? 'Try adjusting your filters' : 'You\'re all caught up!' }}
            </p>
          </div>

          <!-- Pagination -->
          <div v-if="pagination.total > pagination.per_page" class="mt-6 flex items-center justify-between">
            <div class="text-sm text-gray-500">
              Showing {{ ((pagination.current_page - 1) * pagination.per_page) + 1 }} to
              {{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }} of
              {{ pagination.total }} notifications
            </div>
            <div class="flex gap-2">
              <button
                @click="changePage(pagination.current_page - 1)"
                :disabled="pagination.current_page <= 1"
                class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Previous
              </button>
              <button
                @click="changePage(pagination.current_page + 1)"
                :disabled="pagination.current_page >= pagination.last_page"
                class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Next
              </button>
            </div>
          </div>

          <!-- Cleanup Section -->
          <div class="mt-8 p-4 bg-gray-50 rounded-xl border border-gray-200">
            <div class="flex items-center justify-between">
              <div>
                <p class="font-medium text-gray-900">Clean up old notifications</p>
                <p class="text-sm text-gray-500">Remove notifications older than 30 days</p>
              </div>
              <button
                @click="cleanupNotifications"
                :disabled="cleaningUp"
                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 disabled:opacity-50"
              >
                {{ cleaningUp ? 'Cleaning...' : 'Clean Up' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </VendorLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import VendorLayout from '@/layouts/vendor/VendorLayout.vue'

const notifications = ref([])
const loading = ref(false)
const cleaningUp = ref(false)

const stats = ref({
  total_notifications: 0,
  unread_notifications: 0,
  today_notifications: 0
})

const filters = ref({
  type: '',
  status: 'all',
  search: ''
})

const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 20,
  total: 0
})

let searchTimeout = null

const hasFilters = computed(() => {
  return filters.value.type || filters.value.status !== 'all' || filters.value.search
})

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

const formatDate = (dateString) => {
  if (!dateString) return ''
  return new Date(dateString).toLocaleString('en-PH', {
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const loadNotifications = async () => {
  loading.value = true
  try {
    const params = new URLSearchParams({
      page: pagination.value.current_page.toString(),
      per_page: pagination.value.per_page.toString()
    })

    if (filters.value.type) params.append('type', filters.value.type)
    if (filters.value.status !== 'all') params.append('status', filters.value.status)
    if (filters.value.search) params.append('search', filters.value.search)

    const response = await fetch(`/api/vendor/notifications?${params}`, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json'
      }
    })

    if (response.ok) {
      const data = await response.json()
      notifications.value = data.notifications || []
      pagination.value = data.pagination || pagination.value
      stats.value = data.statistics || stats.value
    }
  } catch (error) {
    console.error('Error loading notifications:', error)
  } finally {
    loading.value = false
  }
}

const handleFilterChange = () => {
  pagination.value.current_page = 1
  loadNotifications()
}

const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    pagination.value.current_page = 1
    loadNotifications()
  }, 300)
}

const changePage = (page) => {
  pagination.value.current_page = page
  loadNotifications()
}

const markAsRead = async (notification) => {
  try {
    const response = await fetch(`/api/vendor/notifications/${notification.id}/read`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json'
      }
    })

    if (response.ok) {
      notification.is_read = true
      stats.value.unread_notifications = Math.max(0, stats.value.unread_notifications - 1)
    }
  } catch (error) {
    console.error('Error marking notification as read:', error)
  }
}

const markAllAsRead = async () => {
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
      stats.value.unread_notifications = 0
    }
  } catch (error) {
    console.error('Error marking all as read:', error)
  }
}

const deleteNotification = async (notification) => {
  if (!confirm('Are you sure you want to delete this notification?')) return

  try {
    const response = await fetch(`/api/vendor/notifications/${notification.id}`, {
      method: 'DELETE',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json'
      }
    })

    if (response.ok) {
      await loadNotifications()
    }
  } catch (error) {
    console.error('Error deleting notification:', error)
  }
}

const cleanupNotifications = async () => {
  if (!confirm('This will delete all notifications older than 30 days. Continue?')) return

  cleaningUp.value = true
  try {
    const response = await fetch('/api/vendor/notifications/cleanup', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json'
      }
    })

    if (response.ok) {
      const data = await response.json()
      alert(`${data.deleted_count} old notifications removed`)
      await loadNotifications()
    }
  } catch (error) {
    console.error('Error cleaning up notifications:', error)
  } finally {
    cleaningUp.value = false
  }
}

onMounted(async () => {
  await loadNotifications()
})
</script>
