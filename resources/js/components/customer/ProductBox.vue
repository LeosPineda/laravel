<template>
  <div
    class="group bg-white rounded-2xl border border-gray-100 hover:border-orange-200 shadow-sm hover:shadow-lg transition-all duration-300 overflow-hidden"
    @click="handleImageClick"
  >
    <!-- Product Image -->
    <div class="relative h-36 sm:h-40 bg-gradient-to-br from-orange-50 to-red-50 overflow-hidden cursor-pointer">
      <img
        v-if="product.image_url"
        :src="getImageUrl(product.image_url)"
        :alt="product.name"
        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
        @error="handleImageError"
      />
      <div v-else class="w-full h-full flex items-center justify-center">
        <span class="text-4xl opacity-40">🍽️</span>
      </div>

      <!-- Gradient Overlay -->
      <div class="absolute inset-0 bg-gradient-to-t from-black/20 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

      <!-- Badges Container -->
      <div class="absolute top-2 left-2 right-2 flex justify-between items-start">
        <!-- Left Badge (Featured/Low Stock) -->
        <div>
          <span
            v-if="isLowStock"
            class="px-2.5 py-1 bg-amber-500 text-white text-xs rounded-full font-semibold shadow-md"
          >
            ⚠ Low Stock
          </span>
          <span
            v-else-if="product.is_featured"
            class="px-2.5 py-1 bg-gradient-to-r from-orange-500 to-red-500 text-white text-xs rounded-full font-semibold shadow-md"
          >
            ⭐ Featured
          </span>
        </div>

        <!-- Right Badge (Stock Count) -->
        <span
          v-if="isInStock"
          class="px-2 py-1 bg-white/90 backdrop-blur-sm text-gray-700 text-xs rounded-full font-medium shadow-sm"
        >
          {{ product.stock_quantity }} left
        </span>
        <span
          v-else
          class="px-2 py-1 bg-red-500 text-white text-xs rounded-full font-semibold shadow-md"
        >
          Sold Out
        </span>
      </div>
    </div>

    <!-- Product Info -->
    <div class="p-3.5">
      <!-- Product Name -->
      <h3 class="font-semibold text-gray-900 text-sm leading-tight mb-2 line-clamp-2 group-hover:text-orange-600 transition-colors cursor-pointer min-h-[40px]">
        {{ product.name }}
      </h3>

      <!-- Price and Action Row -->
      <div class="flex items-center justify-between gap-2">
        <!-- Price -->
        <div class="flex-1">
          <div class="flex items-baseline gap-1">
            <span class="text-lg font-bold text-orange-600">
              ₱{{ formatPrice(product.price) }}
            </span>
          </div>
          <p v-if="product.addons && product.addons.length > 0" class="text-xs text-gray-500 mt-0.5">
            +{{ product.addons.length }} add-ons
          </p>
        </div>

        <!-- Order Button -->
        <div class="flex-shrink-0">
          <button
            v-if="isInStock"
            @click.stop="handleOrderNow"
            :disabled="ordering"
            class="px-4 py-2.5 bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 disabled:from-gray-300 disabled:to-gray-400 text-white rounded-xl transition-all duration-200 text-sm font-semibold shadow-sm hover:shadow-md active:scale-95"
          >
            <span v-if="ordering" class="flex items-center gap-1">
              <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
              </svg>
            </span>
            <span v-else class="flex items-center gap-1.5">
              <span>+</span>
              <span>Add</span>
            </span>
          </button>
          <div
            v-else
            class="px-4 py-2.5 bg-gray-100 text-gray-400 rounded-xl text-sm font-medium cursor-not-allowed"
          >
            Sold Out
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'

interface Addon {
  id: number
  name: string
  price: number
  is_active: boolean
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

interface Props {
  product: Product
}

interface Emits {
  (e: 'order-now', product: Product): void
  (e: 'view-details', product: Product): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const ordering = ref(false)

// Computed properties for stock logic
const isInStock = computed(() => {
  return props.product.stock_quantity > 0
})

const isLowStock = computed(() => {
  return props.product.stock_quantity > 0 && props.product.stock_quantity <= 5
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

// Image/card click shows product details
const handleImageClick = () => {
  emit('view-details', props.product)
}

const handleOrderNow = async () => {
  if (ordering.value || !isInStock.value) return

  ordering.value = true

  try {
    emit('order-now', props.product)
  } finally {
    setTimeout(() => {
      ordering.value = false
    }, 300)
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

/* Smooth transitions */
* {
  transition-property: color, background-color, border-color, opacity, box-shadow, transform;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}
</style>
