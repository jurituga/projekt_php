@extends('layouts.app')

@section('title', 'Company Profile')

@section('content')
<div class="container">
    <x-dashboard-nav role="company" />

    <h1>Company Profile</h1>

    <form method="POST" action="{{ route('company.profile.update') }}" enctype="multipart/form-data" class="form-card">
        @csrf
        @method('PUT')

        <h2>Contact</h2>
        <div class="form-group">
            <label for="name">Contact Name</label>
            <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required>
        </div>
        <div class="form-group">
            <label>Email</label>
            <p class="muted">{{ $user->email }}</p>
        </div>

        <h2>Company</h2>
        <div class="form-group">
            <label for="company_name">Company Name</label>
            <input type="text" id="company_name" name="company_name" value="{{ old('company_name', $company->company_name) }}" required>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4">{{ old('description', $company->description) }}</textarea>
        </div>
        <div class="form-group">
            <label for="industry">Industry</label>
            <input type="text" id="industry" name="industry" value="{{ old('industry', $company->industry) }}">
        </div>
        <div class="form-group">
            <label for="website">Website</label>
            <input type="url" id="website" name="website" value="{{ old('website', $company->website) }}">
        </div>
        <div class="form-group">
            <label for="phone">Phone</label>
            <input type="text" id="phone" name="phone" value="{{ old('phone', $company->phone) }}">
        </div>
        <div class="form-group">
            <label for="address">Address</label>
            <textarea id="address" name="address" rows="2">{{ old('address', $company->address) }}</textarea>
        </div>

        <h2 class="form-section-title">Verification &amp; trust</h2>
        <div class="form-group">
            <label for="business_registration_number">Business registration number</label>
            <input type="text" id="business_registration_number" name="business_registration_number" value="{{ old('business_registration_number', $company->business_registration_number) }}" placeholder="Company registration ID">
        </div>
        <div class="form-group">
            <label for="tax_id_vat">Tax ID / VAT number</label>
            <input type="text" id="tax_id_vat" name="tax_id_vat" value="{{ old('tax_id_vat', $company->tax_id_vat) }}" placeholder="Region-dependent">
        </div>
        <div class="form-group">
            <label for="government_id_ref">Government ID number or reference</label>
            <input type="text" id="government_id_ref" name="government_id_ref" value="{{ old('government_id_ref', $company->government_id_ref) }}" placeholder="ID number">
        </div>
        <div class="form-group">
            <label for="government_id_file">Government ID (upload)</label>
            <input type="file" id="government_id_file" name="government_id_file" accept=".pdf,.jpg,.jpeg,.png,.gif,application/pdf,image/jpeg,image/png,image/gif">
            <span class="form-hint">PDF or image (JPEG, PNG, GIF). Max 5MB.</span>
            @if($company->government_id_path)
                <p class="muted">Current file uploaded. Upload a new file to replace.</p>
            @endif
        </div>

        <button type="submit" class="btn btn-primary">Save Profile</button>
    </form>
</div>
@endsection
