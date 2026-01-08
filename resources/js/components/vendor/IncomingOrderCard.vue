<script setup lang="ts">
import { ref } from 'vue';

const props = defineProps<{
  order: {
    id: number;
    order_number: string;
    status: string;
    table_number: string | null;
    total_amount: number;
    created_at: string;
  };
}>();

const emit = defineEmits<{
  viewOrder: [order: any];
  acceptOrder: [order: any];
  declineOrder: [order: any];
  markReady: [order: any];
}>();

// Loading states for buttons
const accepting = ref(false);
const declining = ref(false);
const markingReady = ref(false);

// Emit events with loading states
const acceptOrder = async (order: any) => {
  accepting.value = true;
  emit('acceptOrder', order);
  // Parent will reload orders, so loading state will be cleared when component unmounts
  setTimeout(() => { accepting.value = false; }, 5000); // Fallback timeout
};

const declineOrder = (order: any) => {
  declining.value = true;
  emit('declineOrder', order);
  setTimeout(() => { declining.value = false; }, 5000);
};

const markReady = async (order: any) => {
  markingReady.value = true;
  emit('markReady', order);
  setTimeout(() => { markingReady.value = false; }, 5000);
};

const formatTime = (dateString: string) => {
  if (!dateString) return '';
  return new Date(dateString).toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit' });
};

const getStatusInfo = (status: string) => {
  switch (status) {
    case 'pending':
      return {
        label: 'New',
        class: 'bg-yellow-100 text-yellow-700',
        borderClass: 'border-yellow-300'
      };
    case 'accepted':
      return {
        label: 'Preparing',
        class: 'bg-blue-100 text-blue-700',
        borderClass: 'border-blue-300'
      };
    default:
      return {
        label: status,
        class: 'bg-gray-100 text-gray-700',
        borderClass: 'border-gray-300'
      };
  }
};
</script>

<template>
  <div
    :class="[
      'bg-white rounded-lg border-2 p-6 hover:shadow-lg transition-shadow',
      getStatusInfo(order.status).borderClass
    ]"
  >
    <div class="flex items-start justify-between mb-4">
      <div>
        <div class="mb-2 flex items-center gap-2">
          <span class="text-base font-bold text-gray-900">#{{ order.order_number?.replace('ORD-', '') }}</span>
          <span :class="['px-2 py-1 rounded text-xs font-medium', getStatusInfo(order.status).class]">
            {{ getStatusInfo(order.status).label }}
          </span>
        </div>
        <p class="text-sm text-gray-500">Table {{ order.table_number || 'N/A' }}</p>
        <p class="text-sm text-gray-500">{{ formatTime(order.created_at) }}</p>
      </div>
      <span class="text-lg font-bold text-orange-600">₱{{ (Number(order.total_amount) || 0).toFixed(0) }}</span>
    </div>

    <div class="flex gap-2">
      <button
        @click="emit('viewOrder', order)"
        :disabled="accepting || declining || markingReady"
        class="flex-1 px-3 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200 transition-colors disabled:opacity-50"
      >
        View
      </button>

      <template v-if="order.status === 'pending'">
        <button
          @click="declineOrder(order)"
          :disabled="accepting || declining"
          class="px-3 py-2 bg-red-100 text-red-600 rounded-lg text-sm hover:bg-red-200 transition-colors disabled:opacity-50 flex items-center gap-1"
        >
          <svg v-if="declining" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
          </svg>
          {{ declining ? '...' : 'Decline' }}
        </button>
        <button
          @click="acceptOrder(order)"
          :disabled="accepting || declining"
          class="flex-1 px-3 py-2 bg-green-500 text-white rounded-lg text-sm hover:bg-green-600 transition-colors disabled:opacity-50 flex items-center justify-center gap-1"
        >
          <svg v-if="accepting" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
          </svg>
          {{ accepting ? 'Accepting...' : 'Accept' }}
        </button>
      </template>

      <template v-else-if="order.status === 'accepted'">
        <button
          @click="markReady(order)"
          :disabled="markingReady"
          class="flex-1 px-3 py-2 bg-blue-500 text-white rounded-lg text-sm hover:bg-blue-600 transition-colors disabled:opacity-50 flex items-center justify-center gap-1"
        >
          <svg v-if="markingReady" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
          </svg>
          {{ markingReady ? 'Processing...' : 'Mark Ready' }}
        </button>
      </template>
    </div>
  </div>
</template>
