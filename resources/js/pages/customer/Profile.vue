<template>
  <CustomerLayout>
    <div class="max-w-4xl mx-auto">
      <!-- Header -->
      <div class="bg-white shadow-sm border-b border-gray-200 mb-6">
        <div class="px-6 py-8">
          <h1 class="text-3xl font-bold text-gray-900">Profile Settings</h1>
          <p class="text-gray-600 mt-2">Manage your account information and security settings</p>
        </div>
      </div>

      <!-- Success/Error Messages -->
      <div v-if="$page.props.flash?.success" class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
        <div class="flex items-center">
          <svg class="w-5 h-5 text-green-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <span class="text-green-700">{{ $page.props.flash.success }}</span>
        </div>
      </div>

      <div v-if="$page.props.flash?.error" class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
        <div class="flex items-center">
          <svg class="w-5 h-5 text-red-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <span class="text-red-700">{{ $page.props.flash.error }}</span>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Profile Information -->
        <div class="bg-white shadow-sm border border-gray-200 rounded-lg">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Profile Information</h2>
            <p class="text-sm text-gray-600 mt-1">Update your name and email address</p>
          </div>

          <form @submit.prevent="updateProfile" class="p-6 space-y-4">
            <!-- Name Field -->
            <div>
              <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
              <input
                id="name"
                v-model="profileForm.name"
                type="text"
                :disabled="profileProcessing"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent disabled:bg-gray-50 disabled:text-gray-500"
                :class="{ 'border-red-300': profileErrors.name }"
                placeholder="Enter your full name"
              />
              <div v-if="profileErrors.name" class="mt-1 text-sm text-red-600">
                {{ profileErrors.name[0] }}
              </div>
            </div>

            <!-- Email Field -->
            <div>
              <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
              <input
                id="email"
                v-model="profileForm.email"
                type="email"
                :disabled="profileProcessing"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent disabled:bg-gray-50 disabled:text-gray-500"
                :class="{ 'border-red-300': profileErrors.email }"
                placeholder="Enter your email address"
              />
              <div v-if="profileErrors.email" class="mt-1 text-sm text-red-600">
                {{ profileErrors.email[0] }}
              </div>
            </div>

            <!-- Submit Button -->
            <button
              type="submit"
              :disabled="profileProcessing"
              class="w-full bg-orange-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              <span v-if="profileProcessing" class="flex items-center justify-center">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Updating...
              </span>
              <span v-else>Update Profile</span>
            </button>
          </form>
        </div>

        <!-- Password Change Component -->
        <PasswordChange />
      </div>

      <!-- Delete Account Component -->
      <div class="mt-6">
        <DeleteAccount />
      </div>
    </div>
  </CustomerLayout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { usePage } from '@inertiajs/vue3'
import CustomerLayout from '@/layouts/customer/CustomerLayout.vue'
import PasswordChange from '@/components/PasswordChange.vue'
import DeleteAccount from '@/components/DeleteAccount.vue'

const page = usePage()

// Profile form data
const profileForm = reactive({
  name: page.props.auth?.user?.name || '',
  email: page.props.auth?.user?.email || ''
})

// Error handling
const profileErrors = ref({})
const profileProcessing = ref(false)

// Update profile function
const updateProfile = async () => {
  try {
    profileProcessing.value = true
    profileErrors.value = {}

    const response = await fetch('/settings/profile', {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify({
        name: profileForm.name,
        email: profileForm.email
      })
    })

    if (response.ok) {
      // Success - page will redirect or reload
      window.location.reload()
    } else {
      const data = await response.json()
      profileErrors.value = data.errors || {}
    }
  } catch (error) {
    console.error('Error updating profile:', error)
    profileErrors.value = { general: ['An error occurred. Please try again.'] }
  } finally {
    profileProcessing.value = false
  }
}

// Initialize form with user data on mount
onMounted(() => {
  if (page.props.auth?.user) {
    profileForm.name = page.props.auth.user.name
    profileForm.email = page.props.auth.user.email
  }
})
</script>
