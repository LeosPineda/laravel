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
use Illuminate\Validation\Rule;

class AddonController extends Controller
{
    /**
     * Display addons for a specific product.
     */
    public function index(Product $product): JsonResponse
    {
        try {
            $vendor = $this->getCurrentVendor();
            if (!$vendor || $product->vendor_id !== $vendor->id) {
                return response()->json(['error' => 'Product not found'], 404);
            }

            $addons = $product->addons()->orderBy('created_at', 'desc')->get();

            return response()->json(['addons' => $addons]);

        } catch (\Exception $e) {
            Log::error('Error fetching product addons', [
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to fetch addons'], 500);
        }
    }

    /**
     * Store a newly created addon for a product.
     */
    public function store(Request $request, Product $product): JsonResponse
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
                    Rule::unique('addons')->where(function ($query) use ($product) {
                        return $query->where('product_id', $product->id);
                    })
                ],
                'price' => 'required|numeric|min:0',
            ]);

            $addon = Addon::create([
                'product_id' => $product->id,
                'name' => $request->name,
                'price' => $request->price,
            ]);

            return response()->json([
                'message' => 'Addon created successfully',
                'addon' => $addon
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error creating addon', [
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to create addon'], 500);
        }
    }

    /**
     * Display the specified addon.
     */
    public function show(Addon $addon): JsonResponse
    {
        try {
            $vendor = $this->getCurrentVendor();
            $product = $addon->product;

            if (!$vendor || !$product || $product->vendor_id !== $vendor->id) {
                return response()->json(['error' => 'Addon not found'], 404);
            }

            return response()->json(['addon' => $addon]);

        } catch (\Exception $e) {
            Log::error('Error fetching addon details', [
                'addon_id' => $addon->id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to fetch addon details'], 500);
        }
    }

    /**
     * Update the specified addon.
     */
    public function update(Request $request, Addon $addon): JsonResponse
    {
        try {
            $vendor = $this->getCurrentVendor();
            $product = $addon->product;

            if (!$vendor || !$product || $product->vendor_id !== $vendor->id) {
                return response()->json(['error' => 'Addon not found'], 404);
            }

            $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('addons')->where(function ($query) use ($product, $addon) {
                        return $query->where('product_id', $product->id)
                                    ->where('id', '!=', $addon->id);
                    })
                ],
                'price' => 'required|numeric|min:0',
            ]);

            $addon->update([
                'name' => $request->name,
                'price' => $request->price,
            ]);

            return response()->json([
                'message' => 'Addon updated successfully',
                'addon' => $addon
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating addon', [
                'addon_id' => $addon->id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to update addon'], 500);
        }
    }

    /**
     * Remove the specified addon.
     */
    public function destroy(Addon $addon): JsonResponse
    {
        try {
            $vendor = $this->getCurrentVendor();
            $product = $addon->product;

            if (!$vendor || !$product || $product->vendor_id !== $vendor->id) {
                return response()->json(['error' => 'Addon not found'], 404);
            }

            $addon->delete();

            return response()->json([
                'message' => 'Addon deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting addon', [
                'addon_id' => $addon->id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to delete addon'], 500);
        }
    }

    /**
     * Bulk operations on addons.
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'addon_ids' => 'required|array|min:1',
                'addon_ids.*' => 'integer|exists:addons,id',
            ]);

            $vendor = $this->getCurrentVendor();
            if (!$vendor) {
                return response()->json(['error' => 'Vendor not found'], 404);
            }

            $addonIds = $request->addon_ids;

            // Verify all addons belong to the vendor's products
            $vendorAddons = Addon::whereIn('id', $addonIds)
                ->whereHas('product', function ($query) use ($vendor) {
                    $query->where('vendor_id', $vendor->id);
                })
                ->get();

            if ($vendorAddons->count() !== count($addonIds)) {
                return response()->json(['error' => 'Some addons not found'], 404);
            }

            DB::beginTransaction();

            try {
                $count = Addon::whereIn('id', $addonIds)->delete();

                DB::commit();

                return response()->json([
                    'message' => "Deleted {$count} addons successfully",
                    'count' => $count
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Error performing bulk addon delete', [
                'addon_ids' => $request->addon_ids,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to perform bulk delete'], 500);
        }
    }

    /**
     * Get addons statistics for a product.
     */
    public function getStatistics(Product $product): JsonResponse
    {
        try {
            $vendor = $this->getCurrentVendor();
            if (!$vendor || $product->vendor_id !== $vendor->id) {
                return response()->json(['error' => 'Product not found'], 404);
            }

            $totalAddons = $product->addons()->count();
            $averagePrice = $product->addons()->avg('price') ?? 0;

            return response()->json([
                'statistics' => [
                    'total_addons' => $totalAddons,
                    'average_price' => round($averagePrice, 2),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching addon statistics', [
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to fetch addon statistics'], 500);
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
}
