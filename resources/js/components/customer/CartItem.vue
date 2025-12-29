<template>
  <div class="flex items-center gap-4 p-4 border-b border-gray-100 last:border-b-0">
    <!-- Product Image -->
    <div class="w-16 h-16 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
      <img
        v-if="cartItem.product.image_url"
        :src="cartItem.product.image_url"
        :alt="cartItem.product.name"
        class="w-full h-full object-cover"
      />
      <div v-else class="w-full h-full flex items-center justify-center">
        <span class="text-lg">🍽️</span>
      </div>
    </div>

    <!-- Product Details -->
    <div class="flex-1 min-w-0">
      <h4 class="font-semibold text-gray-900 truncate">{{ cartItem.product.name }}</h4>
      <p class="text-sm text-gray-600">{{ cartItem.vendor.brand_name }}</p>
      <p class="text-orange-600 font-semibold">₱{{ cartItem.product.price }}</p>
    </div>

    <!-- Quantity Controls -->
    <div class="flex items-center gap-2">
      <button
        @click="decreaseQuantity"
        class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center transition-colors"
        :disabled="cartItem.quantity <= 1"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
        </svg>
      </button>

      <span class="w-8 text-center font-medium">{{ cartItem.quantity }}</span>

      <button
        @click="increaseQuantity"
        class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center transition-colors"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
        </svg>
      </button>
    </div>

    <!-- Item Total -->
    <div class="text-right min-w-0">
      <p class="font-semibold text-gray-900">₱{{ (cartItem.quantity * cartItem.product.price).toFixed(2) }}</p>
    </div>

    <!-- Remove Button -->
    <button
      @click="removeItem"
      class="p-2 text-gray-400 hover:text-red-500 transition-colors"
      title="Remove item"
    >
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
      </svg>
    </button>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  cartItem: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['update-quantity', 'remove-item'])

// Local state for optimistic updates
const localQuantity = ref(props.cartItem.quantity)
const isUpdating = ref(false)

const increaseQuantity = async () => {
  if (isUpdating.value) return

  const newQuantity = localQuantity.value + 1
  await updateQuantity(newQuantity)
}

const decreaseQuantity = async () => {
  if (isUpdating.value || localQuantity.value <= 1) return

  const newQuantity = localQuantity.value - 1
  await updateQuantity(newQuantity)
}

const updateQuantity = async (newQuantity) => {
  isUpdating.value = true

  // Optimistic update
  localQuantity.value = newQuantity

  try {
    const result = await emit('update-quantity', props.cartItem.id, newQuantity)
    if (!result.success) {
      // Revert on error
      localQuantity.value = props.cartItem.quantity
      console.error('Failed to update quantity:', result.message)
    }
  } catch (error) {
    // Revert on error
    localQuantity.value = props.cartItem.quantity
    console.error('Error updating quantity:', error)
  } finally {
    isUpdating.value = false
  }
}

const removeItem = async () => {
  if (isUpdating.value) return

  isUpdating.value = true

  try {
    const result = await emit('remove-item', props.cartItem.id)
    if (!result.success) {
      console.error('Failed to remove item:', result.message)
    }
  } catch (error) {
    console.error('Error removing item:', error)
  } finally {
    isUpdating.value = false
  }
}
</script>

<style scoped>
/* No additional styles needed - using Tailwind CSS classes */
</style>
