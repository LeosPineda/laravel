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
    // Simple check - in production, you might want to validate against backend
    // This is a basic example - you can enhance this logic
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
    // You could add a link to contact admin or redirect to support page
    window.location.href = 'mailto:support@4rodzfoodcourt.com?subject=Password Reset Request&body=Please help me reset my vendor account password for email: ' + vendorEmail.value;
};
</script>

<template>
    <Head title="Forgot Password" />

    <div class="min-h-screen flex items-center justify-center bg-[#F5F5F5] px-4">
        <div class="w-full max-w-md">
            <!-- Logo/Brand -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-[#1A1A1A]">Food Court</h1>
                <p class="text-[#1A1A1A]/60 mt-2">Reset your password</p>
            </div>

            <!-- Card -->
            <div class="bg-white rounded-xl shadow-sm border border-[#E0E0E0] p-8">
                <!-- Status Message -->
                <div
                    v-if="status"
                    class="mb-6 p-3 bg-green-50 border border-green-200 rounded-lg text-center text-sm text-green-700"
                >
                    {{ status }}
                </div>

                <p class="text-sm text-[#1A1A1A]/70 mb-6">
                    Enter your email address and we'll send you a link to reset your password.
                </p>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-[#1A1A1A] mb-2">
                            Email Address
                        </label>
                        <input
                            id="email"
                            type="email"
                            v-model="form.email"
                            required
                            autofocus
                            autocomplete="email"
                            placeholder="email@example.com"
                            class="w-full px-4 py-3 border border-[#E0E0E0] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#FF6B35] focus:border-transparent transition-all"
                            :class="{ 'border-red-500': form.errors.email }"
                        />
                        <p v-if="form.errors.email" class="mt-2 text-sm text-red-600">
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full py-3 px-4 bg-[#FF6B35] text-white font-semibold rounded-lg hover:bg-[#FF6B35]/90 focus:outline-none focus:ring-2 focus:ring-[#FF6B35] focus:ring-offset-2 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
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

                <!-- Back to Login -->
                <div class="mt-6 text-center">
                    <a href="/login" class="text-sm text-[#FF6B35] font-medium hover:text-[#FF6B35]/80 transition-colors">
                        ← Back to Sign In
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
        <div
            class="bg-white rounded-xl max-w-md w-full p-6 shadow-2xl"
            @click.stop
        >
            <!-- Modal Header -->
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

            <!-- Modal Content -->
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
                                Vendor account passwords can only be changed by the <strong>Food Court Administrator</strong> for security purposes.
                            </p>
                        </div>
                    </div>
                </div>

                <p class="text-sm text-[#1A1A1A]/70">
                    <strong>Email:</strong> {{ vendorEmail }}
                </p>
            </div>

            <!-- Modal Actions -->
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
