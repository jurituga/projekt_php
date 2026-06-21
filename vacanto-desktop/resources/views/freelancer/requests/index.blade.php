@extends('layouts.app')

@section('title', 'Service Requests')

@section('content')
<div class="container">
    <x-dashboard-nav role="freelancer" />

    <h1>Service Requests</h1>

    @if($requests->isEmpty())
        <p class="muted">No service requests yet.</p>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th>Service</th>
                    <th>Requester</th>
                    <th>Message</th>
                    <th>Booking</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Requested</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($requests as $r)
                    <tr>
                        <td>{{ $r->service->title }}</td>
                            <td>
                                {{ $r->requester->name }}
                                <a href="{{ route('messages.chat', $r->requester) }}" class="btn btn-small btn-secondary" title="Message">&#9993;</a>
                            </td>
                        <td>{{ Str::limit($r->message, 80) ?: '—' }}</td>
                        <td>
                            @if($r->booking_date)
                                {{ $r->booking_date->format('M j, Y') }}
                                @if($r->booking_time)
                                    <br><small>{{ \Carbon\Carbon::parse($r->booking_time)->format('g:i A') }}</small>
                                @endif
                            @else
                                <span class="muted">&mdash;</span>
                            @endif
                        </td>
                        <td><x-payment-status :request="$r" /></td>
                        <td>
                            <span class="status status-{{ $r->status }}">{{ $r->status }}</span>
                            @if($r->status === 'rejected' && $r->rejection_reason)
                                <br><small class="muted">Reason: {{ Str::limit($r->rejection_reason, 60) }}</small>
                            @endif
                        </td>
                        <td>{{ $r->created_at->format('M j, Y') }}</td>
                        <td>
                            @if($r->status === 'pending')
                                <form method="POST" action="{{ route('freelancer.requests.action') }}" style="display:inline">
                                    @csrf
                                    <input type="hidden" name="request_id" value="{{ $r->id }}">
                                    <input type="hidden" name="action" value="accept">
                                    <button type="submit" class="btn btn-small">Accept</button>
                                </form>
                            @endif
                            @if($r->status === 'accepted')
                                <form method="POST" action="{{ route('freelancer.requests.action') }}" style="display:inline" onsubmit="return confirm('Mark this job as completed?');">
                                    @csrf
                                    <input type="hidden" name="request_id" value="{{ $r->id }}">
                                    <input type="hidden" name="action" value="complete">
                                    <button type="submit" class="btn btn-small btn-success">Complete</button>
                                </form>
                            @endif
                            @if(in_array($r->status, ['pending', 'accepted']))
                                <button type="button" class="btn btn-small btn-danger" onclick="toggleRejectForm({{ $r->id }})">Reject</button>
                            @endif
                        </td>
                    </tr>
                    @if(in_array($r->status, ['pending', 'accepted']))
                        <tr class="reject-form-row" id="reject-form-{{ $r->id }}" style="display:none;">
                            <td colspan="8">
                                <form method="POST" action="{{ route('freelancer.requests.action') }}" class="reject-reason-form">
                                    @csrf
                                    <input type="hidden" name="request_id" value="{{ $r->id }}">
                                    <input type="hidden" name="action" value="reject">
                                    <label><strong>Reason for rejection</strong> <span class="required">*</span></label>
                                    <textarea name="rejection_reason" rows="2" required placeholder="Explain why you are rejecting this request..."></textarea>
                                    <div style="margin-top:0.5rem;">
                                        <button type="submit" class="btn btn-small btn-danger">Confirm Rejection</button>
                                        <button type="button" class="btn btn-small btn-ghost" onclick="toggleRejectForm({{ $r->id }})">Cancel</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection

@push('scripts')
<script>
function toggleRejectForm(id) {
    var row = document.getElementById('reject-form-' + id);
    if (row) {
        row.style.display = row.style.display === 'none' ? 'table-row' : 'none';
    }
}
</script>
@endpush
