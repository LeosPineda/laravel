<template>
  <Teleport to="body">
    <div class="fixed bottom-20 right-4 z-[100] flex flex-col gap-2 max-w-sm md:bottom-4">
      <TransitionGroup name="toast">
        <div
          v-for="toast in toasts"
          :key="toast.id"
          @click="remove(toast.id)"
          :class="[
            'px-4 py-3 rounded-lg shadow-lg cursor-pointer transition-all',
            'flex items-start gap-3 min-w-[280px]',
            getToastClasses(toast.type)
          ]"
        >
          <!-- Icon -->
          <span class="text-xl flex-shrink-0">{{ getIcon(toast.type) }}</span>

          <!-- Content -->
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium">{{ toast.message }}</p>
            <p v-if="toast.type === 'order'" class="text-xs opacity-75 mt-1">
              Tap to dismiss
            </p>
          </div>

          <!-- Close button -->
          <button class="text-current opacity-50 hover:opacity-100 flex-shrink-0">
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
    order: '📦'
  }
  return icons[type] || '📢'
}

const getToastClasses = (type: string) => {
  const classes: Record<string, string> = {
    success: 'bg-green-600 text-white',
    error: 'bg-red-600 text-white',
    warning: 'bg-yellow-500 text-black',
    info: 'bg-blue-600 text-white',
    order: 'bg-orange-500 text-white animate-pulse'
  }
  return classes[type] || 'bg-gray-700 text-white'
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
