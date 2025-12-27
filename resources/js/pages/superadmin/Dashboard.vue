<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';

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

const logout = () => {
    router.post('/logout');
};
</script>

<template>
    <Head title="Superadmin Dashboard" />

    <div class="min-h-screen bg-[#F5F5F5]">
        <!-- Header -->
        <header class="bg-white border-b border-[#E0E0E0] sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center gap-8">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 bg-[#FF6B35] rounded-xl flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                                </svg>
                            </div>
                            <span class="text-lg font-bold text-[#1A1A1A] hidden sm:block">Food Court Admin</span>
                        </div>
                        <nav class="hidden md:flex items-center gap-1">
                            <Link
                                href="/superadmin/dashboard"
                                class="px-4 py-2 text-sm font-medium text-white bg-[#FF6B35] rounded-lg"
                            >
                                Dashboard
                            </Link>
                            <Link
                                href="/superadmin/vendors"
                                class="px-4 py-2 text-sm font-medium text-[#1A1A1A]/70 hover:text-[#1A1A1A] hover:bg-[#F5F5F5] rounded-lg transition-colors"
                            >
                                Vendors
                            </Link>
                        </nav>
                    </div>
                    <button
                        @click="logout"
                        class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-[#1A1A1A]/70 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span class="hidden sm:inline">Logout</span>
                    </button>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-[#1A1A1A]">Dashboard</h1>
                <p class="text-[#1A1A1A]/60 mt-1">Overview of your food court performance</p>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Vendor Count -->
                <div class="bg-white rounded-2xl border border-[#E0E0E0] p-6 hover:shadow-lg transition-shadow">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <span class="text-xs font-medium text-blue-600 bg-blue-100 px-2 py-1 rounded-full">Active</span>
                    </div>
                    <p class="text-sm font-medium text-[#1A1A1A]/60">Total Vendors</p>
                    <p class="text-3xl font-bold text-[#1A1A1A] mt-1">{{ statistics.vendor_count }}</p>
                </div>

                <!-- Rent Per Vendor -->
                <div class="bg-white rounded-2xl border border-[#E0E0E0] p-6 hover:shadow-lg transition-shadow">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <span class="text-xs font-medium text-purple-600 bg-purple-100 px-2 py-1 rounded-full">Monthly</span>
                    </div>
                    <p class="text-sm font-medium text-[#1A1A1A]/60">Rent Per Vendor</p>
                    <p class="text-3xl font-bold text-[#1A1A1A] mt-1">{{ formatCurrency(statistics.rent_per_vendor) }}</p>
                </div>

                <!-- Total Rent -->
                <div class="bg-gradient-to-br from-[#FF6B35] to-[#FF8B5C] rounded-2xl p-6 hover:shadow-lg transition-shadow">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <span class="text-xs font-medium text-white bg-white/20 px-2 py-1 rounded-full">Total Income</span>
                    </div>
                    <p class="text-sm font-medium text-white/80">Total Rent Income</p>
                    <p class="text-3xl font-bold text-white mt-1">{{ formatCurrency(statistics.total_rent) }}</p>
                </div>
            </div>

            <!-- Top Vendors Table -->
            <div class="bg-white rounded-2xl border border-[#E0E0E0] overflow-hidden">
                <div class="p-6 border-b border-[#E0E0E0] flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-[#1A1A1A]">Top Performing Vendors</h3>
                        <p class="text-sm text-[#1A1A1A]/60 mt-1">Ranked by total revenue</p>
                    </div>
                    <Link
                        href="/superadmin/vendors"
                        class="text-sm font-medium text-[#FF6B35] hover:text-[#FF6B35]/80 transition-colors"
                    >
                        View all →
                    </Link>
                </div>
                <div class="overflow-x-auto">
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
        </main>
    </div>
</template>
