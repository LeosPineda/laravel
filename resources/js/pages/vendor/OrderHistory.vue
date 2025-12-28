<template>
  <div class="bg-white">
    <!-- Tabs -->
    <div class="border-b border-gray-200 px-4">
      <div class="flex gap-6">
        <button
          @click="activeTab = 'completed'"
          :class="[
            'py-3 text-sm font-medium border-b-2 transition-colors',
            activeTab === 'completed'
              ? 'border-orange-500 text-orange-600'
              : 'border-transparent text-gray-500 hover:text-gray-700'
          ]"
        >
          ✅ Completed
          <span v-if="completedCount > 0" class="ml-1 px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-xs">
            {{ completedCount }}
          </span>
        </button>
        <button
          @click="activeTab = 'cancelled'"
          :class="[
            'py-3 text-sm font-medium border-b-2 transition-colors',
            activeTab === 'cancelled'
              ? 'border-orange-500 text-orange-600'
              : 'border-transparent text-gray-500 hover:text-gray-700'
          ]"
        >
          ❌ Cancelled
          <span v-if="cancelledCount > 0" class="ml-1 px-2 py-0.5 bg-red-100 text-red-700 rounded-full text-xs">
            {{ cancelledCount }}
          </span>
        </button>
      </div>
    </div>

    <!-- Content -->
    <div class="p-4">
      <!-- Loading -->
      <div v-if="loading" class="text-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-orange-500 mx-auto"></div>
        <p class="text-gray-500 mt-4">Loading orders...</p>
      </div>

      <!-- Controls -->
      <div v-if="!loading" class="mb-4 p-3 bg-gray-50 rounded-lg border">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-4">
            <button
              @click="toggleSelectAll"
              class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-100"
            >
              {{ allSelected ? 'Deselect All' : 'Select All' }}
            </button>
            <button
              v-if="selectedOrders.length > 0"
              @click="deleteSelected"
              class="px-3 py-1 text-sm bg-red-600 text-white rounded hover:bg-red-700"
            >
              Delete Selected ({{ selectedOrders.length }})
            </button>
            <button
              @click="unselectAll"
              class="px-3 py-1 text-sm bg-orange-600 text-white rounded hover:bg-orange-700"
            >
              Unselect All
            </button>
          </div>
          <div class="text-sm text-gray-600">
            {{ orders.length }} orders • {{ selectedOrders.length }} selected
          </div>
        </div>
      </div>

      <!-- Completed Orders -->
      <div v-if="activeTab === 'completed'">
        <div v-if="completedOrders.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="order in completedOrders"
            :key="order.id"
            class="bg-white rounded-lg border-2 border-green-300 p-6 relative"
          >
            <!-- Checkbox -->
            <div class="absolute top-3 right-3">
              <input
                type="checkbox"
                :checked="selectedOrders.includes(order.id)"
                @change="toggleOrderSelection(order.id)"
                class="w-4 h-4 text-orange-600 bg-gray-100 border-gray-300 rounded focus:ring-orange-500"
              />
            </div>

            <div class="flex items-start justify-between mb-4">
              <div>
                <div class="mb-2">
                  <span class="text-base font-bold text-gray-900">#{{ order.order_number?.replace('ORD-', '') }}</span>
                  <span class="ml-2 px-2 py-1 bg-green-100 text-green-700 rounded text-sm font-medium">Completed</span>
                </div>
                <p>Table {{ order.table_number || 'N/A' }}</p>
                <p>{{ formatTime(order.updated_at) }}</p>
              </div>
              <span class="text-lg font-bold text-orange-600">₱{{ parseFloat(order.total_amount).toFixed(0) }}</span>
            </div>

            <div class="flex gap-3">
              <button
                @click="openOrderDetail(order)"
                class="flex-1 px-3 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200"
              >
                View Order
              </button>
              <button
                @click="downloadReceipt(order)"
                class="flex-1 px-3 py-2 bg-blue-100 text-blue-600 rounded-lg text-sm hover:bg-blue-200"
              >
                Download Receipt
              </button>
              <button
                @click="deleteOrder(order)"
                class="px-3 py-2 bg-red-100 text-red-600 rounded-lg text-sm hover:bg-red-200"
              >
                Delete
              </button>
            </div>
          </div>
        </div>

        <div v-else class="text-center py-12">
          <div class="text-5xl mb-4">✅</div>
          <h3 class="text-lg font-medium text-gray-900 mb-2">No completed orders</h3>
          <p class="text-gray-500">Completed orders will appear here</p>
        </div>
      </div>

      <!-- Cancelled Orders -->
      <div v-if="activeTab === 'cancelled'">
        <div v-if="cancelledOrders.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="order in cancelledOrders"
            :key="order.id"
            class="bg-white rounded-lg border-2 border-red-300 p-6 relative"
          >
            <!-- Checkbox -->
            <div class="absolute top-3 right-3">
              <input
                type="checkbox"
                :checked="selectedOrders.includes(order.id)"
                @change="toggleOrderSelection(order.id)"
                class="w-4 h-4 text-orange-600 bg-gray-100 border-gray-300 rounded focus:ring-orange-500"
              />
            </div>

            <div class="flex items-start justify-between mb-4">
              <div>
                <div class="mb-2">
                  <span class="text-base font-bold text-gray-900">#{{ order.order_number?.replace('ORD-', '') }}</span>
                  <span class="ml-2 px-2 py-1 bg-red-100 text-red-700 rounded text-sm font-medium">Cancelled</span>
                </div>
                <p>Table {{ order.table_number || 'N/A' }}</p>
                <p>{{ formatTime(order.updated_at) }}</p>
              </div>
              <span class="text-lg font-bold text-orange-600">₱{{ parseFloat(order.total_amount).toFixed(0) }}</span>
            </div>

            <div class="flex gap-3">
              <button
                @click="openOrderDetail(order)"
                class="flex-1 px-3 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200"
              >
                View Order
              </button>
              <button
                @click="deleteOrder(order)"
                class="flex-1 px-3 py-2 bg-red-100 text-red-600 rounded-lg text-sm hover:bg-red-200"
              >
                Delete
              </button>
            </div>
          </div>
        </div>

        <div v-else class="text-center py-12">
          <div class="text-5xl mb-4">❌</div>
          <h3 class="text-lg font-medium text-gray-900 mb-2">No cancelled orders</h3>
          <p class="text-gray-500">Cancelled orders will appear here</p>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="pagination.total > pagination.per_page" class="px-4 pb-4 flex justify-between">
      <div class="text-sm text-gray-500">
        {{ pagination.total }} orders
      </div>
      <div class="flex gap-2">
        <button
          @click="changePage(pagination.current_page - 1)"
          :disabled="pagination.current_page <= 1"
          class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50"
        >
          Previous
        </button>
        <button
          @click="changePage(pagination.current_page + 1)"
          :disabled="pagination.current_page >= pagination.last_page"
          class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50"
        >
          Next
        </button>
      </div>
    </div>

    <!-- Modals -->
    <OrderDetailModal
      :is-open="showOrderModal"
      :order-id="selectedOrderId"
      :processing="false"
      @close="showOrderModal = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import OrderDetailModal from '@/components/vendor/OrderDetailModal.vue'
import { useToast } from '@/composables/useToast'

const toast = useToast()

const orders = ref([])
const loading = ref(false)
const activeTab = ref('completed')
const selectedOrders = ref([])

// Modal
const showOrderModal = ref(false)
const selectedOrderId = ref(null)

// Pagination
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 20,
  total: 0
})

// Computed
const completedOrders = computed(() => orders.value.filter(o => o.status === 'ready_for_pickup'))
const cancelledOrders = computed(() => orders.value.filter(o => o.status === 'cancelled'))
const completedCount = computed(() => completedOrders.value.length)
const cancelledCount = computed(() => cancelledOrders.value.length)
const allSelected = computed(() => orders.value.length > 0 && selectedOrders.value.length === orders.value.length)

const formatTime = (dateString) => {
  if (!dateString) return ''
  return new Date(dateString).toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit' })
}

const loadOrders = async () => {
  loading.value = true
  try {
    const params = new URLSearchParams({
      page: pagination.value.current_page.toString(),
      per_page: pagination.value.per_page.toString()
    })

    const response = await fetch(`/api/vendor/orders?${params}`, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json'
      }
    })

    if (response.ok) {
      const data = await response.json()
      orders.value = (data.orders || []).filter(o =>
        o.status === 'ready_for_pickup' || o.status === 'cancelled'
      )
      pagination.value = data.pagination || pagination.value
      // Clear selections when reloading
      selectedOrders.value = []
    }
  } catch (error) {
    console.error('Error loading orders:', error)
  } finally {
    loading.value = false
  }
}

const changePage = (page) => {
  pagination.value.current_page = page
  loadOrders()
}

const openOrderDetail = (order) => {
  selectedOrderId.value = order.id
  showOrderModal.value = true
}

const downloadReceipt = async (order) => {
  try {
    toast.success('Downloading receipt...')

    const response = await fetch(`/api/vendor/orders/${order.id}/receipt/download`, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
      }
    })

    if (!response.ok) {
      const errorData = await response.json()
      throw new Error(errorData.message || 'Failed to generate receipt')
    }

    const blob = await response.blob()
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `receipt-${order.order_number}.pdf`
    a.click()
    URL.revokeObjectURL(url)

    toast.success('Receipt downloaded successfully!')
  } catch (error) {
    console.error('Error downloading receipt:', error)
    toast.error('Failed to download receipt')
  }
}

const deleteOrder = async (order) => {
  if (!confirm(`Delete order #${order.order_number}? This cannot be undone.`)) return

  try {
    const response = await fetch(`/api/vendor/orders/${order.id}`, {
      method: 'DELETE',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json'
      }
    })

    if (response.ok) {
      toast.success('Order deleted successfully')
      await loadOrders()
    } else {
      const error = await response.json()
      toast.error(error.message || 'Failed to delete order')
    }
  } catch (error) {
    console.error('Error deleting order:', error)
    toast.error('Failed to delete order')
  }
}

// Selection functions
const toggleOrderSelection = (orderId) => {
  const index = selectedOrders.value.indexOf(orderId)
  if (index > -1) {
    selectedOrders.value.splice(index, 1)
  } else {
    selectedOrders.value.push(orderId)
  }
}

const toggleSelectAll = () => {
  if (allSelected.value) {
    selectedOrders.value = []
  } else {
    selectedOrders.value = orders.value.map(order => order.id)
  }
}

const deleteSelected = async () => {
  if (selectedOrders.value.length === 0) return

  if (!confirm(`Delete ${selectedOrders.value.length} selected orders? This cannot be undone.`)) return

  try {
    const deletePromises = selectedOrders.value.map(orderId =>
      fetch(`/api/vendor/orders/${orderId}`, {
        method: 'DELETE',
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
          'Content-Type': 'application/json'
        }
      })
    )

    const responses = await Promise.all(deletePromises)
    const allSuccessful = responses.every(response => response.ok)

    if (allSuccessful) {
      toast.success(`${selectedOrders.value.length} orders deleted successfully`)
      selectedOrders.value = []
      await loadOrders()
    } else {
      toast.error('Some orders failed to delete')
    }
  } catch (error) {
    console.error('Error deleting orders:', error)
    toast.error('Failed to delete orders')
  }
}

const clearAll = async () => {
  if (orders.value.length === 0) return

  if (!confirm(`Delete ALL ${orders.value.length} orders? This cannot be undone.`)) return

  try {
    const deletePromises = orders.value.map(order =>
      fetch(`/api/vendor/orders/${order.id}`, {
        method: 'DELETE',
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
          'Content-Type': 'application/json'
        }
      })
    )

    const responses = await Promise.all(deletePromises)
    const allSuccessful = responses.every(response => response.ok)

    if (allSuccessful) {
      toast.success('All orders cleared successfully')
      selectedOrders.value = []
      await loadOrders()
    } else {
      toast.error('Some orders failed to delete')
    }
  } catch (error) {
    console.error('Error clearing orders:', error)
    toast.error('Failed to clear orders')
  }
}

const unselectAll = () => {
  selectedOrders.value = []
  toast.info('All selections cleared')
}

onMounted(() => {
  loadOrders()
})
</script>
