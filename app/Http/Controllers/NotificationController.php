<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function getNotifications($type)
    {
        $user = auth()->user();
        $query = $user->notificationsReceived()->orderBy('created_at', 'desc');
    
        $notifications = $query->get();
    
        foreach ($notifications as $notification) {
            $notification->created_ago = $this->formatTimeAgo($notification->created_at, Carbon::now());
            $notification->sender = User::find($notification->SenderID);
        }
        $notification_count = $notifications->where('Read', false)->count();
    
        return response()->json(['notifications' => $notifications, 'notification_count' => $notification_count]);
    }

    public function getUnreadNotificationCount()
    {
        $user = Auth::user();
        $unreadCount = $user->notificationsReceived()->where('Read', false)->count();
        if ($unreadCount > 9) {
            $unreadCount = '9+';
        }

        return response()->json(['unreadCount' => $unreadCount]);
    }
    
    public function getNewNotifications($type)
    {
        $user = auth()->user();
        $lastCheckedAt = now()->subSeconds(10);
    
        $newNotifications = $user->notificationsReceived()
            ->where('created_at', '>', $lastCheckedAt)
            ->orderBy('created_at', 'desc')
            ->get();
    
        foreach ($newNotifications as $notification) {
            $notification->created_ago = $this->formatTimeAgo($notification->created_at, now());
            $notification->sender = User::find($notification->SenderID);
        }
    
        return response()->json(['newNotifications' => $newNotifications]);
    }

    public function markSelectedAsReadNotifications(Request $request){
        $notificationIds = $request->input('notificationIds', []);

        if (!is_array($notificationIds)) {
            return response()->json(['message' => 'Invalid request.'], 400);
        }
    
        Notification::whereIn('NotificationID', $notificationIds)->update(['Read' => true]);
    
        return response()->json(['message' => 'Selected notifications marked as read successfully']);
    }

    public function deleteSelectedNotifications(Request $request)
    {
        $notificationIds = $request->input('notificationIds', []);

        if (!is_array($notificationIds)) {
            return response()->json(['message' => 'Invalid request.'], 400);
        }
    
        Notification::whereIn('NotificationID', $notificationIds)->delete();
    
        return response()->json(['message' => 'Selected notifications deleted successfully']);
    }

    private function formatTimeAgo($created_at, $now)
    {
        $diff = $created_at->diff($now);

        if ($diff->y > 0) {
            return $diff->y . 'y';
        } elseif ($diff->m > 0) {
            return $diff->m . 'm';
        } elseif ($diff->d > 0) {
            return $diff->d . 'd';
        } elseif ($diff->h > 0) {
            return $diff->h . 'h';
        } elseif ($diff->i > 0) {
            return $diff->i . 'm';
        } else {
            return $diff->s . 's';
        }
    }
}