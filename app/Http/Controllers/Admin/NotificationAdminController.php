<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationAdminController extends Controller
{
    /**
     * Show form to create a broadcast notification (admin dashboard).
     */
    public function index(): View
    {
        $notifications = Notification::orderByDesc('created_at')->limit(20)->get();
        return view('admin.notifications.index', ['notifications' => $notifications]);
    }

    /**
     * Store a new broadcast notification (all users will see it).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ]);
        Notification::create([
            'title' => $validated['title'],
            'message' => $validated['message'],
        ]);
        return redirect()->route('admin.notifications.index')->with('message', 'Notification sent to all users.');
    }

    /**
     * Delete a broadcast notification (and its read records).
     */
    public function destroy(Notification $notification): RedirectResponse
    {
        $notification->reads()->delete();
        $notification->delete();
        return redirect()->route('admin.notifications.index')->with('message', 'Notification deleted.');
    }
}
