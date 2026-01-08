<template>
  <!-- Product Modal Container -->
  <div
    v-if="isOpen"
    class="fixed inset-0 z-40 flex items-start sm:items-center justify-center"
    @click="handleBackdropClick"
  >
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>

    <!-- Modal Content - Mobile: Full Screen, Desktop: Large Modal -->
    <div
      ref="modalContent"
      class="relative bg-white w-full h-full sm:h-auto sm:max-h-[90vh] sm:rounded-2xl shadow-2xl lg:max-w-[90vw] xl:max-w-[85vw] lg:mx-4 transform transition-all duration-300 ease-out flex flex-col overflow-hidden"
      :class="{
        'translate-y-0 opacity-100': isOpen,
        'translate-y-full opacity-0 sm:translate-y-0 sm:scale-95 sm:opacity-0': !isOpen
      }"
      @click.stop
    >
      <!-- Mobile Header with Back Button (Hidden on Desktop, Sticky on Mobile) -->
      <div class="hidden sm:hidden sticky top-0 z-10 flex items-center justify-between p-4 bg-white border-b border-gray-200 flex-shrink-0">
        <button
          @click="closeModal"
          class="flex items-center gap-2 text-gray-600 hover:text-gray-900 transition-colors"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
          <span class="text-sm font-medium">Back</span>
        </button>

        <div class="flex items-center gap-2">
          <div class="w-8 h-8 bg-gradient-to-br from-orange-100 to-red-100 rounded-lg flex items-center justify-center">
            <span class="text-sm font-bold text-gray-600">{{ getVendorInitials(vendor?.brand_name || '') }}</span>
          </div>
          <h2 class="text-lg font-bold text-gray-900 truncate max-w-[200px]">
            {{ vendor?.brand_name || 'Menu' }}
          </h2>
        </div>

        <div class="w-16"></div> <!-- Spacer for centering -->
      </div>

      <!-- Desktop Header (Hidden on Mobile) -->
      <div class="hidden sm:flex items-center justify-between p-6 border-b border-gray-200 flex-shrink-0">
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 bg-gradient-to-br from-orange-100 to-red-100 rounded-xl flex items-center justify-center">
            <span class="text-xl font-bold text-gray-600">{{ getVendorInitials(vendor?.brand_name || '') }}</span>
          </div>
          <div>
            <h2 class="text-xl font-bold text-gray-900">{{ vendor?.brand_name || 'Menu' }}</h2>
            <p class="text-sm text-gray-500">{{ products?.length || 0 }} items available</p>
          </div>
        </div>
        <button
          @click="closeModal"
          class="p-3 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full transition-colors"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Scrollable Content Area -->
      <div class="flex-1 overflow-y-auto">
        <!-- Loading State -->
        <div v-if="loading" class="flex items-center justify-center h-full min-h-[400px]">
          <div class="text-center">
            <div class="animate-spin rounded-full h-12 w-12 lg:h-16 lg:w-16 border-b-2 border-orange-500 mx-auto mb-4"></div>
            <p class="text-gray-600 font-medium">Loading products...</p>
          </div>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="flex items-center justify-center h-full">
          <div class="text-center px-4">
            <div class="text-red-500 text-4xl mb-4">❌</div>
            <p class="text-gray-900 font-medium mb-2">Failed to load products</p>
            <p class="text-gray-600 text-sm mb-4">{{ error }}</p>
            <button
              @click="loadProducts"
              class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors"
            >
              Try Again
            </button>
          </div>
        </div>

        <!-- Products Content -->
        <div v-else class="p-4 lg:p-6">
          <!-- Search Bar -->
          <div class="mb-4">
            <div class="relative">
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Search products..."
                class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent text-base"
              >
              <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
          </div>

          <!-- Category Filter (if categories exist) -->
          <div v-if="availableCategories.length > 0" class="mb-4">
            <div class="flex flex-wrap gap-2 overflow-x-auto pb-2">
              <button
                @click="setCategoryFilter(null)"
                class="px-3 py-2 rounded-full text-sm font-medium transition-colors whitespace-nowrap"
                :class="selectedCategory === null
                  ? 'bg-orange-500 text-white'
                  : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
              >
                All
              </button>
              <button
                v-for="category in availableCategories"
                :key="category"
                @click="setCategoryFilter(category)"
                class="px-3 py-2 rounded-full text-sm font-medium transition-colors whitespace-nowrap"
                :class="selectedCategory === category
                  ? 'bg-orange-500 text-white'
                  : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
              >
                {{ category }}
              </button>
            </div>
          </div>

          <!-- Products Grid -->
          <div v-if="filteredProducts.length > 0">
            <!-- Mobile: 2 columns, Tablet: 3 columns, Desktop: 4+ columns -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-3 lg:gap-4">
              <ProductBox
                v-for="product in filteredProducts"
                :key="product.id"
                :product="product"
                @order-now="handleOrderNow"
                @view-details="handleViewDetails"
              />
            </div>
          </div>

          <!-- Empty State -->
          <div v-else class="text-center py-12">
            <div class="text-gray-400 text-4xl mb-4">🔍</div>
            <p class="text-gray-500 font-medium mb-2">No products found</p>
            <p class="text-gray-400 text-sm">
              {{ searchQuery || selectedCategory ? 'Try adjusting your filters' : 'This vendor has no available products' }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import ProductBox from './ProductBox.vue'

interface Addon {
  id: number
  name: string
  price: string | number
  is_active: boolean
}

interface AddonInput {
  addon_id: number
  quantity: number
  price: number
}

interface Product {
  id: number
  name: string
  price: string | number
  image_url?: string
  category?: string
  stock_quantity: number
  is_active?: boolean
  is_featured?: boolean
  addons?: Addon[]
}

interface Vendor {
  id: number
  brand_name: string
  brand_logo?: string
}

interface Props {
  vendorId: number | null
  isOpen: boolean
}

interface Emits {
  (e: 'close'): void
  (e: 'product-select', product: Product): void
  (e: 'order-now', product: Product): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

// Reactive data
const products = ref<Product[]>([])
const vendor = ref<Vendor | null>(null)
const loading = ref(false)
const error = ref<string | null>(null)
const searchQuery = ref('')
const selectedCategory = ref<string | null>(null)

// Computed
const availableCategories = computed(() => {
  const categories = products.value
    .map(product => product.category)
    .filter(category => category && category.trim())
    .map(category => category!.trim())

  return [...new Set(categories)].sort()
})

const filteredProducts = computed(() => {
  let filtered = products.value

  // Filter out out of stock products
  filtered = filtered.filter(product => product.stock_quantity > 0)

  // Apply search filter
  if (searchQuery.value) {
    filtered = filtered.filter(product =>
      product.name.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
  }

  // Apply category filter
  if (selectedCategory.value) {
    filtered = filtered.filter(product => product.category === selectedCategory.value)
  }

  return filtered
})

// Methods
const getVendorInitials = (name: string): string => {
  if (!name) return '?'
  return name
    .split(' ')
    .map(word => word.charAt(0).toUpperCase())
    .join('')
    .substring(0, 2)
}

const handleBackdropClick = () => {
  closeModal()
}

const closeModal = () => {
  emit('close')
}

const setCategoryFilter = (category: string | null) => {
  selectedCategory.value = category
}

// Helper function to fetch product with addons
const fetchProductWithAddons = async (productId: number): Promise<Product> => {
  const response = await fetch(`/api/customer/menu/products/${productId}`)
  if (response.ok) {
    const data = await response.json()
    if (data.success && data.product) {
      return data.product
    }
  }
  throw new Error('Failed to fetch product details')
}

// Handle product details view
const handleViewDetails = (product: Product) => {
  if (product.addons && Array.isArray(product.addons)) {
    emit('product-select', { ...product })
  } else {
    fetchProductWithAddons(product.id)
      .then((completeProduct: Product) => {
        emit('product-select', completeProduct)
      })
      .catch(() => {
        emit('product-select', { ...product, addons: [] })
      })
  }
}

// Handle order now action
const handleOrderNow = (product: Product) => {
  if (product.addons && product.addons.length > 0) {
    emit('order-now', product)
  } else {
    fetchProductWithAddons(product.id)
      .then((completeProduct: Product) => {
        emit('order-now', completeProduct)
      })
      .catch(() => {
        emit('order-now', product)
      })
  }
}

const loadProducts = async () => {
  if (!props.vendorId) return

  loading.value = true
  error.value = null

  try {
    // Load vendor info
    const vendorResponse = await fetch(`/api/customer/menu/vendors/${props.vendorId}`)
    if (vendorResponse.ok) {
      const vendorData = await vendorResponse.json()
      vendor.value = vendorData.vendor
    }

    // Load products
    const productsResponse = await fetch(`/api/customer/menu/vendors/${props.vendorId}/products`)
    if (productsResponse.ok) {
      const productsData = await productsResponse.json()
      products.value = productsData.products || []
    } else {
      throw new Error('Failed to load products')
    }
  } catch (err) {
    console.error('Error loading products:', err)
    error.value = err instanceof Error ? err.message : 'Failed to load products'
  } finally {
    loading.value = false
  }
}

// Watch for modal open state
watch(() => props.isOpen, (isOpen) => {
  if (isOpen && props.vendorId) {
    loadProducts()
  }
})

// Watch for vendor changes
watch(() => props.vendorId, (newVendorId) => {
  if (newVendorId && props.isOpen) {
    loadProducts()
  }
})
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Smooth transitions */
* {
  transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}

/* Custom scrollbar for better mobile UX */
.overflow-y-auto::-webkit-scrollbar {
  width: 4px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: transparent;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: rgba(156, 163, 175, 0.5);
  border-radius: 2px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: rgba(156, 163, 175, 0.7);
}

/* Prevent body scroll when modal is open on mobile */
@media (max-width: 640px) {
  .fixed.inset-0 {
    position: fixed;
  }
}
</style>
