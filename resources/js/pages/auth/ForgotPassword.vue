<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

defineProps<{
    status?: string;
}>();

const form = useForm({
    email: '',
});

const showVendorModal = ref(false);
const vendorEmail = ref('');

// Check if email belongs to a vendor
const isVendorEmail = computed(() => {
    return form.email.includes('vendor') || form.email.includes('@vendor.') || form.email.includes('restaurant');
});

const submit = () => {
    if (isVendorEmail.value) {
        vendorEmail.value = form.email;
        showVendorModal.value = true;
        return;
    }

    form.post('/forgot-password');
};

const closeModal = () => {
    showVendorModal.value = false;
    form.reset();
};

const contactAdmin = () => {
    window.location.href = 'mailto:support@4rodzfoodcourt.com?subject=Password Reset Request&body=Please help me reset my vendor account password for email: ' + vendorEmail.value;
};
</script>

<template>
    <Head title="Forgot Password" />

    <div class="min-h-screen flex flex-col lg:flex-row bg-white">
        <!-- Left Side - Branding -->
        <div class="hidden lg:flex lg:w-1/2 bg-[#F5F5F5] p-12 xl:p-16 flex-col justify-between relative">
            <div class="absolute top-0 right-0 w-80 h-80 bg-[#FF6B35]/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
            <div class="absolute bottom-0 left-0 w-64 h-64 bg-[#FF6B35]/5 rounded-full translate-y-1/2 -translate-x-1/2"></div>

            <div class="relative z-10">
                <div class="flex items-center gap-4">
                    <img src="/fast-food.png" alt="4Rodz" class="w-16 h-16 rounded-xl" />
                    <span class="text-2xl font-bold text-[#1A1A1A]">4Rodz Food Court</span>
                </div>
            </div>

            <div class="relative z-10 max-w-lg ml-8 xl:ml-12">
                <h1 class="text-4xl xl:text-5xl font-bold text-[#1A1A1A] leading-tight mb-6">
                    Forgot your<br>
                    <span class="text-[#FF6B35]">password?</span>
                </h1>
                <p class="text-[#1A1A1A]/70 text-lg leading-relaxed mb-10">
                    Don't worry! We'll help you reset your password and get back to ordering your favorite food.
                </p>

                <div class="space-y-5">
                    <div class="flex items-center gap-5 p-5 bg-white rounded-2xl border border-[#E0E0E0] shadow-sm">
                        <div class="w-14 h-14 bg-[#FF6B35]/10 rounded-xl flex items-center justify-center shrink-0">
                            <svg class="w-7 h-7 text-[#FF6B35]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-[#1A1A1A] text-lg">Secure Reset</h3>
                            <p class="text-[#1A1A1A]/60 text-sm">Get a secure link to reset your password</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-5 p-5 bg-white rounded-2xl border border-[#E0E0E0] shadow-sm">
                        <div class="w-14 h-14 bg-[#FF6B35]/10 rounded-xl flex items-center justify-center shrink-0">
                            <svg class="w-7 h-7 text-[#FF6B35]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m0 0v2m0-2h2m-2 0H9m3-9a3 3 0 003 3v7a3 3 0 01-3 3H9a3 3 0 01-3-3V9a3 3 0 013-3h9z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-[#1A1A1A] text-lg">Quick Recovery</h3>
                            <p class="text-[#1A1A1A]/60 text-sm">Reset your password in minutes</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="relative z-10">
                <p class="text-[#1A1A1A]/40 text-base">&copy; 2024 4Rodz Food Court</p>
            </div>
        </div>

        <!-- Right Side - Form -->
        <div class="flex-1 flex items-center justify-center p-6 sm:p-8 lg:p-16 bg-white">
            <div class="w-full max-w-[480px]">
                <!-- Mobile Header -->
                <div class="lg:hidden text-center mb-8">
                    <div class="flex items-center justify-center gap-3 mb-4">
                        <img src="/fast-food.png" alt="4Rodz" class="w-14 h-14 rounded-2xl shadow-lg" />
                    </div>
                    <h1 class="text-2xl font-bold text-[#1A1A1A]">4Rodz Food Court</h1>
                    <p class="text-[#1A1A1A]/50 text-sm mt-1">Order • Pick up • Enjoy</p>
                </div>

                <div class="mb-10">
                    <h2 class="text-3xl sm:text-4xl font-bold text-[#1A1A1A]">Reset password</h2>
                    <p class="text-[#1A1A1A]/60 mt-3 text-lg">Enter your email to receive a reset link</p>
                </div>

                <!-- Status Message -->
                <div
                    v-if="status"
                    class="mb-6 p-4 bg-green-50 border border-green-100 rounded-xl text-center text-sm text-green-700"
                >
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label for="email" class="block text-base font-medium text-[#1A1A1A] mb-3">
                            Email address
                        </label>
                        <input
                            id="email"
                            type="email"
                            v-model="form.email"
                            required
                            autofocus
                            autocomplete="email"
                            placeholder="Enter your email"
                            class="w-full px-5 py-4 border border-[#E0E0E0] rounded-xl bg-[#F5F5F5] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#FF6B35]/20 focus:border-[#FF6B35] transition-all text-[#1A1A1A] placeholder:text-[#1A1A1A]/40 text-lg"
                            :class="{ 'border-red-400 focus:ring-red-400/20 focus:border-red-400': form.errors.email }"
                        />
                        <p v-if="form.errors.email" class="mt-2 text-sm text-red-600">
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full py-5 px-6 bg-[#FF6B35] text-white font-semibold rounded-xl hover:bg-[#e55f2f] active:scale-[0.99] focus:outline-none focus:ring-4 focus:ring-[#FF6B35]/20 transition-all disabled:opacity-60 disabled:cursor-not-allowed text-lg"
                    >
                        <span v-if="form.processing" class="flex items-center justify-center gap-2">
                            <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                            </svg>
                            Sending...
                        </span>
                        <span v-else>Send Reset Link</span>
                    </button>
                </form>

                <div class="mt-10 text-center">
                    <a href="/login" class="text-[#1A1A1A]/60 text-lg">
                        <span class="font-medium text-[#FF6B35] hover:underline">← Back to Sign In</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Vendor Password Reset Modal -->
    <div
        v-if="showVendorModal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
        @click="closeModal"
    >
        <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-2xl" @click.stop>
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m0 0v2m0-2h2m-2 0H9m3-9a3 3 0 003 3v7a3 3 0 01-3 3H9a3 3 0 01-3-3V9a3 3 0 013-3h9z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-[#1A1A1A]">Vendor Account Access Restricted</h3>
                    <p class="text-sm text-[#1A1A1A]/60">Security Policy</p>
                </div>
            </div>

            <div class="mb-6">
                <p class="text-[#1A1A1A]/80 mb-4">
                    We've detected that you're trying to reset a <strong>vendor account</strong> password.
                </p>
                <div class="bg-orange-50 border border-orange-200 rounded-lg p-4 mb-4">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-orange-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-orange-800 mb-2">Important Security Notice:</p>
                            <p class="text-sm text-orange-700">
                                Vendor account passwords can only be changed by the <strong>Food Court Administrator</strong>.
                            </p>
                        </div>
                    </div>
                </div>
                <p class="text-sm text-[#1A1A1A]/70">
                    <strong>Email:</strong> {{ vendorEmail }}
                </p>
            </div>

            <div class="flex gap-3">
                <button
                    @click="contactAdmin"
                    class="flex-1 py-2.5 px-4 bg-[#FF6B35] text-white font-medium rounded-lg hover:bg-[#FF6B35]/90 transition-colors flex items-center justify-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    Contact Administrator
                </button>
                <button
                    @click="closeModal"
                    class="flex-1 py-2.5 px-4 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition-colors"
                >
                    Close
                </button>
            </div>
        </div>
    </div>
</template>
