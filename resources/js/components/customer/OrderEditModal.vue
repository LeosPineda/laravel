<template>
  <div
    class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
    @click="$emit('close')"
  >
    <div
      class="bg-white rounded-xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto"
      @click.stop
    >
      <!-- Header -->
      <div class="flex items-center justify-between p-6 border-b border-gray-200">
        <h2 class="text-xl font-bold text-gray-900">Edit Order</h2>
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
        <!-- Product Info -->
        <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-lg">
          <div class="w-16 h-16 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
            <img
              v-if="item.product.image_url"
              :src="item.product.image_url"
              :alt="item.product.name"
              class="w-full h-full object-cover"
            />
            <div v-else class="w-full h-full flex items-center justify-center">
              <span class="text-lg">🍽️</span>
            </div>
          </div>
          <div class="flex-1">
            <h3 class="font-semibold text-gray-900">{{ item.product.name }}</h3>
            <p class="text-orange-600 font-medium">₱{{ item.product.price }} each</p>
            <p class="text-sm text-gray-600">{{ item.vendor.brand_name }}</p>
          </div>
          <div class="text-right">
            <p class="font-semibold text-gray-900">Current: {{ item.quantity }}</p>
          </div>
        </div>

        <!-- Quantity -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
          <div class="flex items-center gap-3">
            <button
              @click="quantity = Math.max(1, quantity - 1)"
              class="w-10 h-10 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center transition-colors"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
              </svg>
            </button>
            <input
              v-model.number="quantity"
              type="number"
              min="1"
              class="w-20 px-3 py-2 border border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500 text-center"
            />
            <button
              @click="quantity++"
              class="w-10 h-10 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center transition-colors"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Addons Section -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-3">Add-ons (Optional)</label>

          <!-- Loading Addons -->
          <div v-if="loadingAddons" class="space-y-3">
            <div
              v-for="n in 3"
              :key="n"
              class="flex items-center gap-3 animate-pulse"
            >
              <div class="w-4 h-4 bg-gray-200 rounded"></div>
              <div class="flex-1 h-4 bg-gray-200 rounded"></div>
              <div class="w-16 h-4 bg-gray-200 rounded"></div>
            </div>
          </div>

          <!-- Addons List -->
          <div v-else-if="availableAddons.length > 0" class="space-y-3">
            <div
              v-for="addon in availableAddons"
              :key="addon.id"
              class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50"
            >
              <div class="flex items-center gap-3">
                <input
                  :id="`addon-${addon.id}`"
                  v-model="selectedAddons"
                  :value="addon.id"
                  type="checkbox"
                  class="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500"
                />
                <label :for="`addon-${addon.id}`" class="text-sm font-medium text-gray-900 cursor-pointer">
                  {{ addon.name }}
                </label>
              </div>
              <span class="text-sm font-medium text-orange-600">+₱{{ addon.price }}</span>
            </div>
          </div>

          <!-- No Addons Available -->
          <div v-else class="text-center py-8 text-gray-500">
            <p>No add-ons available for this product</p>
          </div>
        </div>

        <!-- Special Instructions -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Special Instructions</label>
          <textarea
            v-model="specialInstructions"
            rows="3"
            placeholder="Any special requests or modifications..."
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500"
          ></textarea>
        </div>

        <!-- Order Summary -->
        <div class="bg-orange-50 p-4 rounded-lg">
          <h4 class="font-semibold text-gray-900 mb-3">Order Summary</h4>
          <div class="space-y-2 text-sm">
            <div class="flex justify-between">
              <span>{{ item.product.name }} x{{ quantity }}</span>
              <span>₱{{ (item.product.price * quantity).toFixed(2) }}</span>
            </div>
            <div v-for="addon in selectedAddonsList" :key="addon.id" class="flex justify-between">
              <span>+ {{ addon.name }}</span>
              <span>+₱{{ addon.price.toFixed(2) }}</span>
            </div>
            <div class="border-t border-orange-200 pt-2 flex justify-between font-semibold">
              <span>Total</span>
              <span>₱{{ totalPrice.toFixed(2) }}</span>
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
          @click="saveOrder"
          class="px-6 py-2 bg-orange-500 text-white font-medium rounded-lg hover:bg-orange-600 transition-colors"
          :disabled="saving"
        >
          <span v-if="saving" class="flex items-center gap-2">
            <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" />
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
            </svg>
            Saving...
          </span>
          <span v-else>Save Changes</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

const props = defineProps({
  item: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['close', 'save'])

// Local state
const quantity = ref(props.item.quantity)
const specialInstructions = ref('')
const selectedAddons = ref([])
const loadingAddons = ref(false)
const saving = ref(false)
const availableAddons = ref([])

// Computed
const selectedAddonsList = computed(() => {
  return availableAddons.value.filter(addon =>
    selectedAddons.value.includes(addon.id)
  )
})

const totalPrice = computed(() => {
  const basePrice = item.product.price * quantity.value
  const addonsPrice = selectedAddonsList.value.reduce((total, addon) => {
    return total + addon.price
  }, 0)
  return basePrice + addonsPrice
})

// Methods
const fetchAddons = async () => {
  loadingAddons.value = true
  try {
    const response = await fetch(`/api/customer/products/${props.item.product_id}/addons`, {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      credentials: 'include'
    })

    if (response.ok) {
      const data = await response.json()
      availableAddons.value = data.addons || []
    }
  } catch (error) {
    console.error('Error fetching addons:', error)
  } finally {
    loadingAddons.value = false
  }
}

const saveOrder = async () => {
  saving.value = true

  try {
    const updatedOrder = {
      cart_item_id: props.item.id,
      quantity: quantity.value,
      special_instructions: specialInstructions.value,
      addon_ids: selectedAddons.value,
      total_price: totalPrice.value
    }

    emit('save', updatedOrder)
  } catch (error) {
    console.error('Error saving order:', error)
  } finally {
    saving.value = false
  }
}

// Initialize
onMounted(() => {
  fetchAddons()
})
</script>

<style scoped>
/* No additional styles needed - using Tailwind CSS classes */
</style>
