<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()->userNotifications()->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function read(Request $request, $id)
    {
        $notification = $request->user()->userNotifications()->findOrFail($id);

        if (! $notification->read_at) {
            $notification->update(['read_at' => now()]);
        }

        return redirect($notification->url ?: route('notifications.index'));
    }

    public function readAll(Request $request)
    {
        $request->user()->userNotifications()->whereNull('read_at')->update(['read_at' => now()]);

        return back()->with('success', __('All notifications were marked as read').'.');
    }
}
