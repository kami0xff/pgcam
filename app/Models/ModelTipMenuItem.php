<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\App;

class ModelTipMenuItem extends Model
{
    protected $fillable = [
        'model_id',
        'action_name',
        'token_price',
        'emoji',
        'sort_order',
        'is_active',
        'last_seen_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_seen_at' => 'datetime',
    ];

    /**
     * Get translations for this tip menu item
     */
    public function translations(): HasMany
    {
        return $this->hasMany(ModelTipMenuTranslation::class);
    }

    /**
     * Get translation for a specific locale
     */
    public function translation(?string $locale = null): ?ModelTipMenuTranslation
    {
        $locale = $locale ?? App::getLocale();
        return $this->translations()->where('locale', $locale)->first();
    }

    /**
     * Get the localized action name
     */
    public function getLocalizedNameAttribute(): string
    {
        $translation = $this->translation();
        return $translation?->action_name ?? $this->action_name;
    }

    /**
     * Scope to active items
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by model
     */
    public function scopeForModel($query, string $modelId)
    {
        return $query->where('model_id', $modelId);
    }

    /**
     * Get all tip menu items for a model
     */
    public static function getForModel(string $modelId): \Illuminate\Database\Eloquent\Collection
    {
        return static::forModel($modelId)
            ->active()
            ->orderBy('sort_order')
            ->orderBy('token_price')
            ->get();
    }
}
