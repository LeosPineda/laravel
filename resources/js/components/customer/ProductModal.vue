<template>
  <!-- Modal Overlay -->
  <div v-if="isOpen" class="fixed inset-0 z-50 overflow-y-auto" @click.self="$emit('close')">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
      <!-- Background overlay -->
      <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="$emit('close')"></div>

      <!-- Modal content -->
      <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
        <!-- Modal Header -->
        <div class="bg-white px-6 pt-6 pb-4">
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <img
                v-if="vendor.brand_logo"
                :src="vendor.brand_logo"
                :alt="vendor.brand_name"
                class="w-12 h-12 rounded-lg object-cover mr-4"
              />
              <div>
                <h3 class="text-lg font-medium text-gray-900">{{ vendor.brand_name }}</h3>
                <p class="text-sm text-gray-500">Select products to add to cart</p>
              </div>
            </div>
            <button
              @click="$emit('close')"
              class="text-gray-400 hover:text-gray-600 transition-colors"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Modal Body -->
        <div class="bg-white px-6 pb-6">
          <!-- Loading State -->
          <div v-if="loading" class="flex justify-center items-center h-64">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
          </div>

          <!-- Error State -->
          <div v-else-if="error" class="text-center py-12">
            <div class="text-red-500 text-lg mb-4">{{ error }}</div>
            <button @click="loadVendorProducts" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
              Try Again
            </button>
          </div>

          <!-- Products Grid -->
          <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <ProductCard
              v-for="product in products"
              :key="product.id"
              :product="product"
              :vendor-id="vendor.id"
              @add-to-cart="handleAddToCart"
            />
          </div>

          <!-- Empty State -->
          <div v-if="!loading && !error && products.length === 0" class="text-center py-12">
            <div class="text-gray-500 text-lg mb-4">No products available</div>
            <p class="text-gray-400">This vendor hasn't added any products yet.</p>
          </div>
        </div>

        <!-- Modal Footer -->
        <div class="bg-gray-50 px-6 py-4 flex items-center justify-between">
          <div class="text-sm text-gray-500">
            {{ products.length }} products available
          </div>
          <div class="flex space-x-3">
            <button
              @click="$emit('close')"
              class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
            >
              Close
            </button>
            <button
              @click="$emit('close')"
              class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
            >
              Continue Browsing
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import ProductCard from '@/components/customer/ProductCard.vue'
import axios from 'axios'

const props = defineProps({
  vendor: {
    type: Object,
    required: true
  },
  isOpen: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['close', 'add-to-cart'])

// Reactive data
const products = ref([])
const loading = ref(false)
const error = ref(null)

// Methods
const loadVendorProducts = async () => {
  try {
    loading.value = true
    error.value = null

    const response = await axios.get(`/api/customer/menu/vendors/${props.vendor.id}/products`)

    if (response.status === 200) {
      products.value = response.data.products || []
    } else {
      throw new Error('Failed to load products')
    }
  } catch (err) {
    console.error('Error loading vendor products:', err)
    error.value = err.response?.data?.message || 'Failed to load products. Please try again.'
  } finally {
    loading.value = false
  }
}

const handleAddToCart = async (item) => {
  try {
    // Emit the add-to-cart event to parent
    emit('add-to-cart', item)

    // Show success feedback (you might want to use a toast notification here)
    console.log('Item added to cart:', item)
  } catch (err) {
    console.error('Error handling add to cart:', err)
  }
}

// Watch for modal open state changes
watch(() => props.isOpen, (newValue) => {
  if (newValue) {
    loadVendorProducts()
  }
})

// Load products when component mounts and modal is open
onMounted(() => {
  if (props.isOpen) {
    loadVendorProducts()
  }
})
</script>

<style scoped>
/* Custom modal animations */
.modal-enter-active, .modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from, .modal-leave-to {
  opacity: 0;
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .sm\:max-w-4xl {
    max-width: 100%;
  }

  .sm\:my-8 {
    margin-top: 1rem;
    margin-bottom: 1rem;
  }
}

/* Product grid responsive breakpoints */
@media (max-width: 640px) {
  .grid-cols-1 {
    grid-template-columns: repeat(1, minmax(0, 1fr));
  }
}

@media (min-width: 641px) and (max-width: 1024px) {
  .sm\:grid-cols-2 {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (min-width: 1025px) {
  .lg\:grid-cols-3 {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
}
</style>
