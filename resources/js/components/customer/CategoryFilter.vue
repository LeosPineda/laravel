<template>
  <div class="flex flex-wrap gap-2">
    <!-- All Categories Button -->
    <button
      @click="selectCategory(null)"
      class="px-4 py-2 rounded-full text-sm font-medium transition-colors"
      :class="selectedCategory === null
        ? 'bg-orange-500 text-white'
        : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
    >
      All
    </button>

    <!-- Individual Category Buttons -->
    <button
      v-for="category in categories"
      :key="category"
      @click="selectCategory(category)"
      class="px-4 py-2 rounded-full text-sm font-medium transition-colors"
      :class="selectedCategory === category
        ? 'bg-orange-500 text-white'
        : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
    >
      {{ category }}
    </button>
  </div>
</template>

<script setup>
defineProps({
  categories: {
    type: Array,
    required: true
  },
  selectedCategory: {
    type: String,
    default: null
  }
})

defineEmits(['select-category'])

const selectCategory = (category) => {
  emit('select-category', category)
}
</script>

<style scoped>
/* No additional styles needed - using Tailwind CSS classes */
</style>
