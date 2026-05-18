<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Poll for unread notifications (JS short-polling, no WebSocket needed).
     * Returns unread count + latest 5 notifications.
     */
    public function poll()
    {
        $user = Auth::user();
        if (!$user) return response()->json(['unread_count' => 0, 'notifications' => []]);

        $unread = $user->unreadNotifications()->count();
        $notifications = $user->notifications()->latest()->take(10)->get()->map(function ($n) {
            return [
                'id'         => $n->id,
                'read'       => !is_null($n->read_at),
                'message'    => $n->data['message'] ?? 'Notifikasi baru',
                'url'        => $n->data['url'] ?? '#',
                'time'       => $n->created_at->diffForHumans(),
                'created_at' => $n->created_at->toISOString(),
            ];
        });

        return response()->json([
            'unread_count'  => $unread,
            'notifications' => $notifications,
        ]);
    }

    public function markRead(Request $request)
    {
        $user = Auth::user();
        if (!$user) return response()->json(['ok' => false]);

        if ($request->filled('id')) {
            $n = $user->notifications()->find($request->id);
            if ($n) { $n->markAsRead(); }
        } else {
            $user->unreadNotifications()->update(['read_at' => now()]);
        }

        return response()->json(['ok' => true]);
    }
}
