<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Resources\Notification\NotificationResource;
use App\Http\Resources\Notification\NotificationCollection;

class NotificationController extends Controller
{
    /**
     * Get paginated notifications
     */
 public function index(Request $request)
{
    $notifications = $request->user()
        ->notifications()
        ->latest()
        ->paginate($request->per_page ?? 15);

    return new NotificationCollection($notifications);
}

public function show(DatabaseNotification $notification)
{
    $this->authorize('view', $notification);
    
    return new NotificationResource($notification);
}

    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId)
    {
        try {
            $user = Auth::user();
            $notification = $user->notifications()->findOrFail($notificationId);
            
            $notification->markAsRead();
            
            return response()->json([
                'success' => true,
                'unread_count' => $user->unreadNotifications()->count()
            ]);
            
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Notification not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to mark notification as read'], 500);
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        try {
            $user = Auth::user();
            $user->unreadNotifications->markAsRead();
            
            return response()->json([
                'success' => true,
                'unread_count' => 0
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to mark all notifications as read'], 500);
        }
    }

    /**
     * Delete all notifications
     */
    public function clearAll()
    {
        try {
            $user = Auth::user();
            $user->notifications()->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'All notifications cleared',
                'unread_count' => 0
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to clear notifications'], 500);
        }
    }

    /**
     * Delete single notification
     */
    public function destroy($notificationId)
    {
        try {
            $user = Auth::user();
            $notification = $user->notifications()->findOrFail($notificationId);
            $notification->delete();
            
            return response()->json([
                'success' => true,
                'unread_count' => $user->unreadNotifications()->count()
            ]);
            
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Notification not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete notification'], 500);
        }
    }
}