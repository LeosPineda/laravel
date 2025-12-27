<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class QrController extends Controller
{
    /**
     * Get current QR code information.
     */
    public function show(): JsonResponse
    {
        try {
            $vendor = $this->getCurrentVendor();
            if (!$vendor) {
                return response()->json(['error' => 'Vendor not found'], 404);
            }

            $qrCodeData = [
                'has_qr_code' => !empty($vendor->qr_code_image),
                'qr_code_url' => $vendor->qr_code_image ? Storage::url($vendor->qr_code_image) : null,
                'mobile_number' => $vendor->qr_mobile_number,
                'last_updated' => $vendor->qr_code_image ? $vendor->updated_at->toISOString() : null,
            ];

            return response()->json($qrCodeData);

        } catch (\Exception $e) {
            Log::error('Error fetching QR code info', [
                'vendor_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to fetch QR code information'], 500);
        }
    }

    /**
     * Upload new QR code.
     */
    public function upload(Request $request): JsonResponse
    {
        try {
            $vendor = $this->getCurrentVendor();
            if (!$vendor) {
                return response()->json(['error' => 'Vendor not found'], 404);
            }

            $request->validate([
                'qr_code' => 'required|image|mimes:jpeg,png,jpg,gif|max:1024', // Max 1MB
                'mobile_number' => 'nullable|string|max:20',
            ]);

            DB::beginTransaction();

            try {
                // Delete old QR code if exists
                if ($vendor->qr_code_image) {
                    Storage::disk('public')->delete($vendor->qr_code_image);
                }

                // Store new QR code
                $qrCodePath = $request->file('qr_code')->store('qr-codes', 'public');

                $updateData = ['qr_code_image' => $qrCodePath];
                if ($request->has('mobile_number')) {
                    $updateData['qr_mobile_number'] = $request->mobile_number;
                }

                $vendor->update($updateData);

                DB::commit();

                return response()->json([
                    'message' => 'QR code uploaded successfully',
                    'qr_code_url' => Storage::url($qrCodePath),
                    'mobile_number' => $vendor->qr_mobile_number,
                    'uploaded_at' => now()->toISOString()
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Error uploading QR code', [
                'vendor_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to upload QR code'], 500);
        }
    }

    /**
     * Update existing QR code.
     */
    public function update(Request $request): JsonResponse
    {
        try {
            $vendor = $this->getCurrentVendor();
            if (!$vendor) {
                return response()->json(['error' => 'Vendor not found'], 404);
            }

            $request->validate([
                'qr_code' => 'required|image|mimes:jpeg,png,jpg,gif|max:1024',
            ]);

            DB::beginTransaction();

            try {
                // Delete old QR code
                if ($vendor->qr_code_image) {
                    Storage::disk('public')->delete($vendor->qr_code_image);
                }

                // Store new QR code
                $qrCodePath = $request->file('qr_code')->store('qr-codes', 'public');

                $vendor->update([
                    'qr_code_image' => $qrCodePath
                ]);

                DB::commit();

                return response()->json([
                    'message' => 'QR code updated successfully',
                    'qr_code_url' => Storage::url($qrCodePath),
                    'updated_at' => now()->toISOString()
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Error updating QR code', [
                'vendor_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to update QR code'], 500);
        }
    }

    /**
     * Remove QR code.
     */
    public function destroy(): JsonResponse
    {
        try {
            $vendor = $this->getCurrentVendor();
            if (!$vendor) {
                return response()->json(['error' => 'Vendor not found'], 404);
            }

            if (!$vendor->qr_code_image) {
                return response()->json(['error' => 'No QR code found to delete'], 404);
            }

            DB::beginTransaction();

            try {
                // Delete QR code file
                Storage::disk('public')->delete($vendor->qr_code_image);

                // Update vendor record
                $vendor->update([
                    'qr_code_image' => null
                ]);

                DB::commit();

                return response()->json([
                    'message' => 'QR code removed successfully'
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Error removing QR code', [
                'vendor_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to remove QR code'], 500);
        }
    }

    /**
     * Get public QR code URL for customers.
     */
    public function getPublicUrl(): JsonResponse
    {
        try {
            $vendor = $this->getCurrentVendor();
            if (!$vendor) {
                return response()->json(['error' => 'Vendor not found'], 404);
            }

            if (!$vendor->qr_code_image) {
                return response()->json(['error' => 'No QR code available'], 404);
            }

            return response()->json([
                'vendor_name' => $vendor->brand_name,
                'qr_code_url' => Storage::url($vendor->qr_code_image),
                'public_url' => route('vendor.qr.public', $vendor->id),
                'instructions' => 'Customers can scan this QR code to make payments via GCash or other mobile payment apps.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting QR code public URL', [
                'vendor_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to get QR code URL'], 500);
        }
    }

    /**
     * Preview current QR code.
     */
    public function preview(): JsonResponse
    {
        try {
            $vendor = $this->getCurrentVendor();
            if (!$vendor) {
                return response()->json(['error' => 'Vendor not found'], 404);
            }

            if (!$vendor->qr_code_image) {
                return response()->json(['error' => 'No QR code available'], 404);
            }

            return response()->json([
                'preview_url' => Storage::url($vendor->qr_code_image),
                'file_size' => Storage::disk('public')->size($vendor->qr_code_image),
                'last_updated' => $vendor->updated_at->toISOString(),
                'vendor_name' => $vendor->brand_name,
            ]);

        } catch (\Exception $e) {
            Log::error('Error previewing QR code', [
                'vendor_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to preview QR code'], 500);
        }
    }

    /**
     * Get QR code statistics.
     * FIXED: Now uses ready_for_pickup status instead of completed
     */
    public function getStatistics(): JsonResponse
    {
        try {
            $vendor = $this->getCurrentVendor();
            if (!$vendor) {
                return response()->json(['error' => 'Vendor not found'], 404);
            }

            $hasQrCode = !empty($vendor->qr_code_image);

            // Get orders with QR payment method
            $qrOrders = \App\Models\Order::forVendor($vendor->id)
                ->where('payment_method', 'qr_code')
                ->where('created_at', '>=', now()->subMonth())
                ->count();

            // Get QR payment revenue - FIXED: Uses ready_for_pickup instead of completed
            $qrRevenue = \App\Models\Order::forVendor($vendor->id)
                ->where('payment_method', 'qr_code')
                ->where('status', 'ready_for_pickup')  // ✅ FIXED: Now uses simplified workflow status
                ->where('created_at', '>=', now()->subMonth())
                ->sum('total_amount');

            $statistics = [
                'has_qr_code' => $hasQrCode,
                'qr_orders_this_month' => $qrOrders,
                'qr_revenue_this_month' => $qrRevenue,
                'total_qr_orders' => \App\Models\Order::forVendor($vendor->id)
                    ->where('payment_method', 'qr_code')
                    ->count(),
                'qr_code_last_updated' => $vendor->qr_code_image ? $vendor->updated_at->toISOString() : null,
            ];

            return response()->json($statistics);

        } catch (\Exception $e) {
            Log::error('Error fetching QR code statistics', [
                'vendor_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to fetch QR code statistics'], 500);
        }
    }

    /**
     * Update mobile number for QR payment.
     */
    public function updateMobileNumber(Request $request): JsonResponse
    {
        try {
            $vendor = $this->getCurrentVendor();
            if (!$vendor) {
                return response()->json(['error' => 'Vendor not found'], 404);
            }

            $request->validate([
                'mobile_number' => 'nullable|string|max:20',
            ]);

            $vendor->update([
                'qr_mobile_number' => $request->mobile_number ?: null
            ]);

            return response()->json([
                'message' => 'Mobile number updated successfully',
                'mobile_number' => $vendor->qr_mobile_number,
                'updated_at' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating mobile number', [
                'vendor_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to update mobile number'], 500);
        }
    }

    /**
     * Validate QR code file.
     */
    public function validate(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'qr_code' => 'required|image|mimes:jpeg,png,jpg,gif|max:1024',
            ]);

            $file = $request->file('qr_code');
            $fileSize = $file->getSize();
            $mimeType = $file->getMimeType();

            // Additional validation
            $validation = [
                'valid' => true,
                'file_size' => $fileSize,
                'file_size_mb' => round($fileSize / 1024 / 1024, 2),
                'mime_type' => $mimeType,
                'recommendations' => [],
            ];

            // Check file size
            if ($fileSize > 1024 * 1024) { // 1MB
                $validation['valid'] = false;
                $validation['recommendations'][] = 'File size should be less than 1MB';
            }

            // Check if it's a reasonable size for QR code
            if ($fileSize < 1024) { // Less than 1KB
                $validation['recommendations'][] = 'File seems too small, ensure QR code is clearly visible';
            }

            return response()->json($validation);

        } catch (\Exception $e) {
            Log::error('Error validating QR code', [
                'vendor_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to validate QR code'], 500);
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
