<?php
namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Store a new notification.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|string|max:50',
            'message' => 'required|string',
        ]);

        $notification = Notification::create([
            'user_id' => $request->user_id,
            'type' => $request->type,
            'message' => $request->message,
        ]);

        return response()->json(['success' => true, 'notification' => $notification], 201);
    }

    /**
     * Get all notifications for a user.
     */
    public function index(Request $request)
    {
        $userId = auth()->id();
       $notifications = Notification::where('user_id', $userId)
        ->where('is_read', false)
        ->orderBy('created_at', 'desc')
        ->get();


        return response()->json(['notifications' => $notifications], 200);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead($notificationId)
    {
        $notification = Notification::find($notificationId);

        if (!$notification) {
            return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
        }

        $notification->is_read = true;
        $notification->save();

        return response()->json(['success' => true, 'notification' => $notification], 200);
    }
}
