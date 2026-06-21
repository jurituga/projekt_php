@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="auth-container">
    <div class="auth-box">
        <h1>Login</h1>
        @if($pending ?? false)
            <div class="alert alert-success">Registration successful. Your account is pending admin approval. You will be able to log in once an administrator approves your request.</div>
        @endif
        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group checkbox" style="display:flex;align-items:center;justify-content:space-between">
                <label><input type="checkbox" name="admin_login" value="1" @checked(old('admin_login'))> Admin login</label>
                <a href="{{ route('password.request') }}" style="font-size:.82rem">Forgot password?</a>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
        <p class="auth-link">Don't have an account? <a href="{{ route('register') }}">Register</a></p>
    </div>
</div>
@endsection
