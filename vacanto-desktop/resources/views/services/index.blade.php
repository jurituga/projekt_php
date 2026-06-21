@extends('layouts.app')

@section('title', 'Services')

@section('content')
<div class="container page-header">
    <h1>Services</h1>
    <form method="get" action="{{ route('services.index') }}" class="search-form inline-form">
        <input type="text" name="q" placeholder="Search services..." value="{{ $search }}">
        <button type="submit" class="btn btn-primary">Search</button>
    </form>
</div>

<div class="container">
    @if($services->isEmpty())
        <p class="muted">No services found.</p>
    @else
        <div class="card-list">
            @foreach($services as $service)
                <x-service-card
                    :service="$service"
                    :avg-rating="$service->avg_rating ?? 0"
                    :rating-count="$service->rating_count ?? 0"
                    :show-description="true"
                />
            @endforeach
        </div>
    @endif
</div>
@endsection
