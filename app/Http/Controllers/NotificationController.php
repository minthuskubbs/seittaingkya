<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()->notifications()->paginate(30);

        return view('notifications.index', compact('notifications'));
    }

    /** JSON feed used by the front-end bell + browser Notification popups. */
    public function unread()
    {
        $user = auth()->user();

        return response()->json([
            'count' => $user->unreadNotifications()->count(),
            'items' => $user->unreadNotifications()->take(10)->get()->map(fn ($n) => [
                'id' => $n->id,
                'title' => $n->data['title'] ?? 'Notification',
                'message' => $n->data['message'] ?? '',
                'created_at' => $n->created_at->diffForHumans(),
            ]),
        ]);
    }

    public function markRead(Request $request)
    {
        if ($request->id) {
            auth()->user()->notifications()->where('id', $request->id)->update(['read_at' => now()]);
        } else {
            auth()->user()->unreadNotifications->markAsRead();
        }

        return response()->json(['ok' => true]);
    }
}
