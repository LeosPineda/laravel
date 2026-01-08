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

// Get reactive sound state
const { isSoundEnabled } = useSound()

// Sound file path - use multiple fallbacks
const NOTIFICATION_SOUND_PATHS = [
  '/storage/Sound/mixkit-software-interface-back-2575.wav',
  '/sounds/notification.mp3',
  '/audio/notification.mp3'
]

// Sound instance (lazy loaded)
let notificationSound: HTMLAudioElement | null = null
let soundInitialized = false

const initSound = () => {
  if (typeof window === 'undefined') return
  if (soundInitialized) return
  if (!notificationSound) {
    // Use the correct sound path
    notificationSound = new Audio('/storage/Sound/mixkit-software-interface-back-2575.wav')
    notificationSound.volume = 0.6

    // Preload the audio
    notificationSound.load()

    notificationSound.addEventListener('canplaythrough', () => {
      console.log('✅ Sound loaded successfully')
      soundInitialized = true
    }, { once: true })

    notificationSound.addEventListener('error', (e) => {
      console.error('❌ Sound load error:', e)
      // Try fallback beep
      fallbackBeep()
    }, { once: true })
  }
}

// Play the notification sound (respects sound settings)
const playNotificationSound = () => {
  // isSoundEnabled is a computed ref, access .value
  if (!isSoundEnabled.value) return

  initSound()
  if (notificationSound) {
    notificationSound.currentTime = 0
    notificationSound.play().catch(() => {
      // Fallback to browser beep if audio fails
      fallbackBeep()
    })
  } else {
    // Fallback to browser beep
    fallbackBeep()
  }
}

// Browser beep fallback using Web Audio API
const fallbackBeep = () => {
  if (typeof window === 'undefined') return
  try {
    const AudioContext = window.AudioContext || (window as any).webkitAudioContext
    if (AudioContext) {
      const audioCtx = new AudioContext()
      const oscillator = audioCtx.createOscillator()
      const gainNode = audioCtx.createGain()

      oscillator.connect(gainNode)
      gainNode.connect(audioCtx.destination)

      oscillator.type = 'sine'
      oscillator.frequency.value = 800
      gainNode.gain.value = 0.1

      oscillator.start()
      setTimeout(() => {
        oscillator.stop()
        audioCtx.close()
      }, 200)
    }
  } catch (e) {
    // Silent fail - some browsers block audio
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

// Convenience methods - all toasts play sound by default
const success = (message: string, duration = 5000) => {
  playCustomerSound()
  return show(message, 'success', duration)
}

const error = (message: string, duration = 5000) => {
  playErrorSound()
  return show(message, 'error', duration)
}

// Vendor error notification with sound (for cancellations, etc.)
const vendorError = (message: string, duration = 10000) => {
  playErrorSound()
  return show(message, 'error', duration)
}

const warning = (message: string, duration = 5000) => {
  playNotificationSound()
  return show(message, 'warning', duration)
}

const info = (message: string, duration = 5000) => {
  playNotificationSound()
  return show(message, 'info', duration)
}

// Special method for new orders - with sound (15 seconds for vendor attention)
const newOrder = (message: string, duration = 10000) => {
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
    vendorError,  // Error toast with sound for vendors
    warning,
    info,
    newOrder,
    customerAlert,
    playCustomerSound,
    playOrderSound,
    playErrorSound
  }
}
