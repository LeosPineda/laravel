<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Addon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class CartController extends Controller
{
    /**
     * Get customer's cart data (multi-vendor)
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();

            // Get all cart items for this customer, grouped by vendor
            $cartItems = CartItem::with([
                'cart.vendor:id,brand_name,brand_image,qr_code_image',
                'product:id,name,image_url',
                'orderItem.addons'
            ])
            ->whereHas('cart', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->get();

            // Group items by vendor
            $vendorCarts = [];
            foreach ($cartItems as $item) {
                $vendorId = $item->cart->vendor->id;

                if (!isset($vendorCarts[$vendorId])) {
                    $vendorCarts[$vendorId] = [
                        'vendor' => [
                            'id' => $item->cart->vendor->id,
                            'brand_name' => $item->cart->vendor->brand_name,
                            'brand_image' => $item->cart->vendor->brand_image,
                            'qr_code_image' => $item->cart->vendor->qr_code_image,
                        ],
                        'items' => []
                    ];
                }

                $vendorCarts[$vendorId]['items'][] = [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'selected_addons' => $item->selected_addons,
                    'product' => [
                        'id' => $item->product->id,
                        'name' => $item->product->name,
                        'image_url' => $item->product->image_url,
                    ]
                ];
            }

            // Calculate cart count
            $cartCount = $cartItems->sum('quantity');

            return response()->json([
                'vendorCarts' => array_values($vendorCarts),
                'cartCount' => $cartCount,
                'success' => true
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting cart: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error retrieving cart',
                'success' => false
            ], 500);
        }
    }

    /**
     * Add item to cart
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'vendor_id' => 'required|exists:vendors,id',
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1',
                'unit_price' => 'required|numeric|min:0',
                'selected_addons' => 'nullable|array',
                'selected_addons.*.id' => 'exists:addons,id',
                'selected_addons.*.name' => 'string',
                'selected_addons.*.price' => 'numeric|min:0'
            ]);

            $user = Auth::user();

            // Get or create cart for this vendor
            $cart = Cart::firstOrCreate(
                ['user_id' => $user->id, 'vendor_id' => $validated['vendor_id']],
                ['created_at' => now(), 'updated_at' => now()]
            );

            // Check if item already exists with same addons
            $existingItem = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $validated['product_id'])
                ->where('selected_addons', json_encode($validated['selected_addons'] ?? []))
                ->first();

            if ($existingItem) {
                // Update quantity
                $existingItem->update([
                    'quantity' => $existingItem->quantity + $validated['quantity'],
                    'updated_at' => now()
                ]);
                $cartItem = $existingItem;
            } else {
                // Create new cart item
                $cartItem = CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $validated['product_id'],
                    'quantity' => $validated['quantity'],
                    'unit_price' => $validated['unit_price'],
                    'selected_addons' => $validated['selected_addons'] ?? []
                ]);
            }

            // Get updated cart count
            $cartCount = CartItem::whereHas('cart', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->sum('quantity');

            return response()->json([
                'message' => 'Item added to cart successfully',
                'cartCount' => $cartCount,
                'success' => true
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors(),
                'success' => false
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error adding to cart: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error adding item to cart',
                'success' => false
            ], 500);
        }
    }

    /**
     * Update cart item quantity
     */
    public function update(Request $request, $cartItemId)
    {
        try {
            $validated = $request->validate([
                'quantity' => 'required|integer|min:1'
            ]);

            $user = Auth::user();

            // Find cart item that belongs to this user
            $cartItem = CartItem::whereHas('cart', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->findOrFail($cartItemId);

            $cartItem->update([
                'quantity' => $validated['quantity'],
                'updated_at' => now()
            ]);

            return response()->json([
                'message' => 'Cart item updated successfully',
                'success' => true
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Cart item not found',
                'success' => false
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors(),
                'success' => false
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error updating cart item: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error updating cart item',
                'success' => false
            ], 500);
        }
    }

    /**
     * Remove cart item
     */
    public function destroy(Request $request, $cartItemId)
    {
        try {
            $user = Auth::user();

            // Find cart item that belongs to this user
            $cartItem = CartItem::whereHas('cart', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->findOrFail($cartItemId);

            $cartItem->delete();

            return response()->json([
                'message' => 'Item removed from cart successfully',
                'success' => true
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Cart item not found',
                'success' => false
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error removing cart item: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error removing cart item',
                'success' => false
            ], 500);
        }
    }

    /**
     * Clear entire cart for a vendor
     */
    public function clear(Request $request, $vendorId = null)
    {
        try {
            $user = Auth::user();

            $query = Cart::where('user_id', $user->id);
            if ($vendorId) {
                $query->where('vendor_id', $vendorId);
            }

            $deletedCount = $query->delete();

            return response()->json([
                'message' => 'Cart cleared successfully',
                'deletedItems' => $deletedCount,
                'success' => true
            ]);

        } catch (\Exception $e) {
            Log::error('Error clearing cart: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error clearing cart',
                'success' => false
            ], 500);
        }
    }

    /**
     * Get cart count for badge
     */
    public function count(Request $request)
    {
        try {
            $user = Auth::user();

            $cartCount = CartItem::whereHas('cart', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->sum('quantity');

            return response()->json([
                'cartCount' => $cartCount,
                'success' => true
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting cart count: ' . $e->getMessage());
            return response()->json([
                'cartCount' => 0,
                'success' => true
            ]);
        }
    }
}
