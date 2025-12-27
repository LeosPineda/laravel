<template>
  <VendorLayout>
    <div class="min-h-screen bg-white">
      <!-- Header -->
      <div class="bg-white border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between">
          <h1 class="text-xl font-bold text-gray-900">QR Code Payment</h1>
          <div class="text-sm text-gray-500">
            Last updated: {{ formatDate(qrData.last_updated) }}
          </div>
        </div>
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

            <div class="flex gap-2">
              <input
                v-model="mobileNumber"
                type="text"
                placeholder="09123456789"
                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
              />
              <button
                @click="updateMobileNumber"
                :disabled="updatingMobile"
                class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 disabled:opacity-50"
              >
                {{ updatingMobile ? 'Updating...' : 'Update' }}
              </button>
            </div>
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
                      @click="previewQr"
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

          <!-- QR Payment Statistics -->
          <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">QR Payment Statistics</h2>

            <div v-if="loadingStats" class="text-center py-4">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-orange-500 mx-auto"></div>
            </div>

            <div v-else class="grid grid-cols-2 md:grid-cols-4 gap-4">
              <div class="bg-orange-50 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-orange-600">{{ qrStats.qr_orders_this_month || 0 }}</div>
                <div class="text-sm text-orange-700">QR Orders (Month)</div>
              </div>

              <div class="bg-green-50 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-green-600">₱{{ formatNumber(qrStats.qr_revenue_this_month) }}</div>
                <div class="text-sm text-green-700">QR Revenue (Month)</div>
              </div>

              <div class="bg-blue-50 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-blue-600">{{ qrStats.total_qr_orders || 0 }}</div>
                <div class="text-sm text-blue-700">Total QR Orders</div>
              </div>

              <div class="bg-purple-50 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold" :class="qrStats.has_qr_code ? 'text-green-600' : 'text-gray-400'">
                  {{ qrStats.has_qr_code ? '✓' : '✗' }}
                </div>
                <div class="text-sm text-purple-700">QR Status</div>
              </div>
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
  </VendorLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import VendorLayout from '@/layouts/vendor/VendorLayout.vue'

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
const loadingStats = ref(false)

const qrStats = ref({
  has_qr_code: false,
  qr_orders_this_month: 0,
  qr_revenue_this_month: 0,
  total_qr_orders: 0,
  qr_code_last_updated: null
})

const formatNumber = (num) => {
  return (num || 0).toLocaleString()
}

const formatDate = (dateString) => {
  if (!dateString) return 'Never'
  return new Date(dateString).toLocaleDateString()
}

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

const loadQrData = async () => {
  try {
    const response = await fetch('/api/vendor/qr', {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json'
      }
    })

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

    const response = await fetch('/api/vendor/qr', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`
      },
      body: formData
    })

    if (response.ok) {
      const data = await response.json()
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
  try {
    const response = await fetch('/api/vendor/qr/mobile', {
      method: 'PATCH',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ mobile_number: mobileNumber.value })
    })

    if (response.ok) {
      await loadQrData()
      alert('Mobile number updated successfully!')
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
    const response = await fetch('/api/vendor/qr', {
      method: 'DELETE',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json'
      }
    })

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

const previewQr = async () => {
  try {
    const response = await fetch('/api/vendor/qr/preview', {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json'
      }
    })

    if (response.ok) {
      const data = await response.json()
      window.open(data.preview_url, '_blank')
    } else {
      const error = await response.json()
      alert(error.error || 'Failed to preview QR code')
    }
  } catch (error) {
    console.error('Error previewing QR code:', error)
    alert('Failed to preview QR code')
  }
}

const loadQrStats = async () => {
  loadingStats.value = true
  try {
    const response = await fetch('/api/vendor/qr/stats', {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json'
      }
    })

    if (response.ok) {
      const data = await response.json()
      qrStats.value = data
    }
  } catch (error) {
    console.error('Error loading QR stats:', error)
  } finally {
    loadingStats.value = false
  }
}

onMounted(async () => {
  await Promise.all([loadQrData(), loadQrStats()])
})
</script>
