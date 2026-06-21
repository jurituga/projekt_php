@extends('layouts.app')

@section('title', 'Forgot Password')

@section('content')
<div class="auth-container">
    <div class="auth-box">
        <h1>Forgot Password</h1>
        <p style="color:var(--text-sub);font-size:.88rem;margin-bottom:1.25rem">Enter the email address linked to your account and we'll generate a reset link.</p>

        @if($success ?? false)
            <div class="alert alert-success">If an account with that email exists, a password reset link has been generated.</div>
            @if(!empty($resetLink))
                <div class="card" style="margin-top:1rem;padding:1rem">
                    <p style="font-size:.8rem;font-weight:600;color:var(--text-sub);margin-bottom:.5rem">Reset link (local dev — no mail server):</p>
                    <a href="{{ $resetLink }}" style="word-break:break-all;font-size:.82rem">{{ $resetLink }}</a>
                </div>
            @endif
            <p style="margin-top:1.25rem"><a href="{{ route('login') }}" class="btn btn-ghost btn-sm">&larr; Back to login</a></p>
        @else
            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <div class="form-group">
                    <label for="email">Email address</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="you@example.com">
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%">Send Reset Link</button>
            </form>
            <p class="auth-link" style="margin-top:1rem"><a href="{{ route('login') }}">&larr; Back to login</a></p>
        @endif
    </div>
</div>
@endsection
