@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="container">
    <x-dashboard-nav role="user" />

    <h1>My Profile</h1>

    <form method="POST" action="{{ route('user.profile.update') }}" class="form-card">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required>
        </div>
        <div class="form-group">
            <label>Email</label>
            <p class="muted">{{ $user->email }} (cannot change)</p>
        </div>
        <div class="form-group">
            <label for="phone">Phone</label>
            <input type="text" id="phone" name="phone" value="{{ old('phone', $profile->phone) }}">
        </div>
        <div class="form-group">
            <label for="address">Address</label>
            <textarea id="address" name="address" rows="2">{{ old('address', $profile->address) }}</textarea>
        </div>
        <div class="form-group">
            <label for="headline">Headline</label>
            <input type="text" id="headline" name="headline" value="{{ old('headline', $profile->headline) }}" placeholder="e.g. Software Developer">
        </div>
        <p class="form-hint">Upload and manage your CVs on the <a href="{{ route('user.cvs.index') }}">My CVs</a> page.</p>
        <button type="submit" class="btn btn-primary">Save Profile</button>
    </form>
</div>
@endsection
