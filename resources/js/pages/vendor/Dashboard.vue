<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import VendorLayout from '@/layouts/vendor/VendorLayout.vue';
import { ShoppingBag, DollarSign, Clock, TrendingUp, ArrowRight } from 'lucide-vue-next';

interface Stats {
    todayOrders: number;
    todayRevenue: number;
    pendingOrders: number;
    totalOrders: number;
}

interface RecentOrder {
    id: number;
    order_number: string;
    status: string;
    total_amount: number;
    table_number: string | null;
    created_at: string;
}

const props = defineProps<{
    stats: Stats;
    recentOrders: RecentOrder[];
}>();

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(price);
};

const formatTime = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit' });
};

const getStatusColor = (status: string) => {
    switch (status) {
        case 'pending': return 'bg-yellow-100 text-yellow-700';
        case 'accepted': return 'bg-blue-100 text-blue-700';
        case 'ready_for_pickup': return 'bg-green-100 text-green-700';
        case 'completed': return 'bg-gray-100 text-gray-700';
        case 'cancelled': return 'bg-red-100 text-red-700';
        default: return 'bg-gray-100 text-gray-700';
    }
};

const getStatusLabel = (status: string) => {
    switch (status) {
        case 'pending': return 'Pending';
        case 'accepted': return 'Accepted';
        case 'ready_for_pickup': return 'Ready';
        case 'completed': return 'Completed';
        case 'cancelled': return 'Cancelled';
        default: return status;
    }
};
</script>

<template>
    <VendorLayout>
        <template #title>Dashboard</template>

        <div class="space-y-6">
            <!-- Stats Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl p-4 border border-[#E0E0E0]">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-orange-50 flex items-center justify-center">
                            <ShoppingBag class="w-5 h-5 text-[#FF6B35]" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-[#1A1A1A]">{{ stats.todayOrders }}</p>
                            <p class="text-sm text-gray-500">Today's Orders</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-4 border border-[#E0E0E0]">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center">
                            <DollarSign class="w-5 h-5 text-green-600" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-[#1A1A1A]">{{ formatPrice(stats.todayRevenue) }}</p>
                            <p class="text-sm text-gray-500">Today's Revenue</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-4 border border-[#E0E0E0]">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-yellow-50 flex items-center justify-center">
                            <Clock class="w-5 h-5 text-yellow-600" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-[#1A1A1A]">{{ stats.pendingOrders }}</p>
                            <p class="text-sm text-gray-500">Pending Orders</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-4 border border-[#E0E0E0]">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center">
                            <TrendingUp class="w-5 h-5 text-blue-600" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-[#1A1A1A]">{{ stats.totalOrders }}</p>
                            <p class="text-sm text-gray-500">Total Orders</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="bg-white rounded-xl border border-[#E0E0E0]">
                <div class="flex items-center justify-between p-4 border-b border-[#E0E0E0]">
                    <h2 class="font-semibold text-[#1A1A1A]">Recent Orders</h2>
                    <Link href="/vendor/orders" class="text-[#FF6B35] text-sm flex items-center gap-1 hover:underline">
                        View All <ArrowRight class="w-4 h-4" />
                    </Link>
                </div>

                <div v-if="recentOrders.length > 0" class="divide-y divide-[#E0E0E0]">
                    <div v-for="order in recentOrders" :key="order.id" class="p-4 flex items-center justify-between">
                        <div>
                            <p class="font-medium text-[#1A1A1A]">#{{ order.order_number }}</p>
                            <p class="text-sm text-gray-500">
                                Table {{ order.table_number || 'N/A' }} • {{ formatTime(order.created_at) }}
                            </p>
                        </div>
                        <div class="text-right">
                            <span
                                class="inline-block px-2 py-1 rounded text-xs font-medium"
                                :class="getStatusColor(order.status)"
                            >
                                {{ getStatusLabel(order.status) }}
                            </span>
                            <p class="text-sm font-medium text-[#1A1A1A] mt-1">{{ formatPrice(order.total_amount) }}</p>
                        </div>
                    </div>
                </div>

                <div v-else class="p-8 text-center text-gray-500">
                    No orders yet today
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <Link
                    href="/vendor/orders"
                    class="bg-[#FF6B35] text-white rounded-xl p-4 text-center hover:bg-orange-600 transition-colors"
                >
                    <ShoppingBag class="w-6 h-6 mx-auto mb-2" />
                    <span class="text-sm font-medium">Manage Orders</span>
                </Link>
                <Link
                    href="/vendor/products/create"
                    class="bg-white border border-[#E0E0E0] rounded-xl p-4 text-center hover:border-[#FF6B35] transition-colors"
                >
                    <span class="text-2xl">➕</span>
                    <p class="text-sm font-medium text-[#1A1A1A] mt-1">Add Product</p>
                </Link>
                <Link
                    href="/vendor/analytics"
                    class="bg-white border border-[#E0E0E0] rounded-xl p-4 text-center hover:border-[#FF6B35] transition-colors"
                >
                    <span class="text-2xl">📊</span>
                    <p class="text-sm font-medium text-[#1A1A1A] mt-1">View Analytics</p>
                </Link>
                <Link
                    href="/vendor/qr"
                    class="bg-white border border-[#E0E0E0] rounded-xl p-4 text-center hover:border-[#FF6B35] transition-colors"
                >
                    <span class="text-2xl">📱</span>
                    <p class="text-sm font-medium text-[#1A1A1A] mt-1">QR Code</p>
                </Link>
            </div>
        </div>
    </VendorLayout>
</template>
