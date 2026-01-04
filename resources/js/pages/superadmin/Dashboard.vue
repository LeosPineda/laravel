<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import SuperadminLayout from '@/layouts/superadmin/SuperadminLayout.vue';

interface TopVendor {
    id: number;
    brand_name: string;
    brand_logo: string | null;
    is_active: boolean;
    user_name: string | null;
    user_email: string | null;
    total_orders: number;
    total_revenue: number;
    net_profit: number;
}

interface Statistics {
    vendor_count: number;
    active_vendor_count: number;
    customer_count: number;
    rent_per_vendor: number;
    total_rent: number;
    total_orders: number;
    completed_orders: number;
    pending_orders: number;
    total_revenue: number;
    today_orders: number;
    today_revenue: number;
    monthly_orders: number;
    monthly_revenue: number;
    top_vendors: TopVendor[];
    recent_orders: Array<{
        id: number;
        order_number: string;
        vendor_name: string | null;
        customer_name: string | null;
        total_amount: number;
        status: string;
        created_at: string;
    }>;
}

defineProps<{
    statistics: Statistics;
}>();

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
    }).format(amount);
};
</script>

<template>
    <Head title="Superadmin Dashboard" />

    <SuperadminLayout>
        <!-- Page Header -->
        <div class="mb-6 sm:mb-8">
            <h1 class="text-xl sm:text-2xl font-bold text-[#1A1A1A]">Dashboard</h1>
            <p class="text-sm sm:text-base text-[#1A1A1A]/60 mt-1">Overview of your food court performance</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 mb-6 sm:mb-8">
            <!-- Vendor Count -->
            <div class="bg-white rounded-xl sm:rounded-2xl border border-[#E0E0E0] p-4 sm:p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between mb-3 sm:mb-4">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-blue-100 rounded-lg sm:rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <span class="text-xs font-medium text-blue-600 bg-blue-100 px-2 py-1 rounded-full">Active</span>
                </div>
                <p class="text-xs sm:text-sm font-medium text-[#1A1A1A]/60">Total Vendors</p>
                <p class="text-2xl sm:text-3xl font-bold text-[#1A1A1A] mt-1">{{ statistics.vendor_count }}</p>
            </div>

            <!-- Rent Per Vendor -->
            <div class="bg-white rounded-xl sm:rounded-2xl border border-[#E0E0E0] p-4 sm:p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between mb-3 sm:mb-4">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-purple-100 rounded-lg sm:rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <span class="text-xs font-medium text-purple-600 bg-purple-100 px-2 py-1 rounded-full">Monthly</span>
                </div>
                <p class="text-xs sm:text-sm font-medium text-[#1A1A1A]/60">Rent Per Vendor</p>
                <p class="text-2xl sm:text-3xl font-bold text-[#1A1A1A] mt-1">{{ formatCurrency(statistics.rent_per_vendor) }}</p>
            </div>

            <!-- Total Rent -->
            <div class="bg-gradient-to-br from-[#FF6B35] to-[#FF8B5C] rounded-xl sm:rounded-2xl p-4 sm:p-6 hover:shadow-lg transition-shadow sm:col-span-2 lg:col-span-1">
                <div class="flex items-center justify-between mb-3 sm:mb-4">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-white/20 rounded-lg sm:rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <span class="text-xs font-medium text-white bg-white/20 px-2 py-1 rounded-full">Total Income</span>
                </div>
                <p class="text-xs sm:text-sm font-medium text-white/80">Total Rent Income</p>
                <p class="text-2xl sm:text-3xl font-bold text-white mt-1">{{ formatCurrency(statistics.total_rent) }}</p>
            </div>
        </div>

        <!-- Top Vendors Table -->
        <div class="bg-white rounded-xl sm:rounded-2xl border border-[#E0E0E0] overflow-hidden">
            <div class="p-4 sm:p-6 border-b border-[#E0E0E0] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <h3 class="text-base sm:text-lg font-semibold text-[#1A1A1A]">Top Performing Vendors</h3>
                    <p class="text-xs sm:text-sm text-[#1A1A1A]/60 mt-1">Ranked by total revenue</p>
                </div>
                <Link
                    href="/superadmin/vendors"
                    class="text-sm font-medium text-[#FF6B35] hover:text-[#FF6B35]/80 transition-colors"
                >
                    View all →
                </Link>
            </div>

            <!-- Mobile Card View -->
            <div class="sm:hidden divide-y divide-[#E0E0E0]">
                <div v-for="(vendor, index) in statistics.top_vendors" :key="vendor.id" class="p-4">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-3">
                            <div :class="[
                                'w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold',
                                index === 0 ? 'bg-yellow-100 text-yellow-700' :
                                index === 1 ? 'bg-gray-100 text-gray-600' :
                                index === 2 ? 'bg-orange-100 text-orange-700' :
                                'bg-[#F5F5F5] text-[#1A1A1A]/60'
                            ]">
                                {{ index + 1 }}
                            </div>
                            <div class="w-10 h-10 bg-[#FF6B35]/10 rounded-xl flex items-center justify-center">
                                <span class="text-sm font-bold text-[#FF6B35]">{{ vendor.brand_name.charAt(0) }}</span>
                            </div>
                            <span class="font-medium text-[#1A1A1A]">{{ vendor.brand_name }}</span>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-sm">
                        <div>
                            <span class="text-[#1A1A1A]/60">Revenue:</span>
                            <span class="ml-1 font-medium">{{ formatCurrency(vendor.total_revenue) }}</span>
                        </div>
                        <div>
                            <span class="text-[#1A1A1A]/60">Profit:</span>
                            <span :class="['ml-1 font-semibold', vendor.net_profit >= 0 ? 'text-green-600' : 'text-red-600']">
                                {{ formatCurrency(vendor.net_profit) }}
                            </span>
                        </div>
                    </div>
                </div>
                <div v-if="statistics.top_vendors.length === 0" class="p-8 text-center">
                    <div class="w-16 h-16 bg-[#F5F5F5] rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-[#1A1A1A]/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <p class="text-[#1A1A1A]/50 font-medium">No vendors yet</p>
                    <Link href="/superadmin/vendors/create" class="mt-2 text-sm text-[#FF6B35] font-medium hover:text-[#FF6B35]/80">
                        Add your first vendor →
                    </Link>
                </div>
            </div>

            <!-- Desktop Table View -->
            <div class="hidden sm:block overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-[#F5F5F5]">
                        <tr>
                            <th class="text-left px-6 py-4 text-xs font-semibold text-[#1A1A1A]/70 uppercase tracking-wider">
                                Rank
                            </th>
                            <th class="text-left px-6 py-4 text-xs font-semibold text-[#1A1A1A]/70 uppercase tracking-wider">
                                Vendor
                            </th>
                            <th class="text-right px-6 py-4 text-xs font-semibold text-[#1A1A1A]/70 uppercase tracking-wider">
                                Total Revenue
                            </th>
                            <th class="text-right px-6 py-4 text-xs font-semibold text-[#1A1A1A]/70 uppercase tracking-wider">
                                Net Profit
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E0E0E0]">
                        <tr v-for="(vendor, index) in statistics.top_vendors" :key="vendor.id" class="hover:bg-[#F5F5F5]/50 transition-colors">
                            <td class="px-6 py-4">
                                <div :class="[
                                    'w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold',
                                    index === 0 ? 'bg-yellow-100 text-yellow-700' :
                                    index === 1 ? 'bg-gray-100 text-gray-600' :
                                    index === 2 ? 'bg-orange-100 text-orange-700' :
                                    'bg-[#F5F5F5] text-[#1A1A1A]/60'
                                ]">
                                    {{ index + 1 }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-[#FF6B35]/10 rounded-xl flex items-center justify-center">
                                        <span class="text-sm font-bold text-[#FF6B35]">{{ vendor.brand_name.charAt(0) }}</span>
                                    </div>
                                    <span class="font-medium text-[#1A1A1A]">{{ vendor.brand_name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right font-medium text-[#1A1A1A]">
                                {{ formatCurrency(vendor.total_revenue) }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span :class="[
                                    'font-semibold',
                                    vendor.net_profit >= 0 ? 'text-green-600' : 'text-red-600'
                                ]">
                                    {{ formatCurrency(vendor.net_profit) }}
                                </span>
                            </td>
                        </tr>
                        <tr v-if="statistics.top_vendors.length === 0">
                            <td colspan="4" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 bg-[#F5F5F5] rounded-2xl flex items-center justify-center mb-4">
                                        <svg class="w-8 h-8 text-[#1A1A1A]/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                    </div>
                                    <p class="text-[#1A1A1A]/50 font-medium">No vendors yet</p>
                                    <Link href="/superadmin/vendors/create" class="mt-2 text-sm text-[#FF6B35] font-medium hover:text-[#FF6B35]/80">
                                        Add your first vendor →
                                    </Link>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </SuperadminLayout>
</template>
