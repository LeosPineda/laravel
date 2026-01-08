import { ref } from 'vue'

// Global sound settings
const soundEnabled = ref(true)

export interface SoundSettings {
  enabled: boolean
  volume: number
}

export function useSound() {
  // Get sound settings from localStorage
  const getSoundSettings = (): SoundSettings => {
    const settings = localStorage.getItem('soundSettings')
    if (settings) {
      try {
        return JSON.parse(settings)
      } catch {
        // Invalid JSON, use defaults
      }
    }
    return {
      enabled: true,
      volume: 0.6
    }
  }

  // Save sound settings to localStorage
  const saveSoundSettings = (settings: SoundSettings) => {
    localStorage.setItem('soundSettings', JSON.stringify(settings))
    soundEnabled.value = settings.enabled
  }

  // Toggle sound on/off
  const toggleSound = (): boolean => {
    const settings = getSoundSettings()
    settings.enabled = !settings.enabled
    saveSoundSettings(settings)
    return settings.enabled
  }

  // Set sound enabled state
  const setSoundEnabled = (enabled: boolean) => {
    const settings = getSoundSettings()
    settings.enabled = enabled
    saveSoundSettings(settings)
  }

  // Check if sound is enabled
  const isSoundEnabled = (): boolean => {
    return soundEnabled.value
  }

  // Initialize sound settings
  const initializeSound = () => {
    const settings = getSoundSettings()
    soundEnabled.value = settings.enabled
  }

  // Initialize on import
  initializeSound()

  return {
    soundEnabled,
    getSoundSettings,
    saveSoundSettings,
    toggleSound,
    setSoundEnabled,
    isSoundEnabled,
    initializeSound
  }
}
