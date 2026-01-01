<template>
  <div class="bg-white shadow-sm border border-gray-200 rounded-lg">
    <div class="px-6 py-4 border-b border-gray-200">
      <h2 class="text-lg font-semibold text-gray-900">Change Password</h2>
      <p class="text-sm text-gray-600 mt-1">Update your account password</p>
    </div>

    <form @submit.prevent="updatePassword" class="p-6 space-y-4">
      <!-- Current Password -->
      <div>
        <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
        <div class="relative">
          <input
            id="current_password"
            v-model="passwordForm.current_password"
            :type="showCurrentPassword ? 'text' : 'password'"
            :disabled="passwordProcessing"
            class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent disabled:bg-gray-50 disabled:text-gray-500"
            :class="{ 'border-red-300': passwordErrors.current_password }"
            placeholder="Enter current password"
            autocomplete="current-password"
          />
          <button
            type="button"
            @click="showCurrentPassword = !showCurrentPassword"
            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
          >
            <svg v-if="showCurrentPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"/>
            </svg>
          </button>
        </div>
        <div v-if="passwordErrors.current_password" class="mt-1 text-sm text-red-600">
          {{ passwordErrors.current_password[0] }}
        </div>
      </div>

      <!-- New Password -->
      <div>
        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
        <div class="relative">
          <input
            id="password"
            v-model="passwordForm.password"
            :type="showNewPassword ? 'text' : 'password'"
            :disabled="passwordProcessing"
            class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent disabled:bg-gray-50 disabled:text-gray-500"
            :class="{ 'border-red-300': passwordErrors.password }"
            placeholder="Enter new password"
            autocomplete="new-password"
          />
          <button
            type="button"
            @click="showNewPassword = !showNewPassword"
            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
          >
            <svg v-if="showNewPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"/>
            </svg>
          </button>
        </div>
        <div v-if="passwordErrors.password" class="mt-1 text-sm text-red-600">
          {{ passwordErrors.password[0] }}
        </div>
      </div>

      <!-- Confirm Password -->
      <div>
        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
        <input
          id="password_confirmation"
          v-model="passwordForm.password_confirmation"
          type="password"
          :disabled="passwordProcessing"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent disabled:bg-gray-50 disabled:text-gray-500"
          :class="{ 'border-red-300': passwordErrors.password_confirmation }"
          placeholder="Confirm new password"
          autocomplete="new-password"
        />
        <div v-if="passwordErrors.password_confirmation" class="mt-1 text-sm text-red-600">
          {{ passwordErrors.password_confirmation[0] }}
        </div>
      </div>

      <!-- Submit Button -->
      <button
        type="submit"
        :disabled="passwordProcessing"
        class="w-full bg-orange-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
      >
        <span v-if="passwordProcessing" class="flex items-center justify-center">
          <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          Updating...
        </span>
        <span v-else>Change Password</span>
      </button>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { router } from '@inertiajs/vue3'

// Reactive data
const passwordForm = reactive({
  current_password: '',
  password: '',
  password_confirmation: ''
})

const passwordErrors = ref({})
const passwordProcessing = ref(false)
const showCurrentPassword = ref(false)
const showNewPassword = ref(false)

// Update password function
const updatePassword = async () => {
  try {
    passwordProcessing.value = true
    passwordErrors.value = {}

    router.patch('/settings/password', {
      current_password: passwordForm.current_password,
      password: passwordForm.password,
      password_confirmation: passwordForm.password_confirmation
    }, {
      onError: (errors) => {
        passwordErrors.value = errors
      },
      onFinish: () => {
        passwordProcessing.value = false
        // Clear form on success
        if (Object.keys(errors).length === 0) {
          passwordForm.current_password = ''
          passwordForm.password = ''
          passwordForm.password_confirmation = ''
        }
      }
    })
  } catch (error) {
    console.error('Error updating password:', error)
    passwordErrors.value = { general: ['An error occurred. Please try again.'] }
    passwordProcessing.value = false
  }
}
</script>
