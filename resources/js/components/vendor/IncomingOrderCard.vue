<script setup lang="ts">
import { ref } from 'vue';
import { useToast } from '@/composables/useToast';

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

const toast = useToast();

const getCsrfToken = () => {
  return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
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
      emit('acceptOrder', order);
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
      emit('declineOrder', order);
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
      emit('markReady', order);
    } else {
      const error = await response.json();
      toast.error(error.message || 'Failed to mark order as ready');
    }
  } catch (error) {
    console.error('Error marking order as ready:', error);
    toast.error('Failed to mark order as ready');
  }
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
      <span class="text-lg font-bold text-orange-600">₱{{ parseFloat(order.total_amount).toFixed(0) }}</span>
    </div>

    <div class="flex gap-2">
      <button
        @click="emit('viewOrder', order)"
        class="flex-1 px-3 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200 transition-colors"
      >
        View Order
      </button>

      <template v-if="order.status === 'pending'">
        <button
          @click="declineOrder(order)"
          class="px-3 py-2 bg-red-100 text-red-600 rounded-lg text-sm hover:bg-red-200 transition-colors"
        >
          Decline
        </button>
        <button
          @click="acceptOrder(order)"
          class="flex-1 px-3 py-2 bg-green-500 text-white rounded-lg text-sm hover:bg-green-600 transition-colors"
        >
          Accept
        </button>
      </template>

      <template v-else-if="order.status === 'accepted'">
        <button
          @click="markReady(order)"
          class="flex-1 px-3 py-2 bg-blue-500 text-white rounded-lg text-sm hover:bg-blue-600 transition-colors"
        >
          Mark Ready
        </button>
      </template>
    </div>
  </div>
</template>
