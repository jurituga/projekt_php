@extends('layouts.app')

@section('title', 'Review: '.$user->name)

@section('content')
<div class="container">
    <x-dashboard-nav role="admin" />

    <p><a href="{{ route('admin.users.index') }}">&larr; Back to Users</a></p>

    <h1>Review: {{ $user->name }}</h1>

    <table class="data-table" style="margin-bottom:1.5rem;">
        <tr><th>Email</th><td>{{ $user->email }}</td></tr>
        <tr><th>Role</th><td>{{ $user->role->value }}</td></tr>
        <tr><th>Status</th><td><span class="status status-{{ $user->status->value }}">{{ $user->status->value }}</span></td></tr>
        <tr><th>Registered</th><td>{{ $user->created_at->format('M j, Y H:i') }}</td></tr>
    </table>

    @if($user->role === \App\Enums\UserRole::Company && $user->company)
        @php $company = $user->company; @endphp
        <div class="card">
            <h2>Company details</h2>
            <table class="data-table">
                <tr><th>Company Name</th><td>{{ $company->company_name ?: '—' }}</td></tr>
                <tr><th>Industry</th><td>{{ $company->industry ?: '—' }}</td></tr>
                <tr><th>Description</th><td>{{ $company->description ?: '—' }}</td></tr>
                <tr>
                    <th>Website</th>
                    <td>
                        @if($company->website)
                            <a href="{{ $company->website }}" target="_blank" rel="noopener">{{ $company->website }}</a>
                        @else
                            —
                        @endif
                    </td>
                </tr>
                <tr><th>Phone</th><td>{{ $company->phone ?: '—' }}</td></tr>
            </table>

            <h3 style="margin-top:1.25rem;">Verification &amp; trust</h3>
            <table class="data-table">
                <tr><th>Business Registration No.</th><td>{{ $company->business_registration_number ?: '—' }}</td></tr>
                <tr><th>Tax ID / VAT</th><td>{{ $company->tax_id_vat ?: '—' }}</td></tr>
                <tr><th>Government ID reference</th><td>{{ $company->government_id_ref ?: '—' }}</td></tr>
                <tr>
                    <th>Government ID (upload)</th>
                    <td><x-admin-document :filename="$company->government_id_path" type="gov" /></td>
                </tr>
            </table>
        </div>
    @endif

    @if($user->role === \App\Enums\UserRole::Freelancer && $user->freelancerProfile)
        @php $profile = $user->freelancerProfile; @endphp
        <div class="card">
            <h2>Freelancer profile</h2>
            <table class="data-table">
                <tr><th>Type</th><td>{{ ucfirst($profile->freelancer_type ?? 'general') }}</td></tr>
                <tr><th>Bio</th><td>{!! nl2br(e($profile->bio ?: '—')) !!}</td></tr>
                <tr><th>Skills</th><td>{{ $profile->skills ?: '—' }}</td></tr>
                <tr><th>Hourly Rate</th><td>{{ $profile->hourly_rate ? '$'.number_format($profile->hourly_rate, 2) : '—' }}</td></tr>
            </table>

            <h3 style="margin-top:1.25rem;">Verification &amp; certifications</h3>
            <table class="data-table">
                <tr><th>Government ID reference</th><td>{{ $profile->government_id_ref ?: '—' }}</td></tr>
                <tr>
                    <th>Government ID (upload)</th>
                    <td><x-admin-document :filename="$profile->government_id_path" type="gov" /></td>
                </tr>
                <tr><th>Qualifications</th><td>{!! nl2br(e($profile->qualifications ?: '—')) !!}</td></tr>
                <tr>
                    <th>Certification / license doc</th>
                    <td><x-admin-document :filename="$profile->certification_path" type="cert" /></td>
                </tr>
            </table>
        </div>
    @endif

    <div style="margin-top:1.5rem;" class="actions">
        @if($user->status === \App\Enums\UserStatus::Pending)
            <form method="POST" action="{{ route('admin.users.action') }}" style="display:inline">
                @csrf
                <input type="hidden" name="user_id" value="{{ $user->id }}">
                <input type="hidden" name="action" value="activate">
                <button type="submit" class="btn btn-primary">Approve</button>
            </form>
        @elseif($user->status === \App\Enums\UserStatus::Blocked)
            <form method="POST" action="{{ route('admin.users.action') }}" style="display:inline">
                @csrf
                <input type="hidden" name="user_id" value="{{ $user->id }}">
                <input type="hidden" name="action" value="activate">
                <button type="submit" class="btn btn-primary">Activate</button>
            </form>
        @else
            <form method="POST" action="{{ route('admin.users.action') }}" style="display:inline">
                @csrf
                <input type="hidden" name="user_id" value="{{ $user->id }}">
                <input type="hidden" name="action" value="block">
                <button type="submit" class="btn btn-secondary">Block</button>
            </form>
        @endif
        <form method="POST" action="{{ route('admin.users.action') }}" style="display:inline" onsubmit="return confirm('Delete this user and all related data?');">
            @csrf
            <input type="hidden" name="user_id" value="{{ $user->id }}">
            <input type="hidden" name="action" value="delete">
            <button type="submit" class="btn btn-danger">Delete</button>
        </form>
    </div>
</div>
@endsection
