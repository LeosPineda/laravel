<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
    email: string;
    token: string;
}>();

const showPassword = ref(false);
const showPasswordConfirmation = ref(false);
const resetSuccess = ref(false);

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post('/reset-password', {
        onSuccess: () => {
            resetSuccess.value = true;
            // Redirect to login after 3 seconds
            setTimeout(() => {
                router.visit('/login');
            }, 3000);
        },
        onFinish: () => {
            if (!resetSuccess.value) {
                form.reset('password', 'password_confirmation');
            }
        },
    });
};

const togglePasswordVisibility = () => {
    showPassword.value = !showPassword.value;
};

const togglePasswordConfirmationVisibility = () => {
    showPasswordConfirmation.value = !showPasswordConfirmation.value;
};
</script>

<template>
    <Head title="Reset Password" />

    <div class="min-h-screen flex items-center justify-center bg-[#F5F5F5] px-4">
        <div class="w-full max-w-md">
            <!-- Logo/Brand -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-[#1A1A1A]">Food Court</h1>
                <p class="text-[#1A1A1A]/60 mt-2">Create new password</p>
            </div>

            <!-- Success Message -->
            <div v-if="resetSuccess" class="bg-white rounded-xl shadow-sm border border-green-200 p-8 text-center">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-green-600 mb-2">Password Reset Successful!</h2>
                <p class="text-gray-600 mb-4">Your password has been changed successfully.</p>
                <p class="text-sm text-gray-500">Redirecting to login page...</p>
                <div class="mt-4">
                    <a href="/login" class="text-[#FF6B35] hover:underline font-medium">Go to Login →</a>
                </div>
            </div>

            <!-- Form Card -->
            <div v-else class="bg-white rounded-xl shadow-sm border border-[#E0E0E0] p-8">
                <form @submit.prevent="submit" class="space-y-5">
                    <!-- Hidden email field -->
                    <input type="hidden" v-model="form.email" />

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-[#1A1A1A] mb-2">
                            New Password
                        </label>
                        <div class="relative">
                            <input
                                id="password"
                                :type="showPassword ? 'text' : 'password'"
                                v-model="form.password"
                                required
                                autofocus
                                autocomplete="new-password"
                                placeholder="Enter new password"
                                class="w-full px-4 py-3 pr-12 border border-[#E0E0E0] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#FF6B35] focus:border-transparent transition-all text-[#1A1A1A] bg-white placeholder:text-gray-400"
                                :class="{ 'border-red-500': form.errors.password }"
                            />
                            <button
                                type="button"
                                @click="togglePasswordVisibility"
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-[#1A1A1A]/60 hover:text-[#1A1A1A] transition-colors"
                            >
                                <svg v-if="showPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
                                </svg>
                                <svg v-else class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="mt-2 text-sm text-red-600">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-[#1A1A1A] mb-2">
                            Confirm New Password
                        </label>
                        <div class="relative">
                            <input
                                id="password_confirmation"
                                :type="showPasswordConfirmation ? 'text' : 'password'"
                                v-model="form.password_confirmation"
                                required
                                autocomplete="new-password"
                                placeholder="Confirm new password"
                                class="w-full px-4 py-3 pr-12 border border-[#E0E0E0] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#FF6B35] focus:border-transparent transition-all text-[#1A1A1A] bg-white placeholder:text-gray-400"
                                :class="{ 'border-red-500': form.errors.password_confirmation }"
                            />
                            <button
                                type="button"
                                @click="togglePasswordConfirmationVisibility"
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-[#1A1A1A]/60 hover:text-[#1A1A1A] transition-colors"
                            >
                                <svg v-if="showPasswordConfirmation" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
                                </svg>
                                <svg v-else class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                        <p v-if="form.errors.password_confirmation" class="mt-2 text-sm text-red-600">
                            {{ form.errors.password_confirmation }}
                        </p>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full py-3 px-4 bg-[#FF6B35] text-white font-semibold rounded-lg hover:bg-[#FF6B35]/90 focus:outline-none focus:ring-2 focus:ring-[#FF6B35] focus:ring-offset-2 transition-all disabled:opacity-50 disabled:cursor-not-allowed mt-2"
                    >
                        <span v-if="form.processing" class="flex items-center justify-center gap-2">
                            <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                            </svg>
                            Resetting...
                        </span>
                        <span v-else>Reset Password</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>
