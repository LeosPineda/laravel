<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const showPassword = ref(false);
const logoPreview = ref<string | null>(null);

const form = useForm({
    name: '',
    email: '',
    password: '',
    brand_name: '',
    brand_logo: null as File | null,
});

const handleLogoChange = (event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0];
    if (file) {
        form.brand_logo = file;
        logoPreview.value = URL.createObjectURL(file);
    }
};

const removeLogo = () => {
    form.brand_logo = null;
    logoPreview.value = null;
};

const submit = () => {
    form.post('/superadmin/vendors', {
        forceFormData: true,
        onSuccess: () => {
            form.reset();
            logoPreview.value = null;
        },
    });
};

const logout = () => {
    router.post('/logout');
};
</script>

<template>
    <Head title="Create Vendor" />

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
                                class="px-4 py-2 text-sm font-medium text-[#1A1A1A]/70 hover:text-[#1A1A1A] hover:bg-[#F5F5F5] rounded-lg transition-colors"
                            >
                                Dashboard
                            </Link>
                            <Link
                                href="/superadmin/vendors"
                                class="px-4 py-2 text-sm font-medium text-white bg-[#FF6B35] rounded-lg"
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
        <main class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Back Link -->
            <Link
                href="/superadmin/vendors"
                class="inline-flex items-center gap-2 text-sm font-medium text-[#1A1A1A]/60 hover:text-[#FF6B35] transition-colors mb-6"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Vendors
            </Link>

            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-[#1A1A1A]">Add New Vendor</h1>
                <p class="text-[#1A1A1A]/60 mt-1">Create a new vendor account for the food court</p>
            </div>

            <!-- Form Card -->
            <div class="bg-white rounded-2xl border border-[#E0E0E0] overflow-hidden">
                <form @submit.prevent="submit">
                    <!-- Brand Info Section -->
                    <div class="p-6 border-b border-[#E0E0E0]">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 bg-[#FF6B35]/10 rounded-xl flex items-center justify-center">
                                <svg class="w-5 h-5 text-[#FF6B35]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-[#1A1A1A]">Brand Information</h3>
                                <p class="text-sm text-[#1A1A1A]/60">This will be displayed to customers</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <!-- Brand Name -->
                            <div>
                                <label for="brand_name" class="block text-sm font-semibold text-[#1A1A1A] mb-2">
                                    Brand Name
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <svg class="w-5 h-5 text-[#1A1A1A]/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                        </svg>
                                    </div>
                                    <input
                                        id="brand_name"
                                        type="text"
                                        v-model="form.brand_name"
                                        required
                                        autofocus
                                        placeholder="e.g., Jollibee, McDonald's, Pizza Hut"
                                        class="w-full pl-12 pr-4 py-3.5 border border-[#E0E0E0] rounded-xl bg-[#F5F5F5]/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#FF6B35]/20 focus:border-[#FF6B35] transition-all text-[#1A1A1A]"
                                        :class="{ 'border-red-500': form.errors.brand_name }"
                                    />
                                </div>
                                <p v-if="form.errors.brand_name" class="mt-2 text-sm text-red-600">{{ form.errors.brand_name }}</p>
                            </div>

                            <!-- Brand Logo -->
                            <div>
                                <label class="block text-sm font-semibold text-[#1A1A1A] mb-2">Brand Logo <span class="font-normal text-[#1A1A1A]/50">(optional)</span></label>
                                <div class="flex items-start gap-4">
                                    <!-- Preview -->
                                    <div class="w-24 h-24 rounded-xl border-2 border-dashed border-[#E0E0E0] flex items-center justify-center bg-[#F5F5F5] overflow-hidden">
                                        <img v-if="logoPreview" :src="logoPreview" alt="Logo preview" class="w-full h-full object-cover" />
                                        <svg v-else class="w-8 h-8 text-[#1A1A1A]/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <!-- Upload -->
                                    <div class="flex-1">
                                        <label class="block w-full cursor-pointer">
                                            <div class="px-4 py-3 border border-[#E0E0E0] rounded-xl bg-[#F5F5F5]/50 hover:bg-[#F5F5F5] transition-colors text-center">
                                                <span class="text-sm font-medium text-[#1A1A1A]">Choose logo</span>
                                                <p class="text-xs text-[#1A1A1A]/50 mt-1">PNG, JPG, GIF up to 2MB</p>
                                            </div>
                                            <input type="file" accept="image/*" @change="handleLogoChange" class="hidden" />
                                        </label>
                                        <button v-if="logoPreview" type="button" @click="removeLogo" class="mt-2 text-sm text-red-600 hover:underline">
                                            Remove logo
                                        </button>
                                    </div>
                                </div>
                                <p v-if="form.errors.brand_logo" class="mt-2 text-sm text-red-600">{{ form.errors.brand_logo }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Account Credentials Section -->
                    <div class="p-6">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-[#1A1A1A]">Account Credentials</h3>
                                <p class="text-sm text-[#1A1A1A]/60">Login details will be emailed to the vendor</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <!-- Owner Name -->
                            <div>
                                <label for="name" class="block text-sm font-semibold text-[#1A1A1A] mb-2">
                                    Owner Name
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <svg class="w-5 h-5 text-[#1A1A1A]/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                    <input
                                        id="name"
                                        type="text"
                                        v-model="form.name"
                                        required
                                        placeholder="Full name of vendor owner"
                                        class="w-full pl-12 pr-4 py-3.5 border border-[#E0E0E0] rounded-xl bg-[#F5F5F5]/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#FF6B35]/20 focus:border-[#FF6B35] transition-all text-[#1A1A1A]"
                                        :class="{ 'border-red-500': form.errors.name }"
                                    />
                                </div>
                                <p v-if="form.errors.name" class="mt-2 text-sm text-red-600">{{ form.errors.name }}</p>
                            </div>

                            <!-- Email -->
                            <div>
                                <label for="email" class="block text-sm font-semibold text-[#1A1A1A] mb-2">
                                    Email Address
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <svg class="w-5 h-5 text-[#1A1A1A]/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                        </svg>
                                    </div>
                                    <input
                                        id="email"
                                        type="email"
                                        v-model="form.email"
                                        required
                                        placeholder="vendor@example.com"
                                        class="w-full pl-12 pr-4 py-3.5 border border-[#E0E0E0] rounded-xl bg-[#F5F5F5]/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#FF6B35]/20 focus:border-[#FF6B35] transition-all text-[#1A1A1A]"
                                        :class="{ 'border-red-500': form.errors.email }"
                                    />
                                </div>
                                <p v-if="form.errors.email" class="mt-2 text-sm text-red-600">{{ form.errors.email }}</p>
                            </div>

                            <!-- Password -->
                            <div>
                                <label for="password" class="block text-sm font-semibold text-[#1A1A1A] mb-2">
                                    Password
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <svg class="w-5 h-5 text-[#1A1A1A]/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                    </div>
                                    <input
                                        id="password"
                                        :type="showPassword ? 'text' : 'password'"
                                        v-model="form.password"
                                        required
                                        placeholder="Minimum 8 characters"
                                        class="w-full pl-12 pr-12 py-3.5 border border-[#E0E0E0] rounded-xl bg-[#F5F5F5]/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#FF6B35]/20 focus:border-[#FF6B35] transition-all text-[#1A1A1A]"
                                        :class="{ 'border-red-500': form.errors.password }"
                                    />
                                    <button type="button" @click="showPassword = !showPassword" class="absolute right-4 top-1/2 -translate-y-1/2 text-[#1A1A1A]/40 hover:text-[#1A1A1A]/70 transition-colors">
                                        <svg v-if="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
                                        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    </button>
                                </div>
                                <p v-if="form.errors.password" class="mt-2 text-sm text-red-600">{{ form.errors.password }}</p>
                                <p class="mt-2 text-xs text-[#1A1A1A]/50">The vendor will receive an email with these login credentials</p>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="px-6 py-4 bg-[#F5F5F5] border-t border-[#E0E0E0]">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full py-3.5 px-4 bg-[#FF6B35] text-white font-semibold rounded-xl hover:bg-[#FF6B35]/90 focus:outline-none focus:ring-4 focus:ring-[#FF6B35]/20 transition-all disabled:opacity-50 disabled:cursor-not-allowed shadow-lg shadow-[#FF6B35]/25"
                        >
                            <span v-if="form.processing" class="flex items-center justify-center gap-2">
                                <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" />
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                                </svg>
                                Creating Vendor...
                            </span>
                            <span v-else class="flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Create Vendor
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</template>
