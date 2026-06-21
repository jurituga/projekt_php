@extends('layouts.app')

@section('title', 'All Applications')

@section('content')
<div class="container">
    <x-dashboard-nav role="company" />

    <h1>All Applications</h1>

    @if($applications->isEmpty())
        <p class="muted">No applications yet.</p>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th>Job</th>
                    <th>Applicant</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>CV</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($applications as $app)
                    <tr>
                        <td><a href="{{ route('company.jobs.applications', $app->jobPosting) }}">{{ $app->jobPosting->title }}</a></td>
                        <td>{{ $app->user->name }}</td>
                        <td>{{ $app->user->email }}</td>
                        <td><span class="status status-{{ $app->status }}">{{ $app->status }}</span></td>
                        <td>
                            @if($app->cv)
                                <a href="{{ route('cvs.download', $app->cv) }}">Download</a>
                            @else
                                —
                            @endif
                        </td>
                        <td>{{ $app->created_at->format('M j, Y') }}</td>
                        <td><x-application-actions :application="$app" /></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
