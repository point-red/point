<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Model\Notification;
use App\Http\Resources\ApiCollection;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $userId = auth()->id();
        $perPage = $request->get('per_page', 10);

        $notifications = Notification::where('user_id', $userId)
            ->orderBy('created_at', 'desc');

        $result = pagination($notifications, $perPage ? $perPage : 10);

        // Hitung jumlah notifikasi belum dibaca
        $unreadCount = Notification::where('user_id', $userId)
            ->where('status', 'UNREAD')
            ->count();

        return response()->json([
            'data' => new ApiCollection($result),
            'unread_count' => $unreadCount
        ]);
    }

    public function update(Request $request, $id)
    {
        $userId = auth()->id();
        $notification = Notification::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        $notification->status = $request->input('status', 'READ');
        $notification->save();

        return response()->json([
            'message' => 'Notification status updated successfully.',
            'data' => $notification
        ]);
    }

    public function markAllAsRead(Request $request)
    {
        $userId = auth()->id();

        $updated = Notification::where('user_id', $userId)
            ->where('status', 'UNREAD')
            ->update(['status' => 'READ']);

        return response()->json([
            'message' => 'All notifications marked as read.',
            'updated_count' => $updated
        ]);
    }

    public function markAsRead(Request $request, $id)
    {
        $notification = Notification::where('id', $id)->firstOrFail();

        $notification->status = 'READ';
        $notification->save();

        return response()->json([
            'message' => 'Notification marked as read.',
            'data' => $notification
        ]);
    }
}