<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';

defineProps<{
    status?: string;
}>();

const form = useForm({});

const submit = () => {
    form.post('/email/verification-notification');
};

const logout = () => {
    router.post('/logout');
};
</script>

<template>
    <Head title="Verify Email" />

    <div class="min-h-screen flex items-center justify-center bg-[#F5F5F5] px-4">
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-[#1A1A1A]">Food Court</h1>
                <p class="text-[#1A1A1A]/60 mt-2">Verify your email</p>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-[#E0E0E0] p-8">
                <p class="text-sm text-[#1A1A1A]/70 mb-6">
                    Thanks for signing up! Before getting started, please verify your email address by clicking on the link we just emailed to you.
                </p>

                <div
                    v-if="status === 'verification-link-sent'"
                    class="mb-6 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700"
                >
                    A new verification link has been sent to your email address.
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full py-3 px-4 bg-[#FF6B35] text-white font-semibold rounded-lg hover:bg-[#FF6B35]/90 disabled:opacity-50"
                    >
                        Resend Verification Email
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <button
                        @click="logout"
                        class="text-sm text-[#1A1A1A]/60 hover:text-[#FF6B35]"
                    >
                        Log Out
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
