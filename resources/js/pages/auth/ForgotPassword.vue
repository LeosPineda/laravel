<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';

defineProps<{
    status?: string;
}>();

const form = useForm({
    email: '',
});

const submit = () => {
    form.post('/forgot-password');
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
</template>
