@extends('layouts.app')

@section('title', 'Job Listings')

@section('content')
<div class="container page-header">
    <h1>Job Listings</h1>
    <form method="get" action="{{ route('jobs.index') }}" class="search-form inline-form">
        <input type="text" name="q" placeholder="Search jobs..." value="{{ $search }}">
        <input type="text" name="location" placeholder="Location" value="{{ $location }}">
        <select name="type">
            <option value="">All types</option>
            <option value="full_time" @selected($jobType === 'full_time')>Full-time</option>
            <option value="part_time" @selected($jobType === 'part_time')>Part-time</option>
            <option value="contract" @selected($jobType === 'contract')>Contract</option>
            <option value="internship" @selected($jobType === 'internship')>Internship</option>
        </select>
        <button type="submit" class="btn btn-primary">Search</button>
    </form>
</div>

<div class="container">
    @if($jobs->isEmpty())
        <p class="muted">No jobs match your criteria.</p>
    @else
        <div class="card-list">
            @foreach($jobs as $job)
                <x-job-card :job="$job" :show-description="true" />
            @endforeach
        </div>
    @endif
</div>
@endsection
