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
    updated_at: string;
  };
  isSelected: boolean;
}>();

const emit = defineEmits<{
  toggleSelection: [orderId: number];
  viewOrder: [order: any];
  downloadReceipt: [order: any];
  deleteOrder: [order: any];
}>();

const toast = useToast();

const downloadReceipt = async (order: any) => {
  try {
    toast.success('Downloading receipt...');

    const response = await fetch(`/api/vendor/orders/${order.id}/receipt/download`, {
      method: 'GET',
      headers: {
        'Accept': 'application/pdf'
      },
      credentials: 'include'
    });

    if (!response.ok) {
      const errorData = await response.json();
      throw new Error(errorData.message || 'Failed to generate receipt');
    }

    const blob = await response.blob();
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `receipt-${order.order_number}.pdf`;
    a.click();
    URL.revokeObjectURL(url);

    toast.success('Receipt downloaded successfully!');
  } catch (error) {
    console.error('Error downloading receipt:', error);
    toast.error('Failed to download receipt');
  }
};

const deleteOrder = async (order: any) => {
  if (!confirm(`Delete order #${order.order_number}? This cannot be undone.`)) return;

  try {
    const response = await fetch(`/api/vendor/orders/${order.id}`, {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      credentials: 'include'
    });

    if (response.ok) {
      toast.success('Order deleted successfully');
      emit('deleteOrder', order);
    } else {
      const error = await response.json();
      toast.error(error.message || 'Failed to delete order');
    }
  } catch (error) {
    console.error('Error deleting order:', error);
    toast.error('Failed to delete order');
  }
};

const formatTime = (dateString: string) => {
  if (!dateString) return '';
  return new Date(dateString).toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit' });
};

const getStatusInfo = (status: string) => {
  switch (status) {
    case 'ready_for_pickup':
      return {
        label: 'Completed',
        class: 'bg-green-100 text-green-700'
      };
    case 'cancelled':
      return {
        label: 'Cancelled',
        class: 'bg-red-100 text-red-700'
      };
    default:
      return {
        label: status,
        class: 'bg-gray-100 text-gray-700'
      };
  }
};

const getBorderClass = (status: string) => {
  switch (status) {
    case 'ready_for_pickup':
      return 'border-green-300';
    case 'cancelled':
      return 'border-red-300';
    default:
      return 'border-gray-300';
  }
};
</script>

<template>
  <div
    :class="[
      'bg-white rounded-lg border-2 p-6 relative hover:shadow-lg transition-shadow',
      getBorderClass(order.status)
    ]"
  >
    <!-- Checkbox -->
    <div class="absolute top-3 right-3">
      <input
        type="checkbox"
        :checked="isSelected"
        @change="emit('toggleSelection', order.id)"
        class="w-5 h-5 text-orange-600 bg-gray-100 border-gray-300 rounded focus:ring-orange-500"
      />
    </div>

    <div class="flex items-start justify-between mb-4">
      <div>
        <div class="mb-2 flex items-center gap-2">
          <span class="text-base font-bold text-gray-900">#{{ order.order_number?.replace('ORD-', '') }}</span>
          <span :class="['px-2 py-1 rounded text-xs font-medium', getStatusInfo(order.status).class]">
            {{ getStatusInfo(order.status).label }}
          </span>
        </div>
        <p class="text-sm text-gray-500">Table {{ order.table_number || 'N/A' }}</p>
        <p class="text-sm text-gray-500">{{ formatTime(order.updated_at) }}</p>
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
      <button
        v-if="order.status === 'ready_for_pickup'"
        @click="downloadReceipt(order)"
        class="flex-1 px-3 py-2 bg-blue-100 text-blue-600 rounded-lg text-sm hover:bg-blue-200 transition-colors"
      >
        Receipt
      </button>
      <button
        @click="deleteOrder(order)"
        class="px-3 py-2 bg-red-100 text-red-600 rounded-lg text-sm hover:bg-red-200 transition-colors"
      >
        Delete
      </button>
    </div>
  </div>
</template>
