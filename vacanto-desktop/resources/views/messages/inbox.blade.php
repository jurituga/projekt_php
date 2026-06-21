@extends('layouts.app')

@section('title', 'Messages')

@section('content')
<div class="container">
    <h1>Messages</h1>

    @if($conversations->isEmpty())
        <div class="card">
            <p class="muted">No conversations yet. Start one by clicking "Message" on a freelancer's service page.</p>
        </div>
    @else
        <div class="conversation-list">
            @foreach($conversations as $conv)
                <a href="{{ route('messages.chat', $conv->other_id) }}" class="conversation-item {{ $conv->unread_count > 0 ? 'unread' : '' }}">
                    <div class="conv-avatar">{{ strtoupper(mb_substr($conv->other_name, 0, 1)) }}</div>
                    <div class="conv-body">
                        <div class="conv-header-row">
                            <span class="conv-name">{{ $conv->other_name }}</span>
                            <span class="conv-role">{{ ucfirst($conv->other_role) }}</span>
                            @if($conv->unread_count > 0)
                                <span class="conv-badge">{{ $conv->unread_count }}</span>
                            @endif
                        </div>
                        <p class="conv-preview">{{ Str::limit($conv->last_message ?? '', 80) }}</p>
                        @if($conv->last_message_at)
                            <span class="conv-time">{{ \Carbon\Carbon::parse($conv->last_message_at)->format('M j, g:i A') }}</span>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
