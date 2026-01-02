<template>
  <!-- Product Detail Modal -->
  <div
    v-if="isOpen && !showImagePreview"
    class="fixed inset-0 z-[60] flex items-center justify-center"
    @click="handleBackdropClick"
  >
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>

    <!-- Modal Content -->
    <div
      ref="modalContent"
      data-modal-content
      class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm lg:max-w-md transform transition-all duration-300 ease-out flex flex-col"
      :class="{
        'translate-y-0 opacity-100': isOpen,
        'translate-y-4 opacity-0': !isOpen
      }"
      :style="{ height: isMobile ? 'auto max-h-[80vh]' : 'auto max-h-[80vh]' }"
      @click.stop
    >
      <!-- Header -->
      <div class="flex items-center justify-between p-3 lg:p-4 border-b border-gray-200 flex-shrink-0">
        <div class="flex items-center gap-2">
          <!-- Close Button -->
          <button
            @click="closeModal"
            class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full transition-colors"
            aria-label="Close modal"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>

          <!-- Product Title -->
          <div>
            <h2 class="text-base lg:text-lg font-bold text-gray-900">
              {{ product?.name || 'Product Details' }}
            </h2>
            <p class="text-xs text-gray-500">Customize your order</p>
          </div>
        </div>
      </div>

      <!-- Content -->
      <div class="flex-1 flex flex-col">
        <div class="flex-1 p-3 lg:p-4 space-y-4">
          <!-- Loading State -->
          <div v-if="loading" class="flex items-center justify-center h-full">
            <div class="text-center">
              <div class="animate-spin rounded-full h-10 w-10 lg:h-12 lg:w-12 border-b-2 border-orange-500 mx-auto mb-4"></div>
              <p class="text-gray-600">Loading product details...</p>
            </div>
          </div>

          <!-- Product Content -->
          <div v-else-if="product" class="space-y-6">
            <!-- Product Image + Stock Info -->
            <div class="rounded-xl overflow-hidden cursor-pointer relative bg-gray-50" @click="showImagePreview = true" style="height: 200px;">
              <img
                v-if="product.image_url"
                :src="getImageUrl(product.image_url)"
                :alt="product.name"
                class="w-full h-full object-cover"
                @error="handleImageError"
              />
              <div v-else class="w-full h-full flex items-center justify-center bg-gray-100 text-gray-400 text-2xl">
                🍽️
              </div>

              <!-- Stock Badge -->
              <div class="absolute top-3 right-3">
                <span
                  v-if="product.stock_quantity > 5"
                  class="px-2 py-1 rounded-full bg-green-100 text-green-800 text-xs font-medium"
                >
                  ✓ In Stock ({{ product.stock_quantity }})
                </span>
                <span
                  v-else-if="product.stock_quantity > 0"
                  class="px-2 py-1 rounded-full bg-yellow-100 text-yellow-800 text-xs font-medium"
                >
                  ⚠ Only {{ product.stock_quantity }} left
                </span>
                <span
                  v-else
                  class="px-2 py-1 rounded-full bg-red-100 text-red-800 text-xs font-medium"
                >
                  ❌ Out of Stock
                </span>
              </div>
            </div>

            <!-- Product Name + Price -->
            <div class="flex justify-between items-center">
              <h3 class="text-lg lg:text-xl font-bold text-gray-900">{{ product.name }}</h3>
              <div class="text-xl lg:text-2xl font-bold text-orange-600">
                ₱{{ formatPrice(product.price) }}
              </div>
            </div>

            <!-- Quantity Selector -->
            <div class="flex items-center justify-between bg-gray-50 p-4 rounded-xl">
              <span class="font-semibold text-gray-700">Quantity</span>
              <div class="flex items-center gap-3">
                <button
                  @click="decrementQuantity"
                  :disabled="quantity <= 1 || adding"
                  class="w-10 h-10 rounded-full border-2 border-gray-300 flex items-center justify-center hover:bg-white hover:border-orange-400 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                  <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                  </svg>
                </button>

                <input
                  v-model="quantityInput"
                  @input="handleQuantityInput"
                  @blur="validateQuantity"
                  @keydown.enter="validateQuantity"
                  type="number"
                  inputmode="numeric"
                  pattern="[0-9]*"
                  :min="1"
                  :max="product?.stock_quantity || 999"
                  :disabled="adding"
                  class="w-20 h-10 text-center text-xl font-bold text-gray-900 border-2 border-gray-300 rounded-lg focus:border-orange-400 focus:ring-0 disabled:opacity-50 disabled:cursor-not-allowed transition-colors spin-button-none"
                />

                <button
                  @click="incrementQuantity"
                  :disabled="quantity >= product.stock_quantity || adding"
                  class="w-10 h-10 rounded-full border-2 border-gray-300 flex items-center justify-center hover:bg-white hover:border-orange-400 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                  <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                  </svg>
                </button>
              </div>
            </div>

            <!-- Add-ons -->
            <div v-if="availableAddons.length > 0" class="space-y-3">
              <h4 class="font-bold text-gray-900 text-lg">Add-ons</h4>
              <div class="space-y-2">
                <div
                  v-for="addon in availableAddons"
                  :key="addon.id"
                  class="flex justify-between items-center p-3 border rounded-xl transition-all duration-200"
                  :class="selectedAddons.includes(addon.id)
                    ? 'border-orange-400 bg-orange-50'
                    : 'border-gray-200 hover:border-orange-200 hover:bg-orange-25'"
                >
                  <label class="flex items-center gap-2 flex-1 cursor-pointer">
                    <input
                      type="checkbox"
                      :value="addon.id"
                      v-model="selectedAddons"
                      class="w-5 h-5 text-orange-600 border-2 border-gray-300 rounded focus:ring-orange-500 focus:ring-2"
                    />
                    <span>{{ addon.name }}</span>
                  </label>
                  <span class="font-bold text-orange-600">+₱{{ formatPrice(addon.price) }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Error -->
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

        <!-- Bottom Section -->
        <div v-if="product" class="border-t bg-white p-3 lg:p-4 flex-shrink-0 space-y-2 rounded-b-3xl">
          <div class="flex justify-between text-sm">
            <span>Base Price ({{ quantity }}x)</span>
            <span class="font-medium">₱{{ formatPrice(Number(product.price) * quantity) }}</span>
          </div>
          <div v-if="selectedAddons.length > 0" class="flex justify-between text-sm">
            <span>Add-ons ({{ selectedAddons.length }}x{{ quantity }})</span>
            <span class="font-medium">₱{{ formatPrice(totalAddonsPrice * quantity) }}</span>
          </div>
          <div class="flex justify-between font-bold text-lg border-t pt-2">
            <span>Total</span>
            <span class="text-orange-600">₱{{ formatPrice(totalPrice) }}</span>
          </div>

          <div class="flex justify-end mt-4">
            <button
              @click="addToCart"
              :disabled="!canAddToCart || adding"
              class="px-6 py-3 bg-orange-500 hover:bg-orange-600 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-medium rounded-lg transition-colors flex items-center justify-center gap-2"
            >
              <svg v-if="adding" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ adding ? 'Adding...' : 'Add to Cart' }}
            </button>
          </div>

          <p v-if="!canAddToCart && product" class="text-red-500 text-sm text-center mt-2">
            {{ product.stock_quantity === 0 ? 'Out of stock' : 'Select quantity' }}
          </p>
        </div>
      </div>
    </div>

    <!-- Image Preview Modal -->
    <div
      v-if="showImagePreview"
      class="fixed inset-0 z-[80] flex items-center justify-center bg-black/90"
      @click="closeImagePreview"
    >
      <div class="relative max-w-lg max-h-[80vh] mx-4" @click.stop>
        <img
          v-if="product?.image_url"
          :src="getImageUrl(product.image_url)"
          :alt="product.name"
          class="w-full h-full object-contain rounded-lg"
        />
        <button
          @click="closeImagePreview"
          class="absolute top-4 right-4 p-3 bg-black/70 text-white rounded-full hover:bg-black/90 transition-colors"
          aria-label="Close image preview"
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
import { ref, computed, watch, onMounted } from 'vue'

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
  category?: string
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
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

// Reactive data
const loading = ref(false)
const adding = ref(false)
const quantity = ref(1)
const quantityInput = ref('1')
const selectedAddons = ref<number[]>([])
const showImagePreview = ref(false)
const isMobile = ref(false)

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
  quantityInput.value = '1'
  selectedAddons.value = []
  showImagePreview.value = false
}

const closeImagePreview = () => {
  showImagePreview.value = false
}

const incrementQuantity = () => {
  if (props.product && quantity.value < props.product.stock_quantity) {
    quantity.value++
    quantityInput.value = quantity.value.toString()
  }
}

const decrementQuantity = () => {
  if (quantity.value > 1) {
    quantity.value--
    quantityInput.value = quantity.value.toString()
  }
}

const handleQuantityInput = (event: Event) => {
  const target = event.target as HTMLInputElement
  quantityInput.value = target.value

  // Parse the input value
  const newValue = parseInt(target.value)
  if (!isNaN(newValue) && newValue > 0) {
    quantity.value = newValue
  }
}

const validateQuantity = () => {
  let newQuantity = parseInt(quantityInput.value)

  // Validate and clamp the quantity
  if (isNaN(newQuantity) || newQuantity < 1) {
    newQuantity = 1
  }

  if (props.product && newQuantity > props.product.stock_quantity) {
    newQuantity = props.product.stock_quantity
  }

  quantity.value = newQuantity
  quantityInput.value = newQuantity.toString()
}

const addToCart = async () => {
  if (!canAddToCart.value || adding.value) return

  adding.value = true

  try {
    // Emit event with all order details
    emit('added-to-cart', props.product!, quantity.value, selectedAddons.value)

    // Reset form after a delay
    setTimeout(() => {
      resetForm()
    }, 2000)
  } finally {
    adding.value = false
  }
}



// Watch for product changes
watch(() => props.product, (newProduct) => {
  if (newProduct) {
    resetForm()
  }
})

// Watch for quantity changes to sync with input
watch(quantity, (newQuantity) => {
  quantityInput.value = newQuantity.toString()
})

// Initialize mobile detection
onMounted(() => {
  isMobile.value = window.innerWidth < 1024

  // Update mobile detection on resize
  const handleResize = () => {
    isMobile.value = window.innerWidth < 1024
  }

  window.addEventListener('resize', handleResize)

  // Cleanup on unmount
  return () => {
    window.removeEventListener('resize', handleResize)
  }
})
</script>

<style scoped>
/* Remove browser spinner controls */
.spin-button-none {
  -moz-appearance: textfield;
}

.spin-button-none::-webkit-outer-spin-button,
.spin-button-none::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

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

/* Modal positioning fixes */
.fixed.inset-0.z-50 {
  position: fixed;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  z-index: 50;
}

.z-\[70\] {
  z-index: 70;
}

/* Bottom sheet handle */
.lg\:hidden.absolute.-top-2 {
  display: none;
}

@media (max-width: 1023px) {
  .lg\:hidden.absolute.-top-2 {
    display: block;
  }
}

/* Responsive improvements */
@media (max-width: 640px) {
  .flex.gap-4 {
    gap: 1rem;
  }

  .p-4 {
    padding: 1rem;
  }

  .space-y-6 > * + * {
    margin-top: 1.5rem;
  }
}

/* Button hover effects */
.hover\:bg-white:hover {
  background-color: white;
}

.hover\:border-orange-400:hover {
  border-color: #fb923c;
}

/* Transition improvements */
* {
  transition-property: color, background-color, border-color, opacity, box-shadow, transform;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}

/* Additional hover states for add-ons */
.hover\:bg-orange-25:hover {
  background-color: #fff7ed;
}
</style>
