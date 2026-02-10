@extends('layouts.pornguru')

@section('title', 'Sign Up')

@section('content')
<div class="container page-section">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1 class="auth-title">Create Account</h1>
                <p class="auth-subtitle">Join to save your favorite models</p>
            </div>

            <form method="POST" action="{{ route('register') }}" class="auth-form">
                @csrf

                <div class="auth-field">
                    <label for="name" class="auth-label">Username</label>
                    <input type="text" 
                           name="name" 
                           id="name" 
                           value="{{ old('name') }}"
                           class="auth-input @error('name') auth-input-error @enderror"
                           required 
                           autofocus>
                    @error('name')
                        <span class="auth-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="auth-field">
                    <label for="email" class="auth-label">Email</label>
                    <input type="email" 
                           name="email" 
                           id="email" 
                           value="{{ old('email') }}"
                           class="auth-input @error('email') auth-input-error @enderror"
                           required>
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

                <div class="auth-field">
                    <label for="password_confirmation" class="auth-label">Confirm Password</label>
                    <input type="password" 
                           name="password_confirmation" 
                           id="password_confirmation" 
                           class="auth-input"
                           required>
                </div>

                <button type="submit" class="auth-submit">
                    Create Account
                </button>
            </form>

            <div class="auth-footer">
                <p>Already have an account? <a href="{{ route('login') }}">Sign in</a></p>
            </div>
        </div>
    </div>
</div>
@endsection
