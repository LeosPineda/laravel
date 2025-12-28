<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps<{
    status?: string;
}>();

const showPassword = ref(false);

const form = useForm({
    email: '',
    password: '',
});

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Sign In" />

    <div class="min-h-screen flex flex-col lg:flex-row bg-white">
        <!-- Left Side - Branding (Full Width) -->
        <div class="hidden lg:flex lg:w-1/2 bg-[#F5F5F5] p-12 xl:p-16 flex-col justify-between relative">
            <!-- Decorative Elements -->
            <div class="absolute top-0 right-0 w-80 h-80 bg-[#FF6B35]/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
            <div class="absolute bottom-0 left-0 w-64 h-64 bg-[#FF6B35]/5 rounded-full translate-y-1/2 -translate-x-1/2"></div>

            <!-- Content -->
            <div class="relative z-10">
                <!-- Logo -->
                <div class="flex items-center gap-4">
                    <img src="/fast-food.png" alt="4Rodz" class="w-16 h-16 rounded-xl" />
                    <span class="text-2xl font-bold text-[#1A1A1A]">4Rodz Food Court</span>
                </div>
            </div>

            <!-- Main Content -->
            <div class="relative z-10 max-w-lg ml-8 xl:ml-12">
                <h1 class="text-4xl xl:text-5xl font-bold text-[#1A1A1A] leading-tight mb-6">
                    Your favorite food,<br>
                    <span class="text-[#FF6B35]">ready when you are</span>
                </h1>
                <p class="text-[#1A1A1A]/70 text-lg leading-relaxed mb-10">
                    Skip the lines. Order from multiple vendors, track your order in real-time, and pick up when it's ready.
                </p>

                <!-- Features -->
                <div class="space-y-5">
                    <div class="flex items-center gap-5 p-5 bg-white rounded-2xl border border-[#E0E0E0] shadow-sm">
                        <div class="w-14 h-14 bg-[#FF6B35]/10 rounded-xl flex items-center justify-center shrink-0">
                            <svg class="w-7 h-7 text-[#FF6B35]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-[#1A1A1A] text-lg">Mobile Ordering</h3>
                            <p class="text-[#1A1A1A]/60 text-sm">Order from anywhere in the food court</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-5 p-5 bg-white rounded-2xl border border-[#E0E0E0] shadow-sm">
                        <div class="w-14 h-14 bg-[#FF6B35]/10 rounded-xl flex items-center justify-center shrink-0">
                            <svg class="w-7 h-7 text-[#FF6B35]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-[#1A1A1A] text-lg">Fast Pickup</h3>
                            <p class="text-[#1A1A1A]/60 text-sm">Get notified when your order is ready</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-5 p-5 bg-white rounded-2xl border border-[#E0E0E0] shadow-sm">
                        <div class="w-14 h-14 bg-[#FF6B35]/10 rounded-xl flex items-center justify-center shrink-0">
                            <svg class="w-7 h-7 text-[#FF6B35]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-[#1A1A1A] text-lg">Multiple Vendors</h3>
                            <p class="text-[#1A1A1A]/60 text-sm">All your favorites in one place</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="relative z-10">
                <p class="text-[#1A1A1A]/40 text-base">&copy; 2024 4Rodz Food Court</p>
            </div>
        </div>

        <!-- Right Side - Login Form (60% white) -->
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

                <!-- Welcome Text -->
                <div class="mb-10">
                    <h2 class="text-3xl sm:text-4xl font-bold text-[#1A1A1A]">Welcome back</h2>
                    <p class="text-[#1A1A1A]/60 mt-3 text-lg">Sign in to continue ordering</p>
                </div>

                <!-- Status Message -->
                <div
                    v-if="status"
                    class="mb-6 p-4 bg-green-50 border border-green-100 rounded-xl text-center text-sm text-green-700"
                >
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Email -->
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

                    <!-- Password -->
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <label for="password" class="block text-base font-medium text-[#1A1A1A]">
                                Password
                            </label>
                            <a
                                href="/forgot-password"
                                class="text-base font-medium text-[#FF6B35] hover:underline"
                            >
                                Forgot password?
                            </a>
                        </div>
                        <div class="relative">
                            <input
                                id="password"
                                :type="showPassword ? 'text' : 'password'"
                                v-model="form.password"
                                required
                                autocomplete="current-password"
                                placeholder="Enter your password"
                                class="w-full px-5 py-4 pr-14 border border-[#E0E0E0] rounded-xl bg-[#F5F5F5] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#FF6B35]/20 focus:border-[#FF6B35] transition-all text-[#1A1A1A] placeholder:text-[#1A1A1A]/40 text-lg"
                                :class="{ 'border-red-400 focus:ring-red-400/20 focus:border-red-400': form.errors.password }"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-[#1A1A1A]/40 hover:text-[#1A1A1A]/70 transition-colors"
                            >
                                <svg v-if="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="mt-2 text-sm text-red-600">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <!-- Submit Button (10% orange - accent) -->
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
                            Signing in...
                        </span>
                        <span v-else>Sign in</span>
                    </button>
                </form>

                <!-- Register Link -->
                <div class="mt-10 text-center">
                    <p class="text-[#1A1A1A]/60 text-lg">
                        New to 4Rodz?
                        <a href="/register" class="font-semibold text-[#FF6B35] hover:underline">
                            Create an account
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
