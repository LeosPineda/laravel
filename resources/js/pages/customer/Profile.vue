<template>
  <CustomerLayout>
    <div class="max-w-4xl mx-auto">
      <!-- Header -->
      <div class="bg-white shadow-sm border-b border-gray-200 mb-6">
        <div class="px-6 py-8">
          <h1 class="text-3xl font-bold text-gray-900">Profile Settings</h1>
          <p class="text-gray-600 mt-2">Manage your account security settings</p>
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



      <!-- Delete Account Component -->
      <div class="mt-6">
        <DeleteAccount />
      </div>
    </div>
  </CustomerLayout>
</template>

<script setup>
import CustomerLayout from '@/layouts/customer/CustomerLayout.vue'
import DeleteAccount from '@/components/customer/DeleteAccount.vue'
</script>
