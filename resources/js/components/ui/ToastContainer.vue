<template>
  <Teleport to="body">
    <div class="fixed top-20 right-4 z-[100] flex flex-col gap-3 max-w-md md:top-4 md:right-6">
      <TransitionGroup name="toast">
        <div
          v-for="toast in toasts"
          :key="toast.id"
          @click="remove(toast.id)"
          :class="[
            'px-5 py-4 rounded-xl shadow-2xl cursor-pointer transition-all border',
            'flex items-start gap-4 min-w-[340px]',
            getToastClasses(toast.type)
          ]"
        >
          <!-- Icon -->
          <span :class="['flex-shrink-0', toast.type === 'order' ? 'text-3xl' : 'text-2xl']">
            {{ getIcon(toast.type) }}
          </span>

          <!-- Content -->
          <div class="flex-1 min-w-0">
            <p :class="['font-semibold', toast.type === 'order' ? 'text-lg' : 'text-base']">
              {{ toast.message }}
            </p>
            <p v-if="toast.type === 'order'" class="text-sm opacity-80 mt-1">
              🔔 Tap to dismiss
            </p>
          </div>

          <!-- Close button -->
          <button class="text-current opacity-60 hover:opacity-100 flex-shrink-0 text-xl font-bold">
            ✕
          </button>
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { useToast } from '@/composables/useToast'

const { toasts, remove } = useToast()

const getIcon = (type: string) => {
  const icons: Record<string, string> = {
    success: '✅',
    error: '❌',
    warning: '⚠️',
    info: 'ℹ️',
    order: '📦',
    customer: '🔔'
  }
  return icons[type] || '📢'
}

const getToastClasses = (type: string) => {
  const classes: Record<string, string> = {
    success: 'bg-green-600 text-white border-green-400',
    error: 'bg-red-600 text-white border-red-400',
    warning: 'bg-yellow-500 text-black border-yellow-400',
    info: 'bg-blue-600 text-white border-blue-400',
    order: 'bg-gradient-to-r from-orange-500 to-red-500 text-white border-orange-400 animate-pulse ring-4 ring-orange-300/50',
    customer: 'bg-gradient-to-r from-green-500 to-teal-500 text-white border-green-400 ring-2 ring-green-300/50'
  }
  return classes[type] || 'bg-gray-700 text-white border-gray-500'
}
</script>

<style scoped>
/* Toast enter/leave transitions */
.toast-enter-active {
  transition: all 0.3s ease-out;
}

.toast-leave-active {
  transition: all 0.2s ease-in;
}

.toast-enter-from {
  opacity: 0;
  transform: translateX(100%);
}

.toast-leave-to {
  opacity: 0;
  transform: translateX(100%);
}

.toast-move {
  transition: transform 0.3s ease;
}
</style>
