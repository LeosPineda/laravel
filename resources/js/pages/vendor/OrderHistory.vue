<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import OrderDetailModal from '@/components/vendor/OrderDetailModal.vue';
import OrderHistoryCard from '@/components/vendor/OrderHistoryCard.vue';
import { useToast } from '@/composables/useToast';
import { apiGet, apiDelete } from '@/composables/useApi';

const emit = defineEmits(['ordersUpdated']);
const toast = useToast();

const orders = ref<any[]>([]);
const loading = ref(false);
const activeTab = ref('completed');
const selectedOrders = ref<number[]>([]);

// Modal
const showOrderModal = ref(false);
const selectedOrderId = ref<number | null>(null);

// Pagination
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 20,
  total: 0
});

// Computed
const completedOrders = computed(() =>
  orders.value.filter(o => o.status === 'ready_for_pickup')
);
const cancelledOrders = computed(() =>
  orders.value.filter(o => o.status === 'cancelled')
);
const completedCount = computed(() => completedOrders.value.length);
const cancelledCount = computed(() => cancelledOrders.value.length);
const allSelected = computed(() =>
  orders.value.length > 0 && selectedOrders.value.length === orders.value.length
);

const loadOrders = async () => {
  loading.value = true;
  try {
    const response = await apiGet(`/api/vendor/orders?page=${pagination.value.current_page}&per_page=${pagination.value.per_page}`);

    if (response.ok) {
      const data = await response.json();
      orders.value = (data.orders || []).filter((o: any) =>
        o.status === 'ready_for_pickup' || o.status === 'cancelled'
      );
      pagination.value = data.pagination || pagination.value;
      selectedOrders.value = [];
    } else {
      console.error('Failed to load orders:', response.status);
      toast.error('Failed to load order history');
    }
  } catch (error) {
    console.error('Error loading orders:', error);
    toast.error('Failed to load order history');
  } finally {
    loading.value = false;
  }
};

const changePage = (page: number) => {
  pagination.value.current_page = page;
  loadOrders();
};

const openOrderDetail = (order: any) => {
  selectedOrderId.value = order.id;
  showOrderModal.value = true;
};

// Selection functions
const toggleOrderSelection = (orderId: number) => {
  const index = selectedOrders.value.indexOf(orderId);
  if (index > -1) {
    selectedOrders.value.splice(index, 1);
  } else {
    selectedOrders.value.push(orderId);
  }
};

const toggleSelectAll = () => {
  if (allSelected.value) {
    selectedOrders.value = [];
  } else {
    selectedOrders.value = orders.value.map(order => order.id);
  }
};

const deleteSelected = async () => {
  if (selectedOrders.value.length === 0) return;

  if (!confirm(`Delete ${selectedOrders.value.length} selected orders? This cannot be undone.`)) return;

  try {
    const deletePromises = selectedOrders.value.map(orderId =>
      apiDelete(`/api/vendor/orders/${orderId}`)
    );

    const responses = await Promise.all(deletePromises);
    const allSuccessful = responses.every(response => response.ok);

    if (allSuccessful) {
      toast.success(`${selectedOrders.value.length} orders deleted successfully`);
      selectedOrders.value = [];
      await loadOrders();
      emit('ordersUpdated');
    } else {
      toast.error('Some orders failed to delete');
    }
  } catch (error) {
    console.error('Error deleting orders:', error);
    toast.error('Failed to delete orders');
  }
};

const clearAll = async () => {
  if (orders.value.length === 0) return;

  if (!confirm(`Delete ALL ${orders.value.length} orders? This cannot be undone.`)) return;

  try {
    const deletePromises = orders.value.map(order =>
      apiDelete(`/api/vendor/orders/${order.id}`)
    );

    const responses = await Promise.all(deletePromises);
    const allSuccessful = responses.every(response => response.ok);

    if (allSuccessful) {
      toast.success('All orders cleared successfully');
      selectedOrders.value = [];
      await loadOrders();
      emit('ordersUpdated');
    } else {
      toast.error('Some orders failed to delete');
    }
  } catch (error) {
    console.error('Error clearing orders:', error);
    toast.error('Failed to clear orders');
  }
};

const unselectAll = () => {
  selectedOrders.value = [];
  toast.info('All selections cleared');
};

// Expose loadOrders for parent component
defineExpose({
  loadOrders
});

onMounted(() => {
  loadOrders();
});
</script>

<template>
  <!-- No header - parent Orders.vue handles navigation -->
  <div>
    <!-- Tabs -->
    <div class="border-b border-gray-200 px-6">
      <div class="flex gap-6">
        <button
          @click="activeTab = 'completed'"
          :class="[
            'py-3 text-sm font-medium border-b-2 transition-colors',
            activeTab === 'completed'
              ? 'border-orange-500 text-orange-600'
              : 'border-transparent text-gray-500 hover:text-gray-700'
          ]"
        >
          ✅ Completed
          <span v-if="completedCount > 0" class="ml-1 px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-xs">
            {{ completedCount }}
          </span>
        </button>
        <button
          @click="activeTab = 'cancelled'"
          :class="[
            'py-3 text-sm font-medium border-b-2 transition-colors',
            activeTab === 'cancelled'
              ? 'border-orange-500 text-orange-600'
              : 'border-transparent text-gray-500 hover:text-gray-700'
          ]"
        >
          ❌ Cancelled
          <span v-if="cancelledCount > 0" class="ml-1 px-2 py-0.5 bg-red-100 text-red-700 rounded-full text-xs">
            {{ cancelledCount }}
          </span>
        </button>
      </div>
    </div>

    <!-- Content -->
    <div class="p-6">
      <!-- Controls -->
      <div v-if="orders.length > 0" class="mb-6 p-4 bg-gray-50 rounded-lg border">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <button
              @click="toggleSelectAll"
              class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-100 transition-colors"
            >
              {{ allSelected ? 'Deselect All' : 'Select All' }}
            </button>
            <button
              v-if="selectedOrders.length > 0"
              @click="deleteSelected"
              class="px-4 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
            >
              Delete Selected ({{ selectedOrders.length }})
            </button>
            <button
              @click="unselectAll"
              class="px-4 py-2 text-sm bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors"
            >
              Clear Selection
            </button>
            <button
              @click="clearAll"
              class="px-4 py-2 text-sm bg-red-800 text-white rounded-lg hover:bg-red-900 transition-colors"
            >
              Clear All
            </button>
          </div>
          <div class="text-sm text-gray-600">
            {{ orders.length }} orders • {{ selectedOrders.length }} selected
          </div>
        </div>
      </div>

      <!-- Completed Orders -->
      <div v-if="activeTab === 'completed'">
        <div v-if="completedOrders.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <OrderHistoryCard
            v-for="order in completedOrders"
            :key="order.id"
            :order="order"
            :is-selected="selectedOrders.includes(order.id)"
            @toggle-selection="toggleOrderSelection"
            @view-order="openOrderDetail"
            @delete-order="loadOrders"
          />
        </div>

        <div v-else class="text-center py-16">
          <div class="text-6xl mb-4">✅</div>
          <h3 class="text-lg font-medium text-gray-900 mb-2">No completed orders</h3>
          <p class="text-gray-500">Completed orders will appear here</p>
        </div>
      </div>

      <!-- Cancelled Orders -->
      <div v-if="activeTab === 'cancelled'">
        <div v-if="cancelledOrders.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <OrderHistoryCard
            v-for="order in cancelledOrders"
            :key="order.id"
            :order="order"
            :is-selected="selectedOrders.includes(order.id)"
            @toggle-selection="toggleOrderSelection"
            @view-order="openOrderDetail"
            @delete-order="loadOrders"
          />
        </div>

        <div v-else class="text-center py-16">
          <div class="text-6xl mb-4">❌</div>
          <h3 class="text-lg font-medium text-gray-900 mb-2">No cancelled orders</h3>
          <p class="text-gray-500">Cancelled orders will appear here</p>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.total > pagination.per_page" class="px-6 pb-6 flex justify-between items-center">
        <div class="text-sm text-gray-500">
          {{ pagination.total }} orders total
        </div>
        <div class="flex gap-2">
          <button
            @click="changePage(pagination.current_page - 1)"
            :disabled="pagination.current_page <= 1"
            class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          >
            Previous
          </button>
          <span class="px-4 py-2 text-sm text-gray-600">
            Page {{ pagination.current_page }} of {{ pagination.last_page }}
          </span>
          <button
            @click="changePage(pagination.current_page + 1)"
            :disabled="pagination.current_page >= pagination.last_page"
            class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          >
            Next
          </button>
        </div>
      </div>
    </div>

    <!-- Order Detail Modal -->
    <OrderDetailModal
      :is-open="showOrderModal"
      :order-id="selectedOrderId"
      :processing="false"
      @close="showOrderModal = false"
    />
  </div>
</template>
