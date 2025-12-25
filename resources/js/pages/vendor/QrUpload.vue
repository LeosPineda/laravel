<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import VendorLayout from '@/layouts/vendor/VendorLayout.vue';
import { QrCode, Upload, Check } from 'lucide-vue-next';

const props = defineProps<{ vendor: { qr_code_image: string | null } }>();

const form = useForm({ qr_code: null as File | null });
const imagePreview = ref<string | null>(props.vendor.qr_code_image ? `/storage/${props.vendor.qr_code_image}` : null);

const handleFileChange = (e: Event) => {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (file) { form.qr_code = file; imagePreview.value = URL.createObjectURL(file); }
};

const submit = () => {
    form.post('/vendor/qr', { forceFormData: true, preserveScroll: true });
};
</script>

<template>
    <VendorLayout>
        <template #title>QR Code</template>
        <div class="max-w-lg">
            <div class="bg-white rounded-xl border border-[#E0E0E0] p-6">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 rounded-full bg-orange-50 flex items-center justify-center mx-auto mb-4">
                        <QrCode class="w-8 h-8 text-[#FF6B35]" />
                    </div>
                    <h2 class="text-xl font-bold text-[#1A1A1A]">Payment QR Code</h2>
                    <p class="text-gray-500 text-sm mt-1">Upload your GCash/Maya QR code for customers to scan and pay</p>
                </div>

                <form @submit.prevent="submit">
                    <div class="border-2 border-dashed border-[#E0E0E0] rounded-xl p-6 text-center mb-6">
                        <input type="file" accept="image/*" @change="handleFileChange" class="hidden" id="qr-upload" />
                        <label for="qr-upload" class="cursor-pointer block">
                            <img v-if="imagePreview" :src="imagePreview" class="max-w-[200px] mx-auto rounded-lg mb-4" />
                            <div v-else class="py-8">
                                <Upload class="w-12 h-12 text-gray-300 mx-auto mb-3" />
                                <p class="text-gray-500">Click to upload QR code image</p>
                            </div>
                        </label>
                    </div>

                    <div v-if="props.vendor.qr_code_image" class="flex items-center gap-2 text-green-600 text-sm mb-4">
                        <Check class="w-4 h-4" /> QR code is set up
                    </div>

                    <button type="submit" :disabled="form.processing || !form.qr_code"
                        class="w-full py-3 bg-[#FF6B35] text-white font-medium rounded-lg hover:bg-orange-600 disabled:opacity-50 disabled:cursor-not-allowed">
                        {{ form.processing ? 'Uploading...' : 'Save QR Code' }}
                    </button>
                </form>

                <div class="mt-6 p-4 bg-[#F5F5F5] rounded-lg">
                    <h3 class="font-medium text-[#1A1A1A] mb-2">Instructions for customers:</h3>
                    <ol class="text-sm text-gray-600 space-y-1 list-decimal list-inside">
                        <li>Open GCash/Maya app</li>
                        <li>Scan the QR code</li>
                        <li>Enter the order total</li>
                        <li>Take a screenshot of payment confirmation</li>
                        <li>Upload screenshot when placing order</li>
                    </ol>
                </div>
            </div>
        </div>
    </VendorLayout>
</template>
