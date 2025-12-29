<template>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
    <VendorCard
      v-for="vendor in vendors"
      :key="vendor.id"
      :vendor="vendor"
      @select-vendor="$emit('select-vendor', $event)"
    />
  </div>

  <!-- Empty State -->
  <div v-if="!loading && vendors.length === 0" class="text-center py-16">
    <div class="text-6xl mb-4">🏪</div>
    <h3 class="text-xl font-semibold text-gray-900 mb-2">No vendors found</h3>
    <p class="text-gray-600">
      {{ searchQuery ? 'Try adjusting your search terms' : 'Check back later for new vendors' }}
    </p>
  </div>

  <!-- Loading State -->
  <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
    <div
      v-for="n in 8"
      :key="n"
      class="bg-white rounded-xl border border-gray-200 overflow-hidden animate-pulse"
    >
      <div class="aspect-video bg-gray-200"></div>
      <div class="p-4">
        <div class="h-6 bg-gray-200 rounded mb-2"></div>
        <div class="h-4 bg-gray-200 rounded mb-2 w-3/4"></div>
        <div class="h-10 bg-gray-200 rounded"></div>
      </div>
    </div>
  </div>
</template>

<script setup>
import VendorCard from './VendorCard.vue'

defineProps({
  vendors: {
    type: Array,
    required: true
  },
  loading: {
    type: Boolean,
    default: false
  },
  searchQuery: {
    type: String,
    default: ''
  }
})

defineEmits(['select-vendor'])
</script>

<style scoped>
/* No additional styles needed - using Tailwind CSS classes */
</style>
