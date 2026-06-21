@extends('layouts.app')

@section('title', $service ? 'Edit Service' : 'Add Service')

@section('content')
<div class="container">
    <x-dashboard-nav role="freelancer" />

    <h1>{{ $service ? 'Edit Service' : 'Add Service' }}
        @if($isScheduled)
            <span class="status status-active">{{ ucfirst($freelancerType) }}</span>
        @endif
    </h1>

    <form method="POST" action="{{ $service ? route('freelancer.services.update', $service) : route('freelancer.services.store') }}" class="form-card">
        @csrf
        @if($service)
            @method('PUT')
        @endif

        <h2 class="form-section-title">Service details</h2>
        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" id="title" name="title" value="{{ old('title', $service?->title) }}" required>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="5" required>{{ old('description', $service?->description) }}</textarea>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="price">Price ($)</label>
                <input type="number" id="price" name="price" step="0.01" min="0" value="{{ old('price', $service?->price) }}">
            </div>
            <div class="form-group">
                <label for="price_type">Price type</label>
                <select id="price_type" name="price_type">
                    @foreach(['fixed' => 'Fixed', 'hourly' => 'Hourly'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('price_type', $service?->price_type ?? 'fixed') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        @if($service)
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    @foreach(['active', 'inactive'] as $status)
                        <option value="{{ $status }}" @selected(old('status', $service->status) === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        <button type="submit" class="btn btn-primary">{{ $service ? 'Update' : 'Create' }} Service</button>
    </form>

    @if($service)
        <div class="card form-card" style="margin-top:2rem;">
            <h2 class="form-section-title">Availability &mdash; add dates &amp; time slots</h2>
            <p class="muted">Add the dates and times when you are free for this service. Clients can pick a slot when booking.</p>

            <form method="POST" action="{{ route('freelancer.services.slots.store', $service) }}">
                @csrf
                <div class="form-row">
                    <div class="form-group">
                        <label for="slot_date">Date</label>
                        <input type="date" id="slot_date" name="slot_date" min="{{ date('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Select time slots</label>
                    <div class="time-slots-grid">
                        @foreach($timeOptions as $t)
                            <label class="time-slot-option">
                                <input type="checkbox" name="slot_times[]" value="{{ $t }}">
                                <span>{{ \Carbon\Carbon::parse($t)->format('g:i A') }}</span>
                            </label>
                        @endforeach
                    </div>
                    <span class="form-hint">Pick one or more times you're available on this date.</span>
                </div>
                <button type="submit" class="btn btn-primary">Add Slots</button>
            </form>
        </div>

        @if(!empty($slotsByDate))
            <div class="card" style="margin-top:1.5rem;">
                <h2>Current availability</h2>
                @foreach($slotsByDate as $date => $daySlots)
                    <div class="avail-day-group">
                        <h3 class="avail-day-heading">{{ \Carbon\Carbon::parse($date)->format('l, M j, Y') }}</h3>
                        <div class="avail-slots-row">
                            @foreach($daySlots as $slot)
                                <div class="avail-slot-chip {{ $slot->is_booked ? 'booked' : '' }}">
                                    <span>{{ \Carbon\Carbon::parse($slot->slot_time)->format('g:i A') }}</span>
                                    @if($slot->is_booked)
                                        <span class="chip-badge">Booked</span>
                                    @else
                                        <form method="POST" action="{{ route('freelancer.services.slots.destroy', [$service, $slot]) }}" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="chip-remove" title="Remove">&times;</button>
                                        </form>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    @endif

    <p style="margin-top:1.5rem;"><a href="{{ route('freelancer.services.index') }}">&larr; Back to Services</a></p>
</div>
@endsection
