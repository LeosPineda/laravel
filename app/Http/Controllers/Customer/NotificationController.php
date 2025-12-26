<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    /**
     * Get customer notifications.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $query = Notification::where('user_id', $user->id)
                ->orderBy('created_at', 'desc');

            // Filter by read status
            if ($request->has('unread_only') && $request->unread_only) {
                $query->whereNull('read_at');
            }

            $notifications = $query->paginate($request->get('per_page', 20));

            return response()->json([
                'notifications' => $notifications,
                'success' => true
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching customer notifications: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch notifications'], 500);
        }
    }

    /**
     * Get unread notification count.
     */
    public function count(): JsonResponse
    {
        try {
            $user = Auth::user();

            $count = Notification::where('user_id', $user->id)
                ->whereNull('read_at')
                ->count();

            return response()->json([
                'count' => $count,
                'success' => true
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching notification count: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch count'], 500);
        }
    }

    /**
     * Get recent notifications.
     */
    public function recent(): JsonResponse
    {
        try {
            $user = Auth::user();

            $notifications = Notification::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            $unreadCount = Notification::where('user_id', $user->id)
                ->whereNull('read_at')
                ->count();

            return response()->json([
                'notifications' => $notifications,
                'unread_count' => $unreadCount,
                'success' => true
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching recent notifications: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch notifications'], 500);
        }
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(int $notificationId): JsonResponse
    {
        try {
            $user = Auth::user();

            $notification = Notification::where('id', $notificationId)
                ->where('user_id', $user->id)
                ->firstOrFail();

            $notification->update(['read_at' => now()]);

            return response()->json([
                'message' => 'Notification marked as read',
                'success' => true
            ]);

        } catch (\Exception $e) {
            Log::error('Error marking notification as read: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to mark as read'], 500);
        }
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(): JsonResponse
    {
        try {
            $user = Auth::user();

            Notification::where('user_id', $user->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            return response()->json([
                'message' => 'All notifications marked as read',
                'success' => true
            ]);

        } catch (\Exception $e) {
            Log::error('Error marking all notifications as read: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to mark all as read'], 500);
        }
    }

    /**
     * Delete a notification.
     */
    public function destroy(int $notificationId): JsonResponse
    {
        try {
            $user = Auth::user();

            $notification = Notification::where('id', $notificationId)
                ->where('user_id', $user->id)
                ->firstOrFail();

            $notification->delete();

            return response()->json([
                'message' => 'Notification deleted',
                'success' => true
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting notification: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete notification'], 500);
        }
    }
}
