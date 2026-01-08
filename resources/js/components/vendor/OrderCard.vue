<template>
  <div
    class="bg-white border border-gray-200 rounded-lg p-3 hover:shadow-sm transition-all cursor-pointer relative"
    :class="[
      selected ? 'border-orange-500 bg-orange-50' : 'border-gray-200 hover:border-gray-300'
    ]"
  >
    <!-- Selection Checkbox -->
    <div class="absolute top-2 left-2">
      <input
        type="checkbox"
        :checked="selected"
        @change="handleToggleSelection"
        class="h-3 w-3 text-orange-600 focus:ring-orange-500 border-gray-300 rounded"
      />
    </div>

    <!-- Compact Order Content -->
    <div class="ml-6">
      <!-- Header Row -->
      <div class="flex items-center justify-between mb-2">
        <div class="flex items-center gap-2">
          <h3 class="text-sm font-semibold text-gray-900">
            #{{ shortOrderNumber }}
          </h3>
          <span class="text-xs text-gray-500">Table {{ order.table_number }}</span>
        </div>
        <span
          class="px-2 py-1 rounded-full text-xs font-medium"
          :class="statusClasses[order.status] || 'bg-gray-100 text-gray-600'"
        >
          {{ shortStatus }}
        </span>
      </div>

      <!-- Items Summary -->
      <div class="text-xs text-gray-600 mb-2 line-clamp-2">
        {{ itemsSummary }}
      </div>

      <!-- Special Instructions -->
      <div v-if="order.special_instructions" class="text-xs text-amber-600 mb-2">
        ⚠️ {{ order.special_instructions }}
      </div>

      <!-- Footer Row -->
      <div class="flex items-center justify-between">
        <div class="text-sm font-semibold text-gray-900">
          ₱{{ (Number(order.total_amount) || 0).toFixed(0) }}
        </div>

        <!-- Compact Action Buttons -->
        <div class="flex gap-1">
          <button
            @click="handleView"
            class="text-xs px-2 py-1 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded transition-colors"
          >
            Details
          </button>

          <button
            v-if="order.status === 'pending'"
            @click="handleAccept"
            class="text-xs px-2 py-1 bg-green-100 hover:bg-green-200 text-green-700 rounded transition-colors"
          >
            ✓ Accept
          </button>

          <button
            v-if="order.status === 'pending'"
            @click="handleDecline"
            class="text-xs px-2 py-1 bg-red-100 hover:bg-red-200 text-red-700 rounded transition-colors"
          >
            Decline
          </button>

          <button
            v-if="order.status === 'accepted'"
            @click="handleMarkReady"
            class="text-xs px-2 py-1 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded transition-colors"
          >
            ✓ Ready
          </button>

          <button
            @click="handleReceipt"
            class="text-xs px-2 py-1 bg-blue-100 hover:bg-blue-200 text-blue-600 rounded transition-colors"
          >
            Receipt
          </button>
        </div>
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

const emit = defineEmits(['view', 'receipt', 'delete', 'toggle-selection', 'accept', 'decline', 'mark-ready'])

// Computed properties for compact display
const shortOrderNumber = computed(() => {
  return props.order.order_number?.replace('ORD-', '') || '000000'
})

const shortStatus = computed(() => {
  const statusMap = {
    'pending': 'Pending',
    'accepted': 'Preparing',
    'ready_for_pickup': 'Ready',
    'cancelled': 'Cancelled'
  }
  return statusMap[props.order.status] || props.order.status
})

const itemsSummary = computed(() => {
  if (!props.order.items || props.order.items.length === 0) return 'No items'

  const summary = props.order.items.slice(0, 2).map(item => {
    const qty = item.quantity || 1
    const name = item.product?.name || 'Item'
    return `${qty}x ${name}`
  }).join(', ')

  if (props.order.items.length > 2) {
    return `${summary} +${props.order.items.length - 2} more`
  }
  return summary
})

// Status styling and labels
const statusClasses = {
  'pending': 'bg-yellow-100 text-yellow-800',
  'accepted': 'bg-blue-100 text-blue-800',
  'ready_for_pickup': 'bg-green-100 text-green-800',
  'cancelled': 'bg-red-100 text-red-800'
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

const handleAccept = () => {
  emit('accept', props.order)
}

const handleDecline = () => {
  emit('decline', props.order)
}

const handleMarkReady = () => {
  emit('mark-ready', props.order)
}
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
