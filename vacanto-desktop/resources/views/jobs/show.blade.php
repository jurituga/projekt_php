@extends('layouts.app')

@section('title', $job->title)

@section('content')
<div class="detail-header">
    <div class="container">
        <p class="breadcrumb"><a href="{{ route('jobs.index') }}">Jobs</a> &rarr; {{ $job->title }}</p>
        <h1>{{ $job->title }}</h1>
        <div class="detail-meta">
            <span class="detail-meta-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21V5a2 2 0 0 1 2-2h6l2 2h6a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                {{ $job->company->company_name }}
            </span>
            <span class="detail-meta-dot"></span>
            <span class="detail-meta-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                {{ $job->location ?? 'Remote' }}
            </span>
            <span class="detail-meta-dot"></span>
            <span class="detail-meta-item">{{ $job->created_at->format('M j, Y') }}</span>
        </div>
        <div class="detail-tags">
            <span class="detail-tag">{{ str_replace('_', ' ', $job->job_type) }}</span>
            @if($job->salary_min || $job->salary_max)
                <span class="detail-tag tag-salary">${{ $job->salary_min ? number_format($job->salary_min) : '—' }} – ${{ $job->salary_max ? number_format($job->salary_max) : '—' }}</span>
            @endif
        </div>
    </div>
</div>

<div class="container detail-content">
    <div class="detail-section">
        <h2 class="detail-section-title">Job Description</h2>
        <div class="content-block">{!! nl2br(e($job->description)) !!}</div>
    </div>

    @if($canApply)
        @if($alreadyApplied)
            <div class="card" style="text-align:center;padding:2rem">
                <p style="font-size:.95rem;color:var(--text-sub)">You have already applied for this job.</p>
            </div>
        @else
            <div class="card form-card">
                <h2>Apply for this position</h2>
                <form method="POST" action="{{ route('jobs.apply', $job) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="cv_id">Attach CV (PDF, max 5MB)</label>
                        @if($userCvs->isNotEmpty())
                            <select id="cv_id" name="cv_id">
                                <option value="">No CV</option>
                                @foreach($userCvs as $cv)
                                    <option value="{{ $cv->id }}" @selected(old('cv_id') == $cv->id || ($cv->is_default && ! old('cv_id')))>{{ $cv->file_name }}</option>
                                @endforeach
                            </select>
                            <p class="form-hint">Or upload a new CV below. <a href="{{ route('user.cvs.index') }}">Manage CVs</a></p>
                        @else
                            <p class="form-hint muted">You haven't uploaded a CV yet. Select a PDF below or <a href="{{ route('user.cvs.index') }}">go to My CVs</a>.</p>
                        @endif
                        <input type="file" id="cv_file" name="cv_file" accept="application/pdf" style="margin-top:.5rem">
                        @error('cv_file')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="cover_letter">Cover letter</label>
                        <textarea id="cover_letter" name="cover_letter" rows="5" placeholder="Tell the employer why you're a great fit...">{{ old('cover_letter') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit Application</button>
                </form>
            </div>
        @endif
    @elseif(!auth()->check())
        <div class="card" style="text-align:center;padding:2rem">
            <p style="margin-bottom:.75rem"><a href="{{ route('login') }}">Log in</a> or <a href="{{ route('register') }}">register</a> as a Job Seeker to apply.</p>
        </div>
    @endif
</div>
@endsection
