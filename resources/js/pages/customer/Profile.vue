<template>
  <CustomerLayout>
    <!-- Header - Full Width -->
    <div class="bg-white shadow-sm border-b border-gray-200">
      <div class="px-4 sm:px-6 py-6">
        <h1 class="text-2xl font-bold text-gray-900">Profile Settings</h1>
        <p class="text-gray-600 mt-1">Manage your account security settings</p>
      </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 py-6">

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

      <!-- Sound Settings Section -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">🔊 Sound Settings</h2>
        <p class="text-gray-600 mb-4">Control sound notifications for order updates</p>

        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-sm font-medium text-gray-900">Order Notifications Sound</h3>
            <p class="text-sm text-gray-500">Get sound alerts when your order status changes</p>
          </div>
          <div class="flex items-center">
            <button
              @click="toggleSound"
              class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
              :class="isSoundEnabled ? 'bg-blue-600' : 'bg-gray-200'"
            >
              <span
                class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform duration-200 ease-in-out"
                :class="isSoundEnabled ? 'translate-x-6' : 'translate-x-1'"
              />
            </button>
            <span class="ml-3 text-sm font-medium text-gray-700">
              {{ isSoundEnabled ? 'ON' : 'OFF' }}
            </span>
          </div>
        </div>
      </div>

      <!-- Delete Account Component -->
      <div class="mt-6">
        <DeleteAccount />
      </div>
    </div>
  </CustomerLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useSound } from '@/composables/useSound'
import CustomerLayout from '@/layouts/customer/CustomerLayout.vue'
import DeleteAccount from '@/components/customer/DeleteAccount.vue'

const { isSoundEnabled, toggleSound } = useSound()
</script>
