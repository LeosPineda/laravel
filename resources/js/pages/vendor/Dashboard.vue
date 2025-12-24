<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';

const logout = () => {
    router.post('/logout');
};
</script>

<template>
    <Head title="Vendor Dashboard" />

    <div class="min-h-screen bg-[#F5F5F5]">
        <!-- Header -->
        <header class="bg-white border-b border-[#E0E0E0] sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 bg-[#FF6B35] rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                            </svg>
                        </div>
                        <span class="text-lg font-bold text-[#1A1A1A]">Vendor Dashboard</span>
                    </div>
                    <button
                        @click="logout"
                        class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-[#1A1A1A]/70 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span class="hidden sm:inline">Logout</span>
                    </button>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="text-center py-16">
                <div class="w-20 h-20 bg-[#FF6B35]/10 rounded-2xl flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-[#FF6B35]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-[#1A1A1A] mb-2">Welcome to Vendor Dashboard!</h1>
                <p class="text-[#1A1A1A]/60">Your vendor account is active. More features coming soon.</p>
            </div>
        </main>
    </div>
</template>
