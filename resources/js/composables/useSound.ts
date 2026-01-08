import { ref, computed } from 'vue'

export interface SoundSettings {
  enabled: boolean
  volume: number
}

const SOUND_STORAGE_KEY = 'food_court_sound_settings'

// Default settings
const DEFAULT_SETTINGS: SoundSettings = {
  enabled: true,
  volume: 0.6
}

// Global reactive state
const settings = ref<SoundSettings>(DEFAULT_SETTINGS)

export function useSound() {
  // Get settings from localStorage
  const loadSettings = (): SoundSettings => {
    if (typeof window === 'undefined') return DEFAULT_SETTINGS

    try {
      const stored = localStorage.getItem(SOUND_STORAGE_KEY)
      if (stored) {
        const parsed = JSON.parse(stored)
        return { ...DEFAULT_SETTINGS, ...parsed }
      }
    } catch (error) {
      console.warn('Failed to load sound settings:', error)
    }

    return DEFAULT_SETTINGS
  }

  // Save settings to localStorage
  const saveSettings = (newSettings: SoundSettings) => {
    if (typeof window !== 'undefined') {
      try {
        localStorage.setItem(SOUND_STORAGE_KEY, JSON.stringify(newSettings))
      } catch (error) {
        console.warn('Failed to save sound settings:', error)
      }
    }
    settings.value = { ...newSettings }
  }

  // Initialize settings
  const initializeSettings = () => {
    const loaded = loadSettings()
    settings.value = loaded
  }

  // Toggle sound enabled state
  const toggleSound = (): boolean => {
    const newSettings = { ...settings.value, enabled: !settings.value.enabled }
    saveSettings(newSettings)
    return newSettings.enabled
  }

  // Set sound enabled state explicitly
  const setSoundEnabled = (enabled: boolean) => {
    const newSettings = { ...settings.value, enabled }
    saveSettings(newSettings)
  }

  // Get current sound enabled state
  const isSoundEnabled = computed(() => settings.value.enabled)

  // Get current volume
  const getVolume = (): number => settings.value.volume

  // Set volume
  const setVolume = (volume: number) => {
    const clampedVolume = Math.max(0, Math.min(1, volume))
    const newSettings = { ...settings.value, volume: clampedVolume }
    saveSettings(newSettings)
  }

  // Get all settings
  const getSettings = (): SoundSettings => ({ ...settings.value })

  // Initialize on first use
  if (typeof window !== 'undefined' && settings.value === DEFAULT_SETTINGS) {
    initializeSettings()
  }

  return {
    // Reactive state
    isSoundEnabled,
    getVolume,

    // Methods
    toggleSound,
    setSoundEnabled,
    setVolume,
    getSettings,
    initializeSettings,

    // Direct access to settings (if needed)
    settings: computed(() => ({ ...settings.value }))
  }
}
