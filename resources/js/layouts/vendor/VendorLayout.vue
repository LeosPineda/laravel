<script setup lang="ts">
import { ref, computed } from 'vue';
import { usePage, Link, router } from '@inertiajs/vue3';
import { LayoutDashboard, ShoppingBag, Package, BarChart3, QrCode, Store, LogOut, Bell, X, Clock, Check, AlertCircle } from 'lucide-vue-next';

interface VendorNotification {
    id: number;
    type: string;
    message: string;
    time: string;
    isNew: boolean;
}

const page = usePage();
const user = computed(() => page.props.auth?.user);
const vendor = computed(() => page.props.vendor as { brand_name: string; brand_image: string | null } | undefined);
const unreadNotifications = computed<number>(() => (page.props.unreadNotifications as number) ?? 0);
const notifications = computed<VendorNotification[]>(() => (page.props.notifications as VendorNotification[]) ?? []);

const showNotifModal = ref(false);

const currentRoute = computed(() => page.url);
const isActive = (path: string) => currentRoute.value.startsWith(path);

const logout = () => {
    router.post('/logout');
};

const navItems = [
    { href: '/vendor/dashboard', label: 'Dashboard', icon: LayoutDashboard },
    { href: '/vendor/orders', label: 'Orders', icon: ShoppingBag },
    { href: '/vendor/products', label: 'Products', icon: Package },
    { href: '/vendor/analytics', label: 'Analytics', icon: BarChart3 },
    { href: '/vendor/qr', label: 'QR Code', icon: QrCode },
];
</script>

<template>
    <div class="min-h-screen bg-[#F5F5F5] overflow-x-hidden">
        <!-- Desktop Sidebar -->
        <aside class="hidden md:fixed md:inset-y-0 md:left-0 md:flex md:w-64 md:flex-col bg-white border-r border-[#E0E0E0]">
            <!-- Logo -->
            <div class="flex items-center gap-3 h-16 px-6 border-b border-[#E0E0E0]">
                <div class="w-10 h-10 rounded-lg bg-[#FF6B35] flex items-center justify-center overflow-hidden">
                    <img v-if="vendor?.brand_image" :src="`/storage/${vendor.brand_image}`" class="w-full h-full object-cover" />
                    <Store v-else class="w-5 h-5 text-white" />
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-[#1A1A1A] truncate">{{ vendor?.brand_name || 'Vendor' }}</p>
                    <p class="text-xs text-gray-500">Vendor Dashboard</p>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-4 py-6 space-y-1">
                <Link
                    v-for="item in navItems"
                    :key="item.href"
                    :href="item.href"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors"
                    :class="isActive(item.href) ? 'bg-orange-50 text-[#FF6B35]' : 'text-gray-600 hover:bg-gray-50 hover:text-[#1A1A1A]'"
                >
                    <component :is="item.icon" class="w-5 h-5" />
                    <span>{{ item.label }}</span>
                </Link>
            </nav>

            <!-- Footer -->
            <div class="p-4 border-t border-[#E0E0E0]">
                <button
                    @click="logout"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-red-500 hover:bg-red-50 transition-colors w-full"
                >
                    <LogOut class="w-5 h-5" />
                    <span>Logout</span>
                </button>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="md:pl-64">
            <!-- Top Bar -->
            <header class="hidden md:flex items-center justify-between h-16 px-6 bg-white border-b border-[#E0E0E0]">
                <h1 class="text-lg font-semibold text-[#1A1A1A]">
                    <slot name="title">Dashboard</slot>
                </h1>
                <div class="flex items-center gap-4">
                    <button @click="showNotifModal = true" class="relative p-2 text-gray-500 hover:text-[#FF6B35] transition-colors">
                        <Bell class="w-5 h-5" />
                        <span
                            v-if="unreadNotifications > 0"
                            class="absolute -top-0 -right-0 bg-red-500 text-white text-xs w-4 h-4 rounded-full flex items-center justify-center font-bold animate-pulse"
                        >
                            {{ unreadNotifications > 9 ? '9+' : unreadNotifications }}
                        </span>
                    </button>
                </div>
            </header>

            <!-- Mobile Header -->
            <header class="md:hidden bg-white border-b border-[#E0E0E0] sticky top-0 z-40">
                <div class="flex items-center justify-between h-14 px-4">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-[#FF6B35] flex items-center justify-center overflow-hidden">
                            <img v-if="vendor?.brand_image" :src="`/storage/${vendor.brand_image}`" class="w-full h-full object-cover" />
                            <Store v-else class="w-4 h-4 text-white" />
                        </div>
                        <span class="font-semibold text-[#1A1A1A]">{{ vendor?.brand_name || 'Vendor' }}</span>
                    </div>
                    <button @click="showNotifModal = true" class="relative p-2">
                        <Bell class="w-5 h-5 text-gray-500" />
                        <span
                            v-if="unreadNotifications > 0"
                            class="absolute top-0 right-0 bg-red-500 text-white text-xs w-4 h-4 rounded-full flex items-center justify-center font-bold"
                        >
                            {{ unreadNotifications > 9 ? '9+' : unreadNotifications }}
                        </span>
                    </button>
                </div>
            </header>

            <!-- Page Content -->
            <main class="p-4 md:p-6 pb-24 md:pb-6">
                <slot />
            </main>
        </div>

        <!-- Mobile Bottom Navigation -->
        <nav class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-[#E0E0E0] z-50">
            <div class="flex justify-around items-center h-16">
                <Link
                    href="/vendor/dashboard"
                    class="flex flex-col items-center gap-1 p-2"
                    :class="isActive('/vendor/dashboard') ? 'text-[#FF6B35]' : 'text-gray-500'"
                >
                    <LayoutDashboard class="w-5 h-5" />
                    <span class="text-xs">Home</span>
                </Link>
                <Link
                    href="/vendor/orders"
                    class="flex flex-col items-center gap-1 p-2"
                    :class="isActive('/vendor/orders') ? 'text-[#FF6B35]' : 'text-gray-500'"
                >
                    <ShoppingBag class="w-5 h-5" />
                    <span class="text-xs">Orders</span>
                </Link>
                <Link
                    href="/vendor/products"
                    class="flex flex-col items-center gap-1 p-2"
                    :class="isActive('/vendor/products') ? 'text-[#FF6B35]' : 'text-gray-500'"
                >
                    <Package class="w-5 h-5" />
                    <span class="text-xs">Products</span>
                </Link>
                <Link
                    href="/vendor/analytics"
                    class="flex flex-col items-center gap-1 p-2"
                    :class="isActive('/vendor/analytics') ? 'text-[#FF6B35]' : 'text-gray-500'"
                >
                    <BarChart3 class="w-5 h-5" />
                    <span class="text-xs">Stats</span>
                </Link>
                <Link
                    href="/vendor/qr"
                    class="flex flex-col items-center gap-1 p-2"
                    :class="isActive('/vendor/qr') ? 'text-[#FF6B35]' : 'text-gray-500'"
                >
                    <QrCode class="w-5 h-5" />
                    <span class="text-xs">QR</span>
                </Link>
            </div>
        </nav>

        <!-- Notification Modal -->
        <Teleport to="body">
            <div v-if="showNotifModal" class="fixed inset-0 z-50 flex items-start justify-center pt-20 px-4">
                <div class="fixed inset-0 bg-black/50" @click="showNotifModal = false"></div>
                <div class="relative bg-white rounded-2xl w-full max-w-md max-h-[70vh] overflow-hidden shadow-xl">
                    <div class="flex items-center justify-between p-4 border-b border-gray-100">
                        <h3 class="font-semibold text-[#1A1A1A]">Notifications</h3>
                        <button @click="showNotifModal = false" class="p-1 hover:bg-gray-100 rounded-full">
                            <X class="w-5 h-5 text-gray-500" />
                        </button>
                    </div>
                    <div class="overflow-y-auto max-h-[50vh]">
                        <div v-if="notifications.length === 0" class="p-8 text-center">
                            <Bell class="w-12 h-12 text-gray-300 mx-auto mb-3" />
                            <p class="text-gray-500">No notifications yet</p>
                            <p class="text-sm text-gray-400">Customer orders will appear here</p>
                        </div>
                        <div v-else class="divide-y divide-gray-100">
                            <div
                                v-for="notif in notifications"
                                :key="notif.id"
                                class="p-4 hover:bg-gray-50 transition-colors"
                                :class="notif.isNew ? 'bg-orange-50/50' : ''"
                            >
                                <div class="flex gap-3">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0"
                                        :class="notif.type === 'order' ? 'bg-green-100' : notif.type === 'payment' ? 'bg-blue-100' : 'bg-gray-100'"
                                    >
                                        <ShoppingBag v-if="notif.type === 'order'" class="w-5 h-5 text-green-600" />
                                        <Check v-else-if="notif.type === 'payment'" class="w-5 h-5 text-blue-600" />
                                        <AlertCircle v-else class="w-5 h-5 text-gray-600" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm text-[#1A1A1A]">{{ notif.message }}</p>
                                        <div class="flex items-center gap-1 mt-1 text-xs text-gray-500">
                                            <Clock class="w-3 h-3" />
                                            <span>{{ notif.time }}</span>
                                        </div>
                                    </div>
                                    <div v-if="notif.isNew" class="w-2 h-2 rounded-full bg-[#FF6B35] shrink-0 mt-2"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>
