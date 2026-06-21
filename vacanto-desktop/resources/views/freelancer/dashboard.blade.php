@extends('layouts.app')

@section('title', 'Freelancer Dashboard')

@section('content')
<div class="container">
    <x-dashboard-nav role="freelancer" />

    <div class="page-header">
        <h1>Freelancer Dashboard
            @if($isScheduled)
                <span class="status status-active">{{ ucfirst($freelancerType) }}</span>
            @endif
        </h1>
        <p class="muted">Welcome back, {{ auth()->user()->name }}.</p>
    </div>

    <div class="dashboard-stats">
        <div class="stat-card">
            <strong>{{ $activeServicesCount }}</strong>
            <span>Active services</span>
        </div>
        <div class="stat-card">
            <strong>{{ $pendingCount }}</strong>
            <span>Pending requests</span>
        </div>
        <div class="stat-card">
            <strong>${{ number_format($totalEarned, 2) }}</strong>
            <span>Total Earned</span>
        </div>
        <div class="stat-card">
            @if($ratingCount > 0)
                <strong>{!! render_stars($avgRating) !!} {{ number_format($avgRating, 1) }}</strong>
                <span>{{ $ratingCount }} review{{ $ratingCount > 1 ? 's' : '' }}</span>
            @else
                <strong>&mdash;</strong>
                <span>No ratings yet</span>
            @endif
        </div>
    </div>

    <section class="card" style="margin-bottom:1.5rem">
        <div class="page-header-row" style="margin-bottom:1rem">
            <h2 style="margin:0">My Services</h2>
            <a href="{{ route('freelancer.services.create') }}" class="btn btn-primary btn-sm">Add Service</a>
        </div>

        @if($services->isEmpty())
            <p class="muted">No services yet. Create a service, then add the dates and times you are available.</p>
            <a href="{{ route('freelancer.services.create') }}" class="btn btn-primary" style="margin-top:.75rem">Create your first service</a>
        @else
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Price</th>
                        <th>Open slots</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($services as $service)
                        <tr>
                            <td><strong>{{ $service->title }}</strong></td>
                            <td>
                                @if($service->price)
                                    ${{ number_format($service->price, 0) }} {{ $service->price_type }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @if($service->open_slots_count > 0)
                                    {{ $service->open_slots_count }} available
                                @else
                                    <span class="muted">None set</span>
                                @endif
                            </td>
                            <td><span class="status status-{{ $service->status }}">{{ $service->status }}</span></td>
                            <td>
                                <a href="{{ route('freelancer.services.edit', $service) }}" class="btn btn-small">Edit &amp; availability</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <p style="margin-top:1rem"><a href="{{ route('freelancer.services.index') }}">View all services &rarr;</a></p>
        @endif
    </section>

    <section class="card" style="margin-bottom:1.5rem">
        <h2>Service Requests</h2>
        @if($requests->isEmpty())
            <p class="muted">No service requests yet.</p>
        @else
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Service</th>
                        <th>Requester</th>
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
                            <td><span class="status status-{{ $r->status }}">{{ $r->status }}</span></td>
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
                                <td colspan="7">
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
    </section>
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
