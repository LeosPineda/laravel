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
          <!-- QR Code Preview Section -->
          <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Current QR Code</h2>

            <div v-if="qrData.has_qr_code" class="flex flex-col md:flex-row gap-6">
              <!-- QR Code Image -->
              <div class="flex-shrink-0">
                <div class="w-48 h-48 border-2 border-gray-200 rounded-lg flex items-center justify-center bg-gray-50">
                  <img :src="qrData.qr_code_url" alt="QR Code" class="max-w-full max-h-full object-contain" />
                </div>
              </div>

              <!-- QR Code Info -->
              <div class="flex-1">
                <div class="space-y-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mobile Number</label>
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

                  <div class="flex gap-2">
                    <button
                      @click="previewQr"
                      class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600"
                    >
                      Preview
                    </button>
                    <button
                      @click="showPublicUrl"
                      class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600"
                    >
                      Get Public URL
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

          <!-- Statistics -->
          <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">QR Payment Statistics</h2>

            <div v-if="loadingStats" class="text-center py-4">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-orange-500 mx-auto"></div>
              <p class="text-sm text-gray-500 mt-2">Loading statistics...</p>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div class="bg-orange-50 rounded-lg p-4">
                <div class="text-2xl font-bold text-orange-600">{{ stats.qr_orders_this_month || 0 }}</div>
                <div class="text-sm text-orange-700">QR Orders This Month</div>
              </div>

              <div class="bg-green-50 rounded-lg p-4">
                <div class="text-2xl font-bold text-green-600">₱{{ (stats.qr_revenue_this_month || 0).toLocaleString() }}</div>
                <div class="text-sm text-green-700">QR Revenue This Month</div>
              </div>

              <div class="bg-blue-50 rounded-lg p-4">
                <div class="text-2xl font-bold text-blue-600">{{ stats.total_qr_orders || 0 }}</div>
                <div class="text-sm text-blue-700">Total QR Orders</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Public URL Modal -->
      <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click="closeModal">
        <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4" @click.stop>
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold">Public QR Code URL</h3>
            <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
              <span class="text-xl">×</span>
            </button>
          </div>
          <div class="space-y-4">
            <div>
              <p class="text-sm text-gray-600 mb-2">Share this URL with customers:</p>
              <div class="flex gap-2">
                <input
                  :value="publicUrl"
                  readonly
                  class="flex-1 px-3 py-2 border border-gray-300 rounded-lg bg-gray-50"
                />
                <button
                  @click="copyToClipboard"
                  class="px-3 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600"
                >
                  Copy
                </button>
              </div>
            </div>
            <p class="text-xs text-gray-500">
              Customers can access this QR code directly without logging in.
            </p>
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

const stats = ref({
  qr_orders_this_month: 0,
  qr_revenue_this_month: 0,
  total_qr_orders: 0
})

const mobileNumber = ref('')
const selectedFile = ref(null)
const uploading = ref(false)
const updatingMobile = ref(false)
const loadingStats = ref(false)
const validationError = ref('')
const showModal = ref(false)
const publicUrl = ref('')

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

const loadStats = async () => {
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
      stats.value = data
    }
  } catch (error) {
    console.error('Error loading stats:', error)
  } finally {
    loadingStats.value = false
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
      await loadStats()
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
      await loadStats()
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

const showPublicUrl = async () => {
  try {
    const response = await fetch('/api/vendor/qr/public-url', {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json'
      }
    })

    if (response.ok) {
      const data = await response.json()
      publicUrl.value = data.public_url
      showModal.value = true
    } else {
      const error = await response.json()
      alert(error.error || 'Failed to get public URL')
    }
  } catch (error) {
    console.error('Error getting public URL:', error)
    alert('Failed to get public URL')
  }
}

const closeModal = () => {
  showModal.value = false
  publicUrl.value = ''
}

const copyToClipboard = async () => {
  try {
    await navigator.clipboard.writeText(publicUrl.value)
    alert('URL copied to clipboard!')
  } catch (error) {
    console.error('Error copying to clipboard:', error)
  }
}

onMounted(async () => {
  await loadQrData()
  await loadStats()
})
</script>
