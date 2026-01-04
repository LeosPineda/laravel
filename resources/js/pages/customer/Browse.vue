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
        </div>
      </div>
    </div>

    <!-- Vendor Grid -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div v-if="loading" class="flex justify-center items-center min-h-[60vh]">
        <div class="text-center">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
          <p class="text-gray-600">Loading vendors...</p>
        </div>
      </div>

      <div v-else-if="loadError" class="text-center py-12">
        <div class="text-red-500 text-lg mb-4">{{ loadError }}</div>
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
          @browse-products="openProductModal"
        />
      </div>
    </div>

    <!-- Product Modal Container -->
    <ProductModalContainer
      :vendor-id="selectedVendorId"
      :is-open="isProductModalOpen"
      @close="closeProductModal"
      @product-select="handleProductSelect"
      @order-now="handleOrderNow"
    />

    <!-- Product Detail Modal -->
    <ProductDetailModal
      ref="productDetailModalRef"
      :product="selectedProduct"
      :is-open="isProductDetailModalOpen"
      :cart-count="cartItemCount"
      @close="closeProductDetailModal"
      @added-to-cart="handleAddedToCart"
    />
  </CustomerLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import CustomerLayout from '@/layouts/customer/CustomerLayout.vue'
import VendorBox from '@/components/customer/VendorBox.vue'
import ProductModalContainer from '@/components/customer/ProductModalContainer.vue'
import ProductDetailModal from '@/components/customer/ProductDetailModal.vue'
import { useCart } from '@/composables/useCart'
import { useToast } from '@/composables/useToast'
import axios from 'axios'

// Reactive data
const vendors = ref([])
const loading = ref(true)
const loadError = ref(null)

// Modal state
const isProductModalOpen = ref(false)
const selectedVendorId = ref(null)
const isProductDetailModalOpen = ref(false)
const selectedProduct = ref(null)
const productDetailModalRef = ref(null)

// Cart and toast composables
const { cartCount, fetchCart, addToCart } = useCart()
const { success, error } = useToast()

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
    loadError.value = null

    const response = await axios.get('/api/customer/menu/vendors')

    if (response.status === 200) {
      vendors.value = response.data.vendors || []
    } else {
      throw new Error('Failed to load vendors')
    }
  } catch (err) {
    console.error('Error loading vendors:', err)
    loadError.value = err.response?.data?.message || 'Failed to load vendors. Please try again.'
  } finally {
    loading.value = false
  }
}

const openProductModal = (vendorId) => {
  selectedVendorId.value = vendorId
  isProductModalOpen.value = true
}

const closeProductModal = () => {
  isProductModalOpen.value = false
  selectedVendorId.value = null
}

const handleProductSelect = (product) => {
  selectedProduct.value = product
  isProductDetailModalOpen.value = true
}

const handleOrderNow = (product) => {
  selectedProduct.value = product
  isProductDetailModalOpen.value = true
}

const closeProductDetailModal = () => {
  isProductDetailModalOpen.value = false
  selectedProduct.value = null
}

const handleAddedToCart = async (product, quantity, addonIds) => {
  try {
    // Convert addon IDs to addon objects with price info
    const addons = addonIds.map(addonId => {
      const addon = product.addons?.find(a => a.id === addonId)
      return {
        addon_id: addonId,
        quantity: 1,
        price: addon ? parseFloat(addon.price) : 0
      }
    })

    // Call the actual cart API
    const result = await addToCart(product.id, quantity, addons)

    // Reset adding state in modal
    productDetailModalRef.value?.setAdding(false)

    if (result.success) {
      success(`${product.name} added to cart!`)
      // Close modal after a delay
      setTimeout(() => {
        closeProductDetailModal()
      }, 1200)
    } else {
      error(result.message || 'Failed to add item to cart')
    }
  } catch (err) {
    console.error('Error adding to cart:', err)
    error('Failed to add item to cart. Please try again.')
    // Reset adding state on error
    productDetailModalRef.value?.setAdding(false)
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
