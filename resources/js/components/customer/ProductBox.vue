<template>
  <div
    class="bg-white border border-gray-200 rounded-lg hover:shadow-md hover:border-orange-200 transition-all duration-200 cursor-pointer group overflow-hidden"
    @click="handleViewDetails"
  >
    <!-- Product Image -->
    <div class="relative h-32 sm:h-36 bg-gradient-to-br from-orange-50 to-red-50">
      <img
        v-if="product.image_url"
        :src="getImageUrl(product.image_url)"
        :alt="product.name"
        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200"
        @error="handleImageError"
      />
      <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
        <span class="text-xl">🍽️</span>
      </div>

      <!-- Stock Status Badge -->
      <div class="absolute top-1 left-1">
        <span
          v-if="!product.is_in_stock"
          class="px-2 py-0.5 bg-red-500 text-white text-xs rounded-full font-medium"
        >
          Out
        </span>
        <span
          v-else-if="product.is_featured"
          class="px-2 py-0.5 bg-orange-500 text-white text-xs rounded-full font-medium"
        >
          Featured
        </span>
      </div>
    </div>

    <!-- Product Info -->
    <div class="p-3">
      <!-- Product Name -->
      <h3 class="font-medium text-gray-900 text-sm mb-1 line-clamp-2 group-hover:text-orange-600 transition-colors">
        {{ product.name }}
      </h3>

      <!-- Price and Action Row -->
      <div class="flex items-center justify-between">
        <!-- Price -->
        <span class="font-bold text-orange-600 text-sm">
          ₱{{ formatPrice(product.price) }}
        </span>

        <!-- Order Button -->
        <button
          v-if="product.is_in_stock"
          @click.stop="handleOrderNow"
          :disabled="ordering"
          class="px-3 py-1.5 bg-orange-500 hover:bg-orange-600 disabled:bg-orange-300 text-white rounded-md transition-colors text-xs font-medium"
        >
          {{ ordering ? '...' : 'Order Now' }}
        </button>

        <!-- Out of Stock -->
        <span
          v-else
          class="px-3 py-1.5 bg-gray-300 text-gray-500 rounded-md text-xs font-medium"
        >
          Out
        </span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'

interface Product {
  id: number
  name: string
  price: string | number
  image_url?: string
  is_in_stock: boolean
  is_featured?: boolean
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

const handleOrderNow = async () => {
  if (ordering.value || !props.product.is_in_stock) return

  ordering.value = true

  try {
    emit('order-now', props.product)
  } finally {
    setTimeout(() => {
      ordering.value = false
    }, 300)
  }
}

const handleViewDetails = () => {
  emit('view-details', props.product)
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

/* Hover effects */
.group:hover .group-hover\:scale-105 {
  transform: scale(1.05);
}

.group:hover .group-hover\:text-orange-600 {
  color: #ea580c;
}
</style>
