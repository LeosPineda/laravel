<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import OrderDetailModal from '@/components/vendor/OrderDetailModal.vue';
import IncomingOrderCard from '@/components/vendor/IncomingOrderCard.vue';
import { useToast } from '@/composables/useToast';

const props = defineProps({
  filters: {
    type: Object,
    default: () => ({})
  }
});

const emit = defineEmits(['ordersUpdated']);
const toast = useToast();
const page = usePage();

const vendorId = ref<number | null>(null);
const allOrders = ref<any[]>([]);
const loading = ref(false);
const activeTab = ref('pending');

// Local filter state
const searchQuery = ref('');
let searchTimeout: NodeJS.Timeout | null = null;

// Modal
const showOrderModal = ref(false);
const selectedOrderId = ref<number | null>(null);

// Computed
const pendingOrders = computed(() => {
  let filtered = allOrders.value.filter(o => o.status === 'pending');
  return applySearch(filtered);
});

const acceptedOrders = computed(() => {
  let filtered = allOrders.value.filter(o => o.status === 'accepted');
  return applySearch(filtered);
});

const pendingCount = computed(() => pendingOrders.value.length);
const acceptedCount = computed(() => acceptedOrders.value.length);

// Helper function to apply search
const applySearch = (orders: any[]) => {
  if (searchQuery.value) {
    const searchTerm = searchQuery.value.toLowerCase();
    return orders.filter(order =>
      order.order_number?.toLowerCase().includes(searchTerm) ||
      order.table_number?.toString().includes(searchTerm)
    );
  }
  return orders;
};

const formatTime = (dateString: string) => {
  if (!dateString) return '';
  return new Date(dateString).toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit' });
};

const getCsrfToken = () => {
  return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
};

const loadOrders = async () => {
  try {
    loading.value = true;
    const response = await fetch('/api/vendor/orders?per_page=50', {
      method: 'GET',
      headers: {
        'X-CSRF-TOKEN': getCsrfToken(),
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      credentials: 'include'
    });

    if (response.ok) {
      const data = await response.json();
      allOrders.value = (data.orders || []).filter((o: any) =>
        o.status === 'pending' || o.status === 'accepted'
      );
    } else {
      console.error('Failed to load orders:', response.status);
      toast.error('Failed to load orders');
    }
  } catch (error) {
    console.error('Error loading orders:', error);
    toast.error('Failed to load orders');
  } finally {
    loading.value = false;
  }
};

const openOrderDetail = (order: any) => {
  selectedOrderId.value = order.id;
  showOrderModal.value = true;
};

const acceptOrder = async (order: any) => {
  try {
    const response = await fetch(`/api/vendor/orders/${order.id}/accept`, {
      method: 'PATCH',
      headers: {
        'X-CSRF-TOKEN': getCsrfToken(),
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      credentials: 'include'
    });

    if (response.ok) {
      toast.success(`Order #${order.order_number} accepted! Customer will be notified.`);
      await loadOrders();
      activeTab.value = 'accepted';
      emit('ordersUpdated');
    } else {
      const error = await response.json();
      toast.error(error.message || 'Failed to accept order');
    }
  } catch (error) {
    console.error('Error accepting order:', error);
    toast.error('Failed to accept order');
  }
};

const declineOrder = async (order: any) => {
  if (!confirm(`Decline order #${order.order_number}? The customer will be notified.`)) return;

  try {
    const response = await fetch(`/api/vendor/orders/${order.id}/decline`, {
      method: 'PATCH',
      headers: {
        'X-CSRF-TOKEN': getCsrfToken(),
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      credentials: 'include'
    });

    if (response.ok) {
      toast.warning(`Order #${order.order_number} declined. Customer will be notified.`);
      await loadOrders();
      emit('ordersUpdated');
    } else {
      const error = await response.json();
      toast.error(error.message || 'Failed to decline order');
    }
  } catch (error) {
    console.error('Error declining order:', error);
    toast.error('Failed to decline order');
  }
};

const markReady = async (order: any) => {
  try {
    const response = await fetch(`/api/vendor/orders/${order.id}/ready`, {
      method: 'PATCH',
      headers: {
        'X-CSRF-TOKEN': getCsrfToken(),
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      credentials: 'include'
    });

    if (response.ok) {
      toast.success(`Order #${order.order_number} is ready! Customer notified + receipt sent.`);
      await loadOrders();
      emit('ordersUpdated');
    } else {
      const error = await response.json();
      toast.error(error.message || 'Failed to mark order as ready');
    }
  } catch (error) {
    console.error('Error marking order as ready:', error);
    toast.error('Failed to mark order as ready');
  }
};

// Real-time subscription
const subscribeToChannel = () => {
  if (window.Echo && vendorId.value) {
    window.Echo.private(`vendor-orders.${vendorId.value}`)
      .listen('.OrderReceived', (e: any) => {
        loadOrders();
        toast.success(`New Order #${e.order?.order_number}!`);
      })
      .listen('.OrderStatusChanged', (e: any) => {
        loadOrders();
      });
  }
};

const debouncedSearch = () => {
  if (searchTimeout) {
    clearTimeout(searchTimeout);
  }
  searchTimeout = setTimeout(() => {
    // Search is handled by computed properties
  }, 300);
};

onMounted(async () => {
  const user = page.props.auth?.user;
  vendorId.value = user?.vendor?.id || null;

  await loadOrders();
  subscribeToChannel();
});

onUnmounted(() => {
  if (window.Echo && vendorId.value) {
    window.Echo.leave(`vendor-orders.${vendorId.value}`);
  }
});
</script>

<template>
  <VendorLayout>
    <div class="bg-white">
      <!-- Header with navigation back to dashboard -->
      <div class="bg-white border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-4">
            <Link
              href="/vendor/dashboard"
              class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-2"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
              Back to Dashboard
            </Link>
            <div class="w-px h-6 bg-gray-300"></div>
            <h1 class="text-xl font-bold text-gray-900">Incoming Orders</h1>
          </div>
          <div class="flex items-center gap-2">
            <button
              @click="loadOrders"
              :disabled="loading"
              class="px-3 py-2 text-sm text-gray-600 hover:text-gray-900 flex items-center gap-2"
            >
              <svg class="w-4 h-4" :class="{ 'animate-spin': loading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
              Refresh
            </button>
          </div>
        </div>
      </div>

      <!-- Tabs -->
      <div class="border-b border-gray-200 px-6">
        <div class="flex gap-6">
          <button
            @click="activeTab = 'pending'"
            :class="[
              'py-3 text-sm font-medium border-b-2 transition-colors',
              activeTab === 'pending'
                ? 'border-orange-500 text-orange-600'
                : 'border-transparent text-gray-500 hover:text-gray-700'
            ]"
          >
            🔔 Pending
            <span v-if="pendingCount > 0" class="ml-1 px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded-full text-xs">
              {{ pendingCount }}
            </span>
          </button>
          <button
            @click="activeTab = 'accepted'"
            :class="[
              'py-3 text-sm font-medium border-b-2 transition-colors',
              activeTab === 'accepted'
                ? 'border-orange-500 text-orange-600'
                : 'border-transparent text-gray-500 hover:text-gray-700'
            ]"
          >
            🍳 Preparing
            <span v-if="acceptedCount > 0" class="ml-1 px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full text-xs">
              {{ acceptedCount }}
            </span>
          </button>
        </div>
      </div>

      <!-- Content -->
      <div class="p-6">
        <!-- Loading -->
        <div v-if="loading" class="text-center py-12">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-orange-500 mx-auto"></div>
          <p class="text-gray-500 mt-4">Loading orders...</p>
        </div>

        <!-- Search Control -->
        <div v-if="!loading" class="mb-6 p-4 bg-gray-50 rounded-lg border">
          <div class="flex items-center gap-4">
            <div class="flex items-center gap-2">
              <label class="text-sm text-gray-600">Search:</label>
              <input
                v-model="searchQuery"
                @input="debouncedSearch"
                type="text"
                placeholder="Order number, table..."
                class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 w-64"
              />
            </div>
            <div class="text-sm text-gray-600 ml-auto">
              {{ allOrders.length }} orders
            </div>
          </div>
        </div>

        <!-- Pending Orders -->
        <div v-if="activeTab === 'pending'">
          <div v-if="pendingOrders.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <IncomingOrderCard
              v-for="order in pendingOrders"
              :key="order.id"
              :order="order"
              @view-order="openOrderDetail"
              @accept-order="acceptOrder"
              @decline-order="declineOrder"
            />
          </div>

          <div v-else class="text-center py-16">
            <div class="text-6xl mb-4">📭</div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No pending orders</h3>
            <p class="text-gray-500">New orders will appear here automatically</p>
          </div>
        </div>

        <!-- Accepted Orders (Preparing) -->
        <div v-if="activeTab === 'accepted'">
          <div v-if="acceptedOrders.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <IncomingOrderCard
              v-for="order in acceptedOrders"
              :key="order.id"
              :order="order"
              @view-order="openOrderDetail"
              @mark-ready="markReady"
            />
          </div>

          <div v-else class="text-center py-16">
            <div class="text-6xl mb-4">👨‍🍳</div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No orders being prepared</h3>
            <p class="text-gray-500">Accepted orders will appear here</p>
          </div>
        </div>
      </div>

      <!-- Order Detail Modal -->
      <OrderDetailModal
        :is-open="showOrderModal"
        :order-id="selectedOrderId"
        @close="showOrderModal = false"
      />
    </div>
  </VendorLayout>
</template>
