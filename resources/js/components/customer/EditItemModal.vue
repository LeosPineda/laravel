<template>
  <!-- Edit Item Modal - Mobile Bottom Sheet -->
  <div
    v-if="isOpen"
    class="fixed inset-0 z-[60] flex items-end sm:items-center justify-center"
    @click="handleBackdropClick"
  >
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>

    <!-- Modal Content - Bottom sheet on mobile -->
    <div
      class="relative bg-white w-full sm:max-w-md sm:mx-4 sm:rounded-2xl rounded-t-3xl shadow-2xl transform transition-all duration-300 ease-out max-h-[90vh] flex flex-col"
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
      <div class="flex items-center justify-between p-4 border-b border-gray-200">
        <h2 class="text-lg font-bold text-gray-900">Edit Item</h2>
        <button
          @click="closeModal"
          class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full transition-colors"
          aria-label="Close modal"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Content -->
      <div class="p-4 space-y-6 max-h-[60vh] overflow-y-auto">
        <!-- Product Info -->
        <div class="flex gap-4">
          <!-- Product Image -->
          <div class="w-20 h-20 bg-gray-100 rounded-xl overflow-hidden flex-shrink-0">
            <img
              v-if="item?.product?.image_url"
              :src="getImageUrl(item.product.image_url)"
              :alt="item.product.name"
              class="w-full h-full object-cover"
              @error="handleImageError"
            />
            <div v-else class="w-full h-full flex items-center justify-center text-3xl">
              🍽️
            </div>
          </div>

          <!-- Product Details -->
          <div class="flex-1">
            <h3 class="font-semibold text-gray-900">{{ item?.product?.name }}</h3>
            <p class="text-orange-600 font-medium">₱{{ formatPrice(item?.unit_price) }}</p>
          </div>
        </div>

        <!-- Quantity Selector -->
        <div class="flex items-center justify-between bg-gray-50 p-4 rounded-xl">
          <span class="font-semibold text-gray-700">Quantity</span>
          <div class="flex items-center gap-3">
            <button
              @click="decrementQuantity"
              :disabled="quantity <= 1 || saving"
              class="w-10 h-10 rounded-full border-2 border-gray-300 flex items-center justify-center hover:bg-white hover:border-orange-400 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
              </svg>
            </button>

            <span class="w-12 text-center text-xl font-bold text-gray-900">{{ quantity }}</span>

            <button
              @click="incrementQuantity"
              :disabled="saving"
              class="w-10 h-10 rounded-full border-2 border-gray-300 flex items-center justify-center hover:bg-white hover:border-orange-400 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Addons Section -->
        <div v-if="availableAddons.length > 0" class="space-y-3">
          <h4 class="font-bold text-gray-900">Add-ons</h4>
          <div class="space-y-2">
            <div
              v-for="addon in availableAddons"
              :key="addon.id"
              class="flex justify-between items-center p-3 border rounded-xl transition-all duration-200 cursor-pointer"
              :class="isAddonSelected(addon.id)
                ? 'border-orange-400 bg-orange-50'
                : 'border-gray-200 hover:border-orange-200 hover:bg-orange-50/50'"
              @click="toggleAddon(addon)"
            >
              <label class="flex items-center gap-2 flex-1 cursor-pointer" @click.stop>
                <input
                  type="checkbox"
                  :checked="isAddonSelected(addon.id)"
                  @change="toggleAddon(addon)"
                  class="w-5 h-5 text-orange-600 border-2 border-gray-300 rounded focus:ring-orange-500"
                />
                <span>{{ addon.name }}</span>
              </label>
              <span class="font-bold text-orange-600">+₱{{ formatPrice(addon.price) }}</span>
            </div>
          </div>
        </div>

        <!-- No Addons Message -->
        <div v-else class="bg-gray-50 p-4 rounded-xl text-center">
          <p class="text-gray-500">No add-ons available for this item</p>
        </div>

        <!-- Item Total -->
        <div class="border-t pt-4">
          <div class="flex justify-between items-center">
            <span class="font-semibold text-gray-700">Item Total</span>
            <span class="text-xl font-bold text-orange-600">₱{{ formatPrice(itemTotal) }}</span>
          </div>
          <p class="text-sm text-gray-500 mt-1">
            {{ quantity }}× ₱{{ formatPrice(item?.unit_price) }}
            <span v-if="selectedAddons.length > 0"> + ₱{{ formatPrice(addonsTotal) }} addons</span>
          </p>
        </div>
      </div>

      <!-- Footer -->
      <div class="p-4 border-t border-gray-200">
        <button
          @click="saveChanges"
          :disabled="saving || !hasChanges"
          class="w-full py-3 bg-orange-500 hover:bg-orange-600 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-medium rounded-xl transition-colors flex items-center justify-center gap-2"
        >
          <svg v-if="saving" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
          </svg>
          {{ saving ? 'Saving...' : '💾 Save Changes' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
  item: {
    type: Object,
    default: null
  },
  isOpen: {
    type: Boolean,
    default: false
  },
  availableAddons: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['close', 'save'])

// Local state
const quantity = ref(1)
const selectedAddons = ref([])
const saving = ref(false)

// Original values for change detection
const originalQuantity = ref(1)
const originalAddons = ref([])

// Computed
const addonsTotal = computed(() => {
  return selectedAddons.value.reduce((sum, addon) => {
    return sum + parseFloat(addon.price || 0)
  }, 0)
})

const itemTotal = computed(() => {
  const basePrice = parseFloat(props.item?.unit_price || 0) * quantity.value
  const addons = addonsTotal.value * quantity.value
  return basePrice + addons
})

const hasChanges = computed(() => {
  if (quantity.value !== originalQuantity.value) return true

  const currentAddonIds = selectedAddons.value.map(a => a.addon_id || a.id).sort()
  const originalAddonIds = originalAddons.value.map(a => a.addon_id || a.id).sort()

  if (currentAddonIds.length !== originalAddonIds.length) return true

  return currentAddonIds.some((id, index) => id !== originalAddonIds[index])
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

const handleImageError = (event) => {
  event.target.style.display = 'none'
}

const handleBackdropClick = () => {
  closeModal()
}

const closeModal = () => {
  emit('close')
}

const incrementQuantity = () => {
  quantity.value++
}

const decrementQuantity = () => {
  if (quantity.value > 1) {
    quantity.value--
  }
}

const isAddonSelected = (addonId) => {
  return selectedAddons.value.some(a => (a.addon_id || a.id) === addonId)
}

const toggleAddon = (addon) => {
  const addonId = addon.id
  const index = selectedAddons.value.findIndex(a => (a.addon_id || a.id) === addonId)

  if (index >= 0) {
    selectedAddons.value.splice(index, 1)
  } else {
    selectedAddons.value.push({
      addon_id: addon.id,
      quantity: 1,
      price: parseFloat(addon.price),
      name: addon.name
    })
  }
}

const saveChanges = async () => {
  if (saving.value || !hasChanges.value) return

  saving.value = true

  try {
    // Sort addons by addon_id to ensure consistent order for merge comparison
    const sortedAddons = [...selectedAddons.value]
      .map(a => ({
        addon_id: a.addon_id || a.id,
        quantity: a.quantity || 1,
        price: parseFloat(a.price)
      }))
      .sort((a, b) => a.addon_id - b.addon_id)

    const updatedItem = {
      id: props.item.id,
      quantity: quantity.value,
      addons: sortedAddons
    }

    emit('save', updatedItem)
  } finally {
    saving.value = false
  }
}

// Initialize state when modal opens
watch(() => props.isOpen, (isOpen) => {
  if (isOpen && props.item) {
    quantity.value = props.item.quantity || 1
    originalQuantity.value = props.item.quantity || 1

    // Parse selected addons from item
    const itemAddons = props.item.selected_addons || []
    selectedAddons.value = itemAddons.map(a => ({
      addon_id: a.addon_id,
      quantity: a.quantity || 1,
      price: a.price,
      name: a.name || ''
    }))
    originalAddons.value = [...selectedAddons.value]
  }
}, { immediate: true })
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
