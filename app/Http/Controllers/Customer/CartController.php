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
                'product:id,name,image_url,category'
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
                    'special_instructions' => $item->special_instructions,
                    'total_price' => $item->total_price,
                    'product' => [
                        'id' => $item->product->id,
                        'name' => $item->product->name,
                        'image_url' => $item->product->image_url,
                        'category' => $item->product->category,
                    ]
                ];
            }

            // Calculate cart count and total
            $cartCount = $cartItems->sum('quantity');
            $cartTotal = $cartItems->sum(function ($item) {
                return $item->total_price;
            });

            return response()->json([
                'vendorCarts' => array_values($vendorCarts),
                'cartCount' => $cartCount,
                'cartTotal' => $cartTotal,
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
     * Add item to cart with addons and special instructions
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1',
                'special_instructions' => 'nullable|string|max:500',
                'addons' => 'nullable|array',
                'addons.*.addon_id' => 'exists:addons,id',
                'addons.*.quantity' => 'required|integer|min:1',
                'addons.*.price' => 'required|numeric|min:0'
            ]);

            $user = Auth::user();

            // Get product and its vendor
            $product = Product::with('vendor')->findOrFail($validated['product_id']);

            // Get or create cart for this vendor
            $cart = Cart::firstOrCreate(
                ['user_id' => $user->id, 'vendor_id' => $product->vendor->id],
                ['created_at' => now(), 'updated_at' => now()]
            );

            // Prepare addons data
            $selectedAddons = [];
            if (!empty($validated['addons'])) {
                foreach ($validated['addons'] as $addon) {
                    $selectedAddons[] = [
                        'addon_id' => $addon['addon_id'],
                        'quantity' => $addon['quantity'],
                        'price' => $addon['price']
                    ];
                }
            }

            // Check if item already exists with same addons and instructions
            $existingItem = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $validated['product_id'])
                ->where('selected_addons', json_encode($selectedAddons))
                ->where('special_instructions', $validated['special_instructions'] ?? null)
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
                    'unit_price' => $product->price,
                    'selected_addons' => $selectedAddons,
                    'special_instructions' => $validated['special_instructions'] ?? null
                ]);
            }

            // Get updated cart count
            $cartCount = CartItem::whereHas('cart', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->sum('quantity');

            return response()->json([
                'message' => 'Item added to cart successfully',
                'cartCount' => $cartCount,
                'cartItem' => $cartItem->getSummary(),
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
                'cartItem' => $cartItem->getSummary(),
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
                'count' => $cartCount,
                'success' => true
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting cart count: ' . $e->getMessage());
            return response()->json([
                'count' => 0,
                'success' => true
            ]);
        }
    }

    /**
     * Get addons for a product
     */
    public function productAddons(Request $request, $productId)
    {
        try {
            $product = Product::findOrFail($productId);

            // Get addons for this product's vendor
            $addons = Addon::where('vendor_id', $product->vendor->id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get();

            return response()->json([
                'addons' => $addons,
                'success' => true
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Product not found',
                'success' => false
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error getting product addons: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error fetching product addons',
                'success' => false
            ], 500);
        }
    }
}
