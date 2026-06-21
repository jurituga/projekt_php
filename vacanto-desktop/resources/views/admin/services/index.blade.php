@extends('layouts.app')

@section('title', 'Manage Services')

@section('content')
<div class="container">
    <x-dashboard-nav role="admin" />

    <h1>Manage Services</h1>

    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Freelancer</th>
                <th>Price</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($services as $service)
                <tr>
                    <td>{{ $service->id }}</td>
                    <td><a href="{{ route('services.show', $service) }}">{{ $service->title }}</a></td>
                    <td>{{ $service->freelancer->name }}</td>
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
                        <form method="POST" action="{{ route('admin.services.destroy') }}" style="display:inline" onsubmit="return confirm('Delete this service?');">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="service_id" value="{{ $service->id }}">
                            <button type="submit" class="btn btn-small btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
