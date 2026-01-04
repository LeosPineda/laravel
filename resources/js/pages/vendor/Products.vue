<template>
  <VendorLayout>
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
      <div class="px-4 sm:px-6 py-6">
        <h1 class="text-2xl font-bold text-gray-900">Products</h1>
        <p class="text-gray-600 mt-1">Manage your product catalog</p>
      </div>
    </div>

    <!-- Sticky Filters Header -->
    <div class="sticky top-0 z-40 bg-white border-b border-gray-200 px-4 sm:px-6 py-3">
        <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center">
          <!-- Search -->
          <div class="flex-1">
            <input
              v-model="searchQuery"
              @input="debouncedSearch"
              type="text"
              placeholder="Search products..."
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 text-sm"
            />
          </div>

          <!-- Category Filter -->
          <div class="w-full sm:w-40">
            <select
              v-model="selectedCategory"
              @change="loadProducts"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 text-sm"
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
              class="w-4 h-4 rounded border-gray-300 text-orange-500 focus:ring-orange-500"
            />
            <label class="text-sm text-gray-600">Select All</label>
          </div>

          <!-- Actions -->
          <div class="flex gap-2 ml-auto">
            <!-- Bulk Delete (only shown when items selected) -->
            <template v-if="selectedProducts.length > 0">
              <span class="text-sm text-gray-500 self-center hidden sm:inline">{{ selectedProducts.length }} selected</span>
              <button
                @click="bulkDelete"
                :disabled="bulkProcessing"
                class="px-3 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 text-sm"
              >
                Delete
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
              class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 text-sm font-medium"
            >
              Add Product
            </button>
          </div>
        </div>
      </div>

      <!-- Products Grid -->
      <div class="px-4 py-4">
        <!-- Loading State -->
        <div v-if="loading" class="text-center py-12">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-orange-500 mx-auto"></div>
          <p class="text-gray-500 mt-4">Loading products...</p>
        </div>

        <!-- Products Grid - Consistent Layout -->
        <div v-else-if="products.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
          <div
            v-for="product in products"
            :key="product.id"
            class="bg-white border border-gray-200 rounded-lg overflow-hidden hover:shadow-md transition-shadow relative group flex flex-col h-full"
          >
            <!-- Selection Checkbox -->
            <div class="absolute top-1 left-1 z-10">
              <input
                type="checkbox"
                :checked="selectedProducts.includes(product.id)"
                @change="toggleSelection(product.id)"
                class="w-4 h-4 rounded border-gray-300 text-orange-500 focus:ring-orange-500"
              />
            </div>

            <!-- Product Image - Fixed Height -->
            <div class="h-24 bg-gray-100 flex items-center justify-center relative flex-shrink-0">
              <img
                v-if="product.image_url"
                :src="getImageUrl(product.image_url)"
                :alt="product.name"
                class="w-full h-full object-cover"
              />
              <div v-else class="text-gray-400">
                <span class="text-2xl">🍔</span>
              </div>
              <!-- Stock Badge -->
              <div
                v-if="product.stock_quantity <= 5"
                class="absolute top-1 right-1 px-1 py-0.5 bg-red-500 text-white text-xs rounded"
              >
                {{ product.stock_quantity }}
              </div>
            </div>

            <!-- Product Info - Consistent Spacing -->
            <div class="p-2 flex flex-col flex-1 space-y-1.5">
              <!-- Product Name & Category - Always Visible -->
              <div class="space-y-0.5">
                <h3 class="font-medium text-gray-900 text-sm line-clamp-1 leading-tight">{{ product.name }}</h3>
                <p class="text-xs text-gray-500 truncate">{{ product.category || 'Uncategorized' }}</p>
              </div>

              <!-- Price & Stock - Always Visible -->
              <div class="flex items-center justify-between">
                <span class="text-sm font-bold text-orange-600">₱{{ parseFloat(product.price).toFixed(0) }}</span>
                <span class="text-xs text-gray-500">Stock: {{ product.stock_quantity }}</span>
              </div>

              <!-- Addons Info - Only if Available -->
              <div v-if="product.addons && product.addons.length > 0" class="text-xs text-gray-400">
                {{ product.addons.length }} add-ons available
              </div>

              <!-- Spacer to push actions to bottom -->
              <div class="flex-1"></div>

              <!-- Actions - Always at Bottom -->
              <div class="pt-2 border-t border-gray-100">
                <div class="grid grid-cols-2 gap-1">
                  <button
                    @click="openEditModal(product)"
                    class="px-2 py-1.5 bg-orange-500 text-white rounded text-xs hover:bg-orange-600 font-medium"
                  >
                    Edit
                  </button>
                  <button
                    @click="deleteProduct(product)"
                    :disabled="processingProduct === product.id"
                    class="px-2 py-1.5 bg-gray-100 text-gray-700 rounded text-xs hover:bg-gray-200 disabled:opacity-50"
                  >
                    Delete
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-12">
          <div class="text-4xl mb-4">🍔</div>
          <h3 class="text-lg font-medium text-gray-900 mb-2">No products found</h3>
          <p class="text-gray-500 mb-6">
            {{ searchQuery || selectedCategory ? 'Try adjusting your filters' : 'Get started by adding your first product' }}
          </p>
          <button
            @click="openCreateModal"
            class="px-6 py-3 bg-orange-500 text-white rounded-lg hover:bg-orange-600"
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
              class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed text-sm"
            >
              Previous
            </button>
            <button
              @click="changePage(pagination.current_page + 1)"
              :disabled="pagination.current_page >= pagination.last_page"
              class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed text-sm"
            >
              Next
            </button>
          </div>
        </div>
      </div>

    <!-- Product Form Modal -->
    <ProductFormModal
      :is-open="showProductModal"
      :product-id="selectedProductId"
      :categories="categories"
      @close="closeProductModal"
      @saved="handleProductSaved"
    />

    <!-- Delete Confirmation Modal -->
    <ConfirmModal
      :is-open="showDeleteModal"
      :title="deleteModalConfig.title"
      :message="deleteModalConfig.message"
      :confirm-text="deleteModalConfig.confirmText"
      :loading="processingDelete"
      icon="🗑️"
      variant="danger"
      @confirm="confirmDelete"
      @cancel="cancelDelete"
    />
  </VendorLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import VendorLayout from '@/layouts/vendor/VendorLayout.vue'
import ProductFormModal from '@/components/vendor/ProductFormModal.vue'
import ConfirmModal from '@/components/ui/ConfirmModal.vue'
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

// Delete Modal State
const showDeleteModal = ref(false)
const processingDelete = ref(false)
const deleteTarget = ref(null)
const deleteModalConfig = ref({
  title: 'Delete Product',
  message: 'Are you sure you want to delete this product?',
  confirmText: 'Delete'
})

const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 48, // Show more products per page
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
const deleteProduct = (product) => {
  deleteTarget.value = product
  deleteModalConfig.value = {
    title: 'Delete Product',
    message: `Are you sure you want to delete "${product.name}"? This action cannot be undone.`,
    confirmText: 'Delete'
  }
  showDeleteModal.value = true
}

const bulkDelete = () => {
  deleteTarget.value = 'bulk'
  deleteModalConfig.value = {
    title: 'Delete Products',
    message: `Are you sure you want to delete ${selectedProducts.value.length} products? This action cannot be undone.`,
    confirmText: `Delete ${selectedProducts.value.length} Products`
  }
  showDeleteModal.value = true
}

const confirmDelete = async () => {
  processingDelete.value = true

  try {
    if (deleteTarget.value === 'bulk') {
      const response = await apiPost('/api/vendor/products/bulk', {
        product_ids: selectedProducts.value,
        action: 'delete'
      })

      if (response.ok) {
        clearSelection()
        await loadProducts()
      } else {
        const error = await response.json()
        alert(error.error || 'Failed to delete products')
      }
    } else {
      const product = deleteTarget.value
      const response = await apiDelete(`/api/vendor/products/${product.id}`)

      if (response.ok) {
        await loadProducts()
      } else {
        const error = await response.json()
        alert(error.error || 'Failed to delete product')
      }
    }
  } catch (error) {
    console.error('Error deleting:', error)
    alert('Failed to delete')
  } finally {
    processingDelete.value = false
    showDeleteModal.value = false
    deleteTarget.value = null
  }
}

const cancelDelete = () => {
  showDeleteModal.value = false
  deleteTarget.value = null
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

onMounted(async () => {
  await loadProducts()
})
</script>
