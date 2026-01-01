<template>
  <div class="bg-white shadow-sm border border-red-200 rounded-lg">
    <div class="px-6 py-4 border-b border-red-200">
      <h2 class="text-lg font-semibold text-red-900">Delete Account</h2>
      <p class="text-sm text-red-600 mt-1">Once your account is deleted, all of your data will be permanently removed</p>
    </div>

    <div class="p-6">
      <button
        @click="showDeleteModal = true"
        class="bg-red-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors"
      >
        Delete Account
      </button>
    </div>

    <!-- Delete Account Confirmation Modal -->
    <div
      v-if="showDeleteModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      @click="showDeleteModal = false"
    >
      <div
        class="bg-white rounded-lg max-w-md w-full mx-4 p-6"
        @click.stop
      >
        <div class="flex items-center mb-4">
          <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mr-4">
            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
            </svg>
          </div>
          <div>
            <h3 class="text-lg font-semibold text-gray-900">Delete Account</h3>
            <p class="text-sm text-gray-600">This action cannot be undone</p>
          </div>
        </div>

        <form @submit.prevent="deleteAccount">
          <p class="text-gray-700 mb-6">
            Are you sure you want to delete your account? All of your data will be permanently removed and cannot be recovered.
          </p>

          <!-- Password confirmation for delete -->
          <div class="mb-6">
            <label for="delete_password" class="block text-sm font-medium text-gray-700 mb-2">Enter your password to confirm</label>
            <input
              id="delete_password"
              v-model="deleteForm.password"
              type="password"
              :disabled="deleteProcessing"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent disabled:bg-gray-50"
              :class="{ 'border-red-300': deleteErrors.password }"
              placeholder="Enter your password"
              autocomplete="current-password"
              required
            />
            <div v-if="deleteErrors.password" class="mt-1 text-sm text-red-600">
              {{ deleteErrors.password[0] }}
            </div>
          </div>

          <div class="flex justify-end space-x-3">
            <button
              type="button"
              @click="showDeleteModal = false"
              :disabled="deleteProcessing"
              class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="deleteProcessing || !deleteForm.password"
              class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              <span v-if="deleteProcessing" class="flex items-center">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Deleting...
              </span>
              <span v-else>Delete Account</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { router } from '@inertiajs/vue3'

// Reactive data
const showDeleteModal = ref(false)
const deleteForm = reactive({
  password: ''
})

const deleteErrors = ref({})
const deleteProcessing = ref(false)

// Delete account function
const deleteAccount = async () => {
  try {
    deleteProcessing.value = true
    deleteErrors.value = {}

    router.delete('/settings/profile', {
      data: {
        password: deleteForm.password
      },
      onError: (errors) => {
        deleteErrors.value = errors
      },
      onSuccess: () => {
        // User will be redirected/logged out by the backend
      },
      onFinish: () => {
        deleteProcessing.value = false
      }
    })
  } catch (error) {
    console.error('Error deleting account:', error)
    deleteErrors.value = { general: ['An error occurred. Please try again.'] }
    deleteProcessing.value = false
  }
}
</script>
