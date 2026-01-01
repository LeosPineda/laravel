<template>
  <CustomerLayout>
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Browse Vendors</h1>
            <p class="text-gray-600 mt-1">Choose from our active food vendors</p>
          </div>
          <div class="flex items-center space-x-4">
            <span class="text-sm text-gray-500">{{ activeVendors.length }} active vendors</span>
            <Link href="/customer/cart" class="relative inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.5 6H19M7 13v6a2 2 0 002 2h7a2 2 0 002-2v-6M7 13H5.4" />
              </svg>
              Cart
              <span v-if="cartItemCount > 0" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                {{ cartItemCount }}
              </span>
            </Link>
          </div>
        </div>
      </div>
    </div>

    <!-- Vendor Grid -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div v-if="loading" class="flex justify-center items-center h-64">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>

      <div v-else-if="error" class="text-center py-12">
        <div class="text-red-500 text-lg mb-4">{{ error }}</div>
        <button @click="loadVendors" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
          Try Again
        </button>
      </div>

      <div v-else-if="activeVendors.length === 0" class="text-center py-12">
        <div class="text-gray-500 text-lg mb-4">No active vendors available</div>
        <p class="text-gray-400">Check back later for new vendors!</p>
      </div>

      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <VendorBox
          v-for="vendor in activeVendors"
          :key="vendor.id"
          :vendor="vendor"
          @click="selectVendor(vendor)"
        />
      </div>
    </div>

    <!-- Vendor Products Modal -->
    <ProductModal
      v-if="selectedVendor"
      :vendor="selectedVendor"
      :is-open="showProductModal"
      @close="closeProductModal"
      @add-to-cart="handleAddToCart"
    />
  </CustomerLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import CustomerLayout from '@/layouts/customer/CustomerLayout.vue'
import VendorBox from '@/components/customer/VendorBox.vue'
import ProductModal from '@/components/customer/ProductModal.vue'
import { useCart } from '@/composables/useCart'
import axios from 'axios'

// Reactive data
const vendors = ref([])
const selectedVendor = ref(null)
const showProductModal = ref(false)
const loading = ref(true)
const error = ref(null)

// Cart composable - FIXED: Using fetchCart instead of refreshCart
const { cartCount, fetchCart } = useCart()

// Computed properties
const activeVendors = computed(() => {
  return vendors.value.filter(vendor => vendor.is_active)
})

const cartItemCount = computed(() => {
  return cartCount.value || 0
})

// Methods
const loadVendors = async () => {
  try {
    loading.value = true
    error.value = null

    const response = await axios.get('/api/customer/menu/vendors')

    if (response.status === 200) {
      vendors.value = response.data.vendors || []
    } else {
      throw new Error('Failed to load vendors')
    }
  } catch (err) {
    console.error('Error loading vendors:', err)
    error.value = err.response?.data?.message || 'Failed to load vendors. Please try again.'
  } finally {
    loading.value = false
  }
}

const selectVendor = (vendor) => {
  selectedVendor.value = vendor
  showProductModal.value = true
}

const closeProductModal = () => {
  showProductModal.value = false
  selectedVendor.value = null
}

const handleAddToCart = async (item) => {
  try {
    // Add item to cart using the cart composable
    const response = await axios.post('/api/customer/cart/items', {
      vendor_id: item.vendor_id,
      product_id: item.product_id,
      quantity: item.quantity,
      selected_addons: item.selected_addons || [],
      unit_price: item.unit_price
    })

    if (response.status === 201) {
      // Refresh cart count - FIXED: Using fetchCart instead of refreshCart
      await fetchCart()

      // Show success message
      console.log('Item added to cart successfully')
    }
  } catch (err) {
    console.error('Error adding to cart:', err)
    // Handle error
  }
}

// Lifecycle
onMounted(() => {
  loadVendors()
  fetchCart()
})
</script>

<style scoped>
/* Custom styles for the Browse page */
.vendor-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1.5rem;
}

/* Responsive breakpoints */
@media (max-width: 640px) {
  .vendor-grid {
    grid-template-columns: 1fr;
  }
}

@media (min-width: 641px) and (max-width: 1024px) {
  .vendor-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (min-width: 1025px) {
  .vendor-grid {
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  }
}
</style>
