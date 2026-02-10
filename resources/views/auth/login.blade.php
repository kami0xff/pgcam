@extends('layouts.pornguru')

@section('title', 'Login')

@section('content')
<div class="container page-section">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1 class="auth-title">Welcome Back</h1>
                <p class="auth-subtitle">Sign in to access your favorites</p>
            </div>

            <form method="POST" action="{{ route('login') }}" class="auth-form">
                @csrf

                <div class="auth-field">
                    <label for="email" class="auth-label">Email</label>
                    <input type="email" 
                           name="email" 
                           id="email" 
                           value="{{ old('email') }}"
                           class="auth-input @error('email') auth-input-error @enderror"
                           required 
                           autofocus>
                    @error('email')
                        <span class="auth-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="auth-field">
                    <label for="password" class="auth-label">Password</label>
                    <input type="password" 
                           name="password" 
                           id="password" 
                           class="auth-input @error('password') auth-input-error @enderror"
                           required>
                    @error('password')
                        <span class="auth-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="auth-remember">
                    <label class="auth-checkbox">
                        <input type="checkbox" name="remember">
                        <span>Remember me</span>
                    </label>
                </div>

                <button type="submit" class="auth-submit">
                    Sign In
                </button>
            </form>

            <div class="auth-footer">
                <p>Don't have an account? <a href="{{ route('register') }}">Sign up</a></p>
            </div>
        </div>
    </div>
</div>
@endsection
