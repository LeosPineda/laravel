<script setup lang="ts">
import { computed } from 'vue';
import { usePage, Link, router } from '@inertiajs/vue3';
import { Home, ShoppingCart, Bell, User, LogOut } from 'lucide-vue-next';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const cartCount = computed<number>(() => (page.props.cartCount as number) ?? 0);
const unreadNotifications = computed<number>(() => (page.props.unreadNotifications as number) ?? 0);

const currentRoute = computed(() => page.url);

const isActive = (path: string) => currentRoute.value.startsWith(path);

const logout = () => {
    router.post('/logout');
};
</script>

<template>
    <div class="min-h-screen bg-[#F5F5F5] overflow-x-hidden">
        <!-- Desktop Header -->
        <header class="hidden md:block bg-white shadow-sm border-b border-[#E0E0E0] sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <!-- Logo -->
                    <Link href="/customer/home" class="flex items-center gap-2">
                        <img src="/fast-food.png" alt="4Rodz" class="h-8 w-8" />
                        <span class="text-xl font-bold text-[#1A1A1A]">4Rodz Food Court</span>
                    </Link>

                    <!-- Navigation -->
                    <nav class="flex items-center gap-6">
                        <Link
                            href="/customer/home"
                            class="flex items-center gap-2 px-3 py-2 rounded-lg transition-colors"
                            :class="isActive('/customer/home') ? 'text-[#FF6B35] bg-orange-50' : 'text-[#1A1A1A] hover:text-[#FF6B35]'"
                        >
                            <Home class="w-5 h-5" />
                            <span>Home</span>
                        </Link>

                        <Link
                            href="/customer/cart"
                            class="flex items-center gap-2 px-3 py-2 rounded-lg transition-colors relative"
                            :class="isActive('/customer/cart') ? 'text-[#FF6B35] bg-orange-50' : 'text-[#1A1A1A] hover:text-[#FF6B35]'"
                        >
                            <ShoppingCart class="w-5 h-5" />
                            <span>Cart</span>
                            <span
                                v-if="cartCount > 0"
                                class="absolute -top-1 -right-1 bg-red-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center font-bold shadow-sm animate-pulse"
                            >
                                {{ cartCount > 9 ? '9+' : cartCount }}
                            </span>
                        </Link>

                        <Link
                            href="/customer/notifications"
                            class="flex items-center gap-2 px-3 py-2 rounded-lg transition-colors relative"
                            :class="isActive('/customer/notifications') ? 'text-[#FF6B35] bg-orange-50' : 'text-[#1A1A1A] hover:text-[#FF6B35]'"
                        >
                            <Bell class="w-5 h-5" />
                            <span>Notifications</span>
                            <span
                                v-if="unreadNotifications > 0"
                                class="absolute -top-1 -right-1 bg-red-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center font-bold shadow-sm animate-pulse"
                            >
                                {{ unreadNotifications > 9 ? '9+' : unreadNotifications }}
                            </span>
                        </Link>

                        <Link
                            href="/customer/profile"
                            class="flex items-center gap-2 px-3 py-2 rounded-lg transition-colors"
                            :class="isActive('/customer/profile') ? 'text-[#FF6B35] bg-orange-50' : 'text-[#1A1A1A] hover:text-[#FF6B35]'"
                        >
                            <User class="w-5 h-5" />
                            <span>Profile</span>
                        </Link>

                        <button
                            @click="logout"
                            class="flex items-center gap-2 px-3 py-2 rounded-lg text-[#1A1A1A] hover:text-red-500 transition-colors"
                        >
                            <LogOut class="w-5 h-5" />
                            <span>Logout</span>
                        </button>
                    </nav>
                </div>
            </div>
        </header>

        <!-- Mobile Header -->
        <header class="md:hidden bg-white shadow-sm border-b border-[#E0E0E0] sticky top-0 z-50">
            <div class="flex justify-between items-center h-14 px-4">
                <Link href="/customer/home" class="flex items-center gap-2">
                    <img src="/fast-food.png" alt="4Rodz" class="h-7 w-7" />
                    <span class="text-lg font-bold text-[#1A1A1A]">4Rodz</span>
                </Link>

                <!-- Cart with badge -->
                <Link href="/customer/cart" class="relative p-2">
                    <ShoppingCart class="w-6 h-6 text-[#1A1A1A]" />
                    <span
                        v-if="cartCount > 0"
                        class="absolute -top-0 -right-0 bg-red-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center font-bold shadow-sm"
                    >
                        {{ cartCount > 9 ? '9+' : cartCount }}
                    </span>
                </Link>
            </div>
        </header>

        <!-- Main Content -->
        <main class="pb-20 md:pb-8">
            <slot />
        </main>

        <!-- Mobile Bottom Navigation -->
        <nav class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-[#E0E0E0] z-50">
            <div class="flex justify-around items-center h-16">
                <Link
                    href="/customer/home"
                    class="flex flex-col items-center gap-1 p-2"
                    :class="isActive('/customer/home') ? 'text-[#FF6B35]' : 'text-gray-500'"
                >
                    <Home class="w-6 h-6" />
                    <span class="text-xs">Home</span>
                </Link>

                <Link
                    href="/customer/cart"
                    class="flex flex-col items-center gap-1 p-2 relative"
                    :class="isActive('/customer/cart') ? 'text-[#FF6B35]' : 'text-gray-500'"
                >
                    <ShoppingCart class="w-6 h-6" />
                    <span class="text-xs">Cart</span>
                    <span
                        v-if="cartCount > 0"
                        class="absolute top-0 right-0 bg-red-500 text-white text-xs w-4 h-4 rounded-full flex items-center justify-center font-bold"
                    >
                        {{ cartCount > 9 ? '9+' : cartCount }}
                    </span>
                </Link>

                <Link
                    href="/customer/notifications"
                    class="flex flex-col items-center gap-1 p-2 relative"
                    :class="isActive('/customer/notifications') ? 'text-[#FF6B35]' : 'text-gray-500'"
                >
                    <Bell class="w-6 h-6" />
                    <span class="text-xs">Alerts</span>
                    <span
                        v-if="unreadNotifications > 0"
                        class="absolute top-0 right-0 bg-red-500 text-white text-xs w-4 h-4 rounded-full flex items-center justify-center font-bold"
                    >
                        {{ unreadNotifications > 9 ? '9+' : unreadNotifications }}
                    </span>
                </Link>

                <Link
                    href="/customer/profile"
                    class="flex flex-col items-center gap-1 p-2"
                    :class="isActive('/customer/profile') ? 'text-[#FF6B35]' : 'text-gray-500'"
                >
                    <User class="w-6 h-6" />
                    <span class="text-xs">Profile</span>
                </Link>
            </div>
        </nav>
    </div>
</template>
