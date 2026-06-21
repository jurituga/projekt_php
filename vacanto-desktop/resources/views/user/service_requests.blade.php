@extends('layouts.app')

@section('title', 'My Service Requests')

@section('content')
<div class="container">
    <x-dashboard-nav role="user" />

    <h1>My Service Requests</h1>

    @if($serviceRequests->isEmpty())
        <p class="muted">No service requests. <a href="{{ route('services.index') }}">Browse services</a>.</p>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th>Service</th>
                    <th>Freelancer</th>
                    <th>Booking</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Rating</th>
                    <th>Requested</th>
                </tr>
            </thead>
            <tbody>
                @foreach($serviceRequests as $sr)
                    <tr>
                        <td><a href="{{ route('services.show', $sr->service) }}">{{ $sr->service->title }}</a></td>
                        <td>
                            {{ $sr->service->freelancer->name }}
                            <a href="{{ route('messages.chat', $sr->service->freelancer) }}" class="btn btn-small btn-secondary" title="Message">&#9993;</a>
                        </td>
                        <td>
                            @if($sr->booking_date)
                                {{ $sr->booking_date->format('M j, Y') }}
                                @if($sr->booking_time)
                                    <br><small>{{ \Carbon\Carbon::parse($sr->booking_time)->format('g:i A') }}</small>
                                @endif
                            @else
                                <span class="muted">&mdash;</span>
                            @endif
                        </td>
                        <td><x-payment-status :request="$sr" /></td>
                        <td>
                            <span class="status status-{{ $sr->status }}">{{ $sr->status }}</span>
                            @if($sr->status === 'rejected' && $sr->rejection_reason)
                                <br><small class="text-danger"><strong>Reason:</strong> {{ $sr->rejection_reason }}</small>
                            @endif
                        </td>
                        <td>
                            @if($sr->status === 'completed' && $sr->rating)
                                {!! render_stars($sr->rating->rating) !!}
                            @elseif($sr->status === 'completed')
                                <a href="{{ route('ratings.create', $sr) }}" class="btn btn-small btn-success">Rate</a>
                            @else
                                <span class="muted">&mdash;</span>
                            @endif
                        </td>
                        <td>{{ $sr->created_at->format('M j, Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
