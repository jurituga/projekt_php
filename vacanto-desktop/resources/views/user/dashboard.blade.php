@extends('layouts.app')

@section('title', 'My Dashboard')

@section('content')
<div class="container">
    <x-dashboard-nav role="user" />

    <div class="page-header">
        <h1>My Dashboard</h1>
        <p class="muted">Welcome back, {{ auth()->user()->name }}.</p>
    </div>

    <section class="card" style="margin-bottom:1.5rem">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;margin-bottom:1rem">
            <h2 style="margin:0">My CVs</h2>
            <a href="{{ route('user.cvs.index') }}" class="btn btn-ghost btn-sm">Manage CVs</a>
        </div>
        @if($cvs->isEmpty())
            <p class="muted" style="margin-bottom:1rem">Upload a PDF CV to attach when applying for jobs.</p>
            <form method="POST" action="{{ route('user.cvs.store') }}" enctype="multipart/form-data" class="inline-upload-form">
                @csrf
                <div class="form-group" style="margin-bottom:.75rem">
                    <input type="file" name="cv_file" accept="application/pdf" required>
                    @error('cv_file')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary btn-sm">Upload CV</button>
            </form>
        @else
            <ul class="cv-summary-list">
                @foreach($cvs->take(3) as $cv)
                    <li>
                        <span>{{ $cv->file_name }}</span>
                        @if($cv->is_default)
                            <span class="status status-published" style="font-size:.7rem">Default</span>
                        @endif
                    </li>
                @endforeach
            </ul>
            @if($cvs->count() > 3)
                <p class="muted" style="margin-top:.5rem;font-size:.85rem">+ {{ $cvs->count() - 3 }} more</p>
            @endif
        @endif
    </section>

    <section class="card" style="margin-bottom:1.5rem">
        <h2>My Applications</h2>
        @if($applications->isEmpty())
            <p class="muted">You haven't applied to any jobs yet. <a href="{{ route('jobs.index') }}">Browse jobs</a>.</p>
        @else
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Job</th>
                        <th>Company</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($applications as $app)
                        <tr>
                            <td><a href="{{ route('jobs.show', $app->jobPosting) }}">{{ $app->jobPosting->title }}</a></td>
                            <td>{{ $app->jobPosting->company->company_name }}</td>
                            <td><span class="status status-{{ $app->status }}">{{ $app->status }}</span></td>
                            <td>{{ $app->created_at->format('M j, Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </section>

    <section class="card" style="margin-bottom:1.5rem">
        <h2>My Service Requests</h2>
        @if($serviceRequests->isEmpty())
            <p class="muted">No service requests. <a href="{{ route('services.index') }}">Browse services</a>.</p>
        @else
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Service</th>
                        <th>Freelancer</th>
                        <th>Booking Date</th>
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
                                    <br><small class="text-danger"><strong>Reason:</strong> {{ Str::limit($sr->rejection_reason, 60) }}</small>
                                @endif
                            </td>
                            <td>{{ $sr->created_at->format('M j, Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </section>
</div>
@endsection
