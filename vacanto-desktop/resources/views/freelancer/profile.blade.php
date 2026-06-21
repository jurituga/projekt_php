@extends('layouts.app')

@section('title', 'Freelancer Profile')

@section('content')
<div class="container">
    <x-dashboard-nav role="freelancer" />

    <h1>Freelancer Profile</h1>

    <form method="POST" action="{{ route('freelancer.profile.update') }}" enctype="multipart/form-data" class="form-card">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required>
        </div>
        <div class="form-group">
            <label>Email</label>
            <p class="muted">{{ $user->email }}</p>
        </div>
        <div class="form-group">
            <label for="freelancer_type">Freelancer type</label>
            <select id="freelancer_type" name="freelancer_type">
                @foreach(['general' => 'General Freelancer', 'electrician' => 'Electrician', 'plumber' => 'Plumber'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('freelancer_type', $profile->freelancer_type ?? 'general') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <span class="form-hint">Electricians and Plumbers can manage daily availability for bookings.</span>
        </div>
        <div class="form-group">
            <label for="bio">Bio</label>
            <textarea id="bio" name="bio" rows="4">{{ old('bio', $profile->bio) }}</textarea>
        </div>
        <div class="form-group">
            <label for="skills">Skills (comma-separated)</label>
            <input type="text" id="skills" name="skills" value="{{ old('skills', $profile->skills) }}" placeholder="PHP, MySQL, JavaScript">
        </div>
        <div class="form-group">
            <label for="hourly_rate">Hourly rate ($)</label>
            <input type="number" id="hourly_rate" name="hourly_rate" step="0.01" min="0" value="{{ old('hourly_rate', $profile->hourly_rate) }}">
        </div>

        <h2 class="form-section-title">Verification &amp; certifications</h2>
        <div class="form-group">
            <label for="government_id_ref">Government ID number or reference</label>
            <input type="text" id="government_id_ref" name="government_id_ref" value="{{ old('government_id_ref', $profile->government_id_ref) }}" placeholder="ID number">
        </div>
        <div class="form-group">
            <label for="government_id_file">Government ID (upload)</label>
            <input type="file" id="government_id_file" name="government_id_file" accept=".pdf,.jpg,.jpeg,.png,.gif,application/pdf,image/jpeg,image/png,image/gif">
            <span class="form-hint">PDF or image. Max 5MB.</span>
            @if($profile->government_id_path)
                <p class="muted">Current file uploaded. Upload a new file to replace.</p>
            @endif
        </div>
        <div class="form-group">
            <label for="qualifications">Licenses / certifications / qualifications list</label>
            <textarea id="qualifications" name="qualifications" rows="3" placeholder="List licenses, certifications, or qualifications...">{{ old('qualifications', $profile->qualifications) }}</textarea>
        </div>
        <div class="form-group">
            <label for="certification_file">Certification or license document (upload)</label>
            <input type="file" id="certification_file" name="certification_file" accept=".pdf,.jpg,.jpeg,.png,.gif,application/pdf,image/jpeg,image/png,image/gif">
            <span class="form-hint">PDF or image. Max 5MB.</span>
            @if($profile->certification_path)
                <p class="muted">Current file uploaded. Upload a new file to replace.</p>
            @endif
        </div>

        <button type="submit" class="btn btn-primary">Save Profile</button>
    </form>
</div>
@endsection
