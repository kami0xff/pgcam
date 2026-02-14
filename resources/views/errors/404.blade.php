@extends('layouts.pornguru')

@section('title', '404 - Page Not Found')

@section('content')
<div class="container page-section">
    <div class="empty-state" style="padding: 6rem 0;">
        <div class="empty-state-icon" style="width: 6rem; height: 6rem; margin: 0 auto 1.5rem; color: var(--accent);">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
            </svg>
        </div>
        <h1 class="empty-state-title" style="font-size: 3rem; margin-bottom: 1rem; color: var(--text-primary);">404</h1>
        <h2 style="font-size: 1.5rem; margin-bottom: 1rem; color: var(--text-primary); font-weight: 600;">Page Not Found</h2>
        <p class="empty-state-text" style="font-size: 1.1rem; max-width: 600px; margin: 0 auto 2rem;">
            The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.
        </p>
        <a href="{{ localized_route('home') }}" class="btn btn-primary">
            Back to Homepage
        </a>
    </div>

    @php
        // Fetch random online models for suggestions
        try {
            $suggestedModels = \App\Models\CamModel::online()
                ->inRandomOrder()
                ->limit(12)
                ->get();
        } catch (\Exception $e) {
            $suggestedModels = collect();
        }
    @endphp

    @if($suggestedModels->isNotEmpty())
        <section class="section" style="border-top: 1px solid var(--border);">
            <div class="section-header">
                <div class="section-icon section-icon-fire">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path fill-rule="evenodd" d="M12.963 2.286a.75.75 0 00-1.071-.136 9.742 9.742 0 00-3.539 6.177 7.547 7.547 0 01-1.705-1.715.75.75 0 00-1.152-.082A9 9 0 1015.68 4.534a7.46 7.46 0 01-2.717-2.248zM15.75 14.25a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" clip-rule="evenodd" />
                    </svg>
                </div>
                <h2 class="section-title">Check Out These Live Models</h2>
            </div>
            
            <x-pornguru.models-grid :models="$suggestedModels" />
        </section>
    @endif
</div>
@endsection
