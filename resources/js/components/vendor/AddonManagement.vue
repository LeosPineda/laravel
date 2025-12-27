<template>
  <Teleport to="body">
    <div
      v-if="isOpen"
      class="fixed inset-0 z-50 overflow-y-auto"
      aria-labelledby="modal-title"
      role="dialog"
      aria-modal="true"
    >
      <!-- Backdrop -->
      <div
        class="fixed inset-0 bg-black/50 transition-opacity"
        @click="close"
      ></div>

      <!-- Slide Panel -->
      <div class="fixed inset-y-0 right-0 flex max-w-full pl-10">
        <div
          class="w-screen max-w-md transform bg-white shadow-xl transition-all"
          @click.stop
        >
          <!-- Header -->
          <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-lg font-bold text-gray-900">Manage Add-ons</h3>
                <p class="text-sm text-gray-500">{{ productName }}</p>
              </div>
              <button
                @click="close"
                class="text-gray-400 hover:text-gray-600"
              >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>

          <!-- Content -->
          <div class="flex-1 overflow-y-auto p-6">
            <!-- Statistics -->
            <div v-if="statistics" class="grid grid-cols-3 gap-3 mb-6">
              <div class="bg-gray-50 rounded-lg p-3 text-center">
                <p class="text-lg font-bold text-gray-900">{{ statistics.total_addons }}</p>
                <p class="text-xs text-gray-500">Total</p>
              </div>
              <div class="bg-green-50 rounded-lg p-3 text-center">
                <p class="text-lg font-bold text-green-600">{{ statistics.active_addons }}</p>
                <p class="text-xs text-gray-500">Active</p>
              </div>
              <div class="bg-gray-50 rounded-lg p-3 text-center">
                <p class="text-lg font-bold text-gray-500">₱{{ statistics.average_price }}</p>
                <p class="text-xs text-gray-500">Avg Price</p>
              </div>
            </div>

            <!-- Bulk Actions -->
            <div v-if="selectedAddons.length > 0" class="bg-orange-50 rounded-lg p-3 mb-4 flex items-center justify-between">
              <span class="text-sm text-orange-700">{{ selectedAddons.length }} selected</span>
              <div class="flex gap-2">
                <button
                  @click="bulkAction('activate')"
                  :disabled="bulkProcessing"
                  class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs hover:bg-green-200"
                >
                  Activate
                </button>
                <button
                  @click="bulkAction('deactivate')"
                  :disabled="bulkProcessing"
                  class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded text-xs hover:bg-yellow-200"
                >
                  Deactivate
                </button>
                <button
                  @click="bulkAction('delete')"
                  :disabled="bulkProcessing"
                  class="px-2 py-1 bg-red-100 text-red-700 rounded text-xs hover:bg-red-200"
                >
                  Delete
                </button>
                <button
                  @click="clearSelection"
                  class="px-2 py-1 bg-gray-100 text-gray-700 rounded text-xs hover:bg-gray-200"
                >
                  Clear
                </button>
              </div>
            </div>

            <!-- Add New Addon Form -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
              <h4 class="text-sm font-medium text-gray-700 mb-3">Add New Add-on</h4>
              <form @submit.prevent="createAddon" class="space-y-3">
                <div>
                  <input
                    v-model="newAddon.name"
                    type="text"
                    placeholder="Add-on name (e.g., Extra Cheese)"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 text-sm"
                    :class="{ 'border-red-500': addonErrors.name }"
                  />
                  <p v-if="addonErrors.name" class="text-red-600 text-xs mt-1">{{ addonErrors.name }}</p>
                </div>
                <div class="flex gap-2">
                  <div class="flex-1">
                    <input
                      v-model.number="newAddon.price"
                      type="number"
                      step="0.01"
                      min="0"
                      placeholder="Price (₱)"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 text-sm"
                      :class="{ 'border-red-500': addonErrors.price }"
                    />
                    <p v-if="addonErrors.price" class="text-red-600 text-xs mt-1">{{ addonErrors.price }}</p>
                  </div>
                  <button
                    type="submit"
                    :disabled="creatingAddon"
                    class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 disabled:opacity-50 text-sm"
                  >
                    {{ creatingAddon ? 'Adding...' : 'Add' }}
                  </button>
                </div>
              </form>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="text-center py-8">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-orange-500 mx-auto"></div>
              <p class="text-gray-500 mt-2 text-sm">Loading add-ons...</p>
            </div>

            <!-- Addons List -->
            <div v-else-if="addons.length > 0" class="space-y-3">
              <div
                v-for="addon in addons"
                :key="addon.id"
                class="bg-white border border-gray-200 rounded-lg p-4"
              >
                <!-- View Mode -->
                <div v-if="editingAddonId !== addon.id">
                  <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                      <input
                        type="checkbox"
                        :checked="selectedAddons.includes(addon.id)"
                        @change="toggleSelection(addon.id)"
                        class="w-4 h-4 rounded border-gray-300 text-orange-500 focus:ring-orange-500"
                      />
                      <span
                        :class="[
                          'w-2 h-2 rounded-full',
                          addon.is_active ? 'bg-green-500' : 'bg-gray-400'
                        ]"
                      ></span>
                      <div>
                        <p class="font-medium text-gray-900">{{ addon.name }}</p>
                        <p class="text-sm text-orange-600">+₱{{ parseFloat(addon.price).toFixed(2) }}</p>
                      </div>
                    </div>
                    <div class="flex items-center gap-2">
                      <button
                        @click="toggleAddonStatus(addon)"
                        :disabled="processingAddon === addon.id"
                        :class="[
                          'px-3 py-1 rounded text-xs',
                          addon.is_active
                            ? 'bg-red-100 text-red-700 hover:bg-red-200'
                            : 'bg-green-100 text-green-700 hover:bg-green-200'
                        ]"
                      >
                        {{ addon.is_active ? 'Disable' : 'Enable' }}
                      </button>
                      <button
                        @click="startEditAddon(addon)"
                        class="px-3 py-1 bg-gray-100 text-gray-700 rounded hover:bg-gray-200 text-xs"
                      >
                        Edit
                      </button>
                      <button
                        @click="deleteAddon(addon)"
                        :disabled="processingAddon === addon.id"
                        class="px-3 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200 text-xs"
                      >
                        Delete
                      </button>
                    </div>
                  </div>
                </div>

                <!-- Edit Mode -->
                <div v-else>
                  <form @submit.prevent="updateAddon(addon)" class="space-y-3">
                    <input
                      v-model="editForm.name"
                      type="text"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 text-sm"
                    />
                    <div class="flex gap-2">
                      <input
                        v-model.number="editForm.price"
                        type="number"
                        step="0.01"
                        min="0"
                        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 text-sm"
                      />
                      <button
                        type="submit"
                        :disabled="updatingAddon"
                        class="px-3 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 disabled:opacity-50 text-sm"
                      >
                        Save
                      </button>
                      <button
                        type="button"
                        @click="cancelEdit"
                        class="px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm"
                      >
                        Cancel
                      </button>
                    </div>
                  </form>
                </div>
              </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-8">
              <div class="text-4xl mb-2">🧀</div>
              <p class="text-gray-500 text-sm">No add-ons yet</p>
              <p class="text-gray-400 text-xs">Add extras like cheese, bacon, etc.</p>
            </div>
          </div>

          <!-- Footer -->
          <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
            <button
              @click="close"
              class="w-full px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200"
            >
              Close
            </button>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  productId: {
    type: [Number, String],
    default: null
  },
  productName: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['close', 'updated'])

const addons = ref([])
const statistics = ref(null)
const selectedAddons = ref([])
const loading = ref(false)
const creatingAddon = ref(false)
const updatingAddon = ref(false)
const bulkProcessing = ref(false)
const processingAddon = ref(null)
const editingAddonId = ref(null)

const newAddon = ref({
  name: '',
  price: null
})

const editForm = ref({
  name: '',
  price: null
})

const addonErrors = ref({})

const loadAddons = async () => {
  if (!props.productId) return

  loading.value = true
  try {
    const response = await fetch(`/api/vendor/products/${props.productId}/addons`, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json'
      }
    })

    if (response.ok) {
      const data = await response.json()
      addons.value = data.addons || []
    }
  } catch (error) {
    console.error('Error loading addons:', error)
  } finally {
    loading.value = false
  }
}

const createAddon = async () => {
  addonErrors.value = {}

  if (!newAddon.value.name?.trim()) {
    addonErrors.value.name = 'Name is required'
    return
  }

  if (newAddon.value.price === null || newAddon.value.price < 0) {
    addonErrors.value.price = 'Valid price required'
    return
  }

  creatingAddon.value = true
  try {
    const response = await fetch(`/api/vendor/products/${props.productId}/addons`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        name: newAddon.value.name,
        price: newAddon.value.price
      })
    })

    if (response.ok) {
      newAddon.value = { name: '', price: null }
      await loadAddons()
      emit('updated')
    } else {
      const error = await response.json()
      alert(error.error || 'Failed to create add-on')
    }
  } catch (error) {
    console.error('Error creating addon:', error)
    alert('Failed to create add-on')
  } finally {
    creatingAddon.value = false
  }
}

const startEditAddon = (addon) => {
  editingAddonId.value = addon.id
  editForm.value = {
    name: addon.name,
    price: parseFloat(addon.price)
  }
}

const cancelEdit = () => {
  editingAddonId.value = null
  editForm.value = { name: '', price: null }
}

const updateAddon = async (addon) => {
  if (!editForm.value.name?.trim()) {
    alert('Name is required')
    return
  }

  updatingAddon.value = true
  try {
    const response = await fetch(`/api/vendor/addons/${addon.id}`, {
      method: 'PUT',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        name: editForm.value.name,
        price: editForm.value.price
      })
    })

    if (response.ok) {
      cancelEdit()
      await loadAddons()
      emit('updated')
    } else {
      const error = await response.json()
      alert(error.error || 'Failed to update add-on')
    }
  } catch (error) {
    console.error('Error updating addon:', error)
    alert('Failed to update add-on')
  } finally {
    updatingAddon.value = false
  }
}

const toggleAddonStatus = async (addon) => {
  processingAddon.value = addon.id
  try {
    const response = await fetch(`/api/vendor/addons/${addon.id}/toggle`, {
      method: 'PATCH',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json'
      }
    })

    if (response.ok) {
      await loadAddons()
      emit('updated')
    } else {
      const error = await response.json()
      alert(error.error || 'Failed to toggle add-on status')
    }
  } catch (error) {
    console.error('Error toggling addon status:', error)
  } finally {
    processingAddon.value = null
  }
}

const deleteAddon = async (addon) => {
  if (!confirm(`Are you sure you want to delete "${addon.name}"?`)) return

  processingAddon.value = addon.id
  try {
    const response = await fetch(`/api/vendor/addons/${addon.id}`, {
      method: 'DELETE',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json'
      }
    })

    if (response.ok) {
      await loadAddons()
      emit('updated')
    } else {
      const error = await response.json()
      alert(error.error || 'Failed to delete add-on')
    }
  } catch (error) {
    console.error('Error deleting addon:', error)
  } finally {
    processingAddon.value = null
  }
}

const close = () => {
  editingAddonId.value = null
  selectedAddons.value = []
  newAddon.value = { name: '', price: null }
  addonErrors.value = {}
  emit('close')
}

// Statistics
const loadStatistics = async () => {
  if (!props.productId) return

  try {
    const response = await fetch(`/api/vendor/products/${props.productId}/addons/stats`, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json'
      }
    })

    if (response.ok) {
      const data = await response.json()
      statistics.value = data.statistics || null
    }
  } catch (error) {
    console.error('Error loading addon statistics:', error)
  }
}

// Bulk Operations
const toggleSelection = (addonId) => {
  const index = selectedAddons.value.indexOf(addonId)
  if (index > -1) {
    selectedAddons.value.splice(index, 1)
  } else {
    selectedAddons.value.push(addonId)
  }
}

const clearSelection = () => {
  selectedAddons.value = []
}

const bulkAction = async (action) => {
  if (!confirm(`Are you sure you want to ${action} ${selectedAddons.value.length} add-ons?`)) return

  bulkProcessing.value = true
  try {
    const response = await fetch('/api/vendor/addons/bulk', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        addon_ids: selectedAddons.value,
        action: action
      })
    })

    if (response.ok) {
      const data = await response.json()
      alert(data.message)
      clearSelection()
      await loadAddons()
      await loadStatistics()
      emit('updated')
    } else {
      const error = await response.json()
      alert(error.error || `Failed to ${action} add-ons`)
    }
  } catch (error) {
    console.error('Error performing bulk action:', error)
    alert(`Failed to ${action} add-ons`)
  } finally {
    bulkProcessing.value = false
  }
}

// Watch for modal open
watch(
  () => props.isOpen,
  (isOpen) => {
    if (isOpen && props.productId) {
      loadAddons()
      loadStatistics()
    }
  }
)
</script>
