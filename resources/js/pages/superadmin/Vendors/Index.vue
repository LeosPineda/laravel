<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import SuperadminLayout from '@/layouts/superadmin/SuperadminLayout.vue';

interface User {
    id: number;
    name: string;
    email: string;
    is_active: boolean;
}

interface Vendor {
    id: number;
    brand_name: string;
    is_active: boolean;
    created_at: string;
    user: User;
}

interface PaginatedVendors {
    data: Vendor[];
    links: any[];
    current_page: number;
    last_page: number;
}

defineProps<{
    vendors: PaginatedVendors;
}>();

// Delete modal state
const showDeleteModal = ref(false);
const vendorToDelete = ref<{ id: number; brandName: string } | null>(null);

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const logout = () => {
    router.post('/logout');
};

const toggleActive = (vendorId: number) => {
    router.patch(`/superadmin/vendors/${vendorId}/toggle-active`);
};

const openDeleteModal = (vendorId: number, brandName: string) => {
    vendorToDelete.value = { id: vendorId, brandName };
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    vendorToDelete.value = null;
};

const confirmDelete = () => {
    if (vendorToDelete.value) {
        router.delete(`/superadmin/vendors/${vendorToDelete.value.id}`);
        closeDeleteModal();
    }
};
</script>

<template>
    <Head title="Manage Vendors" />

    <SuperadminLayout>
            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-2xl font-bold text-[#1A1A1A]">Vendors</h1>
                    <p class="text-[#1A1A1A]/60 mt-1">Manage all food court vendors</p>
                </div>
                <Link
                    href="/superadmin/vendors/create"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#FF6B35] text-white font-semibold rounded-xl hover:bg-[#FF6B35]/90 transition-colors shadow-lg shadow-[#FF6B35]/25"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Vendor
                </Link>
            </div>

            <!-- Vendors Grid/Table -->
            <div class="bg-white rounded-2xl border border-[#E0E0E0] overflow-hidden">
                <!-- Mobile Cards -->
                <div class="md:hidden divide-y divide-[#E0E0E0]">
                    <div v-for="vendor in vendors.data" :key="vendor.id" class="p-4">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-[#FF6B35]/10 rounded-xl flex items-center justify-center">
                                    <span class="text-lg font-bold text-[#FF6B35]">{{ vendor.brand_name.charAt(0) }}</span>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-[#1A1A1A]">{{ vendor.brand_name }}</h3>
                                    <p class="text-sm text-[#1A1A1A]/60">{{ vendor.user.name }}</p>
                                </div>
                            </div>
                            <span
                                :class="[
                                    'px-2 py-1 text-xs font-semibold rounded-full',
                                    vendor.is_active
                                        ? 'bg-green-100 text-green-700'
                                        : 'bg-red-100 text-red-700'
                                ]"
                            >
                                {{ vendor.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <div class="text-sm text-[#1A1A1A]/60 mb-3">
                            {{ vendor.user.email }}
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-[#1A1A1A]/60">{{ formatDate(vendor.created_at) }}</span>
                            <div class="flex gap-2">
                                <Link
                                    :href="`/superadmin/vendors/${vendor.id}/edit`"
                                    class="px-3 py-1.5 text-xs font-medium bg-blue-100 text-blue-700 rounded-lg"
                                >
                                    Edit
                                </Link>
                                <button
                                    @click="toggleActive(vendor.id)"
                                    :class="[
                                        'px-3 py-1.5 text-xs font-medium rounded-lg transition-colors',
                                        vendor.is_active
                                            ? 'bg-yellow-100 text-yellow-700'
                                            : 'bg-green-100 text-green-700'
                                    ]"
                                >
                                    {{ vendor.is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                                <button
                                    @click="openDeleteModal(vendor.id, vendor.brand_name)"
                                    class="px-3 py-1.5 text-xs font-medium bg-red-100 text-red-700 rounded-lg"
                                >
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Desktop Table -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-[#F5F5F5]">
                            <tr>
                                <th class="text-left px-6 py-4 text-xs font-semibold text-[#1A1A1A]/70 uppercase tracking-wider">
                                    Brand
                                </th>
                                <th class="text-left px-6 py-4 text-xs font-semibold text-[#1A1A1A]/70 uppercase tracking-wider">
                                    Owner
                                </th>
                                <th class="text-left px-6 py-4 text-xs font-semibold text-[#1A1A1A]/70 uppercase tracking-wider">
                                    Email
                                </th>
                                <th class="text-left px-6 py-4 text-xs font-semibold text-[#1A1A1A]/70 uppercase tracking-wider">
                                    Created
                                </th>
                                <th class="text-center px-6 py-4 text-xs font-semibold text-[#1A1A1A]/70 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="text-center px-6 py-4 text-xs font-semibold text-[#1A1A1A]/70 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E0E0E0]">
                            <tr v-for="vendor in vendors.data" :key="vendor.id" class="hover:bg-[#F5F5F5]/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-[#FF6B35]/10 rounded-xl flex items-center justify-center">
                                            <span class="text-sm font-bold text-[#FF6B35]">{{ vendor.brand_name.charAt(0) }}</span>
                                        </div>
                                        <span class="font-medium text-[#1A1A1A]">{{ vendor.brand_name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-[#1A1A1A]">
                                    {{ vendor.user.name }}
                                </td>
                                <td class="px-6 py-4 text-[#1A1A1A]/70">
                                    {{ vendor.user.email }}
                                </td>
                                <td class="px-6 py-4 text-[#1A1A1A]/70">
                                    {{ formatDate(vendor.created_at) }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span
                                        :class="[
                                            'inline-flex px-3 py-1 text-xs font-semibold rounded-full',
                                            vendor.is_active
                                                ? 'bg-green-100 text-green-700'
                                                : 'bg-red-100 text-red-700'
                                        ]"
                                    >
                                        {{ vendor.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <Link
                                            :href="`/superadmin/vendors/${vendor.id}/edit`"
                                            class="px-3 py-1.5 text-xs font-medium bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors"
                                        >
                                            Edit
                                        </Link>
                                        <button
                                            @click="toggleActive(vendor.id)"
                                            :class="[
                                                'px-3 py-1.5 text-xs font-medium rounded-lg transition-colors',
                                                vendor.is_active
                                                    ? 'bg-yellow-100 text-yellow-700 hover:bg-yellow-200'
                                                    : 'bg-green-100 text-green-700 hover:bg-green-200'
                                            ]"
                                        >
                                            {{ vendor.is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                        <button
                                            @click="openDeleteModal(vendor.id, vendor.brand_name)"
                                            class="px-3 py-1.5 text-xs font-medium bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div v-if="vendors.data.length === 0" class="px-6 py-16 text-center">
                    <div class="flex flex-col items-center">
                        <div class="w-20 h-20 bg-[#F5F5F5] rounded-2xl flex items-center justify-center mb-4">
                            <svg class="w-10 h-10 text-[#1A1A1A]/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-[#1A1A1A] mb-2">No vendors yet</h3>
                        <p class="text-[#1A1A1A]/60 mb-4">Get started by adding your first vendor</p>
                        <Link
                            href="/superadmin/vendors/create"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#FF6B35] text-white font-semibold rounded-xl hover:bg-[#FF6B35]/90 transition-colors"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add Your First Vendor
                        </Link>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="vendors.last_page > 1" class="px-6 py-4 border-t border-[#E0E0E0] flex items-center justify-center gap-2">
                    <template v-for="link in vendors.links" :key="link.label">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            :class="[
                                'px-4 py-2 text-sm font-medium rounded-lg transition-colors',
                                link.active
                                    ? 'bg-[#FF6B35] text-white'
                                    : 'bg-[#F5F5F5] text-[#1A1A1A] hover:bg-[#E0E0E0]'
                            ]"
                            v-html="link.label"
                        />
                        <span
                            v-else
                            class="px-4 py-2 text-sm text-[#1A1A1A]/30"
                            v-html="link.label"
                        />
                    </template>
                </div>
            </div>

        <!-- Delete Confirmation Modal -->
        <Teleport to="body">
            <div
                v-if="showDeleteModal"
                class="fixed inset-0 z-50 flex items-center justify-center p-4"
            >
                <!-- Backdrop -->
                <div
                    class="absolute inset-0 bg-black/50 backdrop-blur-sm"
                    @click="closeDeleteModal"
                />

                <!-- Modal -->
                <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 animate-in zoom-in-95 duration-200">
                    <!-- Warning Icon -->
                    <div class="w-14 h-14 mx-auto mb-4 bg-red-100 rounded-full flex items-center justify-center">
                        <svg class="w-7 h-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>

                    <!-- Content -->
                    <h3 class="text-xl font-bold text-[#1A1A1A] text-center mb-2">
                        Delete Vendor
                    </h3>
                    <p class="text-[#1A1A1A]/70 text-center mb-6">
                        Are you sure you want to delete
                        <span class="font-semibold text-[#1A1A1A]">"{{ vendorToDelete?.brandName }}"</span>?
                        This action cannot be undone.
                    </p>

                    <!-- Actions -->
                    <div class="flex gap-3">
                        <button
                            @click="closeDeleteModal"
                            class="flex-1 px-4 py-3 text-sm font-semibold text-[#1A1A1A] bg-[#F5F5F5] rounded-xl hover:bg-[#E0E0E0] transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            @click="confirmDelete"
                            class="flex-1 px-4 py-3 text-sm font-semibold text-white bg-red-600 rounded-xl hover:bg-red-700 transition-colors"
                        >
                            Delete Vendor
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </SuperadminLayout>
</template>
