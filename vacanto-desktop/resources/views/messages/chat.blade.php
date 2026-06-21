@extends('layouts.app')

@section('title', 'Chat with '.$otherUser->name)

@section('content')
<div class="container chat-container">
    <div class="chat-header-bar">
        <a href="{{ route('messages.inbox') }}" class="btn btn-small btn-secondary">&larr; Inbox</a>
        <div class="chat-with">
            <span class="conv-avatar conv-avatar-sm">{{ strtoupper(mb_substr($otherUser->name, 0, 1)) }}</span>
            <strong>{{ $otherUser->name }}</strong>
            <span class="conv-role">{{ ucfirst($otherUser->role->value) }}</span>
        </div>
    </div>

    <div class="chat-messages" id="chatMessages">
        @if($messages->isEmpty())
            <p class="muted chat-empty">No messages yet. Say hello!</p>
        @else
            @php $lastDate = ''; @endphp
            @foreach($messages as $msg)
                @php $msgDate = $msg->created_at->format('M j, Y'); @endphp
                @if($msgDate !== $lastDate)
                    @php $lastDate = $msgDate; @endphp
                    <div class="chat-date-divider"><span>{{ $msgDate }}</span></div>
                @endif
                <div class="chat-bubble {{ $msg->sender_id === auth()->id() ? 'chat-mine' : 'chat-theirs' }}">
                    <p>{!! nl2br(e($msg->body)) !!}</p>
                    <span class="chat-time">{{ $msg->created_at->format('g:i A') }}</span>
                </div>
            @endforeach
        @endif
    </div>

    <form method="POST" action="{{ route('messages.store', $otherUser) }}" class="chat-input-bar" id="chatForm">
        @csrf
        <textarea name="message" id="chatInput" rows="1" placeholder="Type a message..." required>{{ old('message') }}</textarea>
        <button type="submit" class="btn btn-primary">Send</button>
    </form>
</div>
@endsection

@push('scripts')
<script>
var chatBox = document.getElementById('chatMessages');
if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;

var input = document.getElementById('chatInput');
if (input) {
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            if (this.value.trim()) document.getElementById('chatForm').submit();
        }
    });
}

var convWith = {{ $otherUser->id }};
var lastMsgId = {{ $messages->isNotEmpty() ? $messages->last()->id : 0 }};

setInterval(function() {
    fetch(@json(route('messages.poll')) + '?with=' + convWith + '&after=' + lastMsgId)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.messages && data.messages.length > 0) {
                data.messages.forEach(function(m) {
                    var empty = chatBox.querySelector('.chat-empty');
                    if (empty) empty.remove();
                    var div = document.createElement('div');
                    div.className = 'chat-bubble ' + (m.is_mine ? 'chat-mine' : 'chat-theirs');
                    div.innerHTML = '<p>' + escHtml(m.body) + '</p><span class="chat-time">' + m.time + '</span>';
                    chatBox.appendChild(div);
                    lastMsgId = m.id;
                });
                chatBox.scrollTop = chatBox.scrollHeight;
            }
            if (data.unread_total !== undefined) {
                var badge = document.getElementById('msgBadge');
                if (badge) {
                    badge.textContent = data.unread_total > 0 ? data.unread_total : '';
                    badge.style.display = data.unread_total > 0 ? 'inline-flex' : 'none';
                }
            }
        })
        .catch(function() {});
}, 5000);

function escHtml(s) {
    var d = document.createElement('div');
    d.textContent = s;
    return d.innerHTML.replace(/\n/g, '<br>');
}
</script>
@endpush
