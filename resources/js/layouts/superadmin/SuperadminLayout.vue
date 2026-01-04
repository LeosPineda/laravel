<template>
  <div class="min-h-screen bg-[#F5F5F5] flex flex-col">
    <!-- Header -->
    <header class="bg-white border-b border-[#E0E0E0] sticky top-0 z-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <!-- Logo & Brand -->
          <div class="flex items-center gap-4 lg:gap-8">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 bg-[#FF6B35] rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                </svg>
              </div>
              <span class="text-lg font-bold text-[#1A1A1A] hidden sm:block">Food Court Admin</span>
            </div>

            <!-- Desktop Navigation -->
            <nav class="hidden md:flex items-center gap-1">
              <Link
                href="/superadmin/dashboard"
                :class="[
                  $page.url.startsWith('/superadmin/dashboard')
                    ? 'text-white bg-[#FF6B35]'
                    : 'text-[#1A1A1A]/70 hover:text-[#1A1A1A] hover:bg-[#F5F5F5]',
                  'px-4 py-2 text-sm font-medium rounded-lg transition-colors'
                ]"
              >
                Dashboard
              </Link>
              <Link
                href="/superadmin/vendors"
                :class="[
                  $page.url.startsWith('/superadmin/vendors')
                    ? 'text-white bg-[#FF6B35]'
                    : 'text-[#1A1A1A]/70 hover:text-[#1A1A1A] hover:bg-[#F5F5F5]',
                  'px-4 py-2 text-sm font-medium rounded-lg transition-colors'
                ]"
              >
                Vendors
              </Link>
            </nav>
          </div>

          <!-- Right side -->
          <div class="flex items-center gap-2 sm:gap-4">
            <!-- Logout -->
            <button
              @click="logout"
              class="flex items-center gap-2 px-3 sm:px-4 py-2 text-sm font-medium text-[#1A1A1A]/70 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
              </svg>
              <span class="hidden sm:inline">Logout</span>
            </button>
          </div>
        </div>
      </div>
    </header>

    <!-- Mobile Navigation -->
    <div class="md:hidden border-t border-[#E0E0E0] bg-white">
      <div class="px-4">
        <nav class="flex justify-center gap-8 py-3">
          <Link
            href="/superadmin/dashboard"
            class="flex flex-col items-center py-2 px-4 hover:bg-[#F5F5F5] rounded-lg transition-colors"
          >
            <span class="text-xl">📊</span>
            <span
              class="text-xs mt-1 font-medium"
              :class="$page.url.startsWith('/superadmin/dashboard') ? 'text-[#FF6B35]' : 'text-[#1A1A1A]/60'"
            >
              Dashboard
            </span>
          </Link>

          <Link
            href="/superadmin/vendors"
            class="flex flex-col items-center py-2 px-4 hover:bg-[#F5F5F5] rounded-lg transition-colors"
          >
            <span class="text-xl">🏪</span>
            <span
              class="text-xs mt-1 font-medium"
              :class="$page.url.startsWith('/superadmin/vendors') ? 'text-[#FF6B35]' : 'text-[#1A1A1A]/60'"
            >
              Vendors
            </span>
          </Link>
        </nav>
      </div>
    </div>

    <!-- Main Content - Scrollable -->
    <main class="flex-1 px-4 sm:px-6 lg:px-8 py-4 sm:py-6 overflow-y-auto">
      <div class="max-w-7xl mx-auto">
        <slot />
      </div>
    </main>
  </div>
</template>

<script setup>
import { Link, router, usePage } from '@inertiajs/vue3'

const page = usePage()

const logout = () => {
  router.post('/logout')
}
</script>
