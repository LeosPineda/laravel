<script setup lang="ts">
import { ref } from 'vue';
import { useForm, usePage, router } from '@inertiajs/vue3';
import CustomerLayout from '@/layouts/customer/CustomerLayout.vue';
import { User, Mail, Lock, LogOut, Eye, EyeOff, Trash2, AlertTriangle, X } from 'lucide-vue-next';

const page = usePage();
const user = page.props.auth?.user as { name: string; email: string } | undefined;

const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    passwordForm.put('/customer/profile/password', {
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset();
        },
    });
};

const logout = () => {
    router.post('/logout');
};

// Delete Account
const showDeleteModal = ref(false);
const deleteConfirmation = ref('');
const deleting = ref(false);

const deleteAccount = () => {
    if (deleteConfirmation.value !== 'DELETE') {
        alert('Please type DELETE to confirm');
        return;
    }
    deleting.value = true;
    router.delete('/customer/profile', {
        onSuccess: () => {
            showDeleteModal.value = false;
        },
        onError: () => {
            deleting.value = false;
        },
    });
};
</script>

<template>
    <CustomerLayout>
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <h1 class="text-2xl font-bold text-[#1A1A1A] mb-6">Profile</h1>

            <!-- User Info -->
            <div class="bg-white rounded-xl shadow-sm border border-[#E0E0E0] p-6 mb-6">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center border-2 border-[#FF6B35]">
                        <User class="w-8 h-8 text-[#1A1A1A]" />
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-[#1A1A1A]">{{ user?.name }}</h2>
                        <p class="text-gray-500 flex items-center gap-1">
                            <Mail class="w-4 h-4" />
                            {{ user?.email }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Change Password -->
            <div class="bg-white rounded-xl shadow-sm border border-[#E0E0E0] p-6 mb-6">
                <h3 class="text-lg font-semibold text-[#1A1A1A] mb-4 flex items-center gap-2">
                    <Lock class="w-5 h-5" />
                    Change Password
                </h3>

                <form @submit.prevent="updatePassword" class="space-y-4">
                    <!-- Current Password -->
                    <div>
                        <label class="block text-sm font-medium text-[#1A1A1A] mb-2">Current Password</label>
                        <div class="relative">
                            <input
                                v-model="passwordForm.current_password"
                                :type="showCurrentPassword ? 'text' : 'password'"
                                class="w-full px-4 py-2 border border-[#E0E0E0] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#FF6B35] pr-10"
                            />
                            <button
                                type="button"
                                @click="showCurrentPassword = !showCurrentPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400"
                            >
                                <Eye v-if="!showCurrentPassword" class="w-5 h-5" />
                                <EyeOff v-else class="w-5 h-5" />
                            </button>
                        </div>
                        <p v-if="passwordForm.errors.current_password" class="text-red-500 text-sm mt-1">
                            {{ passwordForm.errors.current_password }}
                        </p>
                    </div>

                    <!-- New Password -->
                    <div>
                        <label class="block text-sm font-medium text-[#1A1A1A] mb-2">New Password</label>
                        <div class="relative">
                            <input
                                v-model="passwordForm.password"
                                :type="showNewPassword ? 'text' : 'password'"
                                class="w-full px-4 py-2 border border-[#E0E0E0] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#FF6B35] pr-10"
                            />
                            <button
                                type="button"
                                @click="showNewPassword = !showNewPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400"
                            >
                                <Eye v-if="!showNewPassword" class="w-5 h-5" />
                                <EyeOff v-else class="w-5 h-5" />
                            </button>
                        </div>
                        <p v-if="passwordForm.errors.password" class="text-red-500 text-sm mt-1">
                            {{ passwordForm.errors.password }}
                        </p>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label class="block text-sm font-medium text-[#1A1A1A] mb-2">Confirm New Password</label>
                        <div class="relative">
                            <input
                                v-model="passwordForm.password_confirmation"
                                :type="showConfirmPassword ? 'text' : 'password'"
                                class="w-full px-4 py-2 border border-[#E0E0E0] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#FF6B35] pr-10"
                            />
                            <button
                                type="button"
                                @click="showConfirmPassword = !showConfirmPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400"
                            >
                                <Eye v-if="!showConfirmPassword" class="w-5 h-5" />
                                <EyeOff v-else class="w-5 h-5" />
                            </button>
                        </div>
                    </div>

                    <button
                        type="submit"
                        :disabled="passwordForm.processing"
                        class="w-full py-3 bg-[#FF6B35] text-white font-medium rounded-lg hover:bg-orange-600 transition-colors disabled:opacity-50"
                    >
                        {{ passwordForm.processing ? 'Updating...' : 'Update Password' }}
                    </button>
                </form>
            </div>

            <!-- Logout -->
            <button
                @click="logout"
                class="w-full py-3 bg-white border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition-colors flex items-center justify-center gap-2 mb-4"
            >
                <LogOut class="w-5 h-5" />
                Logout
            </button>

            <!-- Delete Account -->
            <div class="bg-red-50 rounded-xl border border-red-200 p-6">
                <h3 class="text-lg font-semibold text-red-700 mb-2 flex items-center gap-2">
                    <AlertTriangle class="w-5 h-5" />
                    Danger Zone
                </h3>
                <p class="text-sm text-red-600 mb-4">
                    Once you delete your account, there is no going back. Please be certain.
                </p>
                <button
                    @click="showDeleteModal = true"
                    class="w-full py-3 bg-red-500 text-white font-medium rounded-xl hover:bg-red-600 transition-colors flex items-center justify-center gap-2"
                >
                    <Trash2 class="w-5 h-5" />
                    Delete Account
                </button>
            </div>
        </div>

        <!-- Delete Account Confirmation Modal -->
        <Teleport to="body">
            <div
                v-if="showDeleteModal"
                class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
                @click.self="showDeleteModal = false"
            >
                <div class="bg-white w-full max-w-md rounded-2xl overflow-hidden shadow-xl">
                    <!-- Header -->
                    <div class="bg-red-500 text-white p-6 text-center relative">
                        <button
                            @click="showDeleteModal = false"
                            class="absolute top-3 right-3 p-1 hover:bg-white/20 rounded-full"
                        >
                            <X class="w-5 h-5" />
                        </button>
                        <AlertTriangle class="w-12 h-12 mx-auto mb-3" />
                        <h2 class="text-xl font-bold">Delete Account?</h2>
                    </div>

                    <!-- Content -->
                    <div class="p-6">
                        <p class="text-gray-600 text-center mb-6">
                            This action <strong>cannot be undone</strong>. This will permanently delete your account and remove all your data.
                        </p>

                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Type <strong class="text-red-500">DELETE</strong> to confirm
                            </label>
                            <input
                                v-model="deleteConfirmation"
                                type="text"
                                placeholder="Type DELETE"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500"
                            />
                        </div>

                        <div class="flex gap-3">
                            <button
                                @click="showDeleteModal = false"
                                class="flex-1 py-3 bg-gray-100 text-gray-700 font-medium rounded-xl hover:bg-gray-200 transition-colors"
                            >
                                Cancel
                            </button>
                            <button
                                @click="deleteAccount"
                                :disabled="deleting || deleteConfirmation !== 'DELETE'"
                                class="flex-1 py-3 bg-red-500 text-white font-medium rounded-xl hover:bg-red-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                            >
                                <Trash2 class="w-4 h-4" />
                                {{ deleting ? 'Deleting...' : 'Delete' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </CustomerLayout>
</template>
