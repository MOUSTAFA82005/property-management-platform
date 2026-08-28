<?php

namespace App\Http\Controllers;

use App\Http\Resources\NotificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The authenticated user's own notifications.
 *
 * Scoping is structural rather than checked: every query starts from
 * `$request->user()->notifications`, so there is no code path that could read
 * or write another user's row. Both roles share these endpoints — a
 * notification belongs to a user, not to a portal.
 */
class NotificationController extends Controller
{
    /** GET /api/notifications */
    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->notifications()
            ->when($request->boolean('unread'), fn ($query) => $query->whereNull('read_at'))
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return NotificationResource::collection($notifications)->response();
    }

    /** GET /api/notifications/unread-count — polled by the bell. */
    public function unreadCount(Request $request): JsonResponse
    {
        return response()->json([
            'count' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    /** POST /api/notifications/{notification}/read */
    public function markRead(Request $request, string $notification): JsonResponse
    {
        // findOrFail on the user's own relation: someone else's id is a 404,
        // never a silent success.
        $record = $request->user()->notifications()->findOrFail($notification);

        $record->markAsRead();

        return (new NotificationResource($record->fresh()))->response();
    }

    /** POST /api/notifications/read-all */
    public function markAllRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json([
            'count' => $request->user()->unreadNotifications()->count(),
        ]);
    }
}
