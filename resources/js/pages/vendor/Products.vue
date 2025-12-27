<template>
  <VendorLayout>
    <div class="min-h-screen bg-white">
      <!-- Header -->
      <div class="bg-white border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between">
          <h1 class="text-xl font-bold text-gray-900">Products</h1>
          <div class="flex gap-2">
            <button
              @click="showCreateModal = true"
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

              <!-- Status Filter -->
              <div class="w-full md:w-32">
                <select
                  v-model="selectedStatus"
                  @change="loadProducts"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                >
                  <option value="">All Status</option>
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
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
              class="bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow"
            >
              <!-- Product Image -->
              <div class="h-48 bg-gray-100 flex items-center justify-center">
                <img
                  v-if="product.image_url"
                  :src="product.image_url"
                  :alt="product.name"
                  class="w-full h-full object-cover"
                />
                <div v-else class="text-gray-400">
                  <span class="text-4xl">🍔</span>
                </div>
              </div>

              <!-- Product Info -->
              <div class="p-4">
                <div class="flex items-start justify-between mb-2">
                  <h3 class="font-semibold text-gray-900">{{ product.name }}</h3>
                  <span
                    :class="[
                      'px-2 py-1 rounded-full text-xs font-medium',
                      product.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'
                    ]"
                  >
                    {{ product.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </div>

                <p class="text-sm text-gray-500 mb-2" v-if="product.category">{{ product.category }}</p>

                <div class="flex items-center justify-between mb-3">
                  <span class="text-lg font-bold text-orange-600">₱{{ product.price.toFixed(2) }}</span>
                  <span class="text-sm text-gray-500">Stock: {{ product.stock_quantity }}</span>
                </div>

                <!-- Addons -->
                <div v-if="product.addons && product.addons.length > 0" class="mb-3">
                  <p class="text-xs text-gray-500 mb-1">Add-ons:</p>
                  <div class="flex flex-wrap gap-1">
                    <span
                      v-for="addon in product.addons.slice(0, 3)"
                      :key="addon.id"
                      class="px-2 py-1 bg-gray-100 text-gray-600 rounded text-xs"
                    >
                      {{ addon.name }} (+₱{{ addon.price }})
                    </span>
                    <span v-if="product.addons.length > 3" class="px-2 py-1 bg-gray-100 text-gray-600 rounded text-xs">
                      +{{ product.addons.length - 3 }} more
                    </span>
                  </div>
                </div>

                <!-- Actions -->
                <div class="flex gap-2">
                  <button
                    @click="editProduct(product)"
                    class="flex-1 px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm"
                  >
                    Edit
                  </button>
                  <button
                    @click="toggleProductStatus(product)"
                    :class="[
                      'flex-1 px-3 py-2 rounded-lg text-sm',
                      product.is_active
                        ? 'bg-red-100 text-red-700 hover:bg-red-200'
                        : 'bg-green-100 text-green-700 hover:bg-green-200'
                    ]"
                  >
                    {{ product.is_active ? 'Deactivate' : 'Activate' }}
                  </button>
                  <button
                    @click="deleteProduct(product)"
                    class="px-3 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 text-sm"
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
              {{ searchQuery || selectedCategory || selectedStatus ? 'Try adjusting your filters' : 'Get started by adding your first product' }}
            </p>
            <button
              @click="showCreateModal = true"
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
  </VendorLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import VendorLayout from '@/layouts/vendor/VendorLayout.vue'

const products = ref([])
const categories = ref([])
const loading = ref(false)
const searchQuery = ref('')
const selectedCategory = ref('')
const selectedStatus = ref('')
const showCreateModal = ref(false)

const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 20,
  total: 0
})

let searchTimeout = null

const loadProducts = async () => {
  loading.value = true
  try {
    const params = new URLSearchParams({
      page: pagination.value.current_page,
      per_page: pagination.value.per_page
    })

    if (searchQuery.value) params.append('search', searchQuery.value)
    if (selectedCategory.value) params.append('category', selectedCategory.value)
    if (selectedStatus.value) params.append('status', selectedStatus.value)

    const response = await fetch(`/api/vendor/products?${params}`, {
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json'
      }
    })

    if (response.ok) {
      const data = await response.json()
      products.value = data.products
      pagination.value = data.pagination
      categories.value = data.categories
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

const toggleProductStatus = async (product) => {
  try {
    const response = await fetch(`/api/vendor/products/${product.id}/toggle-status`, {
      method: 'PATCH',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json'
      }
    })

    if (response.ok) {
      await loadProducts()
    } else {
      const error = await response.json()
      alert(error.error || 'Failed to update product status')
    }
  } catch (error) {
    console.error('Error toggling product status:', error)
    alert('Failed to update product status')
  }
}

const deleteProduct = async (product) => {
  if (!confirm(`Are you sure you want to delete "${product.name}"?`)) return

  try {
    const response = await fetch(`/api/vendor/products/${product.id}`, {
      method: 'DELETE',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('token')}`,
        'Content-Type': 'application/json'
      }
    })

    if (response.ok) {
      await loadProducts()
      alert('Product deleted successfully!')
    } else {
      const error = await response.json()
      alert(error.error || 'Failed to delete product')
    }
  } catch (error) {
    console.error('Error deleting product:', error)
    alert('Failed to delete product')
  }
}

const editProduct = (product) => {
  // TODO: Implement edit functionality
  alert('Edit functionality coming soon!')
}

onMounted(async () => {
  await loadProducts()
})
</script>
