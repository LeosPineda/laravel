<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class NotificationController extends Controller
{
    /**
     * Display vendor notifications with pagination and filtering.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $vendor = $this->getCurrentVendor();
            if (!$vendor) {
                return response()->json(['error' => 'Vendor not found'], 404);
            }

            $type = $request->query('type');
            $status = $request->query('status', 'all'); // all, read, unread
            $perPage = min($request->query('per_page', 20), 100);
            $search = $request->query('search');

            $query = Notification::forVendor($vendor->id)
                ->with('order')
                ->orderBy('created_at', 'desc');

            // Apply type filter
            if ($type && in_array($type, ['order', 'system', 'payment', 'general'])) {
                $query->where('type', $type);
            }

            // Apply status filter
            if ($status === 'read') {
                $query->where('is_read', true);
            } elseif ($status === 'unread') {
                $query->where('is_read', false);
            }

            // Apply search filter
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('message', 'like', "%{$search}%");
                });
            }

            $notifications = $query->paginate($perPage);

            return response()->json([
                'notifications' => $notifications->items(),
                'pagination' => [
                    'current_page' => $notifications->currentPage(),
                    'last_page' => $notifications->lastPage(),
                    'per_page' => $notifications->perPage(),
                    'total' => $notifications->total(),
                ],
                'statistics' => $this->getNotificationStats($vendor->id)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching vendor notifications', [
                'vendor_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to fetch notifications'], 500);
        }
    }

    /**
     * Get unread notification count.
     */
    public function count(): JsonResponse
    {
        try {
            $vendor = $this->getCurrentVendor();
            if (!$vendor) {
                return response()->json(['error' => 'Vendor not found'], 404);
            }

            $unreadCount = Notification::forVendor($vendor->id)
                ->where('is_read', false)
                ->count();

            return response()->json([
                'unread_count' => $unreadCount,
                'total_count' => Notification::forVendor($vendor->id)->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching notification count', [
                'vendor_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to fetch notification count'], 500);
        }
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead(Notification $notification): JsonResponse
    {
        try {
            $vendor = $this->getCurrentVendor();
            if (!$vendor || $notification->vendor_id !== $vendor->id) {
                return response()->json(['error' => 'Notification not found'], 404);
            }

            $notification->update(['is_read' => true]);

            return response()->json([
                'message' => 'Notification marked as read',
                'notification' => $notification
            ]);

        } catch (\Exception $e) {
            Log::error('Error marking notification as read', [
                'notification_id' => $notification->id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to mark notification as read'], 500);
        }
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(): JsonResponse
    {
        try {
            $vendor = $this->getCurrentVendor();
            if (!$vendor) {
                return response()->json(['error' => 'Vendor not found'], 404);
            }

            $updatedCount = Notification::forVendor($vendor->id)
                ->where('is_read', false)
                ->update(['is_read' => true]);

            return response()->json([
                'message' => "{$updatedCount} notifications marked as read",
                'updated_count' => $updatedCount
            ]);

        } catch (\Exception $e) {
            Log::error('Error marking all notifications as read', [
                'vendor_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to mark all notifications as read'], 500);
        }
    }

    /**
     * Delete a specific notification.
     */
    public function destroy(Notification $notification): JsonResponse
    {
        try {
            $vendor = $this->getCurrentVendor();
            if (!$vendor || $notification->vendor_id !== $vendor->id) {
                return response()->json(['error' => 'Notification not found'], 404);
            }

            $notification->delete();

            return response()->json([
                'message' => 'Notification deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting notification', [
                'notification_id' => $notification->id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to delete notification'], 500);
        }
    }

    /**
     * Clean up old notifications (30+ days).
     */
    public function cleanup(): JsonResponse
    {
        try {
            $vendor = $this->getCurrentVendor();
            if (!$vendor) {
                return response()->json(['error' => 'Vendor not found'], 404);
            }

            $cutoffDate = Carbon::now()->subDays(30);

            $deletedCount = Notification::forVendor($vendor->id)
                ->where('created_at', '<', $cutoffDate)
                ->delete();

            return response()->json([
                'message' => "{$deletedCount} old notifications cleaned up",
                'deleted_count' => $deletedCount,
                'cutoff_date' => $cutoffDate->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::error('Error cleaning up notifications', [
                'vendor_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to clean up notifications'], 500);
        }
    }

    /**
     * Get notification statistics.
     */
    public function getStatistics(): JsonResponse
    {
        try {
            $vendor = $this->getCurrentVendor();
            if (!$vendor) {
                return response()->json(['error' => 'Vendor not found'], 404);
            }

            $stats = $this->getNotificationStats($vendor->id);

            return response()->json($stats);

        } catch (\Exception $e) {
            Log::error('Error fetching notification statistics', [
                'vendor_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to fetch notification statistics'], 500);
        }
    }

    /**
     * Get recent notifications for dashboard widget.
     */
    public function getRecent(): JsonResponse
    {
        try {
            $vendor = $this->getCurrentVendor();
            if (!$vendor) {
                return response()->json(['error' => 'Vendor not found'], 404);
            }

            $limit = min(request()->query('limit', 5), 20);

            $recentNotifications = Notification::forVendor($vendor->id)
                ->with('order')
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->map(function ($notification) {
                    return [
                        'id' => $notification->id,
                        'type' => $notification->type,
                        'title' => $notification->title,
                        'message' => $notification->message,
                        'is_read' => $notification->is_read,
                        'created_at' => $notification->created_at->diffForHumans(),
                        'order' => $notification->order ? [
                            'id' => $notification->order->id,
                            'order_number' => $notification->order->order_number,
                            'status' => $notification->order->status,
                        ] : null,
                    ];
                });

            return response()->json([
                'notifications' => $recentNotifications,
                'unread_count' => Notification::forVendor($vendor->id)->where('is_read', false)->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching recent notifications', [
                'vendor_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to fetch recent notifications'], 500);
        }
    }

    /**
     * Create a new notification (for internal use).
     */
    public function create(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'type' => 'required|in:order,system,payment,general',
                'title' => 'required|string|max:255',
                'message' => 'required|string',
                'order_id' => 'nullable|exists:orders,id',
                'is_read' => 'boolean',
            ]);

            $vendor = $this->getCurrentVendor();
            if (!$vendor) {
                return response()->json(['error' => 'Vendor not found'], 404);
            }

            $notification = Notification::create([
                'vendor_id' => $vendor->id,
                'type' => $request->type,
                'title' => $request->title,
                'message' => $request->message,
                'order_id' => $request->order_id,
                'is_read' => $request->is_read ?? false,
            ]);

            return response()->json([
                'message' => 'Notification created successfully',
                'notification' => $notification
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error creating notification', [
                'vendor_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to create notification'], 500);
        }
    }

    /**
     * Bulk operations on notifications.
     */
    public function bulkOperation(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'notification_ids' => 'required|array|min:1',
                'notification_ids.*' => 'integer|exists:notifications,id',
                'action' => 'required|in:mark_read,mark_unread,delete',
            ]);

            $vendor = $this->getCurrentVendor();
            if (!$vendor) {
                return response()->json(['error' => 'Vendor not found'], 404);
            }

            $notificationIds = $request->notification_ids;
            $action = $request->action;

            // Verify all notifications belong to the vendor
            $vendorNotifications = Notification::forVendor($vendor->id)
                ->whereIn('id', $notificationIds)
                ->get();

            if ($vendorNotifications->count() !== count($notificationIds)) {
                return response()->json(['error' => 'Some notifications not found'], 404);
            }

            DB::beginTransaction();

            try {
                $count = 0;

                switch ($action) {
                    case 'mark_read':
                        $count = Notification::whereIn('id', $notificationIds)
                            ->update(['is_read' => true]);
                        break;

                    case 'mark_unread':
                        $count = Notification::whereIn('id', $notificationIds)
                            ->update(['is_read' => false]);
                        break;

                    case 'delete':
                        $count = Notification::whereIn('id', $notificationIds)->delete();
                        break;
                }

                DB::commit();

                return response()->json([
                    'message' => ucfirst(str_replace('_', ' ', $action)) . "d {$count} notifications successfully",
                    'count' => $count
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Error performing bulk notification operation', [
                'action' => $request->action,
                'notification_ids' => $request->notification_ids,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to perform bulk operation'], 500);
        }
    }

    /**
     * Get notification types and their counts.
     */
    public function getTypes(): JsonResponse
    {
        try {
            $vendor = $this->getCurrentVendor();
            if (!$vendor) {
                return response()->json(['error' => 'Vendor not found'], 404);
            }

            $types = Notification::forVendor($vendor->id)
                ->select('type', DB::raw('COUNT(*) as count'))
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray();

            return response()->json(['types' => $types]);

        } catch (\Exception $e) {
            Log::error('Error fetching notification types', [
                'vendor_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to fetch notification types'], 500);
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
     * Calculate notification statistics for a vendor.
     */
    private function getNotificationStats(int $vendorId): array
    {
        return [
            'total_notifications' => Notification::forVendor($vendorId)->count(),
            'unread_notifications' => Notification::forVendor($vendorId)->where('is_read', false)->count(),
            'read_notifications' => Notification::forVendor($vendorId)->where('is_read', true)->count(),
            'today_notifications' => Notification::forVendor($vendorId)
                ->whereDate('created_at', today())
                ->count(),
            'this_week_notifications' => Notification::forVendor($vendorId)
                ->where('created_at', '>=', Carbon::now()->startOfWeek())
                ->count(),
            'type_breakdown' => Notification::forVendor($vendorId)
                ->select('type', DB::raw('COUNT(*) as count'))
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray(),
        ];
    }
}
