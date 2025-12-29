<template>
  <CustomerLayout>
    <div class="min-h-screen bg-white">
      <!-- Header -->
      <div class="bg-white border-b border-gray-200 px-4 py-6">
        <div class="max-w-7xl mx-auto">
          <div class="flex items-center justify-between">
            <div>
              <h1 class="text-2xl font-bold text-gray-900">{{ selectedVendor ? selectedVendor.brand_name : 'Food Court Menu' }}</h1>
              <p class="text-gray-600 mt-1">
                {{ selectedVendor ? 'Browse products from this vendor' : 'Browse vendors and order your favorite food' }}
              </p>
            </div>

            <!-- Cart Button -->
            <button
              class="flex items-center gap-2 px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 relative"
              @click="showCart = !showCart"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.5 1.5M7 13l1.5 1.5M17 17a2 2 0 100 4 2 2 0 000-4zM9 17a2 2 0 100 4 2 2 0 000-4z" />
              </svg>
              <span>Cart ({{ cartCount }})</span>
              <span
                v-if="cartCount > 0"
                class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-6 h-6 flex items-center justify-center"
              >
                {{ cartCount }}
              </span>
            </button>
          </div>
        </div>
      </div>

      <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Back Button (when vendor is selected) -->
        <div v-if="selectedVendor" class="mb-6">
          <button
            @click="selectedVendor = null"
            class="flex items-center gap-2 text-orange-600 hover:text-orange-700 font-medium"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Vendors
          </button>
        </div>

        <!-- Category Filter (when vendor is selected) -->
        <div v-if="selectedVendor && categories.length > 0" class="mb-6">
          <CategoryFilter
            :categories="categories"
            :selected-category="selectedCategory"
            @select-category="selectCategory"
          />
        </div>

        <!-- Content -->
        <div v-if="loading">
          <!-- Loading State -->
          <div v-if="!selectedVendor">
            <VendorGrid :vendors="[]" :loading="true" />
          </div>
          <div v-else>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
              <div
                v-for="n in 8"
                :key="n"
                class="bg-white rounded-xl border border-gray-200 overflow-hidden animate-pulse"
              >
                <div class="aspect-square bg-gray-200"></div>
                <div class="p-4">
                  <div class="h-6 bg-gray-200 rounded mb-2"></div>
                  <div class="h-4 bg-gray-200 rounded mb-2 w-3/4"></div>
                  <div class="h-10 bg-gray-200 rounded"></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Vendor Selection -->
        <div v-else-if="!selectedVendor">
          <VendorGrid
            :vendors="vendors"
            :loading="false"
            @select-vendor="selectVendor"
          />
        </div>

        <!-- Product Selection -->
        <div v-else>
          <div v-if="filteredProducts.length > 0">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
              <ProductCard
                v-for="product in filteredProducts"
                :key="product.id"
                :product="product"
                @add-to-cart="handleAddToCart"
              />
            </div>
          </div>
          <div v-else class="text-center py-16">
            <div class="text-6xl mb-4">🍽️</div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">No products found</h3>
            <p class="text-gray-600">
              {{ selectedCategory ? `No products in ${selectedCategory} category` : 'This vendor has no products available' }}
            </p>
          </div>
        </div>
      </div>

      <!-- Cart Sidebar -->
      <CartSidebar
        :show="showCart"
        :cart="cart"
        :cart-by-vendor="cartByVendor"
        :cart-total="cartTotal"
        :cart-count="cartCount"
        :loading="cartLoading"
        @close="showCart = false"
        @update-quantity="handleUpdateQuantity"
        @remove-item="handleRemoveItem"
        @clear-vendor="handleClearVendor"
        @clear-all="handleClearAll"
        @checkout="handleCheckout"
      />
    </div>
  </CustomerLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { usePage } from '@inertiajs/vue3'
import CustomerLayout from '@/layouts/customer/CustomerLayout.vue'
import VendorGrid from '@/components/customer/VendorGrid.vue'
import ProductCard from '@/components/customer/ProductCard.vue'
import CategoryFilter from '@/components/customer/CategoryFilter.vue'
import CartSidebar from '@/components/customer/CartSidebar.vue'
import { useCart } from '@/composables/useCart'

const page = usePage()

// Cart composable
const {
  cart,
  loading: cartLoading,
  cartCount,
  cartByVendor,
  cartTotal,
  fetchCart,
  addToCart,
  updateCartItem,
  removeFromCart,
  clearCart
} = useCart()

// State
const loading = ref(true)
const vendors = ref([])
const selectedVendor = ref(null)
const products = ref([])
const selectedCategory = ref(null)
const showCart = ref(false)

// Computed
const categories = computed(() => {
  const cats = [...new Set(products.value.map(p => p.category).filter(Boolean))]
  return cats.sort()
})

const filteredProducts = computed(() => {
  if (!selectedCategory.value) return products.value
  return products.value.filter(p => p.category === selectedCategory.value)
})

// API functions
const fetchVendors = async () => {
  try {
    const response = await fetch('/api/customer/vendors', {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      credentials: 'include'
    })

    if (response.ok) {
      const data = await response.json()
      vendors.value = data.vendors || []
    }
  } catch (error) {
    console.error('Error fetching vendors:', error)
  }
}

const fetchVendorMenu = async (vendorId) => {
  try {
    const response = await fetch(`/api/customer/vendors/${vendorId}/menu`, {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      credentials: 'include'
    })

    if (response.ok) {
      const data = await response.json()
      products.value = data.products || []
    }
  } catch (error) {
    console.error('Error fetching vendor menu:', error)
  }
}

// Event handlers
const selectVendor = async (vendor) => {
  selectedVendor.value = vendor
  selectedCategory.value = null
  loading.value = true
  await fetchVendorMenu(vendor.id)
  loading.value = false
}

const selectCategory = (category) => {
  selectedCategory.value = category
}

const handleAddToCart = async (product) => {
  const result = await addToCart(product.id, 1)
  if (result.success) {
    // Show success message
    console.log('Added to cart:', result.message)
    // Optionally show toast notification
  } else {
    console.error('Failed to add to cart:', result.message)
  }
}

const handleUpdateQuantity = async (cartItemId, quantity) => {
  return await updateCartItem(cartItemId, quantity)
}

const handleRemoveItem = async (cartItemId) => {
  return await removeFromCart(cartItemId)
}

const handleClearVendor = async (vendorId) => {
  return await clearCart(vendorId)
}

const handleClearAll = async () => {
  return await clearCart()
}

const handleCheckout = () => {
  // TODO: Implement checkout flow
  console.log('Proceeding to checkout...')
  // This would navigate to checkout page or open checkout modal
}

// Initialize
onMounted(async () => {
  await Promise.all([
    fetchVendors(),
    fetchCart() // Load cart on page mount
  ])
  loading.value = false
})
</script>

<style scoped>
/* No additional styles needed - using Tailwind CSS classes */
</style>
