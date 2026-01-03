<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Models\Product;
use App\Models\Addon;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MenuController extends Controller
{
    /**
     * Get list of active vendors
     */
    public function vendors(Request $request)
    {
        try {
            $query = Vendor::where('is_active', true);

            // Apply search filter
            if ($request->has('search') && $request->search) {
                $searchTerm = $request->search;
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('brand_name', 'like', "%{$searchTerm}%");
                });
            }

            // Get vendor count with all their products (no is_active filtering)
            $vendors = $query->select('id', 'brand_name', 'brand_logo', 'is_active')
                ->withCount('products')
                ->orderBy('brand_name')
                ->get();

            return response()->json([
                'vendors' => $vendors,
                'success' => true
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting vendors: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error retrieving vendors',
                'success' => false
            ], 500);
        }
    }

    /**
     * Get vendor's products
     */
    public function vendorProducts(Request $request, $vendorId)
    {
        try {
            $vendor = Vendor::where('id', $vendorId)
                ->where('is_active', true)
                ->select('id', 'brand_name', 'brand_logo')
                ->firstOrFail();

            // Build product query - NO is_active filtering
            $query = Product::where('vendor_id', $vendorId)
                ->select('id', 'name', 'price', 'category', 'image_url', 'stock_quantity')
                ->with('addons');

            // Filter by category if provided
            if ($request->has('category') && $request->category) {
                $query->where('category', $request->category);
            }

            $query->orderBy('category')->orderBy('name');

            // Get all products for modal
            $products = $query->get();

            return response()->json([
                'vendor' => $vendor,
                'products' => $products,
                'success' => true
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Vendor not found or inactive',
                'success' => false
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error getting vendor products: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error retrieving vendor products',
                'success' => false
            ], 500);
        }
    }

    /**
     * Get vendor details
     */
    public function showVendor(Request $request, $vendorId)
    {
        try {
            $vendor = Vendor::where('id', $vendorId)
                ->where('is_active', true)
                ->select('id', 'brand_name', 'brand_logo', 'is_active')
                ->firstOrFail();

            return response()->json([
                'vendor' => $vendor,
                'success' => true
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Vendor not found or inactive',
                'success' => false
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error getting vendor: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error retrieving vendor',
                'success' => false
            ], 500);
        }
    }

    /**
     * Get vendor menu with products
     */
    public function vendorMenu(Request $request, $vendorId)
    {
        try {
            $vendor = Vendor::where('id', $vendorId)
                ->where('is_active', true)
                ->select('id', 'brand_name', 'brand_logo', 'qr_code_image')
                ->firstOrFail();

            // Build product query - NO is_active filtering
            $query = Product::where('vendor_id', $vendorId)
                ->select('id', 'name', 'price', 'category', 'image_url', 'stock_quantity')
                ->with('addons');

            // Filter by category if provided
            if ($request->has('category') && $request->category) {
                $query->where('category', $request->category);
            }

            $query->orderBy('category')->orderBy('name');

            // Paginate or get all based on request
            $perPage = $request->get('per_page', 20);
            $products = $request->has('all') ? $query->get() : $query->paginate($perPage);

            // Get unique categories (from all products, not just current page)
            $categories = Product::where('vendor_id', $vendorId)
                ->whereNotNull('category')
                ->distinct()
                ->pluck('category')
                ->sort()
                ->values()
                ->all();

            return response()->json([
                'vendor' => $vendor,
                'products' => $products,
                'categories' => $categories,
                'success' => true
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Vendor not found or inactive',
                'success' => false
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error getting vendor menu: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error retrieving vendor menu',
                'success' => false
            ], 500);
        }
    }

    /**
     * Get vendor's QR payment information (for checkout page)
     */
    public function getQrPayment(Request $request, $vendorId)
    {
        try {
            $vendor = Vendor::where('id', $vendorId)
                ->where('is_active', true)
                ->firstOrFail();

            if (!$vendor->qr_code_image) {
                return response()->json([
                    'message' => 'Vendor has no QR payment setup',
                    'has_qr' => false,
                    'success' => true
                ]);
            }

            return response()->json([
                'has_qr' => true,
                'vendor_name' => $vendor->brand_name,
                'qr_code_url' => Storage::url($vendor->qr_code_image),
                'mobile_number' => $vendor->qr_mobile_number, // Customer can copy this
                'download_url' => route('customer.vendor.qr-download', $vendor->id),
                'success' => true
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Vendor not found',
                'success' => false
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error getting QR payment info: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error getting QR payment info',
                'success' => false
            ], 500);
        }
    }

    /**
     * Download vendor's QR code for payment
     */
    public function downloadQr(Request $request, $vendorId)
    {
        try {
            $vendor = Vendor::where('id', $vendorId)
                ->where('is_active', true)
                ->firstOrFail();

            if (!$vendor->qr_code_image) {
                return response()->json([
                    'message' => 'Vendor has no QR code available',
                    'success' => false
                ], 404);
            }

            $filePath = $vendor->qr_code_image;

            if (!Storage::disk('public')->exists($filePath)) {
                return response()->json([
                    'message' => 'QR code file not found',
                    'success' => false
                ], 404);
            }

            $fileName = $vendor->brand_name . '-payment-qr.' . pathinfo($filePath, PATHINFO_EXTENSION);

            return Storage::disk('public')->download($filePath, $fileName);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Vendor not found',
                'success' => false
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error downloading QR code: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error downloading QR code',
                'success' => false
            ], 500);
        }
    }

    /**
     * Search products across all vendors
     */
    public function searchProducts(Request $request)
    {
        try {
            $query = Product::whereHas('vendor', function ($q) {
                $q->where('is_active', true);
            });

            // Apply search filter
            if ($request->has('search') && $request->search) {
                $searchTerm = $request->search;
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'like', "%{$searchTerm}%")
                      ->orWhere('category', 'like', "%{$searchTerm}%");
                });
            }

            // Apply category filter
            if ($request->has('category') && $request->category) {
                $query->where('category', $request->category);
            }

            // Apply vendor filter
            if ($request->has('vendor_id') && $request->vendor_id) {
                $query->where('vendor_id', $request->vendor_id);
            }

            // Apply price range filter
            if ($request->has('min_price')) {
                $query->where('price', '>=', $request->min_price);
            }

            if ($request->has('max_price')) {
                $query->where('price', '<=', $request->max_price);
            }

            $products = $query->with([
                'vendor:id,brand_name,brand_logo',
                'addons'
            ])
            ->select('id', 'name', 'price', 'category', 'image_url', 'stock_quantity', 'vendor_id')
            ->orderBy('name')
            ->paginate(20);

            return response()->json([
                'products' => $products,
                'success' => true
            ]);

        } catch (\Exception $e) {
            Log::error('Error searching products: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error searching products',
                'success' => false
            ], 500);
        }
    }

    /**
     * Get all available categories
     */
    public function categories(Request $request)
    {
        try {
            $categories = Product::whereHas('vendor', function ($q) {
                $q->where('is_active', true);
            })
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->sort()
            ->values()
            ->all();

            return response()->json([
                'categories' => $categories,
                'success' => true
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting categories: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error retrieving categories',
                'success' => false
            ], 500);
        }
    }

    /**
     * Get all products (used by search)
     */
    public function allProducts(Request $request)
    {
        return $this->searchProducts($request);
    }

    /**
     * Get product details
     */
    public function showProduct(Request $request, $productId)
    {
        return $this->productDetails($request, $productId);
    }

    /**
     * Get product details
     */
    public function productDetails(Request $request, $productId)
    {
        try {
            $product = Product::where('id', $productId)
                ->whereHas('vendor', function ($q) {
                    $q->where('is_active', true);
                })
                ->with([
                    'vendor:id,brand_name,brand_logo',
                    'addons'
                ])
                ->firstOrFail();

            return response()->json([
                'product' => $product,
                'success' => true
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Product not found',
                'success' => false
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error getting product details: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error retrieving product details',
                'success' => false
            ], 500);
        }
    }

    /**
     * Quick add product to cart
     */
    public function quickAddToCart(Request $request, $productId)
    {
        try {
            $validated = $request->validate([
                'quantity' => 'required|integer|min:1',
                'selected_addons' => 'nullable|array',
                'selected_addons.*.id' => 'exists:addons,id',
                'selected_addons.*.name' => 'string',
                'selected_addons.*.price' => 'numeric|min:0'
            ]);

            $product = Product::where('id', $productId)
                ->whereHas('vendor', function ($q) {
                    $q->where('is_active', true);
                })
                ->with('vendor')
                ->firstOrFail();

            // Check stock availability
            if ($product->stock_quantity < $validated['quantity']) {
                return response()->json([
                    'message' => 'Insufficient stock',
                    'available_stock' => $product->stock_quantity,
                    'success' => false
                ], 400);
            }

            $user = Auth::user();

            // Get or create cart for this vendor
            $cart = Cart::getOrCreate($user->id, $product->vendor->id);

            // Check if item already exists with same addons
            $existingItem = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $product->id)
                ->where('selected_addons', json_encode($validated['selected_addons'] ?? []))
                ->first();

            if ($existingItem) {
                // Update quantity
                $newQuantity = $existingItem->quantity + $validated['quantity'];

                // Check if new quantity exceeds stock
                if ($newQuantity > $product->stock_quantity) {
                    return response()->json([
                        'message' => 'Insufficient stock for quantity increase',
                        'available_stock' => $product->stock_quantity,
                        'success' => false
                    ], 400);
                }

                $existingItem->update([
                    'quantity' => $newQuantity,
                    'updated_at' => now()
                ]);
                $cartItem = $existingItem;
            } else {
                // Create new cart item
                $cartItem = CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $product->id,
                    'quantity' => $validated['quantity'],
                    'unit_price' => $product->price,
                    'selected_addons' => $validated['selected_addons'] ?? []
                ]);
            }

            // Get updated cart count
            $cartCount = CartItem::whereHas('cart', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->sum('quantity');

            return response()->json([
                'message' => 'Product added to cart successfully',
                'cartCount' => $cartCount,
                'cartItem' => $cartItem->load('product'),
                'success' => true
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Product not found or unavailable',
                'success' => false
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors(),
                'success' => false
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error quick adding to cart: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error adding product to cart',
                'success' => false
            ], 500);
        }
    }
}
