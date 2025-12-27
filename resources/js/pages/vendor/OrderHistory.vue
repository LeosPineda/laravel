<template>
  <div class="bg-white">
    <!-- Content -->
    <div class="p-6">
      <div class="max-w-4xl mx-auto">
        <!-- Filters & Bulk Actions -->
        <div class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
          <div class="flex items-center gap-4 flex-wrap">
            <!-- Status Filter -->
            <select
              v-model="selectedStatus"
              @change="handleStatusChange"
              class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
            >
              <option value="">All History</option>
              <option value="ready_for_pickup">✅ Completed</option>
              <option value="cancelled">❌ Declined</option>
            </select>

            <!-- Search -->
            <input
              v-model="searchQuery"
              @input="debouncedSearch"
              type="text"
              placeholder="Search order # or table..."
              class="flex-1 min-w-40 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
            />

            <!-- Bulk Actions -->
            <div class="flex items-center gap-2">
              <button
                v-if="orders.length > 0"
                @click="toggleSelectAll"
                class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50"
              >
                {{ allSelected ? 'Deselect All' : 'Select All' }}
              </button>
              <button
                v-if="selectedOrders.length > 0"
                @click="openBulkDeleteModal"
                class="px-3 py-2 text-sm bg-red-100 text-red-700 rounded-lg hover:bg-red-200"
              >
                Delete ({{ selectedOrders.length }})
              </button>
            </div>
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="text-center py-12">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-orange-500 mx-auto"></div>
          <p class="text-gray-500 mt-4">Loading orders...</p>
        </div>

        <!-- Orders List -->
        <div v-else-if="orders.length > 0" class="space-y-3">
          <div
            v-for="order in orders"
            :key="order.id"
            :class="[
              'bg-white rounded-xl border-2 p-4 transition-all',
              selectedOrders.includes(order.id) ? 'border-orange-400 bg-orange-50' : getBorderColor(order.status),
              'hover:shadow-md'
            ]"
          >
            <div class="flex items-start gap-3">
              <!-- Checkbox -->
              <input
                type="checkbox"
                :checked="selectedOrders.includes(order.id)"
                @change="toggleOrderSelection(order.id)"
                class="mt-1 w-4 h-4 text-orange-500 rounded border-gray-300 focus:ring-orange-500"
              />

              <!-- Order Info -->
              <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between flex-wrap gap-2 mb-2">
                  <div class="flex items-center gap-2 flex-wrap">
                    <span class="font-bold text-gray-900">#{{ order.order_number }}</span>
                    <span
                      :class="[
                        'px-2 py-0.5 rounded-full text-xs font-medium',
                        order.status === 'ready_for_pickup' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'
                      ]"
                    >
                      {{ order.status === 'ready_for_pickup' ? 'Completed' : 'Declined' }}
                    </span>
                    <span class="text-sm text-gray-500">Table {{ order.table_number || 'N/A' }}</span>
                  </div>
                  <span class="font-bold text-orange-600">₱{{ parseFloat(order.total_amount).toFixed(2) }}</span>
                </div>

                <!-- Items -->
                <div class="text-sm text-gray-600 mb-2">
                  <span v-for="(item, idx) in order.items?.slice(0, 3)" :key="item.id">
                    {{ item.quantity }}x {{ item.product?.name }}<span v-if="idx < Math.min(order.items.length, 3) - 1">, </span>
                  </span>
                  <span v-if="order.items?.length > 3" class="text-gray-400"> +{{ order.items.length - 3 }} more</span>
                </div>

                <!-- Timestamp -->
                <div class="text-xs text-gray-400">
                  {{ formatDateTime(order.created_at) }}
                  <span v-if="order.status === 'ready_for_pickup' && order.completed_at">
                    • Completed {{ formatDateTime(order.completed_at) }}
                  </span>
                </div>
              </div>

              <!-- Actions -->
              <div class="flex items-center gap-2 flex-shrink-0">
                <!-- View Receipt (completed only) -->
                <button
                  v-if="order.status === 'ready_for_pickup'"
                  @click="openReceiptModal(order)"
                  class="px-3 py-1.5 text-sm bg-green-100 text-green-700 rounded-lg hover:bg-green-200"
                >
                  📄 Receipt
                </button>
                <button
                  @click="openOrderDetail(order)"
                  class="px-3 py-1.5 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200"
                >
                  View
                </button>
                <button
                  @click="openDeleteModal(order)"
                  class="px-3 py-1.5 text-sm bg-red-100 text-red-700 rounded-lg hover:bg-red-200"
                >
                  🗑️
                </button>
              </div>
            </div>
          </div>
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

    <!-- Receipt Modal -->
    <Teleport to="body">
      <div
        v-if="showReceiptModal && receiptOrder"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
      >
        <div class="fixed inset-0 bg-black/50" @click="showReceiptModal = false"></div>
        <div class="relative bg-white rounded-2xl max-w-md w-full max-h-[80vh] overflow-y-auto" @click.stop>
          <!-- Receipt Header -->
          <div class="bg-orange-500 text-white px-6 py-4 rounded-t-2xl">
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-bold">Order Receipt</h3>
              <button @click="showReceiptModal = false" class="text-white/80 hover:text-white text-xl">×</button>
            </div>
          </div>

          <!-- Receipt Content -->
          <div class="p-6">
            <div class="text-center mb-4">
              <p class="text-2xl font-bold text-gray-900">#{{ receiptOrder.order_number }}</p>
              <p class="text-sm text-gray-500">Table {{ receiptOrder.table_number || 'N/A' }}</p>
            </div>

            <div class="border-t border-b border-dashed border-gray-300 py-4 my-4">
              <div v-for="item in receiptOrder.items" :key="item.id" class="flex justify-between mb-2 text-sm">
                <span>{{ item.quantity }}x {{ item.product?.name }}</span>
                <span>₱{{ parseFloat(item.total_price).toFixed(2) }}</span>
              </div>
            </div>

            <div class="flex justify-between font-bold text-lg">
              <span>Total</span>
              <span class="text-orange-600">₱{{ parseFloat(receiptOrder.total_amount).toFixed(2) }}</span>
            </div>

            <div class="mt-4 text-xs text-gray-400 text-center">
              <p>{{ formatDateTime(receiptOrder.completed_at || receiptOrder.created_at) }}</p>
              <p>Payment: {{ receiptOrder.payment_method === 'qr_code' ? 'QR Code' : 'Cash' }}</p>
            </div>
          </div>

          <!-- Download Button -->
          <div class="px-6 pb-6">
            <button
              @click="downloadReceipt"
              class="w-full py-3 bg-orange-500 text-white rounded-xl hover:bg-orange-600 font-medium"
            >
              📥 Download Receipt
            </button>
          </div>
        </div>
      </div>
    </Teleport>

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
const showReceiptModal = ref(false)
const receiptOrder = ref(null)
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

const getBorderColor = (status) => {
  return status === 'ready_for_pickup' ? 'border-green-200' : 'border-red-200'
}

const formatDateTime = (dateString) => {
  if (!dateString) return ''
  return new Date(dateString).toLocaleString('en-PH', {
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

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

const openReceiptModal = async (order) => {
  // Load full order details for receipt
  try {
    const response = await fetch(`/api/vendor/orders/${order.id}`, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json'
      }
    })

    if (response.ok) {
      const data = await response.json()
      receiptOrder.value = data.order
      showReceiptModal.value = true
    } else {
      toast.error('Failed to load receipt')
    }
  } catch (error) {
    console.error('Error loading receipt:', error)
    toast.error('Failed to load receipt')
  }
}

const downloadReceipt = () => {
  // Create printable receipt
  const receiptContent = `
ORDER RECEIPT
=============
Order #: ${receiptOrder.value.order_number}
Table: ${receiptOrder.value.table_number || 'N/A'}
Date: ${formatDateTime(receiptOrder.value.completed_at || receiptOrder.value.created_at)}

Items:
${receiptOrder.value.items?.map(i => `${i.quantity}x ${i.product?.name} - ₱${parseFloat(i.total_price).toFixed(2)}`).join('\n')}

TOTAL: ₱${parseFloat(receiptOrder.value.total_amount).toFixed(2)}
Payment: ${receiptOrder.value.payment_method === 'qr_code' ? 'QR Code' : 'Cash'}
  `.trim()

  const blob = new Blob([receiptContent], { type: 'text/plain' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `receipt-${receiptOrder.value.order_number}.txt`
  a.click()
  URL.revokeObjectURL(url)

  toast.success('Receipt downloaded!')
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

onMounted(async () => {
  await loadOrders()
})

defineExpose({ loadOrders })
</script>
