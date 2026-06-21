@extends('layouts.app')

@section('title', 'Reset Password')

@section('content')
<div class="auth-container">
    <div class="auth-box">
        <h1>Reset Password</h1>

        @if($success ?? false)
            <div class="alert alert-success">Your password has been reset successfully.</div>
            <p style="margin-top:1rem"><a href="{{ route('login') }}" class="btn btn-primary" style="width:100%">Log in with new password</a></p>
        @elseif(!($validToken ?? false))
            @if(!empty($error))
                <div class="alert alert-error">{{ $error }}</div>
            @endif
            <p style="margin-top:1rem"><a href="{{ route('password.request') }}" class="btn btn-ghost btn-sm">Request a new link</a></p>
        @else
            <p style="color:var(--text-sub);font-size:.88rem;margin-bottom:1.25rem">Hi {{ $userName }}, enter your new password below.</p>
            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <div class="form-group">
                    <label for="password">New password</label>
                    <input type="password" id="password" name="password" required autofocus minlength="6">
                </div>
                <div class="form-group">
                    <label for="password_confirmation">Confirm password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required minlength="6">
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%">Reset Password</button>
            </form>
        @endif
    </div>
</div>
@endsection
