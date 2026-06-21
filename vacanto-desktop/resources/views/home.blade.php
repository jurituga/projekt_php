@extends('layouts.app')

@section('title', 'Home')

@section('content')
<div class="hero">
    <div class="container">
        <h1>Hire experts.<br>Get hired.</h1>
        <p>The modern platform for job opportunities and professional freelance services. Find the right match, fast.</p>
        <div class="hero-actions">
            <a href="{{ route('jobs.index') }}" class="btn btn-primary btn-lg">Browse Jobs</a>
            <a href="{{ route('services.index') }}" class="btn btn-secondary btn-lg">Explore Services</a>
        </div>
    </div>
</div>

<div class="container section" style="margin-top: 3rem;">
    <h2>Latest Job Openings</h2>
    @if($recentJobs->isEmpty())
        <p class="muted">No job listings yet.</p>
    @else
        <div class="card-list">
            @foreach($recentJobs as $job)
                <x-job-card :job="$job" />
            @endforeach
        </div>
        <p style="margin-top:1.25rem;"><a href="{{ route('jobs.index') }}" class="btn btn-ghost btn-sm">View all jobs &rarr;</a></p>
    @endif
</div>

<div class="container section">
    <h2>Popular Services</h2>
    @if($recentServices->isEmpty())
        <p class="muted">No services yet.</p>
    @else
        <div class="card-list">
            @foreach($recentServices as $service)
                <x-service-card :service="$service" />
            @endforeach
        </div>
        <p style="margin-top:1.25rem;"><a href="{{ route('services.index') }}" class="btn btn-ghost btn-sm">View all services &rarr;</a></p>
    @endif
</div>
@endsection
