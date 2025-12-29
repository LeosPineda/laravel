import { ref, computed } from 'vue'

interface Notification {
  id: number
  type: 'order' | 'payment' | 'status' | 'vendor' | 'system'
  title: string
  message: string
  is_read: boolean
  created_at: string
  updated_at: string
  vendor?: {
    id: number
    brand_name: string
  }
  order?: {
    id: number
    status: string
  }
  marking?: boolean
  deleting?: boolean
}

export function useNotifications() {
  const notifications = ref<Notification[]>([])
  const loading = ref(false)
  const unreadCount = ref(0)

  // Real-time WebSocket connection
  let echoChannel: any = null

  // API functions
  const fetchNotifications = async () => {
    loading.value = true
    try {
      const response = await fetch('/api/customer/notifications', {
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'include'
      })

      if (response.ok) {
        const data = await response.json()
        notifications.value = data.notifications || []
        unreadCount.value = data.unread_count || 0
      }
    } catch (error) {
      console.error('Error fetching notifications:', error)
    } finally {
      loading.value = false
    }
  }

  const markAsRead = async (notificationId: number) => {
    const notification = notifications.value.find(n => n.id === notificationId)
    if (notification) {
      notification.marking = true
    }

    try {
      const response = await fetch(`/api/customer/notifications/${notificationId}/read`, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'include'
      })

      if (response.ok) {
        // Update local state
        const notification = notifications.value.find(n => n.id === notificationId)
        if (notification) {
          notification.is_read = true
          unreadCount.value = Math.max(0, unreadCount.value - 1)
        }
        return { success: true }
      } else {
        const errorData = await response.json()
        return { success: false, message: errorData.message || 'Failed to mark as read' }
      }
    } catch (error) {
      console.error('Error marking notification as read:', error)
      return { success: false, message: 'Failed to mark as read' }
    } finally {
      const notification = notifications.value.find(n => n.id === notificationId)
      if (notification) {
        notification.marking = false
      }
    }
  }

  const markAllAsRead = async () => {
    try {
      const response = await fetch('/api/customer/notifications/mark-all', {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'include'
      })

      if (response.ok) {
        // Update local state
        notifications.value.forEach(notification => {
          notification.is_read = true
        })
        unreadCount.value = 0
        return { success: true }
      } else {
        const errorData = await response.json()
        return { success: false, message: errorData.message || 'Failed to mark all as read' }
      }
    } catch (error) {
      console.error('Error marking all notifications as read:', error)
      return { success: false, message: 'Failed to mark all as read' }
    }
  }

  const deleteNotification = async (notificationId: number) => {
    const notification = notifications.value.find(n => n.id === notificationId)
    if (notification) {
      notification.deleting = true
    }

    try {
      const response = await fetch(`/api/customer/notifications/${notificationId}`, {
        method: 'DELETE',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'include'
      })

      if (response.ok) {
        // Remove from local state
        const index = notifications.value.findIndex(n => n.id === notificationId)
        if (index > -1) {
          const notification = notifications.value[index]
          if (!notification.is_read) {
            unreadCount.value = Math.max(0, unreadCount.value - 1)
          }
          notifications.value.splice(index, 1)
        }
        return { success: true }
      } else {
        const errorData = await response.json()
        return { success: false, message: errorData.message || 'Failed to delete notification' }
      }
    } catch (error) {
      console.error('Error deleting notification:', error)
      return { success: false, message: 'Failed to delete notification' }
    } finally {
      const notification = notifications.value.find(n => n.id === notificationId)
      if (notification) {
        notification.deleting = false
      }
    }
  }

  const getUnreadCount = async () => {
    try {
      const response = await fetch('/api/customer/notifications/count', {
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'include'
      })

      if (response.ok) {
        const data = await response.json()
        unreadCount.value = data.count || 0
      }
    } catch (error) {
      console.error('Error fetching notification count:', error)
    }
  }

  // Real-time WebSocket setup
  const setupRealtimeNotifications = () => {
    // Check if Laravel Echo is available
    if (typeof window !== 'undefined' && (window as any).Echo) {
      echoChannel = (window as any).Echo.private('customer.notifications')

      echoChannel.listen('.NewNotification', (data: any) => {
        // Add new notification to the top of the list
        notifications.value.unshift({
          ...data.notification,
          marking: false,
          deleting: false
        })

        if (!data.notification.is_read) {
          unreadCount.value++
        }
      })

      echoChannel.listen('.NotificationRead', (data: any) => {
        const notification = notifications.value.find(n => n.id === data.notificationId)
        if (notification && !notification.is_read) {
          notification.is_read = true
          unreadCount.value = Math.max(0, unreadCount.value - 1)
        }
      })

      echoChannel.listen('.OrderStatusChanged', (data: any) => {
        // Handle order status updates
        const notification: Notification = {
          id: Date.now(), // Generate temporary ID
          type: 'order',
          title: 'Order Update',
          message: data.message,
          is_read: false,
          created_at: new Date().toISOString(),
          updated_at: new Date().toISOString(),
          order: data.order,
          marking: false,
          deleting: false
        }

        notifications.value.unshift(notification)
        unreadCount.value++
      })
    }
  }

  const disconnectRealtimeNotifications = () => {
    if (echoChannel) {
      echoChannel.stopListening('.NewNotification')
      echoChannel.stopListening('.NotificationRead')
      echoChannel.stopListening('.OrderStatusChanged')
      echoChannel = null
    }
  }

  // Initialize real-time notifications
  if (typeof window !== 'undefined') {
    setupRealtimeNotifications()
  }

  return {
    // State
    notifications,
    loading,
    unreadCount,

    // Methods
    fetchNotifications,
    markAsRead,
    markAllAsRead,
    deleteNotification,
    getUnreadCount,
    setupRealtimeNotifications,
    disconnectRealtimeNotifications
  }
}
