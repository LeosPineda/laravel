import { ref, readonly } from 'vue'

export interface Toast {
  id: number
  message: string
  type: 'success' | 'error' | 'warning' | 'info' | 'order'
  duration: number
}

const toasts = ref<Toast[]>([])
let toastId = 0

// Sound for new orders
let orderSound: HTMLAudioElement | null = null

const initSound = () => {
  if (!orderSound && typeof window !== 'undefined') {
    orderSound = new Audio('/sounds/new-order.mp3')
    orderSound.volume = 0.7
  }
}

const playSound = () => {
  initSound()
  if (orderSound) {
    orderSound.currentTime = 0
    orderSound.play().catch(() => {
      // Audio play failed (user hasn't interacted with page yet)
      console.log('Sound play requires user interaction first')
    })
  }
}

const show = (message: string, type: Toast['type'] = 'info', duration = 5000) => {
  const id = ++toastId

  toasts.value.push({
    id,
    message,
    type,
    duration
  })

  // Auto-remove after duration
  if (duration > 0) {
    setTimeout(() => {
      remove(id)
    }, duration)
  }

  return id
}

const remove = (id: number) => {
  const index = toasts.value.findIndex(t => t.id === id)
  if (index > -1) {
    toasts.value.splice(index, 1)
  }
}

const clear = () => {
  toasts.value = []
}

// Convenience methods
const success = (message: string, duration = 5000) => show(message, 'success', duration)
const error = (message: string, duration = 5000) => show(message, 'error', duration)
const warning = (message: string, duration = 5000) => show(message, 'warning', duration)
const info = (message: string, duration = 5000) => show(message, 'info', duration)

// Special method for new orders - with sound
const newOrder = (message: string, duration = 8000) => {
  playSound()
  return show(message, 'order', duration)
}

export function useToast() {
  return {
    toasts: readonly(toasts),
    show,
    remove,
    clear,
    success,
    error,
    warning,
    info,
    newOrder,
    playSound
  }
}
