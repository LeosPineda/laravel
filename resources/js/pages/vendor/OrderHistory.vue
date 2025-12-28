<template>
  <div class="bg-white">
    <!-- Content -->
    <div class="p-6">
      <div class="max-w-4xl mx-auto">
        <!-- Filters -->
        <OrderFilters
          v-model:selected-status="selectedStatus"
          v-model:search-query="searchQuery"
          :orders="orders"
          :selected-orders="selectedOrders"
          :all-selected="allSelected"
          @status-change="handleStatusChange"
          @search="debouncedSearch"
          @toggle-select-all="toggleSelectAll"
          @open-bulk-delete="openBulkDeleteModal"
        />

        <!-- Loading State -->
        <div v-if="loading" class="text-center py-12">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-orange-500 mx-auto"></div>
          <p class="text-gray-500 mt-4">Loading orders...</p>
        </div>

        <!-- Orders List -->
        <div v-else-if="orders.length > 0" class="space-y-3">
          <OrderCard
            v-for="order in orders"
            :key="order.id"
            :order="order"
            :selected="selectedOrders.includes(order.id)"
            @view="openOrderDetail"
            @receipt="downloadReceipt"
            @delete="openDeleteModal"
            @toggle-selection="toggleOrderSelection"
          />
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-12">
          <div class="text-5xl mb-4">📋</div>
          <h3 class="text-lg font-medium text-gray-900 mb-2">No order history</h3>
          <p class="text-gray-500">Completed and declined orders will appear here</p>
        </div>

        <!-- Pagination -->
        <div v-if="pagination.total > pagination.per_page" class="mt-6 flex items-center justify-between">
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
      </div>
    </div>

    <!-- Order Detail Modal -->
    <OrderDetailModal
      :is-open="showOrderModal"
      :order-id="selectedOrderId"
      :processing="false"
      @close="closeOrderModal"
    />

    <!-- Delete Confirmation Modal -->
    <ConfirmModal
      :is-open="showDeleteModal"
      title="Delete Order"
      :message="`Delete order #${deleteTarget?.order_number}? This cannot be undone.`"
      confirm-text="Delete"
      :loading="deleting"
      icon="🗑️"
      variant="danger"
      @confirm="confirmDelete"
      @cancel="showDeleteModal = false"
    />

    <!-- Bulk Delete Confirmation Modal -->
    <ConfirmModal
      :is-open="showBulkDeleteModal"
      title="Delete Selected Orders"
      :message="`Delete ${selectedOrders.length} selected orders? This cannot be undone.`"
      confirm-text="Delete All"
      :loading="deleting"
      icon="🗑️"
      variant="danger"
      @confirm="confirmBulkDelete"
      @cancel="showBulkDeleteModal = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import OrderFilters from '@/components/vendor/OrderFilters.vue'
import OrderCard from '@/components/vendor/OrderCard.vue'
import OrderDetailModal from '@/components/vendor/OrderDetailModal.vue'
import ConfirmModal from '@/components/ui/ConfirmModal.vue'
import { useToast } from '@/composables/useToast'

const toast = useToast()

const orders = ref([])
const loading = ref(false)
const selectedStatus = ref('')
const searchQuery = ref('')
const selectedOrders = ref([])

// Modals
const showOrderModal = ref(false)
const selectedOrderId = ref(null)
const showDeleteModal = ref(false)
const showBulkDeleteModal = ref(false)
const deleteTarget = ref(null)
const deleting = ref(false)

const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 20,
  total: 0
})

let searchTimeout = null

const allSelected = computed(() => {
  return orders.value.length > 0 && selectedOrders.value.length === orders.value.length
})

const loadOrders = async () => {
  loading.value = true
  selectedOrders.value = []
  try {
    const params = new URLSearchParams({
      page: pagination.value.current_page.toString(),
      per_page: pagination.value.per_page.toString()
    })

    if (selectedStatus.value) {
      params.append('status', selectedStatus.value)
    }
    if (searchQuery.value) {
      params.append('search', searchQuery.value)
    }

    const response = await fetch(`/api/vendor/orders?${params}`, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json'
      }
    })

    if (response.ok) {
      const data = await response.json()
      // Only show completed and cancelled orders
      orders.value = (data.orders || []).filter(o =>
        o.status === 'ready_for_pickup' || o.status === 'cancelled'
      )
      pagination.value = data.pagination || pagination.value
    }
  } catch (error) {
    console.error('Error loading orders:', error)
  } finally {
    loading.value = false
  }
}

const handleStatusChange = () => {
  pagination.value.current_page = 1
  loadOrders()
}

const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    pagination.value.current_page = 1
    loadOrders()
  }, 300)
}

const changePage = (page) => {
  pagination.value.current_page = page
  loadOrders()
}

// Selection
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
    selectedOrders.value = orders.value.map(o => o.id)
  }
}

// Modal handlers
const openOrderDetail = (order) => {
  selectedOrderId.value = order.id
  showOrderModal.value = true
}

const closeOrderModal = () => {
  showOrderModal.value = false
  selectedOrderId.value = null
}

// Direct receipt download functionality
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

    // Get the PDF blob
    const blob = await response.blob()

    // Create download link
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

// Delete handlers
const openDeleteModal = (order) => {
  deleteTarget.value = order
  showDeleteModal.value = true
}

const openBulkDeleteModal = () => {
  showBulkDeleteModal.value = true
}

const confirmDelete = async () => {
  if (!deleteTarget.value) return
  deleting.value = true
  try {
    const response = await fetch('/api/vendor/orders/batch', {
      method: 'DELETE',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ order_ids: [deleteTarget.value.id] })
    })

    if (response.ok) {
      toast.success('Order deleted')
      await loadOrders()
    } else {
      toast.error('Failed to delete order')
    }
  } catch (error) {
    toast.error('Failed to delete order')
  } finally {
    deleting.value = false
    showDeleteModal.value = false
    deleteTarget.value = null
  }
}

const confirmBulkDelete = async () => {
  if (selectedOrders.value.length === 0) return
  deleting.value = true
  try {
    const response = await fetch('/api/vendor/orders/batch', {
      method: 'DELETE',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ order_ids: selectedOrders.value })
    })

    if (response.ok) {
      toast.success(`${selectedOrders.value.length} orders deleted`)
      selectedOrders.value = []
      await loadOrders()
    } else {
      toast.error('Failed to delete orders')
    }
  } catch (error) {
    toast.error('Failed to delete orders')
  } finally {
    deleting.value = false
    showBulkDeleteModal.value = false
  }
}

onMounted(() => {
  loadOrders()
})
</script>
