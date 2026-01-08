import { ref, readonly } from 'vue'
import { useSound } from './useSound'

export interface Toast {
  id: number
  message: string
  type: 'success' | 'error' | 'warning' | 'info' | 'order' | 'customer'
  duration: number
}

const toasts = ref<Toast[]>([])
let toastId = 0

// Sound settings
const { isSoundEnabled } = useSound()

// Sound file path
const NOTIFICATION_SOUND = '/storage/Sound/mixkit-software-interface-back-2575.wav'

// Sound instance (lazy loaded)
let notificationSound: HTMLAudioElement | null = null

const initSound = () => {
  if (typeof window === 'undefined') return
  if (!notificationSound) {
    notificationSound = new Audio(NOTIFICATION_SOUND)
    notificationSound.volume = 0.6
  }
}

// Play the notification sound (respects sound settings)
const playNotificationSound = () => {
  if (!isSoundEnabled()) return // Check if sound is enabled

  initSound()
  if (notificationSound) {
    notificationSound.currentTime = 0
    notificationSound.play().catch(() => {
      console.log('Sound play requires user interaction first')
    })
  }
}

// Different sound methods (all use the same sound for consistency)
const playOrderSound = () => {
  playNotificationSound()
}

const playCustomerSound = () => {
  playNotificationSound()
}

const playErrorSound = () => {
  playNotificationSound()
}

const show = (message: string, type: Toast['type'] = 'info', duration = 10000) => {
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

// Convenience methods - 30 seconds default duration for customer alerts
const success = (message: string, duration = 30000, withSound = false) => {
  if (withSound) playCustomerSound()
  return show(message, 'success', duration)
}

const error = (message: string, duration = 30000, withSound = false) => {
  if (withSound) playErrorSound()
  return show(message, 'error', duration)
}

const warning = (message: string, duration = 30000) => show(message, 'warning', duration)
const info = (message: string, duration = 30000) => show(message, 'info', duration)

// Special method for new orders - with sound (15 seconds for vendor attention)
const newOrder = (message: string, duration = 15000) => {
  playOrderSound()
  return show(message, 'order', duration)
}

// Customer notification with sound (30 seconds)
const customerAlert = (message: string, type: 'success' | 'error' = 'success', duration = 30000) => {
  if (type === 'success') {
    playCustomerSound()
  } else {
    playErrorSound()
  }
  return show(message, type === 'success' ? 'customer' : 'error', duration)
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
    customerAlert,
    playCustomerSound,
    playOrderSound,
    playErrorSound
  }
}
