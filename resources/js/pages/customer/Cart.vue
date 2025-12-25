<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import CustomerLayout from '@/layouts/customer/CustomerLayout.vue';
import { ShoppingCart, Trash2, Plus, Minus, X, Upload, ArrowRight, ArrowLeft, Store, Edit3, Banknote, QrCode } from 'lucide-vue-next';

interface CartItem {
    id: number;
    product_id: number;
    quantity: number;
    unit_price: number;
    selected_addons: { name: string; price: number }[] | null;
    product: {
        id: number;
        name: string;
        image_url: string | null;
    };
}

interface VendorCart {
    vendor: {
        id: number;
        brand_name: string;
        brand_image: string | null;
        qr_code_image: string | null;
    };
    items: CartItem[];
}

const props = defineProps<{
    vendorCarts: VendorCart[];
}>();

// Edit Modal State
const showEditModal = ref(false);
const editingVendor = ref<VendorCart | null>(null);

// Checkout Modal State
const showCheckoutModal = ref(false);
const checkoutVendor = ref<VendorCart | null>(null);
const checkoutStep = ref<'method' | 'qr' | 'details'>('method');
const paymentMethod = ref<'cash' | 'qr_code'>('cash');
const paymentProof = ref<File | null>(null);
const paymentProofPreview = ref<string | null>(null);
const tableNumber = ref('');
const specialInstructions = ref('');
const submitting = ref(false);

const calculateItemTotal = (item: CartItem) => {
    const addonsTotal = item.selected_addons?.reduce((sum, a) => sum + a.price, 0) ?? 0;
    return (item.unit_price + addonsTotal) * item.quantity;
};

const calculateVendorTotal = (items: CartItem[]) => {
    return items.reduce((sum, item) => sum + calculateItemTotal(item), 0);
};

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(price);
};

// Edit Modal Functions
const openEditModal = (vendorCart: VendorCart) => {
    editingVendor.value = vendorCart;
    showEditModal.value = true;
};

const closeEditModal = () => {
    showEditModal.value = false;
    editingVendor.value = null;
};

const updateQuantity = (cartId: number, newQuantity: number) => {
    if (newQuantity < 1) return;
    router.put(`/customer/cart/${cartId}`, { quantity: newQuantity }, { preserveScroll: true });
};

const removeItem = (cartId: number) => {
    router.delete(`/customer/cart/${cartId}`, { preserveScroll: true });
};

// Checkout Modal Functions
const openCheckout = (vendorCart: VendorCart) => {
    checkoutVendor.value = vendorCart;
    checkoutStep.value = 'method';
    paymentMethod.value = 'cash';
    paymentProof.value = null;
    paymentProofPreview.value = null;
    tableNumber.value = '';
    specialInstructions.value = '';
    showCheckoutModal.value = true;
};

const selectPaymentMethod = (method: 'cash' | 'qr_code') => {
    paymentMethod.value = method;
    if (method === 'cash') {
        checkoutStep.value = 'details';
    } else {
        checkoutStep.value = 'qr';
    }
};

const closeCheckout = () => {
    showCheckoutModal.value = false;
    checkoutVendor.value = null;
};

const handleFileUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    if (file) {
        paymentProof.value = file;
        paymentProofPreview.value = URL.createObjectURL(file);
    }
};

const proceedToDetails = () => {
    if (!paymentProof.value) {
        alert('Please upload payment proof first');
        return;
    }
    checkoutStep.value = 'details';
};

const submitOrder = () => {
    if (!checkoutVendor.value) return;

    submitting.value = true;

    const formData = new FormData();
    formData.append('vendor_id', checkoutVendor.value.vendor.id.toString());
    formData.append('payment_method', 'qr_code');
    formData.append('table_number', tableNumber.value);
    formData.append('special_instructions', specialInstructions.value);
    if (paymentProof.value) {
        formData.append('payment_proof', paymentProof.value);
    }

    router.post('/customer/orders', formData, {
        forceFormData: true,
        onSuccess: () => {
            closeCheckout();
            submitting.value = false;
        },
        onError: () => {
            submitting.value = false;
        },
    });
};
</script>

<template>
    <CustomerLayout>
        <div class="max-w-lg mx-auto px-4 py-6">
            <h1 class="text-xl font-bold text-[#1A1A1A] mb-4">Your Cart</h1>

            <!-- Vendor Cart Boxes -->
            <div v-if="vendorCarts.length > 0" class="space-y-4">
                <div
                    v-for="vendorCart in vendorCarts"
                    :key="vendorCart.vendor.id"
                    class="bg-white rounded-2xl shadow-sm border border-[#E0E0E0] overflow-hidden"
                >
                    <!-- Vendor Header -->
                    <div class="flex items-center gap-3 p-4 border-b border-[#E0E0E0]">
                        <div class="w-12 h-12 rounded-xl bg-gray-100 border-2 border-[#FF6B35] flex items-center justify-center overflow-hidden">
                            <img v-if="vendorCart.vendor.brand_image" :src="`/storage/${vendorCart.vendor.brand_image}`" class="w-full h-full object-cover" />
                            <Store v-else class="w-6 h-6 text-[#1A1A1A]" />
                        </div>
                        <div class="flex-1">
                            <h2 class="font-semibold text-[#1A1A1A]">{{ vendorCart.vendor.brand_name }}</h2>
                            <p class="text-sm text-gray-500">{{ vendorCart.items.length }} item(s)</p>
                        </div>
                    </div>

                    <!-- Summary -->
                    <div class="p-4 bg-[#F5F5F5]">
                        <div class="flex justify-between items-center mb-4">
                            <span class="text-gray-600">Total</span>
                            <span class="text-xl font-bold text-[#FF6B35]">{{ formatPrice(calculateVendorTotal(vendorCart.items)) }}</span>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-3">
                            <button
                                @click="openEditModal(vendorCart)"
                                class="flex-1 py-3 bg-white border border-[#E0E0E0] text-[#1A1A1A] font-medium rounded-xl hover:bg-gray-50 transition-colors flex items-center justify-center gap-2"
                            >
                                <Edit3 class="w-4 h-4" />
                                Edit Order
                            </button>
                            <button
                                @click="openCheckout(vendorCart)"
                                class="flex-1 py-3 bg-[#FF6B35] text-white font-medium rounded-xl hover:bg-orange-600 transition-colors flex items-center justify-center gap-2"
                            >
                                Checkout
                                <ArrowRight class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty Cart -->
            <div v-else class="text-center py-16">
                <ShoppingCart class="w-16 h-16 text-gray-300 mx-auto mb-4" />
                <h3 class="text-lg font-medium text-[#1A1A1A]">Your cart is empty</h3>
                <p class="text-gray-500 mt-1">Browse vendors and add some delicious food!</p>
            </div>
        </div>

        <!-- Edit Order Modal -->
        <Teleport to="body">
            <div
                v-if="showEditModal && editingVendor"
                class="fixed inset-0 bg-black/50 flex items-end md:items-center justify-center z-50"
                @click.self="closeEditModal"
            >
                <div class="bg-white w-full md:w-[450px] md:rounded-xl rounded-t-xl max-h-[85vh] overflow-hidden">
                    <!-- Header -->
                    <div class="sticky top-0 bg-white border-b border-[#E0E0E0] p-4 flex items-center justify-between">
                        <h2 class="text-lg font-bold text-[#1A1A1A]">Edit Order</h2>
                        <button @click="closeEditModal" class="p-2 hover:bg-gray-100 rounded-full">
                            <X class="w-5 h-5" />
                        </button>
                    </div>

                    <!-- Items List -->
                    <div class="overflow-y-auto max-h-[60vh] divide-y divide-[#E0E0E0]">
                        <div v-for="item in editingVendor.items" :key="item.id" class="p-4">
                            <div class="flex gap-3 items-start">
                                <!-- Image -->
                                <div class="w-14 h-14 rounded-lg bg-[#F5F5F5] overflow-hidden flex-shrink-0">
                                    <img v-if="item.product.image_url" :src="`/storage/${item.product.image_url}`" class="w-full h-full object-cover" />
                                    <div v-else class="w-full h-full flex items-center justify-center text-xl">🍽️</div>
                                </div>

                                <!-- Details -->
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-medium text-[#1A1A1A]">{{ item.product.name }}</h3>
                                    <p class="text-sm text-gray-500">{{ formatPrice(item.unit_price) }}</p>
                                    <p v-if="item.selected_addons?.length" class="text-xs text-[#FF6B35] mt-1">
                                        + {{ item.selected_addons.map(a => a.name).join(', ') }}
                                    </p>
                                </div>

                                <!-- Price -->
                                <span class="font-bold text-[#FF6B35]">{{ formatPrice(calculateItemTotal(item)) }}</span>
                            </div>

                            <!-- Quantity Controls -->
                            <div class="flex items-center justify-between mt-3">
                                <div class="flex items-center gap-2 bg-[#F5F5F5] rounded-full p-1">
                                    <button
                                        @click="updateQuantity(item.id, item.quantity - 1)"
                                        class="w-8 h-8 rounded-full bg-white flex items-center justify-center shadow-sm"
                                    >
                                        <Minus class="w-4 h-4" />
                                    </button>
                                    <span class="w-8 text-center font-medium">{{ item.quantity }}</span>
                                    <button
                                        @click="updateQuantity(item.id, item.quantity + 1)"
                                        class="w-8 h-8 rounded-full bg-white flex items-center justify-center shadow-sm"
                                    >
                                        <Plus class="w-4 h-4" />
                                    </button>
                                </div>
                                <button @click="removeItem(item.id)" class="text-red-500 hover:text-red-600 p-2">
                                    <Trash2 class="w-5 h-5" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="sticky bottom-0 bg-white border-t border-[#E0E0E0] p-4">
                        <div class="flex justify-between items-center mb-4">
                            <span class="font-medium">Total</span>
                            <span class="text-xl font-bold text-[#FF6B35]">{{ formatPrice(calculateVendorTotal(editingVendor.items)) }}</span>
                        </div>
                        <button
                            @click="closeEditModal"
                            class="w-full py-3 bg-[#1A1A1A] text-white font-medium rounded-xl hover:bg-black transition-colors"
                        >
                            Save & Back to Cart
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Checkout Modal -->
        <Teleport to="body">
            <div
                v-if="showCheckoutModal && checkoutVendor"
                class="fixed inset-0 bg-black/50 flex items-end md:items-center justify-center z-50"
                @click.self="closeCheckout"
            >
                <div class="bg-white w-full md:w-[450px] md:rounded-xl rounded-t-xl max-h-[90vh] overflow-hidden">
                    <!-- Header -->
                    <div class="sticky top-0 bg-white border-b border-[#E0E0E0] p-4 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <button v-if="checkoutStep === 'qr'" @click="checkoutStep = 'method'" class="p-1 hover:bg-gray-100 rounded-full">
                                <ArrowLeft class="w-5 h-5" />
                            </button>
                            <button v-if="checkoutStep === 'details' && paymentMethod === 'qr_code'" @click="checkoutStep = 'qr'" class="p-1 hover:bg-gray-100 rounded-full">
                                <ArrowLeft class="w-5 h-5" />
                            </button>
                            <button v-if="checkoutStep === 'details' && paymentMethod === 'cash'" @click="checkoutStep = 'method'" class="p-1 hover:bg-gray-100 rounded-full">
                                <ArrowLeft class="w-5 h-5" />
                            </button>
                            <h2 class="text-lg font-bold text-[#1A1A1A]">
                                {{ checkoutStep === 'method' ? 'Payment Method' : checkoutStep === 'qr' ? 'Scan & Pay' : 'Order Details' }}
                            </h2>
                        </div>
                        <button @click="closeCheckout" class="p-2 hover:bg-gray-100 rounded-full">
                            <X class="w-5 h-5" />
                        </button>
                    </div>

                    <div class="overflow-y-auto max-h-[70vh]">
                        <!-- Step 0: Payment Method Selection -->
                        <div v-if="checkoutStep === 'method'" class="p-4">
                            <p class="text-sm text-gray-500 text-center mb-6">How would you like to pay?</p>

                            <div class="space-y-3">
                                <!-- Cash Option -->
                                <button
                                    @click="selectPaymentMethod('cash')"
                                    class="w-full p-4 bg-white border-2 border-[#E0E0E0] rounded-xl hover:border-[#FF6B35] transition-colors text-left flex items-center gap-4"
                                >
                                    <div class="w-14 h-14 bg-green-100 rounded-xl flex items-center justify-center">
                                        <Banknote class="w-7 h-7 text-green-600" />
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="font-semibold text-[#1A1A1A]">Pay at Cashier</h3>
                                        <p class="text-sm text-gray-500">Pay cash when you pick up your order</p>
                                    </div>
                                    <ArrowRight class="w-5 h-5 text-gray-400" />
                                </button>

                                <!-- QR Code Option -->
                                <button
                                    @click="selectPaymentMethod('qr_code')"
                                    class="w-full p-4 bg-white border-2 border-[#E0E0E0] rounded-xl hover:border-[#FF6B35] transition-colors text-left flex items-center gap-4"
                                    :class="!checkoutVendor.vendor.qr_code_image ? 'opacity-50 cursor-not-allowed' : ''"
                                    :disabled="!checkoutVendor.vendor.qr_code_image"
                                >
                                    <div class="w-14 h-14 bg-blue-100 rounded-xl flex items-center justify-center">
                                        <QrCode class="w-7 h-7 text-blue-600" />
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="font-semibold text-[#1A1A1A]">QR Code Payment</h3>
                                        <p class="text-sm text-gray-500">
                                            {{ checkoutVendor.vendor.qr_code_image ? 'Scan QR and upload proof' : 'Not available for this vendor' }}
                                        </p>
                                    </div>
                                    <ArrowRight class="w-5 h-5 text-gray-400" />
                                </button>
                            </div>

                            <!-- Total Display -->
                            <div class="mt-6 p-4 bg-[#F5F5F5] rounded-xl text-center">
                                <p class="text-sm text-gray-500">Total Amount</p>
                                <p class="text-2xl font-bold text-[#FF6B35]">{{ formatPrice(calculateVendorTotal(checkoutVendor.items)) }}</p>
                            </div>
                        </div>

                        <!-- Step 1: QR Payment -->
                        <div v-if="checkoutStep === 'qr'" class="p-4">
                            <!-- QR Code Display -->
                            <div class="text-center mb-6">
                                <p class="text-sm text-gray-500 mb-4">Scan QR code to pay</p>
                                <div class="w-56 h-56 mx-auto bg-[#F5F5F5] rounded-xl flex items-center justify-center border-2 border-dashed border-[#E0E0E0]">
                                    <img v-if="checkoutVendor.vendor.qr_code_image" :src="`/storage/${checkoutVendor.vendor.qr_code_image}`" class="w-full h-full object-contain p-2" />
                                    <div v-else class="text-center p-4">
                                        <div class="text-4xl mb-2">📱</div>
                                        <p class="text-sm text-gray-400">QR code not available</p>
                                    </div>
                                </div>
                                <p class="text-lg font-bold text-[#FF6B35] mt-4">{{ formatPrice(calculateVendorTotal(checkoutVendor.items)) }}</p>
                            </div>

                            <!-- Upload Payment Proof -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-[#1A1A1A] mb-2">Upload Payment Screenshot</label>
                                <div class="border-2 border-dashed border-[#E0E0E0] rounded-xl p-4 text-center hover:border-[#FF6B35] transition-colors">
                                    <input type="file" accept="image/*" @change="handleFileUpload" class="hidden" id="payment-proof" />
                                    <label for="payment-proof" class="cursor-pointer block">
                                        <div v-if="paymentProofPreview">
                                            <img :src="paymentProofPreview" class="max-h-40 mx-auto rounded-lg" />
                                            <p class="text-sm text-green-600 mt-2">✓ Screenshot uploaded</p>
                                        </div>
                                        <div v-else>
                                            <Upload class="w-10 h-10 text-gray-400 mx-auto mb-2" />
                                            <p class="text-sm text-gray-500">Tap to upload screenshot</p>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Proceed Button -->
                            <button
                                @click="proceedToDetails"
                                class="w-full py-3 bg-[#FF6B35] text-white font-medium rounded-xl hover:bg-orange-600 transition-colors flex items-center justify-center gap-2"
                            >
                                Continue
                                <ArrowRight class="w-4 h-4" />
                            </button>
                        </div>

                        <!-- Step 2: Order Details -->
                        <div v-if="checkoutStep === 'details'" class="p-4 space-y-4">
                            <!-- Table Number -->
                            <div>
                                <label class="block text-sm font-medium text-[#1A1A1A] mb-2">Table Number *</label>
                                <input
                                    v-model="tableNumber"
                                    type="text"
                                    placeholder="e.g. T5, Table 12"
                                    class="w-full px-4 py-3 border border-[#E0E0E0] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#FF6B35]"
                                />
                            </div>

                            <!-- Special Instructions -->
                            <div>
                                <label class="block text-sm font-medium text-[#1A1A1A] mb-2">Special Instructions</label>
                                <textarea
                                    v-model="specialInstructions"
                                    rows="3"
                                    placeholder="e.g. No onions, extra sauce"
                                    class="w-full px-4 py-3 border border-[#E0E0E0] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#FF6B35] resize-none"
                                ></textarea>
                            </div>

                            <!-- Order Summary -->
                            <div class="bg-[#F5F5F5] rounded-xl p-4">
                                <h4 class="font-medium text-[#1A1A1A] mb-3">Order Summary</h4>
                                <div class="space-y-2 text-sm">
                                    <div v-for="item in checkoutVendor.items" :key="item.id" class="flex justify-between">
                                        <span class="text-gray-600">
                                            {{ item.product.name }} x{{ item.quantity }}
                                            <span v-if="item.selected_addons?.length" class="text-xs text-[#FF6B35]">
                                                (+{{ item.selected_addons.map(a => a.name).join(', ') }})
                                            </span>
                                        </span>
                                        <span>{{ formatPrice(calculateItemTotal(item)) }}</span>
                                    </div>
                                </div>
                                <div class="border-t border-[#E0E0E0] mt-3 pt-3 flex justify-between font-bold">
                                    <span>Total</span>
                                    <span class="text-[#FF6B35]">{{ formatPrice(calculateVendorTotal(checkoutVendor.items)) }}</span>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <button
                                @click="submitOrder"
                                :disabled="submitting || !tableNumber"
                                class="w-full py-3 bg-[#FF6B35] text-white font-medium rounded-xl hover:bg-orange-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                {{ submitting ? 'Sending Order...' : 'Place Order 🚀' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </CustomerLayout>
</template>
