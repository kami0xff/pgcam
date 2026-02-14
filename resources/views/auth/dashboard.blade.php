@extends('layouts.pornguru')

@section('title', 'Dashboard')

@section('content')
<div class="container page-section">
    <div class="dashboard-header">
        <div class="dashboard-user">
            <div class="dashboard-avatar">{{ $user->initials() }}</div>
            <div class="dashboard-info">
                <h1 class="dashboard-name">{{ $user->name }}</h1>
                <p class="dashboard-email">{{ $user->email }}</p>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="dashboard-logout">Sign Out</button>
        </form>
    </div>

    <div class="dashboard-stats">
        <div class="dashboard-stat">
            <span class="dashboard-stat-value">{{ $favorites->count() }}</span>
            <span class="dashboard-stat-label">Favorites</span>
        </div>
        <div class="dashboard-stat dashboard-stat-online">
            <span class="dashboard-stat-value">{{ $onlineCount }}</span>
            <span class="dashboard-stat-label">Online Now</span>
        </div>
    </div>

    <section class="dashboard-section">
        <h2 class="dashboard-section-title">Your Favorites</h2>

        @if($favorites->isEmpty())
            <div class="dashboard-empty">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                </svg>
                <h3>No favorites yet</h3>
                <p>Click the heart icon on models to add them to your favorites</p>
                <a href="{{ localized_route('home') }}" class="btn btn-primary">Browse Models</a>
            </div>
        @else
            <div class="models-grid">
                @foreach($favorites as $model)
                    <x-pornguru.model-card :model="$model" />
                @endforeach
            </div>
        @endif
    </section>
</div>
@endsection
