<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Addon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    /**
     * Display the vendor's products with filtering and search.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $vendor = $this->getCurrentVendor();
            if (!$vendor) {
                return response()->json(['error' => 'Vendor not found'], 404);
            }

            $status = $request->query('status');
            $category = $request->query('category');
            $search = $request->query('search');
            $perPage = min($request->query('per_page', 20), 100);

            $query = Product::forVendor($vendor->id)
                ->with(['addons' => function ($q) {
                    $q->where('is_active', true);
                }])
                ->orderBy('created_at', 'desc');

            // Apply status filter
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }

            // Apply category filter
            if ($category) {
                $query->where('category', 'like', "%{$category}%");
            }

            // Apply search filter
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('category', 'like', "%{$search}%");
                });
            }

            $products = $query->paginate($perPage);

            return response()->json([
                'products' => $products->items(),
                'pagination' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                ],
                'categories' => $this->getVendorCategories($vendor->id)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching vendor products', [
                'vendor_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to fetch products'], 500);
        }
    }

    /**
     * Store a newly created product.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:products,name,vendor_id,' . $this->getCurrentVendor()->id,
                'price' => 'required|numeric|min:0.01',
                'category' => 'nullable|string|max:100',
                'stock_quantity' => 'required|integer|min:0',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'addons' => 'nullable|array',
                'addons.*.name' => 'required|string|max:255',
                'addons.*.price' => 'required|numeric|min:0',
                'addons.*.is_active' => 'boolean',
            ]);

            $vendor = $this->getCurrentVendor();
            if (!$vendor) {
                return response()->json(['error' => 'Vendor not found'], 404);
            }

            DB::beginTransaction();

            try {
                // Create product
                $productData = [
                    'vendor_id' => $vendor->id,
                    'name' => $request->name,
                    'price' => $request->price,
                    'category' => $request->category,
                    'stock_quantity' => $request->stock_quantity,
                    'is_active' => true,
                ];

                // Handle image upload
                if ($request->hasFile('image')) {
                    $imagePath = $request->file('image')->store('product-images', 'public');
                    $productData['image_url'] = $imagePath;
                }

                $product = Product::create($productData);

                // Create addons if provided
                if ($request->has('addons')) {
                    foreach ($request->addons as $addonData) {
                        Addon::create([
                            'product_id' => $product->id,
                            'name' => $addonData['name'],
                            'price' => $addonData['price'],
                            'is_active' => $addonData['is_active'] ?? true,
                        ]);
                    }
                }

                $product->load('addons');

                DB::commit();

                return response()->json([
                    'message' => 'Product created successfully',
                    'product' => $product
                ], 201);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Error creating product', [
                'vendor_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to create product'], 500);
        }
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product): JsonResponse
    {
        try {
            $vendor = $this->getCurrentVendor();
            if (!$vendor || $product->vendor_id !== $vendor->id) {
                return response()->json(['error' => 'Product not found'], 404);
            }

            $product->load(['addons']);

            return response()->json(['product' => $product]);

        } catch (\Exception $e) {
            Log::error('Error fetching product details', [
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to fetch product details'], 500);
        }
    }

    /**
     * Update the specified product.
     */
    public function update(Request $request, Product $product): JsonResponse
    {
        try {
            $vendor = $this->getCurrentVendor();
            if (!$vendor || $product->vendor_id !== $vendor->id) {
                return response()->json(['error' => 'Product not found'], 404);
            }

            $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('products')->where(function ($query) use ($vendor, $product) {
                        return $query->where('vendor_id', $vendor->id)
                                    ->where('id', '!=', $product->id);
                    })
                ],
                'price' => 'required|numeric|min:0.01',
                'category' => 'nullable|string|max:100',
                'stock_quantity' => 'required|integer|min:0',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            DB::beginTransaction();

            try {
                $updateData = [
                    'name' => $request->name,
                    'price' => $request->price,
                    'category' => $request->category,
                    'stock_quantity' => $request->stock_quantity,
                ];

                // Handle new image upload
                if ($request->hasFile('image')) {
                    // Delete old image if exists
                    if ($product->image_url) {
                        Storage::disk('public')->delete($product->image_url);
                    }

                    $imagePath = $request->file('image')->store('product-images', 'public');
                    $updateData['image_url'] = $imagePath;
                }

                $product->update($updateData);
                $product->load('addons');

                DB::commit();

                return response()->json([
                    'message' => 'Product updated successfully',
                    'product' => $product
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Error updating product', [
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to update product'], 500);
        }
    }

    /**
     * Remove the specified product.
     */
    public function destroy(Product $product): JsonResponse
    {
        try {
            $vendor = $this->getCurrentVendor();
            if (!$vendor || $product->vendor_id !== $vendor->id) {
                return response()->json(['error' => 'Product not found'], 404);
            }

            DB::beginTransaction();

            try {
                // Delete associated image
                if ($product->image_url) {
                    Storage::disk('public')->delete($product->image_url);
                }

                // Delete addons
                $product->addons()->delete();

                // Delete product
                $product->delete();

                DB::commit();

                return response()->json([
                    'message' => 'Product deleted successfully'
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Error deleting product', [
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to delete product'], 500);
        }
    }

    /**
     * Toggle product active status.
     */
    public function toggleStatus(Product $product): JsonResponse
    {
        try {
            $vendor = $this->getCurrentVendor();
            if (!$vendor || $product->vendor_id !== $vendor->id) {
                return response()->json(['error' => 'Product not found'], 404);
            }

            $product->update(['is_active' => !$product->is_active]);

            return response()->json([
                'message' => 'Product status updated successfully',
                'product' => $product->fresh()
            ]);

        } catch (\Exception $e) {
            Log::error('Error toggling product status', [
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to update product status'], 500);
        }
    }

    /**
     * Get product categories for the vendor.
     */
    public function getCategories(): JsonResponse
    {
        try {
            $vendor = $this->getCurrentVendor();
            if (!$vendor) {
                return response()->json(['error' => 'Vendor not found'], 404);
            }

            $categories = $this->getVendorCategories($vendor->id);

            return response()->json(['categories' => $categories]);

        } catch (\Exception $e) {
            Log::error('Error fetching product categories', [
                'vendor_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to fetch categories'], 500);
        }
    }

    /**
     * Bulk operations on products.
     */
    public function bulkToggle(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'product_ids' => 'required|array|min:1',
                'product_ids.*' => 'integer|exists:products,id',
                'action' => 'required|in:activate,deactivate,delete',
            ]);

            $vendor = $this->getCurrentVendor();
            if (!$vendor) {
                return response()->json(['error' => 'Vendor not found'], 404);
            }

            $productIds = $request->product_ids;
            $action = $request->action;

            // Verify all products belong to the vendor
            $vendorProducts = Product::forVendor($vendor->id)
                ->whereIn('id', $productIds)
                ->get();

            if ($vendorProducts->count() !== count($productIds)) {
                return response()->json(['error' => 'Some products not found'], 404);
            }

            DB::beginTransaction();

            try {
                $count = 0;

                switch ($action) {
                    case 'activate':
                        $count = Product::whereIn('id', $productIds)
                            ->update(['is_active' => true]);
                        break;

                    case 'deactivate':
                        $count = Product::whereIn('id', $productIds)
                            ->update(['is_active' => false]);
                        break;

                    case 'delete':
                        foreach ($vendorProducts as $product) {
                            if ($product->image_url) {
                                Storage::disk('public')->delete($product->image_url);
                            }
                        }
                        Addon::whereIn('product_id', $productIds)->delete();
                        $count = Product::whereIn('id', $productIds)->delete();
                        break;
                }

                DB::commit();

                return response()->json([
                    'message' => ucfirst($action) . "d {$count} products successfully",
                    'count' => $count
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Error performing bulk operation', [
                'action' => $request->action,
                'product_ids' => $request->product_ids,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to perform bulk operation'], 500);
        }
    }

    /**
     * Get the current authenticated vendor.
     */
    private function getCurrentVendor(): ?\App\Models\Vendor
    {
        $user = Auth::user();
        return $user?->vendor ?? null;
    }

    /**
     * Get vendor categories.
     */
    private function getVendorCategories(int $vendorId): array
    {
        return Product::forVendor($vendorId)
            ->select('category')
            ->distinct()
            ->whereNotNull('category')
            ->orderBy('category')
            ->pluck('category')
            ->toArray();
    }
}
