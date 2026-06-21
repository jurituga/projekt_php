@extends('layouts.app')

@section('title', 'My Applications')

@section('content')
<div class="container">
    <x-dashboard-nav role="user" />

    <h1>My Applications</h1>

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
</div>
@endsection
