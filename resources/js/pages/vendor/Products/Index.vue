<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import VendorLayout from '@/layouts/vendor/VendorLayout.vue';
import { Plus, Edit, Trash2, Search, Package } from 'lucide-vue-next';

interface Addon { id: number; name: string; price: number; }
interface Product {
    id: number; name: string; price: number; category: string | null;
    image_url: string | null; stock_quantity: number; is_active: boolean;
    addons: Addon[];
}

const props = defineProps<{ products: Product[]; categories: string[] }>();
const searchQuery = ref('');
const selectedCategory = ref<string | null>(null);

const filteredProducts = computed(() => {
    let result = props.products;
    if (searchQuery.value) result = result.filter(p => p.name.toLowerCase().includes(searchQuery.value.toLowerCase()));
    if (selectedCategory.value) result = result.filter(p => p.category === selectedCategory.value);
    return result;
});

const formatPrice = (p: number) => new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(p);
const deleteProduct = (id: number) => { if (confirm('Delete this product?')) router.delete(`/vendor/products/${id}`); };
</script>

<template>
    <VendorLayout>
        <template #title>Products</template>
        <div class="space-y-4">
            <div class="flex flex-col sm:flex-row gap-4 justify-between">
                <div class="relative flex-1 max-w-md">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                    <input v-model="searchQuery" type="text" placeholder="Search products..." class="w-full pl-10 pr-4 py-2 border border-[#E0E0E0] rounded-lg" />
                </div>
                <Link href="/vendor/products/create" class="inline-flex items-center gap-2 px-4 py-2 bg-[#FF6B35] text-white rounded-lg hover:bg-orange-600">
                    <Plus class="w-5 h-5" /> Add Product
                </Link>
            </div>

            <div v-if="categories.length > 0" class="flex gap-2 overflow-x-auto pb-2">
                <button @click="selectedCategory = null" class="px-3 py-1.5 rounded-full text-sm" :class="selectedCategory === null ? 'bg-[#FF6B35] text-white' : 'bg-white border border-[#E0E0E0]'">All</button>
                <button v-for="cat in categories" :key="cat" @click="selectedCategory = cat" class="px-3 py-1.5 rounded-full text-sm whitespace-nowrap" :class="selectedCategory === cat ? 'bg-[#FF6B35] text-white' : 'bg-white border border-[#E0E0E0]'">{{ cat }}</button>
            </div>

            <div v-if="filteredProducts.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div v-for="product in filteredProducts" :key="product.id" class="bg-white rounded-xl border border-[#E0E0E0] overflow-hidden">
                    <div class="h-32 bg-[#F5F5F5]">
                        <img v-if="product.image_url" :src="`/storage/${product.image_url}`" class="w-full h-full object-cover" />
                        <div v-else class="w-full h-full flex items-center justify-center"><span class="text-4xl">🍽️</span></div>
                    </div>
                    <div class="p-4">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <h3 class="font-medium text-[#1A1A1A]">{{ product.name }}</h3>
                                <p v-if="product.category" class="text-sm text-gray-500">{{ product.category }}</p>
                            </div>
                            <span class="font-bold text-[#FF6B35]">{{ formatPrice(product.price) }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm text-gray-500 mb-3">
                            <span>Stock: {{ product.stock_quantity }}</span>
                            <span>{{ product.addons.length }} addon{{ product.addons.length !== 1 ? 's' : '' }}</span>
                        </div>
                        <div class="flex gap-2">
                            <Link :href="`/vendor/products/${product.id}/edit`" class="flex-1 flex items-center justify-center gap-1 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                                <Edit class="w-4 h-4" /> Edit
                            </Link>
                            <button @click="deleteProduct(product.id)" class="flex items-center justify-center gap-1 px-3 py-2 bg-red-50 text-red-500 rounded-lg hover:bg-red-100">
                                <Trash2 class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="bg-white rounded-xl border border-[#E0E0E0] p-12 text-center">
                <Package class="w-12 h-12 text-gray-300 mx-auto mb-3" />
                <p class="text-gray-500 mb-4">No products found</p>
                <Link href="/vendor/products/create" class="inline-flex items-center gap-2 px-4 py-2 bg-[#FF6B35] text-white rounded-lg">
                    <Plus class="w-5 h-5" /> Add Your First Product
                </Link>
            </div>
        </div>
    </VendorLayout>
</template>
