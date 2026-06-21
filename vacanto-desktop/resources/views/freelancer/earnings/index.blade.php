@extends('layouts.app')

@section('title', 'My Earnings')

@section('content')
<div class="container">
    <x-dashboard-nav role="freelancer" />

    <h1>My Earnings</h1>

    <div class="dashboard-stats">
        <div class="stat-card">
            <strong>${{ number_format($totalEarned, 2) }}</strong>
            <span>Total Earned</span>
        </div>
        <div class="stat-card">
            <strong>{{ $paidCount }}</strong>
            <span>Paid Bookings</span>
        </div>
    </div>

    <section class="card">
        <h2>Payment History</h2>
        @if($payments->isEmpty())
            <p class="muted">No payments received yet.</p>
        @else
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Service</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Job Status</th>
                        <th>Paid On</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payments as $p)
                        <tr>
                            <td>{{ $p->service->title }}</td>
                            <td>{{ $p->requester->name }}</td>
                            <td><strong>${{ number_format($p->payment_amount, 2) }}</strong></td>
                            <td><span class="status status-{{ $p->status }}">{{ $p->status }}</span></td>
                            <td>{{ $p->paid_at?->format('M j, Y g:i A') ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </section>
</div>
@endsection
