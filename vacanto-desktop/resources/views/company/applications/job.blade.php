@extends('layouts.app')

@section('title', 'Applications: '.$job->title)

@section('content')
<div class="container">
    <x-dashboard-nav role="company" />

    <h1>Applications: {{ $job->title }}</h1>
    <p><a href="{{ route('company.jobs.index') }}">&larr; Back to Jobs</a></p>

    @if($applications->isEmpty())
        <p class="muted">No applications for this job yet.</p>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th>Applicant</th>
                    <th>Email</th>
                    <th>Cover letter</th>
                    <th>CV</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($applications as $app)
                    <tr>
                        <td>{{ $app->user->name }}</td>
                        <td>{{ $app->user->email }}</td>
                        <td>{{ Str::limit($app->cover_letter, 100) ?: '—' }}</td>
                        <td>
                            @if($app->cv)
                                <a href="{{ route('cvs.download', $app->cv) }}" target="_blank" rel="noopener">Download</a>
                            @else
                                —
                            @endif
                        </td>
                        <td><span class="status status-{{ $app->status }}">{{ $app->status }}</span></td>
                        <td>{{ $app->created_at->format('M j, Y') }}</td>
                        <td><x-application-actions :application="$app" /></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
