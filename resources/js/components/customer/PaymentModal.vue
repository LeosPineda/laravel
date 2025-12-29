<template>
  <div
    class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
    @click="$emit('close')"
  >
    <div
      class="bg-white rounded-xl shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto"
      @click.stop
    >
      <!-- Header -->
      <div class="flex items-center justify-between p-6 border-b border-gray-200">
        <h2 class="text-xl font-bold text-gray-900">Payment & Checkout</h2>
        <button
          @click="$emit('close')"
          class="text-gray-500 hover:text-gray-700 p-2 rounded-lg hover:bg-gray-100"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Content -->
      <div class="p-6 space-y-6">
        <!-- Vendor Selection (if multiple vendors) -->
        <div v-if="vendorGroups.length > 1" class="space-y-4">
          <h3 class="text-lg font-semibold text-gray-900">Select Vendor to Pay</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div
              v-for="vendorGroup in vendorGroups"
              :key="vendorGroup.vendor.id"
              class="border border-gray-200 rounded-lg p-4 cursor-pointer hover:border-orange-300 hover:bg-orange-50 transition-colors"
              :class="{ 'border-orange-500 bg-orange-50': selectedVendor?.id === vendorGroup.vendor.id }"
              @click="selectedVendor = vendorGroup.vendor"
            >
              <div class="flex items-center justify-between">
                <div>
                  <h4 class="font-semibold text-gray-900">{{ vendorGroup.vendor.brand_name }}</h4>
                  <p class="text-sm text-gray-600">{{ vendorGroup.items.length }} items</p>
                </div>
                <div class="text-right">
                  <p class="font-bold text-orange-600">₱{{ vendorGroup.total.toFixed(2) }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Table Number Display -->
        <div v-if="tableNumber" class="bg-orange-50 p-4 rounded-lg">
          <div class="flex items-center gap-2">
            <span class="text-sm font-medium text-orange-700">Table Number: {{ tableNumber }}</span>
          </div>
        </div>

        <!-- Selected Vendor Info -->
        <div v-if="selectedVendor || vendorGroups.length === 1" class="bg-gray-50 p-4 rounded-lg">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
              <span class="text-orange-600 font-medium">
                {{ (selectedVendor || vendorGroups[0].vendor).brand_name.charAt(0) }}
              </span>
            </div>
            <div>
              <h4 class="font-semibold text-gray-900">
                {{ (selectedVendor || vendorGroups[0].vendor).brand_name }}
              </h4>
              <p class="text-sm text-gray-600">
                Amount to pay: ₱{{ selectedVendorAmount.toFixed(2) }}
              </p>
            </div>
          </div>
        </div>

        <!-- Payment Method Selection -->
        <div>
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Choose Payment Method</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Pay to Cashier -->
            <div
              class="border border-gray-200 rounded-lg p-6 cursor-pointer hover:border-green-300 hover:bg-green-50 transition-colors"
              :class="{ 'border-green-500 bg-green-50': paymentMethod === 'cashier' }"
              @click="paymentMethod = 'cashier'"
            >
              <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                  <span class="text-2xl">💵</span>
                </div>
                <div>
                  <h4 class="font-semibold text-gray-900">Pay to Cashier</h4>
                  <p class="text-sm text-gray-600">Pay at the counter</p>
                </div>
              </div>
            </div>

            <!-- QR Code Payment -->
            <div
              class="border border-gray-200 rounded-lg p-6 cursor-pointer hover:border-blue-300 hover:bg-blue-50 transition-colors"
              :class="{ 'border-blue-500 bg-blue-50': paymentMethod === 'qr' }"
              @click="paymentMethod = 'qr'"
            >
              <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                  <span class="text-2xl">📱</span>
                </div>
                <div>
                  <h4 class="font-semibold text-gray-900">QR Code Payment</h4>
                  <p class="text-sm text-gray-600">Scan or enter mobile number</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Payment Method Details -->
        <div v-if="paymentMethod === 'qr'" class="space-y-4">
          <h4 class="font-semibold text-gray-900">QR Code Payment Options</h4>

          <!-- Mobile Number Payment -->
          <div class="border border-gray-200 rounded-lg p-4">
            <div class="flex items-center gap-3 mb-3">
              <input
                v-model="paymentOption"
                value="mobile"
                type="radio"
                id="mobile-payment"
                class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500"
              />
              <label for="mobile-payment" class="font-medium text-gray-900">Pay via Mobile Number</label>
            </div>
            <div v-if="paymentOption === 'mobile'" class="ml-7">
              <input
                v-model="mobileNumber"
                type="tel"
                placeholder="Enter mobile number"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
              />
            </div>
          </div>

          <!-- QR Code Scanning -->
          <div class="border border-gray-200 rounded-lg p-4">
            <div class="flex items-center gap-3 mb-3">
              <input
                v-model="paymentOption"
                value="scan"
                type="radio"
                id="qr-scan"
                class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500"
              />
              <label for="qr-scan" class="font-medium text-gray-900">Scan QR Code</label>
            </div>
            <div v-if="paymentOption === 'scan'" class="ml-7">
              <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center">
                <div class="text-4xl mb-2">📷</div>
                <p class="text-sm text-gray-600 mb-4">Click to scan vendor's QR code</p>
                <button
                  @click="scanQRCode"
                  class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors"
                >
                  Start QR Scanner
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Payment Proof Upload -->
        <div v-if="paymentMethod === 'qr'">
          <h4 class="font-semibold text-gray-900 mb-3">Upload Payment Proof</h4>
          <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
            <div class="text-4xl mb-2">📷</div>
            <p class="text-sm text-gray-600 mb-4">Upload screenshot of payment confirmation</p>
            <input
              ref="fileInput"
              type="file"
              accept="image/*"
              class="hidden"
              @change="handleFileUpload"
            />
            <button
              @click="$refs.fileInput.click()"
              class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors"
            >
              Choose File
            </button>
            <div v-if="paymentProof" class="mt-4 p-3 bg-blue-50 rounded-lg">
              <p class="text-sm text-blue-800">✓ {{ paymentProof.name }} uploaded</p>
            </div>
          </div>
        </div>

        <!-- Order Summary -->
        <div class="bg-orange-50 p-4 rounded-lg">
          <h4 class="font-semibold text-gray-900 mb-3">Order Summary</h4>
          <div class="space-y-2 text-sm">
            <div class="flex justify-between">
              <span>{{ (selectedVendor || vendorGroups[0].vendor).brand_name }}</span>
              <span>₱{{ selectedVendorAmount.toFixed(2) }}</span>
            </div>
            <div v-if="tableNumber" class="flex justify-between">
              <span>Table Number</span>
              <span>{{ tableNumber }}</span>
            </div>
            <div class="border-t border-orange-200 pt-2 flex justify-between font-semibold">
              <span>Total</span>
              <span>₱{{ selectedVendorAmount.toFixed(2) }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="flex items-center justify-end gap-3 p-6 border-t border-gray-200">
        <button
          @click="$emit('close')"
          class="px-4 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
        >
          Cancel
        </button>
        <button
          @click="processPayment"
          class="px-6 py-2 bg-orange-500 text-white font-medium rounded-lg hover:bg-orange-600 transition-colors"
          :disabled="processing || !canProcessPayment"
        >
          <span v-if="processing" class="flex items-center gap-2">
            <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" />
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
            </svg>
            Processing...
          </span>
          <span v-else>
            {{ paymentMethod === 'cashier' ? 'Send Order Request' : 'Submit Payment & Order' }}
          </span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  vendorGroups: {
    type: Array,
    required: true
  },
  tableNumber: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['close', 'process-payment'])

// Local state
const selectedVendor = ref(null)
const paymentMethod = ref('cashier') // 'cashier' or 'qr'
const paymentOption = ref('mobile') // 'mobile' or 'scan'
const mobileNumber = ref('')
const paymentProof = ref(null)
const processing = ref(false)

// Computed
const selectedVendorAmount = computed(() => {
  if (selectedVendor.value) {
    const vendorGroup = props.vendorGroups.find(vg => vg.vendor.id === selectedVendor.value.id)
    return vendorGroup ? vendorGroup.total : 0
  }
  return props.vendorGroups.length === 1 ? props.vendorGroups[0].total : 0
})

const canProcessPayment = computed(() => {
  if (!selectedVendor.value && props.vendorGroups.length > 1) return false

  if (paymentMethod.value === 'cashier') return true

  if (paymentMethod.value === 'qr') {
    if (paymentOption.value === 'mobile') {
      return mobileNumber.value.trim().length > 0
    }
    if (paymentOption.value === 'scan') {
      return paymentProof.value !== null
    }
  }

  return false
})

// Methods
const handleFileUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    paymentProof.value = file
  }
}

const scanQRCode = () => {
  // TODO: Implement QR code scanning
  // This would integrate with camera and QR code detection
  console.log('Scanning QR code...')
  // For now, simulate QR code detection
  setTimeout(() => {
    alert('QR code detected! (Demo)')
  }, 1000)
}

const processPayment = async () => {
  processing.value = true

  try {
    const paymentData = {
      vendor_id: (selectedVendor.value || props.vendorGroups[0].vendor).id,
      vendor_name: (selectedVendor.value || props.vendorGroups[0].vendor).brand_name,
      amount: selectedVendorAmount.value,
      table_number: props.tableNumber,
      payment_method: paymentMethod.value,
      payment_option: paymentMethod.value === 'qr' ? paymentOption.value : null,
      mobile_number: paymentMethod.value === 'qr' && paymentOption.value === 'mobile' ? mobileNumber.value : null,
      payment_proof: paymentProof.value
    }

    emit('process-payment', paymentData)
  } catch (error) {
    console.error('Error processing payment:', error)
  } finally {
    processing.value = false
  }
}
</script>

<style scoped>
/* No additional styles needed - using Tailwind CSS classes */
</style>
