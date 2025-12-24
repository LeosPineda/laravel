<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const recovery = ref(false);

const form = useForm({
    code: '',
    recovery_code: '',
});

const submit = () => {
    form.post('/two-factor-challenge');
};
</script>

<template>
    <Head title="Two-Factor Authentication" />

    <div class="min-h-screen flex items-center justify-center bg-[#F5F5F5] px-4">
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-[#1A1A1A]">Food Court</h1>
                <p class="text-[#1A1A1A]/60 mt-2">Two-Factor Authentication</p>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-[#E0E0E0] p-8">
                <p class="text-sm text-[#1A1A1A]/70 mb-6">
                    {{ recovery ? 'Please enter your recovery code.' : 'Please enter the authentication code from your authenticator app.' }}
                </p>

                <form @submit.prevent="submit" class="space-y-6">
                    <div v-if="!recovery">
                        <label for="code" class="block text-sm font-medium text-[#1A1A1A] mb-2">
                            Code
                        </label>
                        <input
                            id="code"
                            type="text"
                            v-model="form.code"
                            inputmode="numeric"
                            autofocus
                            autocomplete="one-time-code"
                            class="w-full px-4 py-3 border border-[#E0E0E0] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#FF6B35]"
                            :class="{ 'border-red-500': form.errors.code }"
                        />
                        <p v-if="form.errors.code" class="mt-2 text-sm text-red-600">
                            {{ form.errors.code }}
                        </p>
                    </div>

                    <div v-else>
                        <label for="recovery_code" class="block text-sm font-medium text-[#1A1A1A] mb-2">
                            Recovery Code
                        </label>
                        <input
                            id="recovery_code"
                            type="text"
                            v-model="form.recovery_code"
                            autocomplete="one-time-code"
                            class="w-full px-4 py-3 border border-[#E0E0E0] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#FF6B35]"
                            :class="{ 'border-red-500': form.errors.recovery_code }"
                        />
                        <p v-if="form.errors.recovery_code" class="mt-2 text-sm text-red-600">
                            {{ form.errors.recovery_code }}
                        </p>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full py-3 px-4 bg-[#FF6B35] text-white font-semibold rounded-lg hover:bg-[#FF6B35]/90 disabled:opacity-50"
                    >
                        {{ recovery ? 'Log In' : 'Log In' }}
                    </button>

                    <button
                        type="button"
                        @click="recovery = !recovery"
                        class="w-full text-sm text-[#FF6B35] hover:text-[#FF6B35]/80"
                    >
                        {{ recovery ? 'Use authentication code' : 'Use a recovery code' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>
