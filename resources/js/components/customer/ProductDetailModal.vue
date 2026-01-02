<template>
  <!-- Product Detail Modal -->
  <div
    v-if="isOpen"
    class="fixed inset-0 z-50 flex items-center justify-center"
    @click="handleBackdropClick"
  >
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>

    <!-- Modal Content -->
    <div
      class="relative bg-white rounded-t-3xl lg:rounded-3xl shadow-2xl w-full h-[90vh] lg:h-[85vh] lg:max-w-3xl lg:mx-4 transform transition-all duration-300 ease-out"
      :class="{
        'translate-y-0 opacity-100': isOpen,
        'translate-y-full opacity-0 lg:translate-y-0 lg:scale-95 lg:opacity-0': !isOpen
      }"
      @click.stop
    >
      <!-- Header -->
      <div class="flex items-center justify-between p-4 lg:p-6 border-b border-gray-200">
        <div class="flex items-center gap-3">
          <!-- Close Button (X) -->
          <button
            @click="closeModal"
            class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full transition-colors"
            aria-label="Close modal"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>

          <!-- Product Title -->
          <div>
            <h2 class="text-lg lg:text-xl font-bold text-gray-900">
              {{ product?.name || 'Checkout' }}
            </h2>
            <p class="text-sm text-gray-500">
              Complete your order
            </p>
          </div>
        </div>

        <!-- Cart Badge -->
        <div class="flex items-center gap-2">
          <div class="relative">
            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.5 6M7 13l1.5 6m6.5-6l-1.5-6m-3 6l-1.5-6m-3 6l-1.5-6" />
            </svg>
            <span
              v-if="cartCount && cartCount > 0"
              class="absolute -top-2 -right-2 bg-orange-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-bold"
            >
              {{ cartCount }}
            </span>
          </div>
          <span class="text-sm text-gray-500">Cart</span>
        </div>
      </div>

      <!-- Content -->
      <div class="flex-1 overflow-hidden flex flex-col">
        <div class="flex-1 overflow-y-auto">
          <!-- Loading State -->
          <div v-if="loading" class="flex items-center justify-center h-full">
            <div class="text-center">
              <div class="animate-spin rounded-full h-8 w-8 lg:h-12 lg:w-12 border-b-2 border-orange-500 mx-auto mb-4"></div>
              <p class="text-gray-600">Loading product details...</p>
            </div>
          </div>

          <!-- Product Content -->
          <div v-else-if="product" class="p-4 lg:p-6 space-y-6">
            <!-- Product Image and Basic Info -->
            <div class="flex gap-4">
              <!-- Clickable Product Image -->
              <div
                @click="showImagePreview = true"
                class="w-24 h-24 lg:w-32 lg:h-32 bg-gradient-to-br from-orange-50 to-red-50 rounded-xl overflow-hidden flex-shrink-0 cursor-pointer hover:opacity-80 transition-opacity"
              >
                <img
                  v-if="product.image_url"
                  :src="getImageUrl(product.image_url)"
                  :alt="product.name"
                  class="w-full h-full object-cover"
                  @error="handleImageError"
                />
                <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                  <span class="text-2xl">🍽️</span>
                </div>
              </div>

              <!-- Product Details -->
              <div class="flex-1">
                <h3 class="text-xl font-bold text-gray-900 mb-2">{{ product.name }}</h3>

                <!-- Price -->
                <div class="text-2xl font-bold text-orange-600 mb-2">
                  ₱{{ formatPrice(product.price) }}
                </div>

                <!-- Stock Info -->
                <div class="text-sm">
                  <span
                    v-if="product.stock_quantity > 5"
                    class="text-green-600"
                  >
                    ✓ In Stock ({{ product.stock_quantity }} available)
                  </span>
                  <span
                    v-else-if="product.stock_quantity > 0"
                    class="text-yellow-600"
                  >
                    ⚠ Only {{ product.stock_quantity }} left!
                  </span>
                  <span
                    v-else
                    class="text-red-600"
                  >
                    ❌ Out of Stock
                  </span>
                </div>
              </div>
            </div>

            <!-- Quantity Selector -->
            <div class="border-t pt-6">
              <h4 class="font-semibold text-gray-900 mb-3">Quantity</h4>
              <div class="flex items-center gap-3">
                <button
                  @click="decrementQuantity"
                  :disabled="quantity <= 1 || adding"
                  class="w-10 h-10 rounded-full border border-gray-300 flex items-center justify-center hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                  </svg>
                </button>

                <div class="flex-1 text-center">
                  <span class="text-2xl font-bold">{{ quantity }}</span>
                </div>

                <button
                  @click="incrementQuantity"
                  :disabled="quantity >= product.stock_quantity || adding"
                  class="w-10 h-10 rounded-full border border-gray-300 flex items-center justify-center hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                  </svg>
                </button>
              </div>
            </div>

            <!-- Add-ons Section -->
            <div v-if="availableAddons.length > 0" class="border-t pt-6">
              <h4 class="font-semibold text-gray-900 mb-3">Add-ons</h4>
              <div class="space-y-3">
                <div
                  v-for="addon in availableAddons"
                  :key="addon.id"
                  class="flex items-center justify-between p-3 border border-gray-200 rounded-lg"
                >
                  <label class="flex items-center gap-3 cursor-pointer flex-1">
                    <input
                      type="checkbox"
                      :value="addon.id"
                      v-model="selectedAddons"
                      class="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500"
                    >
                    <div>
                      <div class="font-medium text-gray-900">{{ addon.name }}</div>
                      <div class="text-sm text-gray-500">+₱{{ formatPrice(addon.price) }}</div>
                    </div>
                  </label>

                  <div class="text-right">
                    <div class="font-medium text-orange-600">+₱{{ formatPrice(addon.price) }}</div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Error State -->
          <div v-else class="flex items-center justify-center h-full">
            <div class="text-center px-4">
              <div class="text-red-500 text-4xl mb-4">❌</div>
              <p class="text-gray-900 font-medium mb-2">Product not found</p>
              <button
                @click="closeModal"
                class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition-colors"
              >
                Close
              </button>
            </div>
          </div>
        </div>

        <!-- Bottom Section - Price and Action Buttons -->
        <div v-if="product" class="border-t bg-gray-50 p-4 lg:p-6">
          <!-- Success Message -->
          <div
            v-if="showSuccessMessage"
            class="mb-4 p-3 bg-green-100 border border-green-200 rounded-lg text-green-800 text-sm font-medium"
          >
            ✅ Successfully added to cart!
          </div>

          <!-- Price Breakdown -->
          <div class="mb-4 space-y-1">
            <div class="flex justify-between text-sm">
              <span>Base Price ({{ quantity }}x)</span>
              <span>₱{{ formatPrice(Number(product.price) * quantity) }}</span>
            </div>

            <div v-if="selectedAddons.length > 0" class="flex justify-between text-sm">
              <span>Add-ons ({{ selectedAddons.length }}x{{ quantity }})</span>
              <span>₱{{ formatPrice(totalAddonsPrice * quantity) }}</span>
            </div>

            <div class="flex justify-between font-bold text-lg border-t pt-2">
              <span>Total</span>
              <span class="text-orange-600">₱{{ formatPrice(totalPrice) }}</span>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex gap-3">
            <!-- Add to Cart Button -->
            <button
              @click="addToCart"
              :disabled="!canAddToCart || adding"
              class="flex-1 py-3 bg-orange-500 hover:bg-orange-600 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-medium rounded-lg transition-colors flex items-center justify-center gap-2"
            >
              <svg v-if="adding" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.5 6M7 13l1.5 6m6.5-6l-1.5-6m-3 6l-1.5-6m-3 6l-1.5-6" />
              </svg>

              {{ adding ? 'Adding...' : `Add to Cart` }}
            </button>

            <!-- Proceed to Checkout Button -->
            <button
              @click="proceedToCheckout"
              :disabled="!canAddToCart || adding"
              class="flex-1 py-3 bg-green-600 hover:bg-green-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-medium rounded-lg transition-colors flex items-center justify-center gap-2"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>

              Proceed to Checkout
            </button>
          </div>

          <p v-if="!canAddToCart && product" class="text-red-500 text-sm text-center mt-2">
            {{ product.stock_quantity === 0 ? 'Out of stock' : 'Select quantity' }}
          </p>
        </div>
      </div>

      <!-- Bottom Sheet Handle (Mobile Only) -->
      <div class="lg:hidden absolute -top-2 left-1/2 transform -translate-x-1/2">
        <div class="w-12 h-1 bg-gray-300 rounded-full"></div>
      </div>
    </div>

    <!-- Image Preview Modal -->
    <div
      v-if="showImagePreview"
      class="fixed inset-0 z-60 flex items-center justify-center bg-black/80"
      @click="showImagePreview = false"
    >
      <div class="relative max-w-lg max-h-[80vh] mx-4">
        <img
          v-if="product?.image_url"
          :src="getImageUrl(product.image_url)"
          :alt="product.name"
          class="w-full h-full object-contain rounded-lg"
        />
        <button
          @click="showImagePreview = false"
          class="absolute top-4 right-4 p-2 bg-black/50 text-white rounded-full hover:bg-black/70 transition-colors"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'

interface Addon {
  id: number
  name: string
  price: number
}

interface Product {
  id: number
  name: string
  price: string | number
  image_url?: string
  stock_quantity: number
  addons?: Addon[]
}

interface Props {
  product: Product | null
  isOpen: boolean
  cartCount?: number
}

interface Emits {
  (e: 'close'): void
  (e: 'added-to-cart', product: Product, quantity: number, addons: number[]): void
  (e: 'proceed-to-checkout'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

// Reactive data
const loading = ref(false)
const adding = ref(false)
const quantity = ref(1)
const selectedAddons = ref<number[]>([])
const showSuccessMessage = ref(false)
const showImagePreview = ref(false)

// Computed properties
const availableAddons = computed(() => {
  return props.product?.addons || []
})

const totalAddonsPrice = computed(() => {
  return selectedAddons.value.reduce((total, addonId) => {
    const addon = availableAddons.value.find(a => a.id === addonId)
    return total + (addon?.price || 0)
  }, 0)
})

const totalPrice = computed(() => {
  if (!props.product) return 0
  const basePrice = Number(props.product.price) * quantity.value
  const addonsPrice = totalAddonsPrice.value * quantity.value
  return basePrice + addonsPrice
})

const canAddToCart = computed(() => {
  return props.product &&
         props.product.stock_quantity > 0 &&
         quantity.value > 0 &&
         quantity.value <= props.product.stock_quantity
})

// Methods
const formatPrice = (price: string | number): string => {
  const numPrice = typeof price === 'string' ? parseFloat(price) : price
  return numPrice.toFixed(2)
}

const getImageUrl = (imageUrl: string): string => {
  if (!imageUrl) return ''

  if (imageUrl.startsWith('http://') || imageUrl.startsWith('https://')) {
    return imageUrl
  }

  if (imageUrl.startsWith('storage/')) {
    return `/${imageUrl}`
  }

  return `/storage/${imageUrl}`
}

const handleImageError = (event: Event) => {
  const target = event.target as HTMLImageElement
  target.style.display = 'none'
}

const handleBackdropClick = () => {
  closeModal()
}

const closeModal = () => {
  emit('close')
 resetForm()
}

const resetForm = () => {
  quantity.value = 1
  selectedAddons.value = []
  showSuccessMessage.value = false
  showImagePreview.value = false
}

const incrementQuantity = () => {
  if (props.product && quantity.value < props.product.stock_quantity) {
    quantity.value++
  }
}

const decrementQuantity = () => {
  if (quantity.value > 1) {
    quantity.value--
  }
}

const addToCart = async () => {
  if (!canAddToCart.value || adding.value) return

  adding.value = true

  try {
    // Emit event with all order details
    emit('added-to-cart', props.product!, quantity.value, selectedAddons.value)

    // Show success message
    showSuccessMessage.value = true

    // Reset form after a delay
    setTimeout(() => {
      resetForm()
    }, 2000)
  } finally {
    adding.value = false
  }
}

const proceedToCheckout = () => {
  // First add to cart if not already added
  if (!showSuccessMessage.value) {
    addToCart()
  }

  // Emit proceed to checkout event
  emit('proceed-to-checkout')
}

// Watch for product changes
watch(() => props.product, (newProduct) => {
  if (newProduct) {
    resetForm()
  }
})
</script>

<style scoped>
/* Custom scrollbar */
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}

/* Loading animation */
@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

.animate-spin {
  animation: spin 1s linear infinite;
}
</style>
