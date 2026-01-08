import { ref, computed } from 'vue'

interface CartItem {
  id: number
  product_id: number
  quantity: number
  unit_price: number
  selected_addons: AddonSelection[]
  special_instructions?: string
  total_price: number
  product: {
    id: number
    name: string
    price: number
    image_url?: string
    category?: string
  }
  vendor?: {
    id: number
    brand_name: string
    brand_image?: string
    qr_code_image?: string
  }
  vendor_id?: number
}

interface AddonSelection {
  addon_id: number
  quantity: number
  price: number
}

interface VendorCart {
  vendor: {
    id: number
    brand_name: string
    brand_image?: string
    qr_code_image?: string
  }
  items: CartItem[]
}

// Singleton pattern - state outside function for shared state
const cart = ref<CartItem[]>([])
const vendorCarts = ref<VendorCart[]>([])
const loading = ref(false)
const cartCount = ref(0)

export function useCart() {
  const cartByVendor = computed(() => {
    const grouped = cart.value.reduce((acc, item) => {
      const vendorId = item.vendor_id
      if (vendorId === undefined) return acc
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

  const cartTotal = computed(() => {
    return cart.value.reduce((total, item) => {
      return total + (item.quantity * item.product.price)
    }, 0)
  })

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
        vendorCarts.value = data.vendorCarts || []
        cartCount.value = Number(data.cartCount) || 0

        cart.value = vendorCarts.value.flatMap(vc => vc.items.map(item => ({
          ...item,
          vendor: vc.vendor,
          vendor_id: vc.vendor.id
        })))
      }
    } catch (error) {
      console.error('Error fetching cart:', error)
    } finally {
      loading.value = false
    }
  }

  const addToCart = async (
    productId: number,
    quantity = 1,
    addons: AddonSelection[] = [],
    specialInstructions?: string
  ) => {
    try {
      const response = await fetch('/api/customer/cart/items', {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'include',
        body: JSON.stringify({
          product_id: productId,
          quantity: quantity,
          addons: addons,
          special_instructions: specialInstructions || null
        })
      })

      if (response.ok) {
        const data = await response.json()
        cartCount.value = Number(data.cartCount) || cartCount.value
        await fetchCart()
        return { success: true, message: data.message, cartItem: data.cartItem }
      } else {
        const errorData = await response.json()
        return { success: false, message: errorData.message || 'Failed to add to cart' }
      }
    } catch (error) {
      console.error('Error adding to cart:', error)
      return { success: false, message: 'Failed to add to cart' }
    }
  }

  const updateCartItem = async (cartItemId: number, quantity: number, addons?: AddonSelection[]) => {
    try {
      const body: any = { quantity }
      if (addons !== undefined) {
        body.addons = addons
      }

      const response = await fetch(`/api/customer/cart/items/${cartItemId}`, {
        method: 'PUT',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'include',
        body: JSON.stringify(body)
      })

      if (response.ok) {
        const data = await response.json()
        await fetchCart()
        return { success: true, message: data.message }
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
      const response = await fetch(`/api/customer/cart/items/${cartItemId}`, {
        method: 'DELETE',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'include'
      })

      if (response.ok) {
        await fetchCart()
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
      const url = '/api/customer/cart/items'

      const response = await fetch(url, {
        method: 'DELETE',
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'include'
      })

      if (response.ok) {
        await fetchCart()
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
    cart,
    vendorCarts,
    loading,
    cartCount,
    cartByVendor,
    cartTotal,
    fetchCart,
    addToCart,
    updateCartItem,
    removeFromCart,
    clearCart
  }
}
