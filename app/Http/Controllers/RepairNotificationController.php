<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RepairNotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return $this->payload($request);
    }

    public function poll(Request $request): JsonResponse
    {
        return $this->payload($request);
    }

    public function markRead(Request $request, string $notification): JsonResponse
    {
        $item = $request->user()->notifications()->whereKey($notification)->firstOrFail();
        $item->markAsRead();

        return response()->json(['unread_count' => $request->user()->unreadNotifications()->count()]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->json(['unread_count' => 0]);
    }

    private function payload(Request $request): JsonResponse
    {
        $user = $request->user();
        $items = $user->notifications()->latest()->limit(10)->get()->map(fn ($item) => [
            'notification_id' => (string) $item->getKey(),
            'message' => (string) ($item->data['message'] ?? 'New repair update.'),
            'id' => $item->data['id'] ?? null,
            'status' => $item->data['status'] ?? null,
            'link' => (string) ($item->data['link'] ?? '/repair'),
            'kind' => (string) ($item->data['kind'] ?? 'repair'),
            'created_at' => $item->created_at?->toIso8601String(),
            'read_at' => $item->read_at?->toIso8601String(),
        ]);

        return response()->json([
            'unread_count' => $user->unreadNotifications()->count(),
            'items' => $items,
        ]);
    }
}
