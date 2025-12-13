<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    /**
     * Get notifications for the authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 10);
        $unreadOnly = $request->input('unread_only', false);

        $userId = auth()->id();

        $query = Notification::where(function($q) use ($userId) {
            if ($userId) {
                $q->where('user_id', $userId)
                  ->orWhereNull('user_id');
            } else {
                $q->whereNull('user_id'); // Only global notifications if not authenticated
            }
        });

        if ($unreadOnly) {
            $query->where('is_read', false);
        }

        $notifications = $query->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        $unreadCount = Notification::where(function($q) use ($userId) {
                if ($userId) {
                    $q->where('user_id', $userId)
                      ->orWhereNull('user_id');
                } else {
                    $q->whereNull('user_id');
                }
            })
            ->where('is_read', false)
            ->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Notification $notification): JsonResponse
    {
        if ($notification->user_id === null || $notification->user_id === auth()->id()) {
            $notification->markAsRead();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(): JsonResponse
    {
        Notification::where(function($q) {
                $q->where('user_id', auth()->id())
                  ->orWhereNull('user_id');
            })
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json(['success' => true]);
    }

    /**
     * Get unread count
     */
    public function unreadCount(): JsonResponse
    {
        $count = Notification::where(function($q) {
                $q->where('user_id', auth()->id())
                  ->orWhereNull('user_id');
            })
            ->where('is_read', false)
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Show all notifications page
     */
    public function showAll(Request $request)
    {
        $userId = auth()->id();
        $filter = $request->input('filter', 'all'); // 'all', 'unread', 'read'
        
        $query = Notification::where(function($q) use ($userId) {
            if ($userId) {
                $q->where('user_id', $userId)
                  ->orWhereNull('user_id');
            } else {
                $q->whereNull('user_id');
            }
        });

        if ($filter === 'unread') {
            $query->where('is_read', false);
        } elseif ($filter === 'read') {
            $query->where('is_read', true);
        }

        $notifications = $query->orderBy('created_at', 'desc')
            ->paginate(20);

        $unreadCount = Notification::where(function($q) use ($userId) {
                if ($userId) {
                    $q->where('user_id', $userId)
                      ->orWhereNull('user_id');
                } else {
                    $q->whereNull('user_id');
                }
            })
            ->where('is_read', false)
            ->count();

        return view('admin.notifications.index', compact('notifications', 'unreadCount', 'filter'));
    }
}
