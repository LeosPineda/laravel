<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import VendorLayout from '@/layouts/vendor/VendorLayout.vue';
import { Check, X, Trash2, Image, Bell, Receipt, AlertCircle, Eye } from 'lucide-vue-next';

interface OrderItem {
    id: number;
    quantity: number;
    unit_price: number;
    total_price: number;
    selected_addons: { name: string; price: number }[] | null;
    product: { name: string };
}

interface Order {
    id: number;
    order_number: string;
    status: string;
    total_amount: number;
    payment_method: string;
    table_number: string | null;
    special_instructions: string | null;
    payment_proof_url: string | null;
    created_at: string;
    items: OrderItem[];
}

// Hardcoded data for now - will be dynamic later
const incomingOrders = ref<Order[]>([
    {
        id: 1,
        order_number: '12345',
        status: 'pending',
        total_amount: 450,
        payment_method: 'cashier',
        table_number: '5',
        special_instructions: 'Please make it well-done',
        payment_proof_url: null,
        created_at: '2025-12-25T14:30:00',
        items: [
            {
                id: 1,
                quantity: 2,
                unit_price: 150,
                total_price: 300,
                selected_addons: [{ name: 'Extra Cheese', price: 20 }],
                product: { name: 'Cheeseburger' }
            },
            {
                id: 2,
                quantity: 1,
                unit_price: 80,
                total_price: 80,
                selected_addons: null,
                product: { name: 'French Fries' }
            }
        ]
    },
    {
        id: 2,
        order_number: '12346',
        status: 'pending',
        total_amount: 180,
        payment_method: 'qr_code',
        table_number: '3',
        special_instructions: null,
        payment_proof_url: 'payment-proofs/qr-payment-12346.jpg',
        created_at: '2025-12-25T14:25:00',
        items: [
            {
                id: 3,
                quantity: 1,
                unit_price: 180,
                total_price: 180,
                selected_addons: null,
                product: { name: 'Grilled Chicken' }
            }
        ]
    }
]);

const completedOrders = ref<Order[]>([
    {
        id: 3,
        order_number: '12344',
        status: 'accepted',
        total_amount: 320,
        payment_method: 'cashier',
        table_number: '2',
        special_instructions: 'No onions please',
        payment_proof_url: null,
        created_at: '2025-12-25T14:15:00',
        items: [
            {
                id: 4,
                quantity: 1,
                unit_price: 200,
                total_price: 200,
                selected_addons: [{ name: 'Bacon', price: 50 }],
                product: { name: 'Double Cheeseburger' }
            },
            {
                id: 5,
                quantity: 1,
                unit_price: 70,
                total_price: 70,
                selected_addons: null,
                product: { name: 'Coke' }
            }
        ]
    },
    {
        id: 4,
        order_number: '12343',
        status: 'completed',
        total_amount: 280,
        payment_method: 'qr_code',
        table_number: '1',
        special_instructions: null,
        payment_proof_url: 'payment-proofs/qr-payment-12343.jpg',
        created_at: '2025-12-25T14:00:00',
        items: [
            {
                id: 6,
                quantity: 1,
                unit_price: 280,
                total_price: 280,
                selected_addons: null,
                product: { name: 'Fish Fillet Combo' }
            }
        ]
    },
    {
        id: 5,
        order_number: '12342',
        status: 'declined',
        total_amount: 150,
        payment_method: 'cashier',
        table_number: '4',
        special_instructions: 'Extra spicy',
        payment_proof_url: null,
        created_at: '2025-12-25T13:45:00',
        items: [
            {
                id: 7,
                quantity: 1,
                unit_price: 150,
                total_price: 150,
                selected_addons: [{ name: 'Extra Sauce', price: 20 }],
                product: { name: 'Spicy Wings' }
            }
        ]
    }
]);

const undoAction = ref<{ orderId: number; action: string; timer: ReturnType<typeof setTimeout> } | null>(null);
const showProofModal = ref(false);
const selectedProofUrl = ref<string | null>(null);
const showOrderModal = ref(false);
const showReceiptModal = ref(false);
const selectedOrder = ref<Order | null>(null);
const viewingOrder = ref<Order | null>(null);
const selectedOrders = ref<number[]>([]);
const currentView = ref<'incoming' | 'history'>('incoming'); // Toggle between states

const formatPrice = (price: number) => new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(price);
const formatTime = (d: string) => new Date(d).toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit' });
const formatDate = (d: string) => new Date(d).toLocaleDateString('en-PH', { month: 'short', day: 'numeric' });

const acceptOrder = (orderId: number) => {
    clearUndo();
    undoAction.value = {
        orderId,
        action: 'accept',
        timer: setTimeout(() => {
            // Hardcoded action for now
            const order = incomingOrders.value.find(o => o.id === orderId);
            if (order) {
                order.status = 'accepted';
                completedOrders.value.push(order);
                incomingOrders.value = incomingOrders.value.filter(o => o.id !== orderId);
            }
            undoAction.value = null;
        }, 5000),
    };
};

const declineOrder = (orderId: number) => {
    clearUndo();
    undoAction.value = {
        orderId,
        action: 'decline',
        timer: setTimeout(() => {
            // Hardcoded action for now
            const order = incomingOrders.value.find(o => o.id === orderId);
            if (order) {
                order.status = 'declined';
                completedOrders.value.push(order);
                incomingOrders.value = incomingOrders.value.filter(o => o.id !== orderId);
            }
            undoAction.value = null;
        }, 5000),
    };
};

const clearUndo = () => {
    if (undoAction.value) {
        clearTimeout(undoAction.value.timer);
        undoAction.value = null;
    }
};

const openOrderDetails = (order: Order) => {
    viewingOrder.value = order;
    showOrderModal.value = true;
};

const openReceipt = (order: Order) => {
    selectedOrder.value = order;
    showReceiptModal.value = true;
};

const openProof = (url: string) => {
    selectedProofUrl.value = url;
    showProofModal.value = true;
};

const calculateSubtotal = (order: Order) => {
    return order.items.reduce((sum, item) => sum + item.total_price, 0);
};

const deleteOrders = () => {
    if (selectedOrders.value.length === 0) return;
    if (confirm(`Delete ${selectedOrders.value.length} selected orders?`)) {
        // Hardcoded deletion for now
        completedOrders.value = completedOrders.value.filter(order => !selectedOrders.value.includes(order.id));
        selectedOrders.value = [];
    }
};

const selectAllOrders = () => {
    if (selectedOrders.value.length === completedOrders.value.length) {
        selectedOrders.value = [];
    } else {
        selectedOrders.value = completedOrders.value.map(order => order.id);
    }
};

const toggleOrderSelection = (orderId: number) => {
    const index = selectedOrders.value.indexOf(orderId);
    if (index > -1) {
        selectedOrders.value.splice(index, 1);
    } else {
        selectedOrders.value.push(orderId);
    }
};
</script>

<template>
    <VendorLayout>
        <template #title>Order Management</template>

        <div class="space-y-6">
            <!-- Stats Bar -->
            <div class="grid grid-cols-2 gap-4">
                <div
                    @click="currentView = 'incoming'"
                    class="border rounded-xl p-4 text-center cursor-pointer transition-all"
                    :class="currentView === 'incoming' ? 'bg-yellow-50 border-yellow-300' : 'bg-gray-50 border-gray-200 hover:bg-yellow-50'"
                >
                    <Bell class="w-6 h-6 mx-auto mb-2" :class="currentView === 'incoming' ? 'text-yellow-600' : 'text-gray-600'" />
                    <p class="text-3xl font-bold" :class="currentView === 'incoming' ? 'text-yellow-700' : 'text-gray-700'">{{ incomingOrders.length }}</p>
                    <p class="text-sm" :class="currentView === 'incoming' ? 'text-yellow-600' : 'text-gray-600'">Incoming Orders</p>
                </div>
                <div
                    @click="currentView = 'history'"
                    class="border rounded-xl p-4 text-center cursor-pointer transition-all"
                    :class="currentView === 'history' ? 'bg-gray-50 border-gray-300' : 'bg-gray-50 border-gray-200 hover:bg-gray-100'"
                >
                    <Receipt class="w-6 h-6 mx-auto mb-2 text-gray-600" />
                    <p class="text-3xl font-bold text-gray-700">{{ completedOrders.length }}</p>
                    <p class="text-sm text-gray-600">Order History</p>
                </div>
            </div>

            <!-- STATE 1: INCOMING ORDERS -->
            <div v-if="currentView === 'incoming'">
                <h2 class="text-xl font-bold text-[#1A1A1A] mb-4 flex items-center gap-2">
                    <Bell class="w-6 h-6 text-yellow-500" />
                    Incoming Orders
                    <span v-if="incomingOrders.length > 0" class="bg-red-500 text-white text-sm px-3 py-1 rounded-full animate-pulse">
                        {{ incomingOrders.length }} new
                    </span>
                </h2>

                <div v-if="incomingOrders.length > 0" class="space-y-4">
                    <div v-for="order in incomingOrders" :key="order.id" class="bg-white rounded-xl border-2 border-yellow-300 overflow-hidden shadow-sm">
                        <!-- Header -->
                        <div class="bg-yellow-50 px-4 py-3 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-3 h-3 bg-yellow-500 rounded-full animate-pulse"></div>
                                <div>
                                    <span class="font-bold text-[#1A1A1A] text-lg">#{{ order.order_number }}</span>
                                    <span class="text-gray-600 ml-2">• Table {{ order.table_number || 'N/A' }}</span>
                                </div>
                            </div>
                            <span class="text-sm text-gray-500">{{ formatTime(order.created_at) }}</span>
                        </div>

                        <!-- Order Summary -->
                        <div class="p-4 bg-gray-50">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-gray-600">Items:</span>
                                <span class="font-medium">{{ order.items.length }} item(s)</span>
                            </div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-gray-600">Payment:</span>
                                <span class="font-medium px-2 py-1 rounded text-sm" :class="order.payment_method === 'qr_code' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700'">
                                    {{ order.payment_method === 'qr_code' ? '📱 QR Code' : '💵 Cashier' }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">Total:</span>
                                <span class="text-xl font-bold text-[#FF6B35]">{{ formatPrice(order.total_amount) }}</span>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="p-4">
                            <div class="flex gap-3">
                                <button @click="openOrderDetails(order)" class="flex-1 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 flex items-center justify-center gap-2 font-medium">
                                    <Eye class="w-5 h-5" /> View Details
                                </button>
                                <button @click="declineOrder(order.id)" class="flex-1 py-3 bg-red-500 text-white rounded-xl hover:bg-red-600 flex items-center justify-center gap-2 font-medium">
                                    <X class="w-5 h-5" /> Decline
                                </button>
                                <button @click="acceptOrder(order.id)" class="flex-1 py-3 bg-green-500 text-white rounded-xl hover:bg-green-600 flex items-center justify-center gap-2 font-medium">
                                    <Check class="w-5 h-5" /> Accept
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="bg-white rounded-xl border border-[#E0E0E0] p-12 text-center">
                    <Bell class="w-16 h-16 text-gray-300 mx-auto mb-4" />
                    <p class="text-xl text-gray-500 mb-2">No incoming orders</p>
                    <p class="text-sm text-gray-400">New orders will appear here</p>
                </div>
            </div>

            <!-- STATE 2: ORDER HISTORY -->
            <div v-if="currentView === 'history'">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-[#1A1A1A] flex items-center gap-2">
                        <Receipt class="w-6 h-6 text-gray-500" />
                        Order History
                    </h2>
                    <div v-if="completedOrders.length > 0" class="flex items-center gap-3">
                        <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                            <input
                                type="checkbox"
                                :checked="selectedOrders.length === completedOrders.length && completedOrders.length > 0"
                                @change="selectAllOrders"
                                class="rounded border-gray-300"
                            />
                            Select All ({{ completedOrders.length }})
                        </label>
                        <button
                            v-if="selectedOrders.length > 0"
                            @click="deleteOrders"
                            class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 flex items-center gap-2"
                        >
                            <Trash2 class="w-4 h-4" /> Delete Selected ({{ selectedOrders.length }})
                        </button>
                    </div>
                </div>

                <div v-if="completedOrders.length > 0" class="space-y-3">
                    <div v-for="order in completedOrders" :key="order.id" class="bg-white rounded-lg border border-gray-200 p-4 hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <input
                                    type="checkbox"
                                    :checked="selectedOrders.includes(order.id)"
                                    @change="toggleOrderSelection(order.id)"
                                    class="rounded border-gray-300"
                                />
                                <div>
                                    <span class="font-bold text-[#1A1A1A]">#{{ order.order_number }}</span>
                                    <span class="text-gray-600 ml-2">• Table {{ order.table_number || 'N/A' }}</span>
                                    <span class="text-sm text-gray-500 ml-2">• {{ formatDate(order.created_at) }}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <span class="font-bold text-[#FF6B35]">{{ formatPrice(order.total_amount) }}</span>
                                <button @click="openReceipt(order)" class="px-3 py-1 bg-gray-100 text-gray-700 rounded hover:bg-gray-200 flex items-center gap-1">
                                    <Receipt class="w-4 h-4" /> View Receipt
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="bg-white rounded-xl border border-[#E0E0E0] p-12 text-center">
                    <Receipt class="w-16 h-16 text-gray-300 mx-auto mb-4" />
                    <p class="text-xl text-gray-500 mb-2">No completed orders</p>
                    <p class="text-sm text-gray-400">Order history will appear here</p>
                </div>
            </div>
        </div>

        <!-- Undo Toast -->
