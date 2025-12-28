<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const showPassword = ref(false);
const showConfirmPassword = ref(false);

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post('/register', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Create Account" />

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
                    Join the<br>
                    <span class="text-[#FF6B35]">Food Court family</span>
                </h1>
                <p class="text-[#1A1A1A]/70 text-lg leading-relaxed mb-10">
                    Create your account and start ordering from all your favorite food vendors in one place.
                </p>

                <!-- Benefits -->
                <div class="space-y-5">
                    <div class="flex items-center gap-5 p-5 bg-white rounded-2xl border border-[#E0E0E0] shadow-sm">
                        <div class="w-14 h-14 bg-[#FF6B35]/10 rounded-xl flex items-center justify-center shrink-0">
                            <svg class="w-7 h-7 text-[#FF6B35]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <p class="text-[#1A1A1A] text-lg">Browse multiple food vendors</p>
                    </div>
                    <div class="flex items-center gap-5 p-5 bg-white rounded-2xl border border-[#E0E0E0] shadow-sm">
                        <div class="w-14 h-14 bg-[#FF6B35]/10 rounded-xl flex items-center justify-center shrink-0">
                            <svg class="w-7 h-7 text-[#FF6B35]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <p class="text-[#1A1A1A] text-lg">Track your orders in real-time</p>
                    </div>
                    <div class="flex items-center gap-5 p-5 bg-white rounded-2xl border border-[#E0E0E0] shadow-sm">
                        <div class="w-14 h-14 bg-[#FF6B35]/10 rounded-xl flex items-center justify-center shrink-0">
                            <svg class="w-7 h-7 text-[#FF6B35]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <p class="text-[#1A1A1A] text-lg">Skip the lines, pick up when ready</p>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="relative z-10">
                <p class="text-[#1A1A1A]/40 text-base">&copy; 2024 4Rodz Food Court</p>
            </div>
        </div>

        <!-- Right Side - Register Form (60% white) -->
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
                    <h2 class="text-3xl sm:text-4xl font-bold text-[#1A1A1A]">Create account</h2>
                    <p class="text-[#1A1A1A]/60 mt-3 text-lg">Start ordering your favorite food</p>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-base font-medium text-[#1A1A1A] mb-3">
                            Full name
                        </label>
                        <input
                            id="name"
                            type="text"
                            v-model="form.name"
                            required
                            autofocus
                            autocomplete="name"
                            placeholder="Enter your name"
                            class="w-full px-5 py-4 border border-[#E0E0E0] rounded-xl bg-[#F5F5F5] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#FF6B35]/20 focus:border-[#FF6B35] transition-all text-[#1A1A1A] placeholder:text-[#1A1A1A]/40 text-lg"
                            :class="{ 'border-red-400 focus:ring-red-400/20 focus:border-red-400': form.errors.name }"
                        />
                        <p v-if="form.errors.name" class="mt-2 text-sm text-red-600">
                            {{ form.errors.name }}
                        </p>
                    </div>

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
                        <label for="password" class="block text-base font-medium text-[#1A1A1A] mb-3">
                            Password
                        </label>
                        <div class="relative">
                            <input
                                id="password"
                                :type="showPassword ? 'text' : 'password'"
                                v-model="form.password"
                                required
                                autocomplete="new-password"
                                placeholder="Create a password"
                                class="w-full px-5 py-4 pr-14 border border-[#E0E0E0] rounded-xl bg-[#F5F5F5] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#FF6B35]/20 focus:border-[#FF6B35] transition-all text-[#1A1A1A] placeholder:text-[#1A1A1A]/40 text-lg"
                                :class="{ 'border-red-400 focus:ring-red-400/20 focus:border-red-400': form.errors.password }"
                            />
                            <button type="button" @click="showPassword = !showPassword" class="absolute right-4 top-1/2 -translate-y-1/2 text-[#1A1A1A]/40 hover:text-[#1A1A1A]/70 transition-colors">
                                <svg v-if="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
                                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="mt-2 text-sm text-red-600">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="password_confirmation" class="block text-base font-medium text-[#1A1A1A] mb-3">
                            Confirm password
                        </label>
                        <div class="relative">
                            <input
                                id="password_confirmation"
                                :type="showConfirmPassword ? 'text' : 'password'"
                                v-model="form.password_confirmation"
                                required
                                autocomplete="new-password"
                                placeholder="Confirm your password"
                                class="w-full px-5 py-4 pr-14 border border-[#E0E0E0] rounded-xl bg-[#F5F5F5] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#FF6B35]/20 focus:border-[#FF6B35] transition-all text-[#1A1A1A] placeholder:text-[#1A1A1A]/40 text-lg"
                                :class="{ 'border-red-400 focus:ring-red-400/20 focus:border-red-400': form.errors.password_confirmation }"
                            />
                            <button type="button" @click="showConfirmPassword = !showConfirmPassword" class="absolute right-4 top-1/2 -translate-y-1/2 text-[#1A1A1A]/40 hover:text-[#1A1A1A]/70 transition-colors">
                                <svg v-if="showConfirmPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
                                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                            </button>
                        </div>
                        <p v-if="form.errors.password_confirmation" class="mt-2 text-sm text-red-600">
                            {{ form.errors.password_confirmation }}
                        </p>
                    </div>

                    <!-- Submit Button (10% orange - accent) -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full py-5 px-6 bg-[#FF6B35] text-white font-semibold rounded-xl hover:bg-[#e55f2f] active:scale-[0.99] focus:outline-none focus:ring-4 focus:ring-[#FF6B35]/20 transition-all disabled:opacity-60 disabled:cursor-not-allowed mt-4 text-lg"
                    >
                        <span v-if="form.processing" class="flex items-center justify-center gap-2">
                            <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                            </svg>
                            Creating account...
                        </span>
                        <span v-else>Create account</span>
                    </button>
                </form>

                <!-- Login Link -->
                <div class="mt-10 text-center">
                    <p class="text-[#1A1A1A]/60 text-lg">
                        Already have an account?
                        <a href="/login" class="font-semibold text-[#FF6B35] hover:underline">
                            Sign in
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
