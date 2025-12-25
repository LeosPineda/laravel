<script setup lang="ts">
import { ref } from 'vue';
import CustomerLayout from '@/layouts/customer/CustomerLayout.vue';
import { Bell, Clock, Store, X, ChefHat, CheckCircle, Package, FileText, Download } from 'lucide-vue-next';

interface OrderItem {
    name: string;
    quantity: number;
    price: number;
    addons: string[];
}

interface Order {
    order_number: string;
    vendor_name: string;
    table_number: string;
    items: OrderItem[];
    total: number;
    status: string;
}

interface Notification {
    id: number;
    type: string;
    title: string;
    message: string;
    time: string;
    isNew: boolean;
    order?: Order;
}

const props = defineProps<{
    notifications: Notification[];
}>();

const selectedNotification = ref<Notification | null>(null);

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(price);
};

const getStatusConfig = (type: string) => {
    switch (type) {
        case 'order_ready':
            return {
                bgColor: 'bg-green-50',
                borderColor: 'border-green-400',
                iconBg: 'bg-green-100',
                iconColor: 'text-green-600',
                icon: Package,
                statusBg: 'bg-green-100 text-green-700',
            };
        case 'order_preparing':
            return {
                bgColor: 'bg-yellow-50',
                borderColor: 'border-yellow-400',
                iconBg: 'bg-yellow-100',
                iconColor: 'text-yellow-600',
                icon: ChefHat,
                statusBg: 'bg-yellow-100 text-yellow-700',
            };
        case 'order_completed':
            return {
                bgColor: 'bg-gray-50',
                borderColor: 'border-gray-300',
                iconBg: 'bg-gray-100',
                iconColor: 'text-gray-600',
                icon: CheckCircle,
                statusBg: 'bg-gray-100 text-gray-700',
            };
        default:
            return {
                bgColor: 'bg-orange-50',
                borderColor: 'border-orange-400',
                iconBg: 'bg-orange-100',
                iconColor: 'text-orange-600',
                icon: Bell,
                statusBg: 'bg-orange-100 text-orange-700',
            };
    }
};

const getStatusLabel = (status: string) => {
    switch (status) {
        case 'ready':
            return '🎉 Ready for Pickup';
        case 'preparing':
            return '👨‍🍳 Being Prepared';
        case 'completed':
            return '✅ Completed';
        default:
            return status;
    }
};

const downloadReceipt = (order: Order) => {
    // Create a simple text receipt for download
    let receipt = `
╔══════════════════════════════════╗
║         ORDER RECEIPT            ║
╠══════════════════════════════════╣
║ ${order.vendor_name.padEnd(32)} ║
║ Order #${order.order_number.padEnd(24)} ║
║ Table: ${order.table_number.padEnd(24)} ║
╠══════════════════════════════════╣
`;
    order.items.forEach((item) => {
        receipt += `║ ${item.quantity}x ${item.name.padEnd(27)} ║\n`;
        if (item.addons.length) {
            receipt += `║    + ${item.addons.join(', ').slice(0, 24).padEnd(24)} ║\n`;
        }
        receipt += `║ ${formatPrice(item.price * item.quantity).padStart(32)} ║\n`;
    });
    receipt += `╠══════════════════════════════════╣
║ TOTAL: ${formatPrice(order.total).padStart(24)} ║
╚══════════════════════════════════╝
`;

    const blob = new Blob([receipt], { type: 'text/plain' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `receipt-${order.order_number}.txt`;
    a.click();
    URL.revokeObjectURL(url);
};
</script>

<template>
    <CustomerLayout>
        <div class="max-w-lg mx-auto px-4 py-6">
            <h1 class="text-xl font-bold text-[#1A1A1A] mb-4">Order Updates</h1>

            <!-- Notifications List -->
            <div v-if="notifications.length > 0" class="space-y-3">
                <div
                    v-for="notification in notifications"
                    :key="notification.id"
                    class="rounded-xl shadow-sm border-2 overflow-hidden transition-all"
                    :class="[
                        getStatusConfig(notification.type).bgColor,
                        notification.isNew ? getStatusConfig(notification.type).borderColor : 'border-transparent',
                    ]"
                >
                    <div class="p-4">
                        <div class="flex gap-3">
                            <!-- Status Icon -->
                            <div
                                class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0"
                                :class="getStatusConfig(notification.type).iconBg"
                            >
                                <component
                                    :is="getStatusConfig(notification.type).icon"
                                    class="w-6 h-6"
                                    :class="getStatusConfig(notification.type).iconColor"
                                />
                            </div>

                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <h3 class="font-semibold text-[#1A1A1A]">{{ notification.title }}</h3>
                                    <span class="text-xs text-gray-400 whitespace-nowrap">{{ notification.time }}</span>
                                </div>
                                <p class="text-sm text-gray-600 mt-0.5">{{ notification.message }}</p>
                                <div v-if="notification.order" class="flex items-center gap-2 mt-2">
                                    <span
                                        class="text-xs px-2 py-1 rounded-full font-medium"
                                        :class="getStatusConfig(notification.type).statusBg"
                                    >
                                        {{ notification.order.vendor_name }}
                                    </span>
                                    <span class="text-xs text-gray-500">#{{ notification.order.order_number }}</span>
                                </div>

                                <!-- Action Buttons -->
                                <div v-if="notification.order" class="flex gap-2 mt-3">
                                    <button
                                        @click="selectedNotification = notification"
                                        class="flex items-center gap-1 px-3 py-1.5 bg-white text-[#1A1A1A] rounded-lg hover:bg-gray-100 transition-colors text-sm font-medium border border-gray-200"
                                    >
                                        <FileText class="w-4 h-4" />
                                        View Receipt
                                    </button>
                                    <button
                                        @click="downloadReceipt(notification.order)"
                                        class="flex items-center gap-1 px-3 py-1.5 bg-[#FF6B35] text-white rounded-lg hover:bg-orange-600 transition-colors text-sm font-medium"
                                    >
                                        <Download class="w-4 h-4" />
                                        Download
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-16">
                <Bell class="w-16 h-16 text-gray-300 mx-auto mb-4" />
                <h3 class="text-lg font-medium text-[#1A1A1A]">No notifications yet</h3>
                <p class="text-gray-500 mt-1">You'll see order updates here</p>
            </div>
        </div>

        <!-- Receipt Modal -->
        <Teleport to="body">
            <div
                v-if="selectedNotification?.order"
                class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
                @click.self="selectedNotification = null"
            >
                <div class="bg-white w-full max-w-sm rounded-2xl overflow-hidden shadow-xl">
                    <!-- Receipt Header -->
                    <div class="bg-[#FF6B35] text-white p-6 text-center relative">
                        <button
                            @click="selectedNotification = null"
                            class="absolute top-3 right-3 p-1 hover:bg-white/20 rounded-full"
                        >
                            <X class="w-5 h-5" />
                        </button>
                        <Store class="w-12 h-12 mx-auto mb-3" />
                        <h2 class="text-2xl font-bold">{{ selectedNotification.order.vendor_name }}</h2>
                        <p class="text-white/80 text-sm mt-1">Order #{{ selectedNotification.order.order_number }}</p>
                    </div>

                    <!-- Status Badge -->
                    <div class="flex justify-center -mt-3">
                        <span
                            class="px-4 py-1.5 rounded-full text-sm font-medium shadow-sm bg-white"
                            :class="getStatusConfig(selectedNotification.type).statusBg"
                        >
                            {{ getStatusLabel(selectedNotification.order.status) }}
                        </span>
                    </div>

                    <!-- Receipt Content -->
                    <div class="p-5">
                        <!-- Table Info -->
                        <div class="text-center mb-5 pb-4 border-b border-dashed border-gray-300">
                            <p class="text-sm text-gray-500">Table Number</p>
                            <p class="text-3xl font-bold text-[#1A1A1A]">{{ selectedNotification.order.table_number }}</p>
                        </div>

                        <!-- Items -->
                        <div class="mb-4">
                            <div class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-3">Order Items</div>
                            <div class="space-y-3">
                                <div
                                    v-for="(item, index) in selectedNotification.order.items"
                                    :key="index"
                                    class="bg-gray-50 rounded-lg p-3"
                                >
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2">
                                                <span class="w-6 h-6 bg-[#FF6B35] text-white text-xs rounded-full flex items-center justify-center font-bold">
                                                    {{ item.quantity }}
                                                </span>
                                                <span class="font-medium text-[#1A1A1A]">{{ item.name }}</span>
                                            </div>
                                            <p v-if="item.addons?.length" class="text-xs text-[#FF6B35] mt-1 ml-8">
                                                + {{ item.addons.join(', ') }}
                                            </p>
                                        </div>
                                        <span class="font-semibold text-[#1A1A1A]">{{ formatPrice(item.price * item.quantity) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Divider -->
                        <div class="border-t-2 border-dashed border-gray-300 my-4"></div>

                        <!-- Total -->
                        <div class="flex justify-between items-center mb-4">
                            <span class="text-xl font-bold text-[#1A1A1A]">Total</span>
                            <span class="text-2xl font-bold text-[#FF6B35]">{{ formatPrice(selectedNotification.order.total) }}</span>
                        </div>

                        <!-- Footer -->
                        <div class="text-center pt-4 border-t border-gray-100">
                            <p class="text-sm text-gray-400">Thank you for your order!</p>
                            <p class="text-xs text-gray-400 mt-1">{{ new Date().toLocaleDateString('en-PH', { dateStyle: 'full' }) }}</p>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="p-4 pt-0 flex gap-3">
                        <button
                            @click="downloadReceipt(selectedNotification.order)"
                            class="flex-1 py-3 bg-[#FF6B35] text-white font-medium rounded-xl hover:bg-orange-600 transition-colors flex items-center justify-center gap-2"
                        >
                            <Download class="w-4 h-4" />
                            Download Receipt
                        </button>
                        <button
                            @click="selectedNotification = null"
                            class="flex-1 py-3 bg-gray-100 text-[#1A1A1A] font-medium rounded-xl hover:bg-gray-200 transition-colors"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </CustomerLayout>
</template>
