<template>
  <!-- Product Detail Modal -->
  <div
    v-if="isOpen && !showImagePreview"
    class="fixed inset-0 z-[60] flex items-end sm:items-center justify-center"
    @click="handleBackdropClick"
  >
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>

    <!-- Modal Content - Bottom sheet on mobile, card on desktop -->
    <div
      ref="modalContent"
      class="relative bg-white w-full sm:max-w-lg sm:mx-4 sm:rounded-2xl rounded-t-3xl shadow-2xl transform transition-all duration-300 ease-out max-h-[95vh] sm:max-h-[90vh] flex flex-col overflow-hidden"
      :class="{
        'translate-y-0 opacity-100': isOpen,
        'translate-y-4 opacity-0': !isOpen
      }"
      @click.stop
    >
      <!-- Drag Handle (mobile only) -->
      <div class="sm:hidden flex justify-center pt-3 pb-1 bg-white">
        <div class="w-10 h-1 bg-gray-300 rounded-full"></div>
      </div>

      <!-- Hero Image Section -->
      <div class="relative flex-shrink-0">
        <!-- Product Image -->
        <div
          class="relative w-full h-48 sm:h-56 bg-gradient-to-br from-orange-50 to-red-50 cursor-pointer overflow-hidden"
          @click="product?.image_url && (showImagePreview = true)"
        >
          <img
            v-if="product?.image_url"
            :src="getImageUrl(product.image_url)"
            :alt="product?.name"
            class="w-full h-full object-cover transition-transform duration-300 hover:scale-105"
            @error="handleImageError"
          />
          <div v-else class="w-full h-full flex items-center justify-center">
            <span class="text-6xl">🍽️</span>
          </div>

          <!-- Gradient Overlay -->
          <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent"></div>

          <!-- Close Button -->
          <button
            @click.stop="closeModal"
            class="absolute top-3 right-3 p-2 bg-white/90 hover:bg-white text-gray-700 rounded-full shadow-lg transition-all"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>

          <!-- Stock Badge -->
          <div class="absolute top-3 left-3">
            <span
              v-if="product && product.stock_quantity > 5"
              class="px-3 py-1.5 rounded-full bg-green-500 text-white text-xs font-semibold shadow-lg"
            >
              ✓ In Stock
            </span>
            <span
              v-else-if="product && product.stock_quantity > 0"
              class="px-3 py-1.5 rounded-full bg-amber-500 text-white text-xs font-semibold shadow-lg"
            >
              Only {{ product.stock_quantity }} left
            </span>
            <span
              v-else
              class="px-3 py-1.5 rounded-full bg-red-500 text-white text-xs font-semibold shadow-lg"
            >
              Out of Stock
            </span>
          </div>

          <!-- Tap to zoom hint -->
          <div v-if="product?.image_url" class="absolute bottom-3 right-3 px-2 py-1 bg-black/50 text-white text-xs rounded-full">
            Tap to zoom
          </div>
        </div>
      </div>

      <!-- Content Section -->
      <div class="flex-1 overflow-y-auto">
        <!-- Product Info -->
        <div class="p-4 sm:p-5">
          <!-- Name & Price Header -->
          <div class="flex items-start justify-between gap-4 mb-4">
            <div class="flex-1">
              <h2 class="text-xl sm:text-2xl font-bold text-gray-900 leading-tight">
                {{ product?.name }}
              </h2>
              <p v-if="product?.category" class="text-sm text-gray-500 mt-1">
                {{ product.category }}
              </p>
            </div>
            <div class="flex-shrink-0 text-right">
              <div class="text-2xl sm:text-3xl font-bold text-orange-600">
                ₱{{ formatPrice(product?.price) }}
              </div>
              <p class="text-xs text-gray-500">per item</p>
            </div>
          </div>

          <!-- Quantity Selector Card -->
          <div class="bg-gray-50 rounded-2xl p-4 mb-5">
            <div class="flex items-center justify-between">
              <div>
                <span class="font-semibold text-gray-900">Quantity</span>
                <p class="text-xs text-gray-500 mt-0.5">Max: {{ product?.stock_quantity }}</p>
              </div>
              <div class="flex items-center gap-2">
                <button
                  @click="decrementQuantity"
                  :disabled="quantity <= 1 || adding"
                  class="w-11 h-11 rounded-full bg-white border-2 border-gray-200 flex items-center justify-center hover:border-orange-400 hover:bg-orange-50 disabled:opacity-40 disabled:cursor-not-allowed transition-all shadow-sm"
                >
                  <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                  </svg>
                </button>

                <input
                  v-model="quantityInput"
                  @input="handleQuantityInput"
                  @blur="validateQuantity"
                  type="number"
                  inputmode="numeric"
                  :min="1"
                  :max="product?.stock_quantity || 999"
                  :disabled="adding"
                  class="w-16 h-11 text-center text-xl font-bold text-gray-900 bg-white border-2 border-gray-200 rounded-xl focus:border-orange-400 focus:ring-0 disabled:opacity-40 transition-colors spin-button-none"
                />

                <button
                  @click="incrementQuantity"
                  :disabled="product && quantity >= product.stock_quantity || adding"
                  class="w-11 h-11 rounded-full bg-white border-2 border-gray-200 flex items-center justify-center hover:border-orange-400 hover:bg-orange-50 disabled:opacity-40 disabled:cursor-not-allowed transition-all shadow-sm"
                >
                  <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6" />
                  </svg>
                </button>
              </div>
            </div>
          </div>

          <!-- Add-ons Section -->
          <div v-if="hasProductAddons" class="mb-4">
            <div class="flex items-center justify-between mb-3">
              <h3 class="font-bold text-gray-900 text-lg">Add-ons</h3>
              <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-full">
                {{ selectedAddons.length }} selected
              </span>
            </div>
            <div class="space-y-2">
              <label
                v-for="addon in product?.addons"
                :key="addon.id"
                class="flex items-center justify-between p-3.5 border-2 rounded-xl cursor-pointer transition-all duration-200"
                :class="selectedAddons.includes(addon.id)
                  ? 'border-orange-400 bg-orange-50 shadow-sm'
                  : 'border-gray-200 hover:border-orange-200 hover:bg-orange-50/30'"
              >
                <div class="flex items-center gap-3">
                  <div
                    class="w-6 h-6 rounded-md border-2 flex items-center justify-center transition-colors"
                    :class="selectedAddons.includes(addon.id)
                      ? 'bg-orange-500 border-orange-500'
                      : 'bg-white border-gray-300'"
                  >
                    <svg
                      v-if="selectedAddons.includes(addon.id)"
                      class="w-4 h-4 text-white"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                    </svg>
                  </div>
                  <span class="font-medium text-gray-900">{{ addon.name }}</span>
                </div>
                <span class="font-bold text-orange-600">+₱{{ formatPrice(addon.price) }}</span>
                <input
                  type="checkbox"
                  :value="addon.id"
                  v-model="selectedAddons"
                  class="sr-only"
                />
              </label>
            </div>
          </div>

          <!-- No Addons -->
          <div v-else class="bg-gray-50 rounded-xl p-4 text-center mb-4">
            <span class="text-gray-400 text-2xl">🍴</span>
            <p class="text-gray-500 text-sm mt-1">No add-ons available</p>
          </div>
        </div>
      </div>

      <!-- Bottom Action Section -->
      <div class="flex-shrink-0 border-t border-gray-100 bg-white p-4 sm:p-5">
        <!-- Price Breakdown -->
        <div class="space-y-1.5 mb-4">
          <div class="flex justify-between text-sm text-gray-600">
            <span>{{ quantity }}× {{ product?.name }}</span>
            <span>₱{{ formatPrice(Number(product?.price || 0) * quantity) }}</span>
          </div>
          <div v-if="selectedAddons.length > 0" class="flex justify-between text-sm text-gray-600">
            <span>{{ selectedAddons.length }} add-on(s) × {{ quantity }}</span>
            <span>₱{{ formatPrice(totalAddonsPrice * quantity) }}</span>
          </div>
          <div class="flex justify-between items-center pt-2 border-t border-dashed border-gray-200">
            <span class="font-bold text-gray-900 text-lg">Total</span>
            <span class="font-bold text-orange-600 text-2xl">₱{{ formatPrice(totalPrice) }}</span>
          </div>
        </div>

        <!-- Add to Cart Button -->
        <button
          @click="addToCart"
          :disabled="!canAddToCart || adding"
          class="w-full py-4 bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 disabled:from-gray-300 disabled:to-gray-400 disabled:cursor-not-allowed text-white font-bold text-lg rounded-2xl transition-all duration-200 flex items-center justify-center gap-3 shadow-lg shadow-orange-200 disabled:shadow-none active:scale-[0.98]"
        >
          <svg v-if="adding" class="animate-spin w-6 h-6" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
          </svg>
          <span v-else class="text-xl">🛒</span>
          {{ adding ? 'Adding...' : 'Add to Cart' }}
        </button>

        <!-- Stock Warning -->
        <p v-if="product?.stock_quantity === 0" class="text-red-500 text-sm text-center mt-3 font-medium">
          ❌ This item is currently out of stock
        </p>
      </div>
    </div>
  </div>

  <!-- Image Preview Modal -->
  <div
    v-if="showImagePreview"
    class="fixed inset-0 z-[80] flex items-center justify-center bg-black/95"
    @click="closeImagePreview"
  >
    <div class="relative max-w-2xl w-full mx-4" @click.stop>
      <img
        v-if="product?.image_url"
        :src="getImageUrl(product.image_url)"
        :alt="product?.name"
        class="w-full h-auto max-h-[85vh] object-contain rounded-lg"
      />
      <button
        @click="closeImagePreview"
        class="absolute -top-12 right-0 p-2 text-white/80 hover:text-white transition-colors"
      >
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
      <p class="text-center text-white/60 mt-4">{{ product?.name }}</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'

interface Addon {
  id: number
  name: string
  price: string | number
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

// Computed properties
const hasProductAddons = computed(() => {
  return props.product?.addons && props.product.addons.length > 0
})

const totalAddonsPrice = computed(() => {
  if (!props.product?.addons) return 0

  return selectedAddons.value.reduce((sum, addonId) => {
    const addon = props.product!.addons!.find(a => a.id === addonId)
    const addonPrice = addon?.price ?
      (typeof addon.price === 'string' ? parseFloat(addon.price) : addon.price) : 0
    return sum + addonPrice
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
const formatPrice = (price: string | number | undefined): string => {
  if (!price) return '0.00'
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
  const newValue = parseInt(target.value)
  if (!isNaN(newValue) && newValue > 0) {
    quantity.value = newValue
  }
}

const validateQuantity = () => {
  let newQuantity = parseInt(quantityInput.value)
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
  // Emit and let parent handle the async operation
  // Parent will close the modal when done
  emit('added-to-cart', props.product!, quantity.value, selectedAddons.value)
}

// Expose method to reset adding state from parent
const setAdding = (value: boolean) => {
  adding.value = value
}

defineExpose({ setAdding, resetForm })

// Watch for product changes
watch(() => props.product, (newProduct) => {
  if (newProduct) {
    resetForm()
  }
})

watch(quantity, (newQuantity) => {
  quantityInput.value = newQuantity.toString()
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
  width: 4px;
}
.overflow-y-auto::-webkit-scrollbar-track {
  background: transparent;
}
.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #ddd;
  border-radius: 2px;
}

/* Smooth transitions */
* {
  transition-property: color, background-color, border-color, opacity, box-shadow, transform;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}
</style>
