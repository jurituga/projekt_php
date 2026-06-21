@extends('layouts.app')

@section('title', 'Manage Users')

@section('content')
<div class="container">
    <x-dashboard-nav role="admin" />

    <h1>Manage Users</h1>

    @if($pendingCount > 0)
        <div class="alert alert-success" style="margin-bottom:1rem;">
            <strong>{{ $pendingCount }} registration(s) pending approval.</strong> Approve companies and freelancers so they can log in.
        </div>
    @endif

    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Joined</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $u)
                <tr>
                    <td>{{ $u->id }}</td>
                    <td>{{ $u->name }}</td>
                    <td>{{ $u->email }}</td>
                    <td>{{ $u->role->value }}</td>
                    <td><span class="status status-{{ $u->status->value }}">{{ $u->status->value }}</span></td>
                    <td>{{ $u->created_at->format('M j, Y') }}</td>
                    <td>
                        @if(in_array($u->role, [\App\Enums\UserRole::Company, \App\Enums\UserRole::Freelancer], true))
                            <a href="{{ route('admin.users.review', $u) }}" class="btn btn-small btn-secondary">Review</a>
                        @endif
                        @if($u->status === \App\Enums\UserStatus::Pending)
                            <form method="POST" action="{{ route('admin.users.action') }}" style="display:inline">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $u->id }}">
                                <input type="hidden" name="action" value="activate">
                                <button type="submit" class="btn btn-small">Approve</button>
                            </form>
                        @elseif($u->status === \App\Enums\UserStatus::Blocked)
                            <form method="POST" action="{{ route('admin.users.action') }}" style="display:inline">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $u->id }}">
                                <input type="hidden" name="action" value="activate">
                                <button type="submit" class="btn btn-small">Activate</button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('admin.users.action') }}" style="display:inline">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $u->id }}">
                                <input type="hidden" name="action" value="block">
                                <button type="submit" class="btn btn-small">Block</button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('admin.users.action') }}" style="display:inline" onsubmit="return confirm('Delete this user and all related data?');">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ $u->id }}">
                            <input type="hidden" name="action" value="delete">
                            <button type="submit" class="btn btn-small btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
