@extends('layouts.app')

@section('title', 'Company Dashboard')

@section('content')
<div class="container">
    <x-dashboard-nav role="company" />

    <div class="page-header">
        <h1>Company Dashboard</h1>
        <p class="muted">Welcome back, {{ auth()->user()->name }}.</p>
    </div>

    <div class="dashboard-stats">
        <div class="stat-card">
            <strong>{{ $jobsCount }}</strong>
            <span>Published jobs</span>
        </div>
        <div class="stat-card">
            <strong>{{ $applications->count() }}</strong>
            <span>Recent applications</span>
        </div>
    </div>

    <section class="card" style="margin-bottom:1.5rem">
        <h2>Recent Job Applications</h2>
        @if($applications->isEmpty())
            <p class="muted">No applications yet.</p>
        @else
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Job</th>
                        <th>Applicant</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($applications as $app)
                        <tr>
                            <td>{{ $app->jobPosting->title }}</td>
                            <td>
                                {{ $app->user->name }}
                                @if($app->cv)
                                    <br><a href="{{ route('cvs.download', $app->cv) }}" class="btn btn-small" target="_blank" rel="noopener">CV</a>
                                @endif
                            </td>
                            <td><span class="status status-{{ $app->status }}">{{ $app->status }}</span></td>
                            <td>{{ $app->created_at->format('M j, Y') }}</td>
                            <td>
                                @if(in_array($app->status, ['pending', 'viewed']))
                                    <x-application-actions :application="$app" />
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </section>

    @if($serviceRequests->isNotEmpty())
    <section class="card" style="margin-bottom:1.5rem">
        <h2>My Service Requests</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Service</th>
                    <th>Freelancer</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Requested</th>
                </tr>
            </thead>
            <tbody>
                @foreach($serviceRequests as $sr)
                    <tr>
                        <td>{{ $sr->service->title }}</td>
                        <td>{{ $sr->service->freelancer->name }}</td>
                        <td><x-payment-status :request="$sr" /></td>
                        <td>
                            <span class="status status-{{ $sr->status }}">{{ $sr->status }}</span>
                            @if($sr->status === 'rejected' && $sr->rejection_reason)
                                <br><small class="text-danger"><strong>Reason:</strong> {{ Str::limit($sr->rejection_reason, 60) }}</small>
                            @endif
                        </td>
                        <td>{{ $sr->created_at->format('M j, Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>
    @endif
</div>
@endsection
