<template>
  <div
    v-if="isOpen"
    class="fixed inset-0 z-[80] flex items-center justify-center bg-black/50"
    @click="handleBackdropClick"
  >
    <!-- Modal Content -->
    <div
      class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 transform transition-all duration-300"
      :class="{
        'translate-y-0 opacity-100': isOpen,
        'translate-y-4 opacity-0': !isOpen
      }"
      @click.stop
    >
      <!-- Header -->
      <div class="flex items-center justify-between p-6 border-b border-gray-200">
        <div>
          <h3 class="text-lg font-bold text-gray-900">Decline Order</h3>
          <p class="text-sm text-gray-500 mt-1">Please provide a reason for declining this order</p>
        </div>
        <button
          @click="closeModal"
          class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full transition-colors"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Content -->
      <div class="p-6 space-y-6">
        <!-- Order Info -->
        <div class="bg-gray-50 p-4 rounded-lg">
          <div class="text-sm text-gray-600">Order #{{ order?.order_number }}</div>
          <div class="text-sm text-gray-600">Table {{ order?.table_number }}</div>
          <div class="text-lg font-semibold text-gray-900">₱{{ order?.total_amount?.toFixed(2) }}</div>
        </div>

        <!-- Pre-written Reason -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-3">
            Select a reason (recommended):
          </label>
          <div class="space-y-2">
            <label
              class="flex items-center p-3 border rounded-lg cursor-pointer transition-colors"
              :class="selectedReason === 'cannot_prepare'
                ? 'border-orange-400 bg-orange-50'
                : 'border-gray-200 hover:border-orange-200 hover:bg-orange-25'"
            >
              <input
                type="radio"
                name="declineReason"
                value="cannot_prepare"
                v-model="selectedReason"
                class="w-4 h-4 text-orange-600 border-gray-300 focus:ring-orange-500"
              />
              <span class="ml-3 text-sm text-gray-900">Cannot prepare the order at the moment</span>
            </label>
          </div>
        </div>

        <!-- Custom Reason -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-3">
            Or write a custom reason:
          </label>
          <textarea
            v-model="customReason"
            placeholder="Enter your reason here..."
            rows="3"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 resize-none"
            :disabled="processing"
          ></textarea>
        </div>

        <!-- Error Message -->
        <div v-if="error" class="text-red-600 text-sm">
          {{ error }}
        </div>
      </div>

      <!-- Footer -->
      <div class="flex items-center justify-end p-6 border-t border-gray-200">
        <button
          @click="confirmDecline"
          :disabled="processing || !canConfirm"
          class="px-6 py-2 bg-red-500 hover:bg-red-600 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-medium rounded-lg transition-colors flex items-center justify-center"
        >
          <svg v-if="processing" class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          {{ processing ? 'Declining...' : 'Decline Order' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'

interface Order {
  id: number
  order_number: string
  table_number: string
  total_amount: number
}

interface Props {
  isOpen: boolean
  order: Order | null
}

interface Emits {
  (e: 'close'): void
  (e: 'decline', reason: string): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const selectedReason = ref('')
const customReason = ref('')
const error = ref('')
const processing = ref(false)

// Pre-defined decline reasons
const predefinedReasons = [
  {
    value: 'cannot_prepare',
    label: 'Cannot prepare the order at the moment'
  }
]

// Computed properties
const canConfirm = computed(() => {
  return selectedReason.value || customReason.value.trim().length > 0
})

// Methods
const closeModal = () => {
  if (processing.value) return

  selectedReason.value = ''
  customReason.value = ''
  error.value = ''
  emit('close')
}

const handleBackdropClick = () => {
  closeModal()
}

const confirmDecline = async () => {
  if (!canConfirm.value) {
    error.value = 'Please select a reason or write a custom reason.'
    return
  }

  processing.value = true
  error.value = ''

  try {
    const reason = selectedReason.value || customReason.value.trim()

    // Emit the decline event - parent will handle API call
    emit('decline', reason)

    // Wait a bit then close modal
    setTimeout(() => {
      selectedReason.value = ''
      customReason.value = ''
      error.value = ''
      processing.value = false
      emit('close')
    }, 1000)
  } catch (err) {
    console.error('Error declining order:', err)
    error.value = 'Failed to decline order. Please try again.'
    processing.value = false
  }
}
</script>

<style scoped>
/* Additional hover effects */
.hover\:bg-orange-25:hover {
  background-color: #fff7ed;
}
</style>
