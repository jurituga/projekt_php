@extends('layouts.app')

@section('title', 'My Jobs')

@section('content')
<div class="container">
    <x-dashboard-nav role="company" />

    <div class="page-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem">
        <h1>My Jobs</h1>
        <a href="{{ route('company.jobs.create') }}" class="btn btn-primary">Post a Job</a>
    </div>

    @if($jobs->isEmpty())
        <p class="muted">No jobs yet.</p>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Location</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Applications</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($jobs as $job)
                    <tr>
                        <td><strong>{{ $job->title }}</strong></td>
                        <td>{{ $job->location ?: '—' }}</td>
                        <td>{{ str_replace('_', '-', $job->job_type) }}</td>
                        <td><span class="status status-{{ $job->status }}">{{ $job->status }}</span></td>
                        <td>
                            <a href="{{ route('company.jobs.applications', $job) }}">{{ $job->applications_count }}</a>
                        </td>
                        <td>{{ $job->created_at->format('M j, Y') }}</td>
                        <td>
                            <a href="{{ route('company.jobs.edit', $job) }}" class="btn btn-small">Edit</a>
                            <form method="POST" action="{{ route('company.jobs.destroy', $job) }}" style="display:inline" onsubmit="return confirm('Delete this job?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-small btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
