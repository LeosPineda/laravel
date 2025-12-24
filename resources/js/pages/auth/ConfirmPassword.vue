<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post('/confirm-password', {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Confirm Password" />

    <div class="min-h-screen flex items-center justify-center bg-[#F5F5F5] px-4">
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-[#1A1A1A]">Food Court</h1>
                <p class="text-[#1A1A1A]/60 mt-2">Confirm your password</p>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-[#E0E0E0] p-8">
                <p class="text-sm text-[#1A1A1A]/70 mb-6">
                    This is a secure area. Please confirm your password before continuing.
                </p>

                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label for="password" class="block text-sm font-medium text-[#1A1A1A] mb-2">
                            Password
                        </label>
                        <input
                            id="password"
                            type="password"
                            v-model="form.password"
                            required
                            autofocus
                            autocomplete="current-password"
                            class="w-full px-4 py-3 border border-[#E0E0E0] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#FF6B35] focus:border-transparent"
                            :class="{ 'border-red-500': form.errors.password }"
                        />
                        <p v-if="form.errors.password" class="mt-2 text-sm text-red-600">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full py-3 px-4 bg-[#FF6B35] text-white font-semibold rounded-lg hover:bg-[#FF6B35]/90 disabled:opacity-50"
                    >
                        Confirm
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>
