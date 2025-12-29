<template>
  <div
    class="bg-white rounded-xl border border-gray-200 hover:border-orange-200 hover:shadow-lg transition-all duration-300 cursor-pointer overflow-hidden"
    @click="$emit('select-vendor', vendor)"
  >
    <!-- Vendor Image/Logo -->
    <div class="aspect-video bg-gray-100 relative overflow-hidden">
      <img
        v-if="vendor.brand_logo"
        :src="vendor.brand_logo"
        :alt="vendor.brand_name"
        class="w-full h-full object-cover"
      />
      <img
        v-else-if="vendor.brand_image"
        :src="vendor.brand_image"
        :alt="vendor.brand_name"
        class="w-full h-full object-cover"
      />
      <div v-else class="w-full h-full flex items-center justify-center bg-gray-100">
        <span class="text-4xl">🍽️</span>
      </div>

      <!-- Status Badge -->
      <div
        v-if="vendor.is_active"
        class="absolute top-3 left-3 px-2 py-1 bg-green-500 text-white text-xs font-medium rounded-full"
      >
        Open
      </div>
      <div
        v-else
        class="absolute top-3 left-3 px-2 py-1 bg-gray-500 text-white text-xs font-medium rounded-full"
      >
        Closed
      </div>

      <!-- QR Available Badge -->
      <div
        v-if="vendor.qr_code_image"
        class="absolute top-3 right-3 px-2 py-1 bg-blue-500 text-white text-xs font-medium rounded-full"
      >
        QR
      </div>
    </div>

    <!-- Vendor Info -->
    <div class="p-4">
      <!-- Name -->
      <div class="mb-2">
        <h3 class="font-bold text-lg text-gray-900 truncate">{{ vendor.brand_name }}</h3>
      </div>

      <!-- Contact Info -->
      <div class="flex items-center gap-4 text-sm text-gray-500 mb-3">
        <div v-if="vendor.qr_mobile_number" class="flex items-center gap-1">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
          </svg>
          <span>{{ vendor.qr_mobile_number }}</span>
        </div>

        <div v-if="vendor.qr_code_image" class="flex items-center gap-1">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
          </svg>
          <span>QR Code</span>
        </div>
      </div>

      <!-- Action Button -->
      <button
        class="w-full py-2 px-4 bg-orange-500 text-white font-medium rounded-lg hover:bg-orange-600 transition-colors"
        :disabled="!vendor.is_active"
      >
        {{ vendor.is_active ? 'Browse Menu' : 'Currently Closed' }}
      </button>
    </div>
  </div>
</template>

<script setup>
defineProps({
  vendor: {
    type: Object,
    required: true
  }
})

defineEmits(['select-vendor'])
</script>

<style scoped>
/* No additional styles needed - using Tailwind CSS classes */
</style>
