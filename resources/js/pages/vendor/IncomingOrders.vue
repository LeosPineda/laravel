<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import OrderDetailModal from '@/components/vendor/OrderDetailModal.vue';
import IncomingOrderCard from '@/components/vendor/IncomingOrderCard.vue';
import DeclineReasonModal from '@/components/vendor/DeclineReasonModal.vue';
import { useToast } from '@/composables/useToast';
import { apiGet, apiPatch } from '@/composables/useApi';

const emit = defineEmits(['ordersUpdated']);
const toast = useToast();
const page = usePage();

const allOrders = ref<any[]>([]);
const loading = ref(false);
const activeTab = ref('pending');

// Local filter state
const searchQuery = ref('');
let searchTimeout: ReturnType<typeof setTimeout> | null = null;

// Modals
const showOrderModal = ref(false);
const showDeclineModal = ref(false);
const selectedOrderId = ref<number | null>(null);
const selectedOrder = ref<any | null>(null);

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

// Silent refresh - doesn't show global loading spinner
const refreshOrders = async () => {
  try {
    const response = await apiGet('/api/vendor/orders?per_page=50');

    if (response.ok) {
      const data = await response.json();
      allOrders.value = (data.orders || []).filter((o: any) =>
        o.status === 'pending' || o.status === 'accepted'
      );
    }
  } catch (error) {
    console.error('Error refreshing orders:', error);
  }
};

// Initial load with loading spinner
const loadOrders = async () => {
  try {
    loading.value = true;
    await refreshOrders();
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
    const response = await apiPatch(`/api/vendor/orders/${order.id}/accept`);

    if (response.ok) {
      // Use customerAlert with sound for vendor actions
      toast.customerAlert(`✅ Order #${order.order_number} accepted! Customer notified.`, 'success');
      await refreshOrders();
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

// Show decline modal instead of confirm dialog
const declineOrder = (order: any) => {
  selectedOrder.value = order;
  showDeclineModal.value = true;
};

// Handle decline from modal
const handleDeclineOrder = async (reason: string) => {
  if (!selectedOrder.value) return;

  try {
    const response = await apiPatch(`/api/vendor/orders/${selectedOrder.value.id}/decline`, {
      decline_reason: reason
    });

    if (response.ok) {
      // Use customerAlert with sound for vendor actions
      toast.customerAlert(`❌ Order #${selectedOrder.value.order_number} declined. Customer notified.`, 'error');
      await refreshOrders();
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
    const response = await apiPatch(`/api/vendor/orders/${order.id}/ready`);

    if (response.ok) {
      // Use customerAlert with sound for vendor actions
      toast.customerAlert(`🔔 Order #${order.order_number} ready for pickup! Receipt sent.`, 'success');
      await refreshOrders();
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

// Expose loadOrders for parent component
defineExpose({
  loadOrders,
  refreshOrders
});

const debouncedSearch = () => {
  if (searchTimeout) {
    clearTimeout(searchTimeout);
  }
  searchTimeout = setTimeout(() => {
    // Search is handled by computed properties
  }, 300);
};

onMounted(async () => {
  await loadOrders();
  // NOTE: Real-time subscription is handled by parent Orders.vue
});
</script>

<template>
  <div>
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
      <!-- Search Control -->
      <div class="mb-6 p-4 bg-gray-50 rounded-lg border">
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
      :order-id="selectedOrderId || undefined"
      @close="showOrderModal = false"
    />

    <!-- Decline Reason Modal -->
    <DeclineReasonModal
      :is-open="showDeclineModal"
      :order="selectedOrder"
      @close="showDeclineModal = false"
      @decline="handleDeclineOrder"
    />
  </div>
</template>
