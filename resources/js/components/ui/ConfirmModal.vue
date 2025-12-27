<template>
  <Teleport to="body">
    <div
      v-if="isOpen"
      class="fixed inset-0 z-50 flex items-center justify-center"
    >
      <!-- Backdrop -->
      <div
        class="absolute inset-0 bg-black/50"
        @click="cancel"
      ></div>

      <!-- Modal -->
      <div class="relative z-10 w-full max-w-md mx-4 bg-white rounded-2xl shadow-xl" @click.stop>
        <!-- Icon -->
        <div class="pt-6 text-center">
          <div
            :class="[
              'w-16 h-16 mx-auto rounded-full flex items-center justify-center',
              variant === 'danger' ? 'bg-red-100' : 'bg-orange-100'
            ]"
          >
            <span class="text-3xl">{{ icon }}</span>
          </div>
        </div>

        <!-- Content -->
        <div class="px-6 py-4 text-center">
          <h3 class="text-lg font-bold text-gray-900 mb-2">{{ title }}</h3>
          <p class="text-gray-600">{{ message }}</p>
        </div>

        <!-- Actions -->
        <div class="px-6 pb-6 flex gap-3">
          <button
            @click="cancel"
            class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 font-medium"
          >
            {{ cancelText }}
          </button>
          <button
            @click="confirm"
            :disabled="loading"
            :class="[
              'flex-1 px-4 py-3 rounded-xl font-medium disabled:opacity-50',
              variant === 'danger'
                ? 'bg-red-500 text-white hover:bg-red-600'
                : 'bg-orange-500 text-white hover:bg-orange-600'
            ]"
          >
            {{ loading ? 'Processing...' : confirmText }}
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    default: 'Confirm Action'
  },
  message: {
    type: String,
    default: 'Are you sure you want to proceed?'
  },
  confirmText: {
    type: String,
    default: 'Confirm'
  },
  cancelText: {
    type: String,
    default: 'Cancel'
  },
  variant: {
    type: String,
    default: 'danger' // 'danger' or 'warning'
  },
  icon: {
    type: String,
    default: '⚠️'
  },
  loading: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['confirm', 'cancel'])

const confirm = () => {
  emit('confirm')
}

const cancel = () => {
  emit('cancel')
}
</script>
