<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\NotificationResource;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Display a listing of the user's notifications.
     */
    public function index(Request $request)
    {
        $notifications = $request->user()->notifications()
            ->latest()
            ->cursorPaginate($request->get('per_page', 15));

        return NotificationResource::collection($notifications);
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead(Request $request, $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->update(['read' => true]);

        return response()->json(['message' => 'Notification marked as read']);
    }

    /**
     * Mark all user's notifications as read.
     */
    public function markAllAsRead(Request $request)
    {
        $request->user()->notifications()->unread()->update(['read' => true]);

        return response()->json(['message' => 'All notifications marked as read']);
    }
}
