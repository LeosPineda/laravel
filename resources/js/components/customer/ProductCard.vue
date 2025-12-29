<template>
  <div class="bg-white rounded-xl border border-gray-200 hover:border-orange-200 hover:shadow-lg transition-all duration-300 overflow-hidden">
    <!-- Product Image -->
    <div class="aspect-square bg-gray-100 relative overflow-hidden">
      <img
        v-if="product.image_url"
        :src="product.image_url"
        :alt="product.name"
        class="w-full h-full object-cover"
      />
      <div v-else class="w-full h-full flex items-center justify-center bg-gray-100">
        <span class="text-4xl">🍽️</span>
      </div>

      <!-- Stock Status Badge -->
      <div
        v-if="!product.is_active"
        class="absolute top-3 left-3 px-2 py-1 bg-red-500 text-white text-xs font-medium rounded-full"
      >
        Unavailable
      </div>
      <div
        v-else-if="product.stock_quantity === 0"
        class="absolute top-3 left-3 px-2 py-1 bg-orange-500 text-white text-xs font-medium rounded-full"
      >
        Out of Stock
      </div>
      <div
        v-else-if="product.stock_quantity < 10"
        class="absolute top-3 left-3 px-2 py-1 bg-yellow-500 text-white text-xs font-medium rounded-full"
      >
        Low Stock
      </div>

      <!-- Category Badge -->
      <div
        v-if="product.category"
        class="absolute top-3 right-3 px-2 py-1 bg-blue-500 text-white text-xs font-medium rounded-full"
      >
        {{ product.category }}
      </div>
    </div>

    <!-- Product Info -->
    <div class="p-4">
      <!-- Name & Price -->
      <div class="mb-2">
        <h3 class="font-bold text-lg text-gray-900 truncate">{{ product.name }}</h3>
        <p class="text-orange-600 font-semibold text-xl">₱{{ product.price }}</p>
      </div>

      <!-- Stock Info -->
      <div class="text-sm text-gray-500 mb-3">
        <span v-if="product.is_active">
          {{ product.stock_quantity > 0 ? `${product.stock_quantity} available` : 'Out of stock' }}
        </span>
        <span v-else class="text-red-500">Currently unavailable</span>
      </div>

      <!-- Add to Cart Button -->
      <button
        class="w-full py-2 px-4 bg-orange-500 text-white font-medium rounded-lg hover:bg-orange-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
        :disabled="!product.is_active || product.stock_quantity === 0"
        @click="$emit('add-to-cart', product)"
      >
        {{ !product.is_active ? 'Unavailable' : (product.stock_quantity === 0 ? 'Out of Stock' : 'Add to Cart') }}
      </button>
    </div>
  </div>
</template>

<script setup>
defineProps({
  product: {
    type: Object,
    required: true
  }
})

defineEmits(['add-to-cart'])
</script>

<style scoped>
/* No additional styles needed - using Tailwind CSS classes */
</style>
