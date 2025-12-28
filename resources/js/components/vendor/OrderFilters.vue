<template>
  <div class="bg-white rounded-lg border border-gray-200 p-4 mb-6">
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
      <!-- Search Input -->
      <div class="flex-1 max-w-md">
        <div class="relative">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
          <input
            v-model="searchQueryLocal"
            @input="handleSearchInput"
            type="text"
            placeholder="Search orders..."
            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500"
          />
        </div>
      </div>

      <!-- Status Filter -->
      <div class="flex items-center gap-4">
        <div class="flex items-center gap-2">
          <label for="status-filter" class="text-sm font-medium text-gray-700">
            Status:
          </label>
          <select
            id="status-filter"
            v-model="selectedStatusLocal"
            @change="handleStatusChange"
            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-orange-500 focus:border-orange-500"
          >
            <option value="">All Status</option>
            <option value="ready_for_pickup">Ready for Pickup</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>

        <!-- Bulk Actions -->
        <div v-if="orders.length > 0" class="flex items-center gap-2">
          <button
            @click="handleToggleSelectAll"
            class="text-sm text-gray-600 hover:text-gray-900"
          >
            {{ allSelected ? 'Deselect All' : 'Select All' }}
          </button>

          <button
            v-if="selectedOrders.length > 0"
            @click="handleBulkDelete"
            class="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700 transition-colors"
          >
            Delete Selected ({{ selectedOrders.length }})
          </button>
        </div>
      </div>
    </div>

    <!-- Order Summary -->
    <div v-if="orders.length > 0" class="mt-4 pt-4 border-t border-gray-200">
      <div class="flex items-center justify-between text-sm text-gray-600">
        <span>{{ orders.length }} orders shown</span>
        <span v-if="selectedOrders.length > 0" class="text-orange-600">
          {{ selectedOrders.length }} selected
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  selectedStatus: {
    type: String,
    default: ''
  },
  searchQuery: {
    type: String,
    default: ''
  },
  orders: {
    type: Array,
    default: () => []
  },
  selectedOrders: {
    type: Array,
    default: () => []
  },
  allSelected: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits([
  'update:selected-status',
  'update:search-query',
  'status-change',
  'search',
  'toggle-select-all',
  'open-bulk-delete'
])

// Local reactive state for v-model bindings
const selectedStatusLocal = ref(props.selectedStatus)
const searchQueryLocal = ref(props.searchQuery)

// Watch for prop changes and update local state
watch(() => props.selectedStatus, (newValue) => {
  selectedStatusLocal.value = newValue
})

watch(() => props.searchQuery, (newValue) => {
  searchQueryLocal.value = newValue
})

// Event handlers
const handleStatusChange = () => {
  emit('update:selected-status', selectedStatusLocal.value)
  emit('status-change')
}

const handleSearchInput = () => {
  emit('update:search-query', searchQueryLocal.value)
  emit('search')
}

const handleToggleSelectAll = () => {
  emit('toggle-select-all')
}

const handleBulkDelete = () => {
  emit('open-bulk-delete')
}
</script>
