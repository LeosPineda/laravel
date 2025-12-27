<template>
  <VendorLayout>
    <div class="bg-white min-h-screen">
      <!-- Header -->
      <div class="bg-white border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between">
          <h1 class="text-xl font-bold text-gray-900">Products</h1>
          <div class="flex gap-2">
            <!-- Bulk Delete (only shown when items selected) -->
            <template v-if="selectedProducts.length > 0">
              <span class="text-sm text-gray-500 self-center">{{ selectedProducts.length }} selected</span>
              <button
                @click="bulkDelete"
                :disabled="bulkProcessing"
                class="px-3 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 text-sm"
              >
                Delete Selected
              </button>
              <button
                @click="clearSelection"
                class="px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm"
              >
                Clear
              </button>
            </template>
            <button
              @click="openCreateModal"
              class="px-4 py-2 bg-orange-500 text-white rounded-xl hover:bg-orange-600"
            >
              Add Product
            </button>
          </div>
        </div>
      </div>

      <!-- Products Content -->
      <div class="p-6">
        <div class="max-w-6xl mx-auto">
          <!-- Filters -->
          <div class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
            <div class="flex flex-col md:flex-row gap-4">
              <!-- Search -->
              <div class="flex-1">
                <input
                  v-model="searchQuery"
                  @input="debouncedSearch"
                  type="text"
                  placeholder="Search products..."
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                />
              </div>

              <!-- Category Filter -->
              <div class="w-full md:w-48">
                <select
                  v-model="selectedCategory"
                  @change="loadProducts"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                >
                  <option value="">All Categories</option>
                  <option v-for="category in categories" :key="category" :value="category">
                    {{ category }}
                  </option>
                </select>
              </div>

              <!-- Select All Checkbox -->
              <div class="flex items-center gap-2">
                <input
                  type="checkbox"
                  :checked="allSelected"
                  @change="toggleSelectAll"
                  class="w-5 h-5 rounded border-gray-300 text-orange-500 focus:ring-orange-500"
                />
                <label class="text-sm text-gray-600">Select All</label>
              </div>
            </div>
          </div>

          <!-- Loading State -->
          <div v-if="loading" class="text-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-orange-500 mx-auto"></div>
            <p class="text-gray-500 mt-4">Loading products...</p>
          </div>

          <!-- Products Grid -->
          <div v-else-if="products.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div
              v-for="product in products"
              :key="product.id"
              class="bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow flex flex-col relative"
            >
              <!-- Selection Checkbox -->
              <div class="absolute top-2 left-2 z-10 bg-white rounded p-1 shadow-sm">
                <input
                  type="checkbox"
                  :checked="selectedProducts.includes(product.id)"
                  @change="toggleSelection(product.id)"
                  class="w-5 h-5 rounded border-gray-300 text-orange-500 focus:ring-orange-500 bg-white shadow"
                />
              </div>

              <!-- Product Image -->
              <div class="h-48 bg-gray-100 flex items-center justify-center relative">
                <img
                  v-if="product.image_url"
                  :src="getImageUrl(product.image_url)"
                  :alt="product.name"
                  class="w-full h-full object-cover"
                />
                <div v-else class="text-gray-400">
                  <span class="text-4xl">🍔</span>
                </div>
                <!-- Stock Badge -->
                <div
                  v-if="product.stock_quantity <= 5"
                  class="absolute top-2 right-2 px-2 py-1 bg-red-500 text-white text-xs rounded-full"
                >
                  Low Stock: {{ product.stock_quantity }}
                </div>
              </div>

              <!-- Product Info -->
              <div class="p-4 flex flex-col flex-1">
                <div class="flex items-start justify-between mb-2">
                  <h3 class="font-semibold text-gray-900 line-clamp-1">{{ product.name }}</h3>
                </div>

                <p class="text-sm text-gray-500 mb-2">{{ product.category || 'Uncategorized' }}</p>

                <div class="flex items-center justify-between mb-3">
                  <span class="text-lg font-bold text-orange-600">₱{{ parseFloat(product.price).toFixed(2) }}</span>
                  <span class="text-sm text-gray-500">Stock: {{ product.stock_quantity }}</span>
                </div>

                <!-- Addons Display -->
                <div class="mb-4 flex-1">
                  <p class="text-xs text-gray-500 mb-1">Add-ons ({{ product.addons?.length || 0 }}):</p>
                  <div v-if="product.addons && product.addons.length > 0" class="flex flex-wrap gap-1">
                    <span
                      v-for="addon in product.addons.slice(0, 4)"
                      :key="addon.id"
                      class="px-2 py-0.5 bg-orange-50 text-orange-700 rounded text-xs"
                    >
                      {{ addon.name }} +₱{{ parseFloat(addon.price).toFixed(0) }}
                    </span>
                    <span v-if="product.addons.length > 4" class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded text-xs">
                      +{{ product.addons.length - 4 }} more
                    </span>
                  </div>
                  <p v-else class="text-xs text-gray-400 italic">No add-ons</p>
                </div>

                <!-- Actions - Fixed at bottom -->
                <div class="grid grid-cols-2 gap-2 mt-auto">
                  <button
                    @click="openEditModal(product)"
                    class="px-3 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 text-sm font-medium"
                  >
                    Edit
                  </button>
                  <button
                    @click="deleteProduct(product)"
                    :disabled="processingProduct === product.id"
                    class="px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm"
                  >
                    Delete
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Empty State -->
          <div v-else class="text-center py-12">
            <div class="text-6xl mb-4">🍔</div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No products found</h3>
            <p class="text-gray-500 mb-6">
              {{ searchQuery || selectedCategory ? 'Try adjusting your filters' : 'Get started by adding your first product' }}
            </p>
            <button
              @click="openCreateModal"
              class="px-6 py-3 bg-orange-500 text-white rounded-xl hover:bg-orange-600"
            >
              Add Product
            </button>
          </div>

          <!-- Pagination -->
          <div v-if="pagination.total > pagination.per_page" class="mt-6 flex items-center justify-between">
            <div class="text-sm text-gray-500">
              Showing {{ ((pagination.current_page - 1) * pagination.per_page) + 1 }} to
              {{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }} of
              {{ pagination.total }} results
            </div>
            <div class="flex gap-2">
              <button
                @click="changePage(pagination.current_page - 1)"
                :disabled="pagination.current_page <= 1"
                class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Previous
              </button>
              <button
                @click="changePage(pagination.current_page + 1)"
                :disabled="pagination.current_page >= pagination.last_page"
                class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Next
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Product Form Modal (includes inline addon management) -->
    <ProductFormModal
      :is-open="showProductModal"
      :product-id="selectedProductId"
      :categories="categories"
      @close="closeProductModal"
      @saved="handleProductSaved"
    />
  </VendorLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import VendorLayout from '@/layouts/vendor/VendorLayout.vue'
import ProductFormModal from '@/components/vendor/ProductFormModal.vue'
import { apiGet, apiPost, apiDelete } from '@/composables/useApi'

const products = ref([])
const categories = ref([])
const loading = ref(false)
const processingProduct = ref(null)
const bulkProcessing = ref(false)
const searchQuery = ref('')
const selectedCategory = ref('')
const selectedProducts = ref([])

// Product Modal State
const showProductModal = ref(false)
const selectedProductId = ref(null)

const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 20,
  total: 0
})

let searchTimeout = null

// Computed
const allSelected = computed(() => {
  return products.value.length > 0 && selectedProducts.value.length === products.value.length
})

const getImageUrl = (url) => {
  if (!url) return null
  if (url.startsWith('http')) return url
  return `/storage/${url}`
}

const loadProducts = async () => {
  loading.value = true
  try {
    const params = {
      page: pagination.value.current_page,
      per_page: pagination.value.per_page,
      search: searchQuery.value || undefined,
      category: selectedCategory.value || undefined
    }

    const response = await apiGet('/api/vendor/products', params)

    if (response.ok) {
      const data = await response.json()
      products.value = data.products || []
      pagination.value = data.pagination || pagination.value
      categories.value = data.categories || []
    }
  } catch (error) {
    console.error('Error loading products:', error)
  } finally {
    loading.value = false
  }
}

const debouncedSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    pagination.value.current_page = 1
    loadProducts()
  }, 300)
}

const changePage = (page) => {
  pagination.value.current_page = page
  loadProducts()
}

// Product Modal Functions
const openCreateModal = () => {
  selectedProductId.value = null
  showProductModal.value = true
}

const openEditModal = (product) => {
  selectedProductId.value = product.id
  showProductModal.value = true
}

const closeProductModal = () => {
  showProductModal.value = false
  selectedProductId.value = null
}

const handleProductSaved = () => {
  loadProducts()
}

// Product Actions
const deleteProduct = async (product) => {
  if (!confirm(`Are you sure you want to delete "${product.name}"?`)) return

  processingProduct.value = product.id
  try {
    const response = await apiDelete(`/api/vendor/products/${product.id}`)

    if (response.ok) {
      await loadProducts()
    } else {
      const error = await response.json()
      alert(error.error || 'Failed to delete product')
    }
  } catch (error) {
    console.error('Error deleting product:', error)
    alert('Failed to delete product')
  } finally {
    processingProduct.value = null
  }
}

// Selection Functions
const toggleSelection = (productId) => {
  const index = selectedProducts.value.indexOf(productId)
  if (index > -1) {
    selectedProducts.value.splice(index, 1)
  } else {
    selectedProducts.value.push(productId)
  }
}

const toggleSelectAll = () => {
  if (allSelected.value) {
    selectedProducts.value = []
  } else {
    selectedProducts.value = products.value.map(p => p.id)
  }
}

const clearSelection = () => {
  selectedProducts.value = []
}

const bulkDelete = async () => {
  if (!confirm(`Are you sure you want to delete ${selectedProducts.value.length} products?`)) return

  bulkProcessing.value = true
  try {
    const response = await apiPost('/api/vendor/products/bulk', {
      product_ids: selectedProducts.value,
      action: 'delete'
    })

    if (response.ok) {
      const data = await response.json()
      alert(data.message)
      clearSelection()
      await loadProducts()
    } else {
      const error = await response.json()
      alert(error.error || 'Failed to delete products')
    }
  } catch (error) {
    console.error('Error performing bulk delete:', error)
    alert('Failed to delete products')
  } finally {
    bulkProcessing.value = false
  }
}

onMounted(async () => {
  await loadProducts()
})
</script>
