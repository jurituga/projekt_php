@extends('layouts.app')

@section('title', $job ? 'Edit Job' : 'Post Job')

@section('content')
<div class="container">
    <x-dashboard-nav role="company" />

    <h1>{{ $job ? 'Edit Job' : 'Post a Job' }}</h1>

    <form method="POST" action="{{ $job ? route('company.jobs.update', $job) : route('company.jobs.store') }}" class="form-card">
        @csrf
        @if($job)
            @method('PUT')
        @endif

        <div class="form-group">
            <label for="title">Job Title</label>
            <input type="text" id="title" name="title" value="{{ old('title', $job?->title) }}" required>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="6" required>{{ old('description', $job?->description) }}</textarea>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="location">Location</label>
                <input type="text" id="location" name="location" value="{{ old('location', $job?->location) }}">
            </div>
            <div class="form-group">
                <label for="job_type">Job Type</label>
                <select id="job_type" name="job_type">
                    @foreach(['full_time' => 'Full-time', 'part_time' => 'Part-time', 'contract' => 'Contract', 'internship' => 'Internship'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('job_type', $job?->job_type ?? 'full_time') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="salary_min">Salary Min ($)</label>
                <input type="number" id="salary_min" name="salary_min" step="0.01" min="0" value="{{ old('salary_min', $job?->salary_min) }}">
            </div>
            <div class="form-group">
                <label for="salary_max">Salary Max ($)</label>
                <input type="number" id="salary_max" name="salary_max" step="0.01" min="0" value="{{ old('salary_max', $job?->salary_max) }}">
            </div>
        </div>
        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status">
                @foreach(['draft', 'published', 'closed'] as $status)
                    <option value="{{ $status }}" @selected(old('status', $job?->status ?? 'published') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary">{{ $job ? 'Update' : 'Publish' }} Job</button>
    </form>

    <p style="margin-top:1rem"><a href="{{ route('company.jobs.index') }}">&larr; Back to Jobs</a></p>
</div>
@endsection
