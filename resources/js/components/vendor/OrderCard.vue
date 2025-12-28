<template>
  <div
    class="bg-white border rounded-lg p-4 hover:shadow-md transition-shadow cursor-pointer"
    :class="[
      selected ? 'border-orange-500 bg-orange-50' : 'border-gray-200',
      'relative'
    ]"
  >
    <!-- Selection Checkbox -->
    <div class="absolute top-4 left-4">
      <input
        type="checkbox"
        :checked="selected"
        @change="handleToggleSelection"
        class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded"
      />
    </div>

    <!-- Order Content -->
    <div class="ml-8">
      <div class="flex items-center justify-between mb-2">
        <h3 class="text-lg font-semibold text-gray-900">
          Order #{{ order.order_number }}
        </h3>
        <span
          class="px-3 py-1 rounded-full text-xs font-medium"
          :class="statusClasses[order.status] || 'bg-gray-100 text-gray-800'"
        >
          {{ statusLabels[order.status] || order.status }}
        </span>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm text-gray-600">
        <div>
          <span class="font-medium">Customer:</span>
          <span class="ml-1">{{ order.customer?.name || 'N/A' }}</span>
        </div>
        <div>
          <span class="font-medium">Date:</span>
          <span class="ml-1">{{ formatDate(order.created_at) }}</span>
        </div>
        <div>
          <span class="font-medium">Total:</span>
          <span class="ml-1 font-semibold text-gray-900">
            ₱{{ order.total_amount?.toFixed(2) || '0.00' }}
          </span>
        </div>
        <div>
          <span class="font-medium">Items:</span>
          <span class="ml-1">{{ order.items?.length || 0 }} items</span>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex items-center gap-2 mt-4 pt-4 border-t border-gray-100">
        <button
          @click="handleView"
          class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg text-sm font-medium transition-colors"
        >
          View Details
        </button>

        <button
          @click="handleReceipt"
          class="flex-1 bg-blue-100 hover:bg-blue-200 text-blue-700 px-3 py-2 rounded-lg text-sm font-medium transition-colors"
        >
          Download Receipt
        </button>

        <button
          @click="handleDelete"
          class="flex-1 bg-red-100 hover:bg-red-200 text-red-700 px-3 py-2 rounded-lg text-sm font-medium transition-colors"
        >
          Delete
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  order: {
    type: Object,
    required: true
  },
  selected: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['view', 'receipt', 'delete', 'toggle-selection'])

// Status styling and labels
const statusClasses = {
  'ready_for_pickup': 'bg-green-100 text-green-800',
  'cancelled': 'bg-red-100 text-red-800'
}

const statusLabels = {
  'ready_for_pickup': 'Ready for Pickup',
  'cancelled': 'Cancelled'
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// Event handlers
const handleView = () => {
  emit('view', props.order)
}

const handleReceipt = () => {
  emit('receipt', props.order)
}

const handleDelete = () => {
  emit('delete', props.order)
}

const handleToggleSelection = () => {
  emit('toggle-selection', props.order.id)
}
</script>
