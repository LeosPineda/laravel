<template>
  <VendorLayout>
    <div class="bg-white">
      <!-- Header -->
      <div class="bg-white border-b border-gray-200 px-6 py-4">
        <h1 class="text-xl font-bold text-gray-900">QR Code Payment</h1>
      </div>

      <!-- QR Code Content -->
      <div class="p-6">
        <div class="max-w-4xl mx-auto">
          <!-- Mobile Number Section - Always Visible -->
          <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Mobile Number for Payments</h2>
            <p class="text-sm text-gray-600 mb-4">
              Optional mobile number that customers can copy if QR code scanning fails.
              This number will appear alongside your QR code at checkout.
            </p>

            <!-- Success Message -->
            <div v-if="mobileUpdateSuccess" class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg flex items-center gap-2">
              <span class="text-green-600">✓</span>
              <span class="text-green-700 text-sm">Mobile number updated successfully!</span>
            </div>

            <div class="flex gap-2">
              <input
                v-model="mobileNumber"
                type="text"
                placeholder="09123456789"
                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                :class="{ 'border-green-500': mobileUpdateSuccess }"
              />
              <button
                @click="updateMobileNumber"
                :disabled="updatingMobile"
                class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 disabled:opacity-50"
              >
                {{ updatingMobile ? 'Saving...' : 'Save' }}
              </button>
            </div>

            <!-- Saved indicator -->
            <p v-if="qrData.mobile_number" class="text-xs text-gray-500 mt-2">
              Currently saved: <span class="font-medium text-gray-700">{{ qrData.mobile_number }}</span>
            </p>
          </div>

          <!-- Current QR Code Section -->
          <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Current QR Code</h2>

            <div v-if="qrData.has_qr_code" class="flex flex-col md:flex-row gap-6">
              <!-- QR Code Image -->
              <div class="flex-shrink-0">
                <div class="w-48 h-48 border-2 border-gray-200 rounded-lg flex items-center justify-center bg-gray-50">
                  <img :src="qrData.qr_code_url" alt="QR Code" class="max-w-full max-h-full object-contain" />
                </div>
              </div>

              <!-- QR Code Actions -->
              <div class="flex-1">
                <div class="space-y-4">
                  <div class="flex gap-2">
                    <button
                      @click="showPreviewModal = true"
                      class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600"
                    >
                      Preview
                    </button>
                    <button
                      @click="removeQr"
                      class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600"
                    >
                      Remove QR
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- No QR Code State -->
            <div v-else class="text-center py-8">
              <div class="text-4xl mb-4">📱</div>
              <p class="text-gray-500 mb-4">No QR code uploaded yet</p>
            </div>
          </div>

          <!-- Upload New QR Code -->
          <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Upload QR Code</h2>

            <form @submit.prevent="uploadQr">
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">QR Code Image</label>
                  <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-orange-500 transition-colors">
                    <input
                      ref="fileInput"
                      type="file"
                      accept="image/*"
                      @change="handleFileSelect"
                      class="hidden"
                    />
                    <div v-if="!selectedFile" class="space-y-2">
                      <div class="text-2xl">📷</div>
                      <p class="text-sm text-gray-600">Click to upload or drag and drop</p>
                      <p class="text-xs text-gray-500">PNG, JPG, GIF up to 1MB</p>
                      <button
                        type="button"
                        @click="$refs.fileInput.click()"
                        class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600"
                      >
                        Select File
                      </button>
                    </div>
                    <div v-else class="space-y-2">
                      <div class="text-green-600">✅</div>
                      <p class="text-sm text-gray-900">{{ selectedFile.name }}</p>
                      <p class="text-xs text-gray-500">{{ formatFileSize(selectedFile.size) }}</p>
                      <button
                        type="button"
                        @click="clearSelectedFile"
                        class="text-red-600 hover:text-red-700"
                      >
                        Remove
                      </button>
                    </div>
                  </div>
                </div>

                <div v-if="validationError" class="text-red-600 text-sm">
                  {{ validationError }}
                </div>

                <button
                  type="submit"
                  :disabled="!selectedFile || uploading"
                  class="w-full py-3 bg-orange-500 text-white rounded-xl hover:bg-orange-600 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  {{ uploading ? 'Uploading...' : 'Upload QR Code' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Preview Modal -->
    <Teleport to="body">
      <div
        v-if="showPreviewModal"
        class="fixed inset-0 z-50 flex items-center justify-center"
        @click="showPreviewModal = false"
      >
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/70"></div>

        <!-- Modal Content -->
        <div class="relative z-10 max-w-lg w-full mx-4" @click.stop>
          <div class="bg-white rounded-2xl p-6">
            <!-- Header -->
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-bold text-gray-900">QR Code Preview</h3>
              <button
                @click="showPreviewModal = false"
                class="text-gray-400 hover:text-gray-600 text-2xl leading-none"
              >
                ×
              </button>
            </div>

            <!-- QR Image -->
            <div class="flex justify-center mb-4">
              <img
                :src="qrData.qr_code_url"
                alt="QR Code Preview"
                class="max-w-full max-h-96 object-contain rounded-lg border border-gray-200"
              />
            </div>

            <!-- Close Button -->
            <button
              @click="showPreviewModal = false"
              class="w-full py-3 bg-gray-500 text-white rounded-xl hover:bg-gray-600"
            >
              Close
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </VendorLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import VendorLayout from '@/layouts/vendor/VendorLayout.vue'
import { apiGet, apiPatch, apiDelete, apiUpload } from '@/composables/useApi'

const qrData = ref({
  has_qr_code: false,
  qr_code_url: null,
  mobile_number: null,
  last_updated: null
})

const mobileNumber = ref('')
const selectedFile = ref(null)
const uploading = ref(false)
const updatingMobile = ref(false)
const validationError = ref('')
const showPreviewModal = ref(false)
const mobileUpdateSuccess = ref(false)

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

const loadQrData = async () => {
  try {
    const response = await apiGet('/api/vendor/qr')
    if (response.ok) {
      const data = await response.json()
      qrData.value = data
      mobileNumber.value = data.mobile_number || ''
    }
  } catch (error) {
    console.error('Error loading QR data:', error)
  }
}

const handleFileSelect = (event) => {
  const file = event.target.files[0]
  if (file) {
    // Validate file size (1MB max)
    if (file.size > 1024 * 1024) {
      validationError.value = 'File size must be less than 1MB'
      return
    }

    // Validate file type
    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif']
    if (!allowedTypes.includes(file.type)) {
      validationError.value = 'Only JPG, PNG, and GIF files are allowed'
      return
    }

    validationError.value = ''
    selectedFile.value = file
  }
}

const clearSelectedFile = () => {
  selectedFile.value = null
  validationError.value = ''
}

const uploadQr = async () => {
  if (!selectedFile.value) return

  uploading.value = true
  try {
    const formData = new FormData()
    formData.append('qr_code', selectedFile.value)
    if (mobileNumber.value) {
      formData.append('mobile_number', mobileNumber.value)
    }

    const response = await apiUpload('/api/vendor/qr', formData)

    if (response.ok) {
      await loadQrData()
      clearSelectedFile()
      alert('QR code uploaded successfully!')
    } else {
      const error = await response.json()
      alert(error.error || 'Failed to upload QR code')
    }
  } catch (error) {
    console.error('Error uploading QR code:', error)
    alert('Failed to upload QR code')
  } finally {
    uploading.value = false
  }
}

const updateMobileNumber = async () => {
  updatingMobile.value = true
  mobileUpdateSuccess.value = false
  try {
    const response = await apiPatch('/api/vendor/qr/mobile', { mobile_number: mobileNumber.value })

    if (response.ok) {
      await loadQrData()
      mobileUpdateSuccess.value = true
      // Auto-hide success message after 3 seconds
      setTimeout(() => {
        mobileUpdateSuccess.value = false
      }, 3000)
    } else {
      const error = await response.json()
      alert(error.error || 'Failed to update mobile number')
    }
  } catch (error) {
    console.error('Error updating mobile number:', error)
    alert('Failed to update mobile number')
  } finally {
    updatingMobile.value = false
  }
}

const removeQr = async () => {
  if (!confirm('Are you sure you want to remove the QR code?')) return

  try {
    const response = await apiDelete('/api/vendor/qr')

    if (response.ok) {
      await loadQrData()
      alert('QR code removed successfully!')
    } else {
      const error = await response.json()
      alert(error.error || 'Failed to remove QR code')
    }
  } catch (error) {
    console.error('Error removing QR code:', error)
    alert('Failed to remove QR code')
  }
}

onMounted(async () => {
  await loadQrData()
})
</script>
