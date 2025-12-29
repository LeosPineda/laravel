import { ref, computed } from 'vue'

interface CartItem {
  id: number
  product_id: number
  vendor_id: number
  quantity: number
  product: {
    name: string
    price: number
    image_url?: string
    category?: string
  }
  vendor: {
    brand_name: string
  }
  created_at: string
  updated_at: string
}

export function useCart() {
  const cart = ref<CartItem[]>([])
  const loading = ref(false)
  const cartCount = ref(0)

  // Group cart items by vendor for multi-vendor display
  const cartByVendor = computed(() => {
    const grouped = cart.value.reduce((acc, item) => {
      const vendorId = item.vendor_id
      if (!acc[vendorId]) {
        acc[vendorId] = {
          vendor: item.vendor,
          items: [],
          total: 0
        }
      }
      acc[vendorId].items.push(item)
      acc[vendorId].total += item.quantity * item.product.price
      return acc
    }, {} as Record<number, { vendor: any; items: CartItem[]; total: number }>)

    return Object.values(grouped)
  })

  // Total cart value
  const cartTotal = computed(() => {
    return cart.value.reduce((total, item) => {
      return total + (item.quantity * item.product.price)
    }, 0)
  })

  // API functions
  const fetchCart = async () => {
    loading.value = true
    try {
      const response = await fetch('/api/customer/cart', {
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'include'
      })

      if (response.ok) {
        const data = await response.json()
        cart.value = data.cart || []
        cartCount.value = data.count || 0
      }
    } catch (error) {
      console.error('Error fetching cart:', error)
    } finally {
      loading.value = false
    }
  }

  const addToCart = async (productId: number, quantity = 1) => {
    try {
      const response = await fetch('/api/customer/cart', {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'include',
        body: JSON.stringify({
          product_id: productId,
          quantity: quantity
        })
      })

      if (response.ok) {
        const data = await response.json()
        await fetchCart() // Refresh cart
        return { success: true, message: data.message }
      } else {
        const errorData = await response.json()
        return { success: false, message: errorData.message || 'Failed to add to cart' }
      }
    } catch (error) {
      console.error('Error adding to cart:', error)
      return { success: false, message: 'Failed to add to cart' }
    }
  }

  const updateCartItem = async (cartItemId: number, quantity: number) => {
    try {
      const response = await fetch(`/api/customer/cart/${cartItemId}`, {
        method: 'PUT',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'include',
        body: JSON.stringify({
          quantity: quantity
        })
      })

      if (response.ok) {
        await fetchCart() // Refresh cart
        return { success: true }
      } else {
        const errorData = await response.json()
        return { success: false, message: errorData.message || 'Failed to update cart' }
      }
    } catch (error) {
      console.error('Error updating cart item:', error)
      return { success: false, message: 'Failed to update cart' }
    }
  }

  const removeFromCart = async (cartItemId: number) => {
    try {
      const response = await fetch(`/api/customer/cart/${cartItemId}`, {
        method: 'DELETE',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'include'
      })

      if (response.ok) {
        await fetchCart() // Refresh cart
        return { success: true }
      } else {
        const errorData = await response.json()
        return { success: false, message: errorData.message || 'Failed to remove from cart' }
      }
    } catch (error) {
      console.error('Error removing from cart:', error)
      return { success: false, message: 'Failed to remove from cart' }
    }
  }

  const clearCart = async (vendorId?: number) => {
    try {
      const url = vendorId
        ? `/api/customer/cart/clear/${vendorId}`
        : '/api/customer/cart/clear'

      const response = await fetch(url, {
        method: 'DELETE',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'include'
      })

      if (response.ok) {
        await fetchCart() // Refresh cart
        return { success: true }
      } else {
        const errorData = await response.json()
        return { success: false, message: errorData.message || 'Failed to clear cart' }
      }
    } catch (error) {
      console.error('Error clearing cart:', error)
      return { success: false, message: 'Failed to clear cart' }
    }
  }

  return {
    // State
    cart,
    loading,
    cartCount,

    // Computed
    cartByVendor,
    cartTotal,

    // Methods
    fetchCart,
    addToCart,
    updateCartItem,
    removeFromCart,
    clearCart
  }
}
