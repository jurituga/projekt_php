@extends('layouts.app')

@section('title', 'My Services')

@section('content')
<div class="container">
    <x-dashboard-nav role="freelancer" />

    <div class="page-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem">
        <h1>My Services</h1>
        <a href="{{ route('freelancer.services.create') }}" class="btn btn-primary">Add Service</a>
    </div>

    @if($services->isEmpty())
        <p class="muted">No services yet. Create one, then add the dates and times you are free on the edit page.</p>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($services as $service)
                    <tr>
                        <td><strong>{{ $service->title }}</strong></td>
                        <td>
                            @if($service->price)
                                ${{ number_format($service->price, 0) }} {{ $service->price_type }}
                            @else
                                —
                            @endif
                        </td>
                        <td><span class="status status-{{ $service->status }}">{{ $service->status }}</span></td>
                        <td>{{ $service->created_at->format('M j, Y') }}</td>
                        <td>
                            <a href="{{ route('freelancer.services.edit', $service) }}" class="btn btn-small">Edit</a>
                            <form method="POST" action="{{ route('freelancer.services.destroy', $service) }}" style="display:inline" onsubmit="return confirm('Delete this service?');">
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
