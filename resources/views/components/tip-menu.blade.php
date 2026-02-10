@props(['modelId', 'modelName' => 'this model', 'affiliateUrl' => null])

@php
    use App\Models\ModelTipMenu;
    use App\Models\TipActionType;
    
    $tipMenu = ModelTipMenu::getForModel($modelId);
    
    // If no custom menu, show default actions with suggested prices
    if ($tipMenu->isEmpty()) {
        $tipMenu = TipActionType::active()
            ->orderBy('sort_order')
            ->get()
            ->map(function ($action) {
                return (object) [
                    'actionType' => $action,
                    'token_price' => (int) round(($action->suggested_min_tokens + $action->suggested_max_tokens) / 2 / 5) * 5,
                ];
            });
    }
    
    // Group by category
    $groupedMenu = $tipMenu->groupBy(fn($item) => $item->actionType->category);
    
    $categoryLabels = [
        'tease' => __('Tease'),
        'dance' => __('Dance'),
        'interactive' => __('Interactive'),
        'special' => __('Special Shows'),
        'outfit' => __('Outfit'),
        'general' => __('General'),
    ];
    
    $categoryIcons = [
        'tease' => '😈',
        'dance' => '💃',
        'interactive' => '🎮',
        'special' => '✨',
        'outfit' => '👙',
        'general' => '⭐',
    ];
@endphp

<div class="tip-menu-card">
    {{-- Header --}}
    <div class="tip-menu-header">
        <div class="tip-menu-header-content">
            <h3 class="tip-menu-title">
                <span class="tip-menu-emoji">🎁</span>
                {{ __('Tip Menu') }}
            </h3>
            <p class="tip-menu-subtitle">{{ __('Make her day special!') }}</p>
        </div>
        <div class="tip-menu-badge">
            <span>{{ $tipMenu->count() }} {{ __('Actions') }}</span>
        </div>
    </div>

    {{-- Content --}}
    <div class="tip-menu-content">
        @foreach ($groupedMenu as $category => $items)
            <div class="tip-menu-category">
                <h4 class="tip-menu-category-title">
                    <span class="tip-menu-category-icon">{{ $categoryIcons[$category] ?? '⭐' }}</span>
                    {{ $categoryLabels[$category] ?? ucfirst($category) }}
                </h4>
                <div class="tip-menu-items">
                    @foreach ($items as $item)
                        <div class="tip-menu-item">
                            <div class="tip-menu-item-info">
                                <span class="tip-menu-item-emoji">{{ $item->actionType->emoji }}</span>
                                <span class="tip-menu-item-name">{{ $item->actionType->translated_name }}</span>
                            </div>
                            <div class="tip-menu-item-price">
                                <span class="tip-menu-item-tokens">{{ $item->token_price }}</span>
                                <svg class="tip-menu-coin" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1.41 16.09V20h-2.67v-1.93c-1.71-.36-3.16-1.46-3.27-3.4h1.96c.1 1.05.82 1.87 2.65 1.87 1.96 0 2.4-.98 2.4-1.59 0-.83-.44-1.61-2.67-2.14-2.48-.6-4.18-1.62-4.18-3.67 0-1.72 1.39-2.84 3.11-3.21V4h2.67v1.95c1.86.45 2.79 1.86 2.85 3.39H14.3c-.05-1.11-.64-1.87-2.22-1.87-1.5 0-2.4.68-2.4 1.64 0 .84.65 1.39 2.67 1.91s4.18 1.39 4.18 3.91c-.01 1.83-1.38 2.83-3.12 3.16z"/>
                                </svg>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    {{-- CTA --}}
    <div class="tip-menu-cta">
        <div class="tip-menu-cta-content">
            <p class="tip-menu-cta-title">💎 {{ __('Want to tip') }} {{ $modelName }}?</p>
            <p class="tip-menu-cta-text">{{ __('Register and get tokens to tip and request private shows!') }}</p>
        </div>
        
        @if ($affiliateUrl)
            <a href="{{ $affiliateUrl }}" target="_blank" rel="noopener" class="tip-menu-cta-btn">
                {{ __('Get Tokens') }}
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </a>
        @endif
    </div>
</div>

<style>
/* Tip Menu Card - PornGuru Theme */
.tip-menu-card {
    background: var(--bg-card, #0a0a0a);
    border: 1px solid var(--border, #27272a);
    border-radius: 12px;
    overflow: hidden;
}

.tip-menu-header {
    background: linear-gradient(135deg, rgba(0, 191, 255, 0.15) 0%, rgba(0, 191, 255, 0.05) 100%);
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--border, #27272a);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.tip-menu-header-content {
    flex: 1;
}

.tip-menu-title {
    font-size: 1rem;
    font-weight: 600;
    color: var(--text-primary, #fff);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.tip-menu-emoji {
    font-size: 1.1rem;
}

.tip-menu-subtitle {
    font-size: 0.75rem;
    color: var(--text-muted, #71717a);
    margin: 0.25rem 0 0 0;
}

.tip-menu-badge {
    background: rgba(0, 0, 0, 0.3);
    padding: 0.35rem 0.75rem;
    border-radius: 20px;
    border: 1px solid var(--border, #27272a);
}

.tip-menu-badge span {
    font-size: 0.7rem;
    font-weight: 500;
    color: var(--text-secondary, #a1a1aa);
}

.tip-menu-content {
    padding: 1rem 1.25rem;
    max-height: 400px;
    overflow-y: auto;
}

.tip-menu-content::-webkit-scrollbar {
    width: 4px;
}

.tip-menu-content::-webkit-scrollbar-track {
    background: transparent;
}

.tip-menu-content::-webkit-scrollbar-thumb {
    background: var(--border, #27272a);
    border-radius: 2px;
}

.tip-menu-category {
    margin-bottom: 1.25rem;
}

.tip-menu-category:last-child {
    margin-bottom: 0;
}

.tip-menu-category-title {
    font-size: 0.7rem;
    font-weight: 600;
    color: var(--accent, #00BFFF);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin: 0 0 0.5rem 0;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid var(--border, #27272a);
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.tip-menu-category-icon {
    font-size: 0.9rem;
}

.tip-menu-items {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
}

.tip-menu-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.5rem 0.6rem;
    background: rgba(255,255,255,0.02);
    border: 1px solid transparent;
    border-radius: 6px;
    transition: all 0.15s ease;
}

.tip-menu-item:hover {
    background: rgba(255,255,255,0.05);
    border-color: rgba(0, 191, 255, 0.3);
}

.tip-menu-item-info {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex: 1;
    min-width: 0;
}

.tip-menu-item-emoji {
    font-size: 1rem;
    opacity: 0.8;
}

.tip-menu-item-name {
    font-size: 0.8rem;
    color: var(--text-secondary, #a1a1aa);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.tip-menu-item:hover .tip-menu-item-name {
    color: var(--text-primary, #fff);
}

.tip-menu-item-price {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    background: var(--bg-elevated, #0f0f0f);
    padding: 0.3rem 0.5rem;
    border-radius: 4px;
    border: 1px solid var(--border, #27272a);
    flex-shrink: 0;
}

.tip-menu-item:hover .tip-menu-item-price {
    border-color: rgba(0, 191, 255, 0.3);
}

.tip-menu-item-tokens {
    font-size: 0.75rem;
    font-weight: 600;
    color: #fbbf24;
}

.tip-menu-coin {
    width: 12px;
    height: 12px;
    color: #fbbf24;
}

/* CTA */
.tip-menu-cta {
    padding: 1rem 1.25rem;
    border-top: 1px solid var(--border, #27272a);
    background: rgba(0,0,0,0.2);
}

.tip-menu-cta-content {
    margin-bottom: 0.75rem;
}

.tip-menu-cta-title {
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--text-primary, #fff);
    margin: 0 0 0.25rem 0;
}

.tip-menu-cta-text {
    font-size: 0.75rem;
    color: var(--text-muted, #71717a);
    margin: 0;
    line-height: 1.4;
}

.tip-menu-cta-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    width: 100%;
    padding: 0.75rem 1.25rem;
    background: linear-gradient(135deg, var(--accent, #00BFFF) 0%, #0099cc 100%);
    color: #000;
    font-size: 0.85rem;
    font-weight: 600;
    border-radius: 8px;
    text-decoration: none;
    transition: all 0.2s ease;
}

.tip-menu-cta-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(0, 191, 255, 0.3);
}

.tip-menu-cta-btn svg {
    width: 16px;
    height: 16px;
    transition: transform 0.2s ease;
}

.tip-menu-cta-btn:hover svg {
    transform: translateX(3px);
}

/* Responsive */
@media (max-width: 640px) {
    .tip-menu-header,
    .tip-menu-content,
    .tip-menu-cta {
        padding: 0.75rem;
    }
    
    .tip-menu-content {
        max-height: 280px;
    }
    
    .tip-menu-title {
        font-size: 0.9rem;
    }
    
    .tip-menu-badge {
        padding: 0.25rem 0.5rem;
    }
    
    .tip-menu-badge span {
        font-size: 0.65rem;
    }
    
    .tip-menu-category-title {
        font-size: 0.65rem;
    }
    
    .tip-menu-item {
        padding: 0.4rem 0.5rem;
    }
    
    .tip-menu-item-emoji {
        font-size: 0.9rem;
    }
    
    .tip-menu-item-name {
        font-size: 0.75rem;
    }
    
    .tip-menu-item-price {
        padding: 0.2rem 0.4rem;
    }
    
    .tip-menu-item-tokens {
        font-size: 0.7rem;
    }
    
    .tip-menu-coin {
        width: 10px;
        height: 10px;
    }
    
    .tip-menu-cta-title {
        font-size: 0.85rem;
    }
    
    .tip-menu-cta-text {
        font-size: 0.7rem;
    }
    
    .tip-menu-cta-btn {
        padding: 0.625rem 1rem;
        font-size: 0.8rem;
    }
}
</style>
