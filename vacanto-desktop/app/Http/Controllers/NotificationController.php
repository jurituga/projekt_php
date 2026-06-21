<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __construct(private NotificationService $notifications) {}

    public function index(): View
    {
        $notifications = auth()->user()
            ->notifications()
            ->latest()
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function markRead(string $id): RedirectResponse
    {
        $notification = auth()->user()->notifications()->where('id', $id)->first();

        if ($notification) {
            $notification->markAsRead();
        }

        $url = $notification?->data['url'] ?? null;

        if ($url) {
            return redirect($url);
        }

        return redirect()->route('notifications.index');
    }

    public function markAllRead(): RedirectResponse
    {
        auth()->user()->unreadNotifications->markAsRead();

        return redirect()->route('notifications.index')->with('success', 'All notifications marked as read.');
    }

    public function poll(): JsonResponse
    {
        if (! auth()->check()) {
            return response()->json(['unread_total' => 0]);
        }

        return response()->json([
            'unread_total' => $this->notifications->unreadCount(auth()->id()),
        ]);
    }
}
