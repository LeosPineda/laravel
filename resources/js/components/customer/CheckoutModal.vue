<template>
  <!-- Checkout Modal - Mobile Bottom Sheet -->
  <div
    v-if="isOpen"
    class="fixed inset-0 z-[60] flex items-end sm:items-center justify-center"
    @click="handleBackdropClick"
  >
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>

    <!-- Modal Content - Bottom sheet on mobile, centered on desktop -->
    <div
      class="relative bg-white w-full sm:max-w-md sm:mx-4 sm:rounded-2xl rounded-t-3xl shadow-2xl transform transition-all duration-300 ease-out max-h-[95vh] sm:max-h-[90vh] flex flex-col"
      :class="{
        'translate-y-0 opacity-100': isOpen,
        'translate-y-4 opacity-0': !isOpen
      }"
      @click.stop
    >
      <!-- Drag Handle (mobile only) -->
      <div class="sm:hidden flex justify-center pt-3 pb-1">
        <div class="w-10 h-1 bg-gray-300 rounded-full"></div>
      </div>
      <!-- Header -->
      <div class="flex items-center justify-between p-4 border-b border-gray-200 flex-shrink-0">
        <div class="flex items-center gap-2">
          <button
            v-if="step !== 'payment-method'"
            @click="goBack"
            class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full transition-colors"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
          </button>
          <h2 class="text-lg font-bold text-gray-900">{{ modalTitle }}</h2>
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
      <div class="flex-1 overflow-y-auto p-4">
        <!-- STEP: Payment Method Selection -->
        <div v-if="step === 'payment-method'" class="space-y-4">
          <p class="text-gray-600 text-center mb-6">Choose Payment Method</p>

          <!-- Pay at Cashier -->
          <button
            @click="selectPaymentMethod('cashier')"
            class="w-full p-4 border-2 rounded-xl text-left hover:border-orange-400 hover:bg-orange-50 transition-all"
            :class="paymentMethod === 'cashier' ? 'border-orange-500 bg-orange-50' : 'border-gray-200'"
          >
            <div class="flex items-center gap-3">
              <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center text-2xl">
                💵
              </div>
              <div>
                <h3 class="font-semibold text-gray-900">PAY AT CASHIER</h3>
                <p class="text-sm text-gray-500">Pay when you pick up your order</p>
              </div>
            </div>
          </button>

          <!-- QR Code Payment -->
          <button
            @click="selectPaymentMethod('qr')"
            class="w-full p-4 border-2 rounded-xl text-left hover:border-orange-400 hover:bg-orange-50 transition-all"
            :class="paymentMethod === 'qr' ? 'border-orange-500 bg-orange-50' : 'border-gray-200'"
          >
            <div class="flex items-center gap-3">
              <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center text-2xl">
                📱
              </div>
              <div>
                <h3 class="font-semibold text-gray-900">QR CODE PAYMENT</h3>
                <p class="text-sm text-gray-500">Scan QR & upload payment proof</p>
              </div>
            </div>
          </button>
        </div>

        <!-- STEP: QR Code Payment -->
        <div v-else-if="step === 'qr-payment'" class="space-y-4">
          <p class="text-gray-600 text-center">Scan QR code to pay:</p>

          <!-- QR Code Image -->
          <div class="flex justify-center">
            <div class="w-48 h-48 bg-gray-100 rounded-xl overflow-hidden">
              <img
                v-if="vendorCart?.vendor?.qr_code_image"
                :src="getImageUrl(vendorCart.vendor.qr_code_image)"
                alt="QR Code"
                class="w-full h-full object-contain"
              />
              <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                <span class="text-sm">No QR Code available</span>
              </div>
            </div>
          </div>

          <!-- Mobile Number -->
          <div v-if="vendorCart?.vendor?.qr_mobile_number" class="text-center">
            <p class="text-sm text-gray-500">Or send payment to:</p>
            <div class="flex items-center justify-center gap-2 mt-1">
              <span class="font-semibold text-gray-900">📱 {{ vendorCart.vendor.qr_mobile_number }}</span>
              <button
                @click="copyMobileNumber"
                class="p-1.5 text-gray-400 hover:text-orange-500 hover:bg-orange-50 rounded transition-colors"
                title="Copy"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                </svg>
              </button>
            </div>
          </div>

          <!-- Upload Payment Proof -->
          <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">
              Upload Payment Proof *
            </label>
            <div
              class="border-2 border-dashed rounded-xl p-6 text-center cursor-pointer transition-colors"
              :class="paymentProof ? 'border-green-400 bg-green-50' : 'border-gray-300 hover:border-orange-400'"
              @click="$refs.fileInput.click()"
            >
              <input
                ref="fileInput"
                type="file"
                accept="image/*"
                class="hidden"
                @change="handleFileUpload"
              />
              <div v-if="paymentProof" class="text-green-600">
                <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <p class="font-medium">{{ paymentProof.name }}</p>
                <p class="text-sm text-green-500">Click to change</p>
              </div>
              <div v-else class="text-gray-400">
                <span class="text-3xl">📷</span>
                <p class="mt-2">Click to upload screenshot/receipt</p>
              </div>
            </div>
          </div>

          <!-- Continue Button -->
          <button
            @click="step = 'order-summary'"
            :disabled="!paymentProof"
            class="w-full py-3 bg-orange-500 hover:bg-orange-600 disabled:bg-gray-300 text-white font-medium rounded-xl transition-colors"
          >
            Continue →
          </button>
        </div>

        <!-- STEP: Order Summary -->
        <div v-else-if="step === 'order-summary'" class="space-y-4">
          <!-- Order Items -->
          <div class="space-y-2">
            <h4 class="font-semibold text-gray-900">Your Order</h4>
            <div class="bg-gray-50 rounded-xl p-3 space-y-2">
              <div v-for="item in vendorCart?.items" :key="item.id" class="flex justify-between text-sm">
                <span class="text-gray-700">{{ item.product.name }} ×{{ item.quantity }}</span>
                <span class="font-medium">₱{{ formatPrice(item.unit_price * item.quantity) }}</span>
              </div>
              <!-- Addons -->
              <template v-for="item in vendorCart?.items" :key="'addon-' + item.id">
                <div v-for="addon in item.selected_addons" :key="addon.addon_id" class="flex justify-between text-xs text-gray-500 pl-4">
                  <span>+ {{ addon.name || 'Addon' }} (×{{ item.quantity }})</span>
                  <span>+ ₱{{ formatPrice(addon.price * item.quantity) }}</span>
                </div>
              </template>
            </div>

            <!-- Subtotal -->
            <div class="flex justify-between font-semibold border-t pt-2">
              <span>Subtotal</span>
              <span class="text-orange-600">₱{{ formatPrice(orderTotal) }}</span>
            </div>
          </div>

          <!-- Payment Method Display -->
          <div class="space-y-2">
            <h4 class="font-semibold text-gray-900">Payment Method</h4>
            <div class="bg-gray-50 rounded-xl p-3 flex items-center gap-2">
              <span class="text-xl">{{ paymentMethod === 'cashier' ? '💵' : '📱' }}</span>
              <span class="text-gray-700">
                {{ paymentMethod === 'cashier' ? 'Pay at Cashier' : 'QR Code Payment' }}
              </span>
              <span v-if="paymentProof" class="ml-auto text-green-600 text-sm">✓ Proof uploaded</span>
            </div>
          </div>

          <!-- Table Number -->
          <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">
              Table Number *
            </label>
            <input
              v-model="tableNumber"
              type="text"
              placeholder="Enter table number (e.g., A1, B5)"
              class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent"
            />
          </div>

          <!-- Special Instructions -->
          <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">
              Special Instructions (Optional)
            </label>
            <textarea
              v-model="specialInstructions"
              placeholder="Any allergies or special requests..."
              rows="3"
              class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent resize-none"
            ></textarea>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div v-if="step === 'order-summary'" class="p-4 border-t border-gray-200 flex-shrink-0">
        <button
          @click="submitOrder"
          :disabled="!canSubmitOrder || submitting"
          class="w-full py-3 bg-orange-500 hover:bg-orange-600 disabled:bg-gray-300 text-white font-medium rounded-xl transition-colors flex items-center justify-center gap-2"
        >
          <svg v-if="submitting" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
          </svg>
          {{ submitting ? 'Sending...' : `📤 Send Order - ₱${formatPrice(orderTotal)}` }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
  vendorCart: {
    type: Object,
    default: null
  },
  isOpen: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['close', 'complete'])

// State
const step = ref('payment-method') // 'payment-method', 'qr-payment', 'order-summary'
const paymentMethod = ref(null) // 'cashier' or 'qr'
const paymentProof = ref(null)
const tableNumber = ref('')
const specialInstructions = ref('')
const submitting = ref(false)

// Computed
const modalTitle = computed(() => {
  const vendorName = props.vendorCart?.vendor?.brand_name || 'Checkout'
  switch (step.value) {
    case 'payment-method':
      return `Checkout - ${vendorName}`
    case 'qr-payment':
      return `QR Payment - ${vendorName}`
    case 'order-summary':
      return `Order Summary - ${vendorName}`
    default:
      return vendorName
  }
})

const orderTotal = computed(() => {
  if (!props.vendorCart?.items) return 0
  return props.vendorCart.items.reduce((total, item) => {
    return total + (item.total_price || item.unit_price * item.quantity)
  }, 0)
})

const canSubmitOrder = computed(() => {
  return tableNumber.value.trim() !== ''
})

// Methods
const formatPrice = (price) => {
  const num = typeof price === 'string' ? parseFloat(price) : price
  return num ? num.toFixed(2) : '0.00'
}

const getImageUrl = (imageUrl) => {
  if (!imageUrl) return ''
  if (imageUrl.startsWith('http://') || imageUrl.startsWith('https://')) {
    return imageUrl
  }
  if (imageUrl.startsWith('storage/')) {
    return `/${imageUrl}`
  }
  return `/storage/${imageUrl}`
}

const handleBackdropClick = () => {
  closeModal()
}

const closeModal = () => {
  emit('close')
}

const goBack = () => {
  if (step.value === 'order-summary' && paymentMethod.value === 'qr') {
    step.value = 'qr-payment'
  } else {
    step.value = 'payment-method'
  }
}

const selectPaymentMethod = (method) => {
  paymentMethod.value = method
  if (method === 'cashier') {
    step.value = 'order-summary'
  } else {
    step.value = 'qr-payment'
  }
}

const copyMobileNumber = async () => {
  if (props.vendorCart?.vendor?.qr_mobile_number) {
    try {
      await navigator.clipboard.writeText(props.vendorCart.vendor.qr_mobile_number)
      // Could show a toast here
    } catch (err) {
      console.error('Failed to copy:', err)
    }
  }
}

const handleFileUpload = (event) => {
  const file = event.target.files?.[0]
  if (file) {
    paymentProof.value = file
  }
}

const submitOrder = async () => {
  if (!canSubmitOrder.value || submitting.value) return

  submitting.value = true

  try {
    // Build form data for order
    const formData = new FormData()
    formData.append('vendor_id', props.vendorCart.vendor.id)
    formData.append('table_number', tableNumber.value)
    formData.append('payment_method', paymentMethod.value)
    formData.append('special_instructions', specialInstructions.value || '')

    // Add items
    props.vendorCart.items.forEach((item, index) => {
      formData.append(`items[${index}][cart_item_id]`, item.id)
      formData.append(`items[${index}][product_id]`, item.product_id)
      formData.append(`items[${index}][quantity]`, item.quantity)
      formData.append(`items[${index}][unit_price]`, item.unit_price)

      // Add addons
      if (item.selected_addons) {
        item.selected_addons.forEach((addon, addonIndex) => {
          formData.append(`items[${index}][addons][${addonIndex}][addon_id]`, addon.addon_id)
          formData.append(`items[${index}][addons][${addonIndex}][quantity]`, addon.quantity || 1)
          formData.append(`items[${index}][addons][${addonIndex}][price]`, addon.price)
        })
      }
    })

    // Add payment proof if QR payment
    if (paymentMethod.value === 'qr' && paymentProof.value) {
      formData.append('payment_proof', paymentProof.value)
    }

    const response = await fetch('/api/customer/orders', {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      credentials: 'include',
      body: formData
    })

    if (response.ok) {
      const data = await response.json()
      emit('complete', data)
    } else {
      const errorData = await response.json()
      throw new Error(errorData.message || 'Failed to create order')
    }
  } catch (err) {
    console.error('Error submitting order:', err)
    // Could emit error event or show toast
  } finally {
    submitting.value = false
  }
}

// Reset state when modal opens
watch(() => props.isOpen, (isOpen) => {
  if (isOpen) {
    step.value = 'payment-method'
    paymentMethod.value = null
    paymentProof.value = null
    tableNumber.value = ''
    specialInstructions.value = ''
    submitting.value = false
  }
})
</script>

<style scoped>
/* Smooth transitions */
* {
  transition-property: color, background-color, border-color, opacity, box-shadow, transform;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}

/* Custom scrollbar */
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}
</style>
