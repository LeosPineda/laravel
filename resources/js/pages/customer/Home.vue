<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import CustomerLayout from '@/layouts/customer/CustomerLayout.vue';
import { Search, Store, ChevronRight } from 'lucide-vue-next';

interface Vendor {
    id: number;
    brand_name: string;
    brand_image: string | null;
    is_active: boolean;
    products_count?: number;
}

const props = defineProps<{
    vendors: Vendor[];
}>();

const searchQuery = ref('');

const filteredVendors = computed(() => {
    if (!searchQuery.value) return props.vendors;
    return props.vendors.filter((vendor) =>
        vendor.brand_name.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
});
</script>

<template>
    <CustomerLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl md:text-3xl font-bold text-[#1A1A1A]">
                    Welcome to 4Rodz Food Court 🍽️
                </h1>
                <p class="text-gray-500 mt-1">Browse our vendors and order your favorite food</p>
            </div>

            <!-- Search Bar -->
            <div class="relative mb-6">
                <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search vendors..."
                    class="w-full pl-12 pr-4 py-3 rounded-xl border border-[#E0E0E0] bg-white focus:outline-none focus:ring-2 focus:ring-[#FF6B35] focus:border-transparent transition-all"
                />
            </div>

            <!-- Vendors Grid -->
            <div v-if="filteredVendors.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 md:gap-6">
                <Link
                    v-for="vendor in filteredVendors"
                    :key="vendor.id"
                    :href="`/customer/vendor/${vendor.id}`"
                    class="group bg-white rounded-xl shadow-sm border border-[#E0E0E0] overflow-hidden hover:shadow-md hover:border-[#FF6B35] transition-all"
                >
                    <!-- Vendor Image -->
                    <div class="relative h-40 bg-[#F5F5F5] overflow-hidden">
                        <img
                            v-if="vendor.brand_image"
                            :src="`/storage/${vendor.brand_image}`"
                            :alt="vendor.brand_name"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                        />
                        <div
                            v-else
                            class="w-full h-full flex items-center justify-center"
                        >
                            <Store class="w-16 h-16 text-gray-300" />
                        </div>
                    </div>

                    <!-- Vendor Info -->
                    <div class="p-4">
                        <div class="flex items-center justify-between">
                            <h3 class="font-semibold text-lg text-[#1A1A1A] group-hover:text-[#FF6B35] transition-colors">
                                {{ vendor.brand_name }}
                            </h3>
                            <ChevronRight class="w-5 h-5 text-gray-400 group-hover:text-[#FF6B35] transition-colors" />
                        </div>
                        <p v-if="vendor.products_count !== undefined" class="text-sm text-gray-500 mt-1">
                            {{ vendor.products_count }} products available
                        </p>
                    </div>
                </Link>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-16">
                <Store class="w-16 h-16 text-gray-300 mx-auto mb-4" />
                <h3 class="text-lg font-medium text-[#1A1A1A]">No vendors found</h3>
                <p class="text-gray-500 mt-1">
                    {{ searchQuery ? 'Try a different search term' : 'Check back later for available vendors' }}
                </p>
            </div>
        </div>
    </CustomerLayout>
</template>
