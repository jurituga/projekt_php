@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="container">
    <div class="page-header-row">
        <h1>Notifications</h1>
        @if(auth()->user()->unreadNotifications->count() > 0)
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                <button type="submit" class="btn btn-ghost btn-sm">Mark all as read</button>
            </form>
        @endif
    </div>

    @if($notifications->isEmpty())
        <div class="card">
            <p class="muted">No notifications yet. You'll be notified about applications, service requests, messages, and account updates.</p>
        </div>
    @else
        <div class="notification-list">
            @foreach($notifications as $notification)
                @php
                    $data = $notification->data;
                    $isUnread = $notification->read_at === null;
                @endphp
                <a href="{{ route('notifications.read', $notification->id) }}" class="notification-item {{ $isUnread ? 'unread' : '' }}">
                    <div class="notification-icon">
                        @if(($data['icon'] ?? 'bell') === 'message')
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                        @elseif(($data['icon'] ?? '') === 'briefcase')
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
                        @elseif(($data['icon'] ?? '') === 'star')
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        @elseif(($data['icon'] ?? '') === 'payment')
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                        @else
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                        @endif
                    </div>
                    <div class="notification-body">
                        <div class="notification-title-row">
                            <span class="notification-title">{{ $data['title'] ?? 'Notification' }}</span>
                            @if($isUnread)
                                <span class="notification-dot"></span>
                            @endif
                        </div>
                        <p class="notification-message">{{ $data['message'] ?? '' }}</p>
                        <span class="notification-time">{{ $notification->created_at->diffForHumans() }}</span>
                    </div>
                </a>
            @endforeach
        </div>

        <div style="margin-top:1.5rem">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection
