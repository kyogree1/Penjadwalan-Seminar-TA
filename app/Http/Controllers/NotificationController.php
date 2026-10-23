<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $notifications = Notification::where('user_id', $user->id)
            ->latest()
            ->paginate(15)
            ->through(fn ($n) => [
                'id' => $n->id,
                'title' => $n->title,
                'message' => $n->message,
                'time' => $n->created_at->diffForHumans(),
                'read' => ! is_null($n->read_at),
                'type' => $n->type,
                'link' => $n->link,
            ]);

        return response()->json($notifications);
    }

    public function markAsRead(Request $request, $id)
    {
        $notification = Notification::where('user_id', Auth::id())
            ->findOrFail($id);

        $notification->update(['read_at' => now()]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Notifikasi ditandai sebagai dibaca');
    }

    public function markAllAsRead(Request $request)
    {
        Notification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Semua notifikasi ditandai sebagai dibaca');
    }
}
