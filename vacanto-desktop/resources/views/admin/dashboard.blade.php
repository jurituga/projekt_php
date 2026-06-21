@extends('layouts.app')

@section('title', 'Admin Panel')

@section('content')
<div class="container">
    <x-dashboard-nav role="admin" />

    <div class="page-header">
        <h1>Admin Control Panel</h1>
        <p class="muted">Welcome back, {{ auth()->user()->name }}.</p>
    </div>

    <div class="dashboard-stats admin-stats">
        <div class="stat-card"><strong>{{ $stats['users'] }}</strong><span>Users</span></div>
        <div class="stat-card">
            <strong>{{ $stats['pending'] }}</strong>
            <span>Pending approval</span>
        </div>
        <div class="stat-card"><strong>{{ $stats['companies'] }}</strong><span>Companies</span></div>
        <div class="stat-card"><strong>{{ $stats['jobs'] }}</strong><span>Jobs</span></div>
        <div class="stat-card"><strong>{{ $stats['applications'] }}</strong><span>Applications</span></div>
        <div class="stat-card"><strong>{{ $stats['services'] }}</strong><span>Services</span></div>
        <div class="stat-card"><strong>{{ $stats['service_requests'] }}</strong><span>Requests</span></div>
    </div>

    <div class="admin-links" style="margin-top:1.5rem;display:flex;gap:.75rem;flex-wrap:wrap">
        <a href="{{ route('admin.users.index') }}" class="btn btn-primary">Manage Users</a>
        <a href="{{ route('admin.jobs.index') }}" class="btn btn-secondary">Manage Jobs</a>
        <a href="{{ route('admin.services.index') }}" class="btn btn-secondary">Manage Services</a>
    </div>
</div>
@endsection
