<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import CustomerLayout from '@/layouts/customer/CustomerLayout.vue';
import { ArrowLeft, Store, ShoppingCart, Plus, Minus, X, Check } from 'lucide-vue-next';

interface Addon {
    id: number;
    name: string;
    price: number;
    is_active: boolean;
}

interface Product {
    id: number;
    name: string;
    price: number;
    category: string | null;
    image_url: string | null;
    stock_quantity: number;
    is_active: boolean;
    addons: Addon[];
}

interface Vendor {
    id: number;
    brand_name: string;
    brand_image: string | null;
    qr_code_image: string | null;
}

const props = defineProps<{
    vendor: Vendor;
    products: Product[];
    categories: string[];
}>();

const selectedCategory = ref<string | null>(null);
const showAddModal = ref(false);
const selectedProduct = ref<Product | null>(null);
const quantity = ref(1);
const selectedAddons = ref<{ id: number; name: string; price: number }[]>([]);
const addingToCart = ref(false);

const filteredProducts = computed(() => {
    if (!selectedCategory.value) return props.products;
    return props.products.filter((p) => p.category === selectedCategory.value);
});

const openAddModal = (product: Product) => {
    selectedProduct.value = product;
    quantity.value = 1;
    selectedAddons.value = [];
    showAddModal.value = true;
};

const closeAddModal = () => {
    showAddModal.value = false;
    selectedProduct.value = null;
};

const toggleAddon = (addon: Addon) => {
    const index = selectedAddons.value.findIndex((a) => a.id === addon.id);
    if (index === -1) {
        selectedAddons.value.push({ id: addon.id, name: addon.name, price: addon.price });
    } else {
        selectedAddons.value.splice(index, 1);
    }
};

const isAddonSelected = (addonId: number) => {
    return selectedAddons.value.some((a) => a.id === addonId);
};

const totalPrice = computed(() => {
    if (!selectedProduct.value) return 0;
    const addonsTotal = selectedAddons.value.reduce((sum, a) => sum + a.price, 0);
    return (selectedProduct.value.price + addonsTotal) * quantity.value;
});

const addToCart = () => {
    if (!selectedProduct.value) return;
    addingToCart.value = true;

    router.post(
        '/customer/cart',
        {
            vendor_id: props.vendor.id,
            product_id: selectedProduct.value.id,
            quantity: quantity.value,
            unit_price: selectedProduct.value.price,
            selected_addons: selectedAddons.value,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                closeAddModal();
                addingToCart.value = false;
            },
            onError: () => {
                addingToCart.value = false;
            },
        }
    );
};

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
    }).format(price);
};
</script>

<template>
    <CustomerLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <!-- Back Button & Vendor Header -->
            <div class="mb-6">
                <Link
                    href="/customer/home"
                    class="inline-flex items-center gap-2 text-gray-500 hover:text-[#FF6B35] transition-colors mb-4"
                >
                    <ArrowLeft class="w-5 h-5" />
                    <span>Back to vendors</span>
                </Link>

                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-xl bg-[#F5F5F5] overflow-hidden flex-shrink-0">
                        <img
                            v-if="vendor.brand_image"
                            :src="`/storage/${vendor.brand_image}`"
                            :alt="vendor.brand_name"
                            class="w-full h-full object-cover"
                        />
                        <div v-else class="w-full h-full flex items-center justify-center">
                            <Store class="w-8 h-8 text-gray-300" />
                        </div>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-[#1A1A1A]">{{ vendor.brand_name }}</h1>
                        <p class="text-gray-500">{{ products.length }} products available</p>
                    </div>
                </div>
            </div>

            <!-- Category Filter -->
            <div v-if="categories.length > 0" class="flex gap-2 overflow-x-auto pb-2 mb-6 scrollbar-hide">
                <button
                    @click="selectedCategory = null"
                    class="px-4 py-2 rounded-full whitespace-nowrap transition-colors"
                    :class="
                        selectedCategory === null
                            ? 'bg-[#FF6B35] text-white'
                            : 'bg-white text-[#1A1A1A] border border-[#E0E0E0] hover:border-[#FF6B35]'
                    "
                >
                    All
                </button>
                <button
                    v-for="category in categories"
                    :key="category"
                    @click="selectedCategory = category"
                    class="px-4 py-2 rounded-full whitespace-nowrap transition-colors"
                    :class="
                        selectedCategory === category
                            ? 'bg-[#FF6B35] text-white'
                            : 'bg-white text-[#1A1A1A] border border-[#E0E0E0] hover:border-[#FF6B35]'
                    "
                >
                    {{ category }}
                </button>
            </div>

            <!-- Products Grid -->
            <div v-if="filteredProducts.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                <div
                    v-for="product in filteredProducts"
                    :key="product.id"
                    class="bg-white rounded-xl shadow-sm border border-[#E0E0E0] overflow-hidden"
                >
                    <!-- Product Image -->
                    <div class="relative h-36 bg-[#F5F5F5]">
                        <img
                            v-if="product.image_url"
                            :src="`/storage/${product.image_url}`"
                            :alt="product.name"
                            class="w-full h-full object-cover"
                        />
                        <div v-else class="w-full h-full flex items-center justify-center">
                            <span class="text-4xl">🍽️</span>
                        </div>
                        <span
                            v-if="product.stock_quantity <= 0"
                            class="absolute top-2 right-2 bg-red-500 text-white text-xs px-2 py-1 rounded"
                        >
                            Out of stock
                        </span>
                    </div>

                    <!-- Product Info -->
                    <div class="p-4">
                        <h3 class="font-medium text-[#1A1A1A]">{{ product.name }}</h3>
                        <p v-if="product.category" class="text-sm text-gray-500">{{ product.category }}</p>
                        <div class="flex items-center justify-between mt-3">
                            <span class="font-bold text-[#FF6B35]">{{ formatPrice(product.price) }}</span>
                            <button
                                @click="openAddModal(product)"
                                :disabled="product.stock_quantity <= 0"
                                class="flex items-center gap-1 px-3 py-2 bg-[#FF6B35] text-white rounded-lg hover:bg-orange-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <ShoppingCart class="w-4 h-4" />
                                <span class="text-sm">Add</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-16">
                <span class="text-6xl">🍽️</span>
                <h3 class="text-lg font-medium text-[#1A1A1A] mt-4">No products found</h3>
                <p class="text-gray-500 mt-1">Check back later for available products</p>
            </div>
        </div>

        <!-- Add to Cart Modal -->
        <Teleport to="body">
            <div
                v-if="showAddModal && selectedProduct"
                class="fixed inset-0 bg-black/50 flex items-end md:items-center justify-center z-50"
                @click.self="closeAddModal"
            >
                <div class="bg-white w-full md:w-[480px] md:rounded-xl rounded-t-xl max-h-[90vh] overflow-y-auto">
                    <!-- Modal Header -->
                    <div class="sticky top-0 bg-white border-b border-[#E0E0E0] p-4 flex items-center justify-between">
                        <h2 class="text-lg font-bold text-[#1A1A1A]">Add to Cart</h2>
                        <button @click="closeAddModal" class="p-2 hover:bg-gray-100 rounded-full">
                            <X class="w-5 h-5" />
                        </button>
                    </div>

                    <!-- Product Info -->
                    <div class="p-4 border-b border-[#E0E0E0]">
                        <div class="flex gap-4">
                            <div class="w-20 h-20 rounded-lg bg-[#F5F5F5] overflow-hidden flex-shrink-0">
                                <img
                                    v-if="selectedProduct.image_url"
                                    :src="`/storage/${selectedProduct.image_url}`"
                                    :alt="selectedProduct.name"
                                    class="w-full h-full object-cover"
                                />
                                <div v-else class="w-full h-full flex items-center justify-center">
                                    <span class="text-2xl">🍽️</span>
                                </div>
                            </div>
                            <div>
                                <h3 class="font-medium text-[#1A1A1A]">{{ selectedProduct.name }}</h3>
                                <p class="text-[#FF6B35] font-bold">{{ formatPrice(selectedProduct.price) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Addons -->
                    <div v-if="selectedProduct.addons.length > 0" class="p-4 border-b border-[#E0E0E0]">
                        <h4 class="font-medium text-[#1A1A1A] mb-3">Add-ons</h4>
                        <div class="space-y-2">
                            <button
                                v-for="addon in selectedProduct.addons"
                                :key="addon.id"
                                @click="toggleAddon(addon)"
                                class="w-full flex items-center justify-between p-3 rounded-lg border transition-colors"
                                :class="
                                    isAddonSelected(addon.id)
                                        ? 'border-[#FF6B35] bg-orange-50'
                                        : 'border-[#E0E0E0] hover:border-[#FF6B35]'
                                "
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-5 h-5 rounded border flex items-center justify-center"
                                        :class="
                                            isAddonSelected(addon.id)
                                                ? 'bg-[#FF6B35] border-[#FF6B35]'
                                                : 'border-gray-300'
                                        "
                                    >
                                        <Check v-if="isAddonSelected(addon.id)" class="w-3 h-3 text-white" />
                                    </div>
                                    <span>{{ addon.name }}</span>
                                </div>
                                <span class="text-[#FF6B35]">+{{ formatPrice(addon.price) }}</span>
                            </button>
                        </div>
                    </div>

                    <!-- Quantity -->
                    <div class="p-4 border-b border-[#E0E0E0]">
                        <h4 class="font-medium text-[#1A1A1A] mb-3">Quantity</h4>
                        <div class="flex items-center gap-4">
                            <button
                                @click="quantity = Math.max(1, quantity - 1)"
                                class="w-10 h-10 rounded-full border border-[#E0E0E0] flex items-center justify-center hover:border-[#FF6B35] transition-colors"
                            >
                                <Minus class="w-4 h-4" />
                            </button>
                            <span class="text-xl font-medium w-8 text-center">{{ quantity }}</span>
                            <button
                                @click="quantity++"
                                class="w-10 h-10 rounded-full border border-[#E0E0E0] flex items-center justify-center hover:border-[#FF6B35] transition-colors"
                            >
                                <Plus class="w-4 h-4" />
                            </button>
                        </div>
                    </div>

                    <!-- Add Button -->
                    <div class="p-4">
                        <button
                            @click="addToCart"
                            :disabled="addingToCart"
                            class="w-full py-3 bg-[#FF6B35] text-white font-medium rounded-xl hover:bg-orange-600 transition-colors disabled:opacity-50"
                        >
                            <span v-if="addingToCart">Adding...</span>
                            <span v-else>Add to Cart - {{ formatPrice(totalPrice) }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </CustomerLayout>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
