@extends('layouts.app')

@section('title', 'Manage Jobs')

@section('content')
<div class="container">
    <x-dashboard-nav role="admin" />

    <h1>Manage Jobs</h1>

    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Company</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($jobs as $job)
                <tr>
                    <td>{{ $job->id }}</td>
                    <td><a href="{{ route('jobs.show', $job) }}">{{ $job->title }}</a></td>
                    <td>{{ $job->company->company_name }}</td>
                    <td><span class="status status-{{ $job->status }}">{{ $job->status }}</span></td>
                    <td>{{ $job->created_at->format('M j, Y') }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.jobs.destroy') }}" style="display:inline" onsubmit="return confirm('Delete this job and all applications?');">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="job_id" value="{{ $job->id }}">
                            <button type="submit" class="btn btn-small btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
