<script setup lang="ts">
import { ref } from 'vue';
import { useForm, Link, router } from '@inertiajs/vue3';
import VendorLayout from '@/layouts/vendor/VendorLayout.vue';
import { ArrowLeft, Plus, X, Upload, Trash2 } from 'lucide-vue-next';

interface Addon { id: number; name: string; price: number; }
interface Product { id: number; name: string; price: number; category: string | null; image_url: string | null; stock_quantity: number; addons: Addon[]; }

const props = defineProps<{ product: Product }>();

const form = useForm({
    name: props.product.name,
    price: props.product.price.toString(),
    category: props.product.category || '',
    stock_quantity: props.product.stock_quantity,
    image: null as File | null,
    addons: props.product.addons.map(a => ({ id: a.id, name: a.name, price: a.price.toString() })),
    new_addons: [] as { name: string; price: string }[],
    delete_addons: [] as number[],
});

const imagePreview = ref<string | null>(props.product.image_url ? `/storage/${props.product.image_url}` : null);

const handleImageChange = (e: Event) => {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (file) { form.image = file; imagePreview.value = URL.createObjectURL(file); }
};

const addNewAddon = () => form.new_addons.push({ name: '', price: '' });
const removeNewAddon = (i: number) => form.new_addons.splice(i, 1);
const deleteExistingAddon = (id: number, i: number) => { form.delete_addons.push(id); form.addons.splice(i, 1); };

const submit = () => {
    form.transform((data) => ({ ...data, _method: 'PUT' })).post(`/vendor/products/${props.product.id}`, { forceFormData: true });
};
</script>

<template>
    <VendorLayout>
        <template #title>Edit Product</template>
        <div class="max-w-2xl">
            <Link href="/vendor/products" class="inline-flex items-center gap-2 text-gray-500 hover:text-[#FF6B35] mb-4">
                <ArrowLeft class="w-5 h-5" /> Back to Products
            </Link>

            <form @submit.prevent="submit" class="bg-white rounded-xl border border-[#E0E0E0] p-6 space-y-6">
                <div>
                    <label class="block text-sm font-medium mb-2">Product Name *</label>
                    <input v-model="form.name" type="text" required class="w-full px-4 py-2 border border-[#E0E0E0] rounded-lg" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">Price *</label>
                        <input v-model="form.price" type="number" step="0.01" min="0" required class="w-full px-4 py-2 border border-[#E0E0E0] rounded-lg" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">Stock Quantity</label>
                        <input v-model="form.stock_quantity" type="number" min="0" class="w-full px-4 py-2 border border-[#E0E0E0] rounded-lg" />
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Category</label>
                    <input v-model="form.category" type="text" class="w-full px-4 py-2 border border-[#E0E0E0] rounded-lg" />
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Product Image</label>
                    <div class="border-2 border-dashed border-[#E0E0E0] rounded-lg p-4 text-center">
                        <input type="file" accept="image/*" @change="handleImageChange" class="hidden" id="product-image" />
                        <label for="product-image" class="cursor-pointer">
                            <img v-if="imagePreview" :src="imagePreview" class="max-h-32 mx-auto rounded mb-2" />
                            <div v-else><Upload class="w-8 h-8 text-gray-400 mx-auto mb-2" /><p class="text-sm text-gray-500">Click to upload</p></div>
                        </label>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-3">
                        <label class="text-sm font-medium">Add-ons</label>
                        <button type="button" @click="addNewAddon" class="text-[#FF6B35] text-sm flex items-center gap-1"><Plus class="w-4 h-4" /> Add</button>
                    </div>
                    <!-- Existing addons -->
                    <div v-for="(addon, i) in form.addons" :key="addon.id" class="flex gap-2 mb-2">
                        <input v-model="addon.name" type="text" class="flex-1 px-3 py-2 border border-[#E0E0E0] rounded-lg" />
                        <input v-model="addon.price" type="number" step="0.01" min="0" class="w-24 px-3 py-2 border border-[#E0E0E0] rounded-lg" />
                        <button type="button" @click="deleteExistingAddon(addon.id, i)" class="p-2 text-red-500 hover:bg-red-50 rounded-lg"><Trash2 class="w-5 h-5" /></button>
                    </div>
                    <!-- New addons -->
                    <div v-for="(addon, i) in form.new_addons" :key="`new-${i}`" class="flex gap-2 mb-2">
                        <input v-model="addon.name" type="text" placeholder="New addon" class="flex-1 px-3 py-2 border border-green-300 rounded-lg bg-green-50" />
                        <input v-model="addon.price" type="number" step="0.01" min="0" placeholder="Price" class="w-24 px-3 py-2 border border-green-300 rounded-lg bg-green-50" />
                        <button type="button" @click="removeNewAddon(i)" class="p-2 text-red-500 hover:bg-red-50 rounded-lg"><X class="w-5 h-5" /></button>
                    </div>
                </div>

                <button type="submit" :disabled="form.processing" class="w-full py-3 bg-[#FF6B35] text-white font-medium rounded-lg hover:bg-orange-600 disabled:opacity-50">
                    {{ form.processing ? 'Saving...' : 'Save Changes' }}
                </button>
            </form>
        </div>
    </VendorLayout>
</template>
