@extends('layouts.app')

@section('title', 'Register')

@section('content')
@php
    $isCompany = old('role') === 'company';
    $isFreelancer = old('role') === 'freelancer';
@endphp
<div class="auth-container">
    <div class="auth-box auth-box-wide">
        <h1>Register</h1>
        <form method="POST" action="{{ route('register') }}" id="register-form" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="name">Full Name <span class="required">*</span></label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required>
            </div>
            <div class="form-group">
                <label for="email">Email <span class="required">*</span></label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required>
            </div>
            <div class="form-group">
                <label for="role">I am a <span class="required">*</span></label>
                <select id="role" name="role">
                    <option value="user" @selected(old('role', 'user') === 'user')>Job Seeker</option>
                    <option value="freelancer" @selected(old('role') === 'freelancer')>Service Provider (Freelancer)</option>
                    <option value="company" @selected(old('role') === 'company')>Company</option>
                </select>
            </div>

            <div class="form-section" id="company_fields" style="display:{{ $isCompany ? 'block' : 'none' }}">
                <h2 class="form-section-title">Company details</h2>
                <div class="form-group">
                    <label for="company_name">Company Name <span class="required">*</span></label>
                    <input type="text" id="company_name" name="company_name" value="{{ old('company_name') }}" placeholder="e.g. Acme Inc">
                </div>
                <div class="form-group">
                    <label for="company_industry">Industry <span class="required">*</span></label>
                    <input type="text" id="company_industry" name="company_industry" value="{{ old('company_industry') }}" placeholder="e.g. Technology">
                </div>
                <div class="form-group">
                    <label for="company_description">Description</label>
                    <textarea id="company_description" name="company_description" rows="3">{{ old('company_description') }}</textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="company_website">Website</label>
                        <input type="url" id="company_website" name="company_website" value="{{ old('company_website') }}">
                    </div>
                    <div class="form-group">
                        <label for="company_phone">Phone</label>
                        <input type="text" id="company_phone" name="company_phone" value="{{ old('company_phone') }}">
                    </div>
                </div>
                <h2 class="form-section-title">Verification &amp; trust</h2>
                <div class="form-group">
                    <label for="company_business_registration">Business registration number</label>
                    <input type="text" id="company_business_registration" name="company_business_registration" value="{{ old('company_business_registration') }}">
                </div>
                <div class="form-group">
                    <label for="company_business_reg_file">Business registration document (upload)</label>
                    <input type="file" id="company_business_reg_file" name="company_business_reg_file" accept=".pdf,.jpg,.jpeg,.png,.gif">
                </div>
                <div class="form-group">
                    <label for="company_tax_id">Tax ID / VAT number</label>
                    <input type="text" id="company_tax_id" name="company_tax_id" value="{{ old('company_tax_id') }}">
                </div>
                <div class="form-group">
                    <label for="company_government_id_file">Government ID (upload)</label>
                    <input type="file" id="company_government_id_file" name="company_government_id_file" accept=".pdf,.jpg,.jpeg,.png,.gif">
                </div>
            </div>

            <div class="form-section" id="freelancer_fields" style="display:{{ $isFreelancer ? 'block' : 'none' }}">
                <h2 class="form-section-title">Professional profile</h2>
                <div class="form-group">
                    <label for="freelancer_type">Freelancer type <span class="required">*</span></label>
                    <select id="freelancer_type" name="freelancer_type">
                        <option value="general" @selected(old('freelancer_type') === 'general')>General Freelancer</option>
                        <option value="electrician" @selected(old('freelancer_type') === 'electrician')>Electrician</option>
                        <option value="plumber" @selected(old('freelancer_type') === 'plumber')>Plumber</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="freelancer_bio">Bio <span class="required">*</span></label>
                    <textarea id="freelancer_bio" name="freelancer_bio" rows="4">{{ old('freelancer_bio') }}</textarea>
                </div>
                <div class="form-group">
                    <label for="freelancer_skills">Skills <span class="required">*</span></label>
                    <input type="text" id="freelancer_skills" name="freelancer_skills" value="{{ old('freelancer_skills') }}">
                </div>
                <div class="form-group">
                    <label for="freelancer_hourly_rate">Hourly rate ($)</label>
                    <input type="number" id="freelancer_hourly_rate" name="freelancer_hourly_rate" step="0.01" min="0" value="{{ old('freelancer_hourly_rate') }}">
                </div>
                <h2 class="form-section-title">Verification &amp; certifications</h2>
                <div class="form-group">
                    <label for="freelancer_government_id_file">Government ID (upload)</label>
                    <input type="file" id="freelancer_government_id_file" name="freelancer_government_id_file" accept=".pdf,.jpg,.jpeg,.png,.gif">
                </div>
                <div class="form-group">
                    <label for="freelancer_qualifications">Licenses / certifications</label>
                    <textarea id="freelancer_qualifications" name="freelancer_qualifications" rows="3">{{ old('freelancer_qualifications') }}</textarea>
                </div>
                <div class="form-group">
                    <label for="freelancer_certification_file">Certification document (upload)</label>
                    <input type="file" id="freelancer_certification_file" name="freelancer_certification_file" accept=".pdf,.jpg,.jpeg,.png,.gif">
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password <span class="required">*</span></label>
                <input type="password" id="password" name="password" required minlength="6">
            </div>
            <div class="form-group">
                <label for="password_confirmation">Confirm Password <span class="required">*</span></label>
                <input type="password" id="password_confirmation" name="password_confirmation" required minlength="6">
            </div>
            <button type="submit" class="btn btn-primary">Register</button>
        </form>
        <p class="auth-link">Already have an account? <a href="{{ route('login') }}">Login</a></p>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    var role = document.getElementById('role');
    var companyFields = document.getElementById('company_fields');
    var freelancerFields = document.getElementById('freelancer_fields');
    var companyName = document.getElementById('company_name');
    var companyIndustry = document.getElementById('company_industry');
    var freelancerBio = document.getElementById('freelancer_bio');
    var freelancerSkills = document.getElementById('freelancer_skills');

    function toggleFields() {
        var v = role.value;
        if (v === 'company') {
            companyFields.style.display = 'block';
            freelancerFields.style.display = 'none';
            companyName.required = true;
            companyIndustry.required = true;
            freelancerBio.required = false;
            freelancerSkills.required = false;
        } else if (v === 'freelancer') {
            companyFields.style.display = 'none';
            freelancerFields.style.display = 'block';
            companyName.required = false;
            companyIndustry.required = false;
            freelancerBio.required = true;
            freelancerSkills.required = true;
        } else {
            companyFields.style.display = 'none';
            freelancerFields.style.display = 'none';
            companyName.required = false;
            companyIndustry.required = false;
            freelancerBio.required = false;
            freelancerSkills.required = false;
        }
    }

    role.addEventListener('change', toggleFields);
    toggleFields();
})();
</script>
@endpush
