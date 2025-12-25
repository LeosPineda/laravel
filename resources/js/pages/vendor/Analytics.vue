<script setup lang="ts">
import { ref } from 'vue';
import VendorLayout from '@/layouts/vendor/VendorLayout.vue';
import { TrendingUp, DollarSign, ShoppingBag, Package } from 'lucide-vue-next';

interface Analytics {
    totalSales: number; totalOrders: number; averageOrder: number;
    salesByDay: { date: string; total: number }[];
    topProducts: { name: string; quantity: number; revenue: number }[];
}

const props = defineProps<{ analytics: Analytics; period: string }>();
const selectedPeriod = ref(props.period);

const formatPrice = (p: number) => new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(p);
const maxSales = Math.max(...props.analytics.salesByDay.map(d => d.total), 1);
</script>

<template>
    <VendorLayout>
        <template #title>Analytics</template>
        <div class="space-y-6">
            <!-- Period Selector -->
            <div class="flex gap-2">
                <a v-for="p in ['day', 'week', 'month']" :key="p" :href="`/vendor/analytics?period=${p}`"
                   class="px-4 py-2 rounded-lg transition-colors"
                   :class="selectedPeriod === p ? 'bg-[#FF6B35] text-white' : 'bg-white border border-[#E0E0E0]'">
                    {{ p.charAt(0).toUpperCase() + p.slice(1) }}
                </a>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white rounded-xl p-6 border border-[#E0E0E0]">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-lg bg-green-50 flex items-center justify-center"><DollarSign class="w-6 h-6 text-green-600" /></div>
                        <div><p class="text-2xl font-bold text-[#1A1A1A]">{{ formatPrice(analytics.totalSales) }}</p><p class="text-gray-500">Total Sales</p></div>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-6 border border-[#E0E0E0]">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-lg bg-orange-50 flex items-center justify-center"><ShoppingBag class="w-6 h-6 text-[#FF6B35]" /></div>
                        <div><p class="text-2xl font-bold text-[#1A1A1A]">{{ analytics.totalOrders }}</p><p class="text-gray-500">Total Orders</p></div>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-6 border border-[#E0E0E0]">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center"><TrendingUp class="w-6 h-6 text-blue-600" /></div>
                        <div><p class="text-2xl font-bold text-[#1A1A1A]">{{ formatPrice(analytics.averageOrder) }}</p><p class="text-gray-500">Avg. Order</p></div>
                    </div>
                </div>
            </div>

            <!-- Sales Chart (Simple Bar) -->
            <div class="bg-white rounded-xl p-6 border border-[#E0E0E0]">
                <h3 class="font-semibold text-[#1A1A1A] mb-4">Sales Overview</h3>
                <div class="flex items-end gap-2 h-40">
                    <div v-for="day in analytics.salesByDay" :key="day.date" class="flex-1 flex flex-col items-center gap-2">
                        <div class="w-full bg-[#FF6B35] rounded-t transition-all" :style="{ height: `${(day.total / maxSales) * 100}%`, minHeight: day.total > 0 ? '4px' : '0' }"></div>
                        <span class="text-xs text-gray-500">{{ day.date.slice(-2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Top Products -->
            <div class="bg-white rounded-xl border border-[#E0E0E0]">
                <div class="p-4 border-b border-[#E0E0E0]"><h3 class="font-semibold text-[#1A1A1A]">Top Selling Products</h3></div>
                <div v-if="analytics.topProducts.length > 0" class="divide-y divide-[#E0E0E0]">
                    <div v-for="(product, i) in analytics.topProducts" :key="product.name" class="p-4 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-full bg-[#F5F5F5] flex items-center justify-center font-medium text-[#1A1A1A]">{{ i + 1 }}</span>
                            <span class="font-medium">{{ product.name }}</span>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-[#FF6B35]">{{ formatPrice(product.revenue) }}</p>
                            <p class="text-sm text-gray-500">{{ product.quantity }} sold</p>
                        </div>
                    </div>
                </div>
                <div v-else class="p-8 text-center text-gray-500">No sales data yet</div>
            </div>
        </div>
    </VendorLayout>
</template>
