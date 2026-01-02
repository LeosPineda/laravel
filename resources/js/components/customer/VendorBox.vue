<template>
  <div
    @click="$emit('browse-products', vendor.id)"
    class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 cursor-pointer border border-gray-200 overflow-hidden group"
  >
    <!-- Vendor Image/Logo -->
    <div class="h-32 bg-gradient-to-br from-orange-100 to-red-100 flex items-center justify-center relative overflow-hidden">
      <img
        v-if="vendor.brand_logo"
        :src="getImageUrl(vendor.brand_logo)"
        :alt="vendor.brand_name"
        class="w-full h-full object-cover"
        @error="handleImageError"
      />
      <div v-else class="text-4xl font-bold text-gray-600">
        {{ getInitials(vendor.brand_name) }}
      </div>

      <!-- Status Badge -->
      <div class="absolute top-2 right-2">
        <span
          v-if="vendor.is_active"
          class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800"
        >
          <div class="w-2 h-2 bg-green-400 rounded-full mr-1 animate-pulse"></div>
          Active
        </span>
        <span
          v-else
          class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800"
        >
          <div class="w-2 h-2 bg-gray-400 rounded-full mr-1"></div>
          Inactive
        </span>
      </div>
    </div>

    <!-- Vendor Info -->
    <div class="p-4">
      <div class="flex items-start justify-between">
        <div class="flex-1 min-w-0">
          <h3 class="text-lg font-semibold text-gray-900 truncate group-hover:text-orange-600 transition-colors">
            {{ vendor.brand_name }}
          </h3>
          <div class="mt-2 text-sm text-gray-500">
            {{ vendor.products_count || 0 }} products available
          </div>
        </div>
      </div>

      <!-- Browse Products Button (Primary Action) -->
      <div class="mt-4">
        <button
          :disabled="!vendor.is_active"
          class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md transition-colors"
          :class="vendor.is_active
            ? 'text-white bg-orange-500 hover:bg-orange-600 focus:ring-2 focus:ring-orange-500 focus:ring-offset-2'
            : 'text-gray-400 bg-gray-100 cursor-not-allowed'"
          @click.stop="$emit('browse-products', vendor.id)"
        >
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          {{ vendor.is_active ? 'Browse Products' : 'Closed' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  vendor: {
    type: Object,
    required: true
  }
})

defineEmits(['browse-products'])

// Helper function to get initials from vendor name
const getInitials = (name) => {
  if (!name) return '?'
  return name
    .split(' ')
    .map(word => word.charAt(0).toUpperCase())
    .join('')
    .substring(0, 2)
}

// ✅ FIXED: Convert storage path to proper URL
const getImageUrl = (brandLogo) => {
  if (!brandLogo) return ''

  // If it's already a full URL, return as is
  if (brandLogo.startsWith('http://') || brandLogo.startsWith('https://')) {
    return brandLogo
  }

  // If it's a storage path, prepend storage URL
  if (brandLogo.startsWith('storage/')) {
    return `/${brandLogo}`
  }

  // For storage paths without 'storage/', prepend /storage/
  return `/storage/${brandLogo}`
}

// Handle image loading errors
const handleImageError = (event) => {
  console.warn('Image failed to load:', event.target.src)
  event.target.style.display = 'none'
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
.group:hover .group-hover\:text-orange-600 {
  color: #ea580c;
}

/* Responsive design adjustments */
@media (max-width: 640px) {
  .group {
    margin-bottom: 1rem;
  }
}

/* Smooth transitions */
* {
  transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}
</style>
