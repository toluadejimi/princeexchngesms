<?php

namespace App\Http\Controllers;

use App\Models\Notification as AppNotification;
use App\Models\NotificationRead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class NotificationController extends Controller
{
    /**
     * List broadcast notifications for the authenticated user (with read status).
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $latestNotifications = Cache::remember(AppNotification::LATEST_CACHE_KEY, now()->addMinutes(5), fn () => AppNotification::query()
            ->select(['id', 'title', 'message', 'created_at'])
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(fn (AppNotification $n) => [
                'id' => $n->id,
                'title' => $n->title,
                'message' => $n->message,
                'created_at' => $n->created_at->toIso8601String(),
            ])
            ->all());

        $readByNotification = NotificationRead::query()
            ->where('user_id', $user->id)
            ->whereIn('notification_id', collect($latestNotifications)->pluck('id'))
            ->whereNotNull('read_at')
            ->get(['notification_id', 'read_at'])
            ->mapWithKeys(fn (NotificationRead $read) => [
                $read->notification_id => $read->read_at?->toIso8601String(),
            ]);

        $notifications = collect($latestNotifications)
            ->map(fn (array $notification) => $notification + [
                'read_at' => $readByNotification->get($notification['id']),
            ])
            ->values();

        $unreadCount = AppNotification::query()
            ->whereDoesntHave('reads', fn ($q) => $q->where('user_id', $user->id)->whereNotNull('read_at'))
            ->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ])->header('Cache-Control', 'private, max-age=30, stale-while-revalidate=60');
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
