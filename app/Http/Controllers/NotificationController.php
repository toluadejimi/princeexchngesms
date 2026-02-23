<?php

namespace App\Http\Controllers;

use App\Models\Notification as AppNotification;
use App\Models\NotificationRead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * List broadcast notifications for the authenticated user (with read status).
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $notifications = AppNotification::query()
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(function (AppNotification $n) use ($user) {
                $read = NotificationRead::where('notification_id', $n->id)
                    ->where('user_id', $user->id)
                    ->first();
                return [
                    'id' => $n->id,
                    'title' => $n->title,
                    'message' => $n->message,
                    'created_at' => $n->created_at->toIso8601String(),
                    'read_at' => $read?->read_at?->toIso8601String(),
                ];
            });

        $unreadCount = AppNotification::query()
            ->whereDoesntHave('reads', fn ($q) => $q->where('user_id', $user->id)->whereNotNull('read_at'))
            ->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Mark a notification as read for the authenticated user.
     */
    public function markRead(Request $request, int $id): JsonResponse
    {
        $n = AppNotification::findOrFail($id);
        NotificationRead::updateOrCreate(
            ['notification_id' => $n->id, 'user_id' => $request->user()->id],
            ['read_at' => now()]
        );
        return response()->json(['ok' => true]);
    }
}
