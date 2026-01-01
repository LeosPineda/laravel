<template>
  <div class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-300 overflow-hidden group">
    <!-- Product Image -->
    <div class="h-48 bg-gray-100 flex items-center justify-center relative overflow-hidden">
      <img
        v-if="product.image_url"
        :src="product.image_url"
        :alt="product.name"
        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
      />
      <div v-else class="text-4xl text-gray-400">
        🍎
      </div>

      <!-- Price Badge -->
      <div class="absolute top-2 right-2">
        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
          ₱{{ formatPrice(product.price) }}
        </span>
      </div>
    </div>

    <!-- Product Info -->
    <div class="p-4">
      <div class="flex items-start justify-between">
        <div class="flex-1 min-w-0">
          <h3 class="text-lg font-semibold text-gray-900 truncate group-hover:text-blue-600 transition-colors">
            {{ product.name }}
          </h3>
          <p v-if="product.description" class="text-sm text-gray-600 mt-1 line-clamp-2">
            {{ product.description }}
          </p>
          <div class="flex items-center mt-2 text-sm text-gray-500">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
            </svg>
            ₱{{ formatPrice(product.price) }}
          </div>
        </div>
      </div>

      <!-- Quantity Selector and Add to Cart -->
      <div class="mt-4 flex items-center justify-between">
        <!-- Quantity Controls -->
        <div class="flex items-center space-x-2">
          <button
            @click="decreaseQuantity"
            :disabled="quantity <= 1"
            class="w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
            </svg>
          </button>

          <span class="w-8 text-center font-medium">{{ quantity }}</span>

          <button
            @click="increaseQuantity"
            class="w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center hover:bg-gray-50 transition-colors"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
          </button>
        </div>

        <!-- Add to Cart Button -->
        <button
          @click="addToCart"
          :disabled="isAddingToCart"
          class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
        >
          <svg v-if="isAddingToCart" class="w-4 h-4 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.5 6H19M7 13v6a2 2 0 002 2h7a2 2 0 002-2v-6M7 13H5.4" />
          </svg>
          {{ isAddingToCart ? 'Adding...' : 'Add to Cart' }}
        </button>
      </div>
    </div>

    <!-- Product Details Modal -->
    <div v-if="showDetailsModal" class="fixed inset-0 z-60 overflow-y-auto" @click.self="showDetailsModal = false">
      <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="showDetailsModal = false"></div>

        <!-- Modal content -->
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <!-- Modal Header -->
          <div class="bg-white px-6 pt-6 pb-4">
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-medium text-gray-900">{{ product.name }}</h3>
              <button
                @click="showDetailsModal = false"
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
            <!-- Product Image -->
            <div class="h-64 bg-gray-100 flex items-center justify-center rounded-lg mb-4 overflow-hidden">
              <img
                v-if="product.image_url"
                :src="product.image_url"
                :alt="product.name"
                class="w-full h-full object-cover"
              />
              <div v-else class="text-6xl text-gray-400">
                🍎
              </div>
            </div>

            <!-- Product Description -->
            <p v-if="product.description" class="text-gray-600 mb-4">
              {{ product.description }}
            </p>

            <!-- Price -->
            <div class="flex items-center justify-between mb-6">
              <span class="text-2xl font-bold text-gray-900">
                ₱{{ formatPrice(product.price) }}
              </span>
              <span v-if="product.sub_price" class="text-sm text-gray-500 line-through">
                ₱{{ formatPrice(product.sub_price) }}
              </span>
            </div>

            <!-- Addons Section -->
            <div v-if="product.addons && product.addons.length > 0" class="mb-6">
              <h4 class="text-sm font-medium text-gray-900 mb-3">Add-ons</h4>
              <div class="space-y-2">
                <label v-for="addon in product.addons" :key="addon.id" class="flex items-center">
                  <input
                    type="checkbox"
                    v-model="selectedAddons"
                    :value="addon"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                  />
                  <span class="ml-2 text-sm text-gray-700">
                    {{ addon.name }} (+₱{{ formatPrice(addon.price) }})
                  </span>
                </label>
              </div>
            </div>

            <!-- Quantity and Add to Cart -->
            <div class="flex items-center justify-between">
              <!-- Quantity Controls -->
              <div class="flex items-center space-x-3">
                <button
                  @click="decreaseQuantity"
                  :disabled="quantity <= 1"
                  class="w-10 h-10 rounded-full border border-gray-300 flex items-center justify-center hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                  </svg>
                </button>

                <span class="w-8 text-center font-medium text-lg">{{ quantity }}</span>

                <button
                  @click="increaseQuantity"
                  class="w-10 h-10 rounded-full border border-gray-300 flex items-center justify-center hover:bg-gray-50 transition-colors"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                  </svg>
                </button>
              </div>

              <!-- Add to Cart Button -->
              <button
                @click="addToCartFromModal"
                :disabled="isAddingToCart"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
              >
                <svg v-if="isAddingToCart" class="w-4 h-4 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.5 6H19M7 13v6a2 2 0 002 2h7a2 2 0 002-2v-6M7 13H5.4" />
                </svg>
                {{ isAddingToCart ? 'Adding...' : 'Add to Cart' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Product Details Button -->
    <button
      @click="showDetailsModal = true"
      class="absolute top-2 left-2 bg-white bg-opacity-90 hover:bg-opacity-100 rounded-full p-2 shadow-sm transition-all opacity-0 group-hover:opacity-100"
    >
      <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
    </button>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  product: {
    type: Object,
    required: true
  },
  vendorId: {
    type: [Number, String],
    required: true
  }
})

const emit = defineEmits(['add-to-cart'])

// Reactive data
const quantity = ref(1)
const selectedAddons = ref([])
const showDetailsModal = ref(false)
const isAddingToCart = ref(false)

// Methods
const formatPrice = (price) => {
  return new Intl.NumberFormat('en-PH', {
    minimumFractionDigits: 2
  }).format(price)
}

const increaseQuantity = () => {
  quantity.value++
}

const decreaseQuantity = () => {
  if (quantity.value > 1) {
    quantity.value--
  }
}

const calculateTotalPrice = () => {
  let total = props.product.price * quantity.value

  // Add addon prices
  selectedAddons.value.forEach(addon => {
    total += addon.price * quantity.value
  })

  return total
}

const addToCart = async () => {
  await addToCartCommon()
}

const addToCartFromModal = async () => {
  await addToCartCommon()
  showDetailsModal.value = false
}

const addToCartCommon = async () => {
  try {
    isAddingToCart.value = true

    const cartItem = {
      vendor_id: props.vendorId,
      product_id: props.product.id,
      quantity: quantity.value,
      selected_addons: selectedAddons.value,
      unit_price: calculateTotalPrice() / quantity.value,
      total_price: calculateTotalPrice()
    }

    emit('add-to-cart', cartItem)

    // Reset form
    quantity.value = 1
    selectedAddons.value = []

    // Show success feedback
    console.log('Item added to cart:', cartItem)
  } catch (error) {
    console.error('Error adding to cart:', error)
  } finally {
    isAddingToCart.value = false
  }
}
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Hover effects */
.group:hover .group-hover\:scale-105 {
  transform: scale(1.05);
}

.group:hover .group-hover\:text-blue-600 {
  color: #2563eb;
}

.group:hover .group-hover\:opacity-100 {
  opacity: 1;
}

/* Smooth transitions */
* {
  transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, transform;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}

/* Responsive design adjustments */
@media (max-width: 640px) {
  .group {
    margin-bottom: 1rem;
  }
}
</style>
