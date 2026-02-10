<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\App;

class TipActionType extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'emoji',
        'category',
        'suggested_min_tokens',
        'suggested_max_tokens',
        'description',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'suggested_min_tokens' => 'integer',
        'suggested_max_tokens' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * Get translations for this action type
     */
    public function translations(): HasMany
    {
        return $this->hasMany(TipActionTranslation::class);
    }

    /**
     * Get model tip menus using this action
     */
    public function modelTipMenus(): HasMany
    {
        return $this->hasMany(ModelTipMenu::class);
    }

    /**
     * Get translated name for current locale
     */
    public function getTranslatedNameAttribute(): string
    {
        $locale = App::getLocale();
        
        if ($locale === 'en') {
            return $this->name;
        }

        $translation = $this->translations()
            ->where('locale', $locale)
            ->first();

        return $translation?->name ?? $this->name;
    }

    /**
     * Get translated description for current locale
     */
    public function getTranslatedDescriptionAttribute(): ?string
    {
        $locale = App::getLocale();
        
        if ($locale === 'en') {
            return $this->description;
        }

        $translation = $this->translations()
            ->where('locale', $locale)
            ->first();

        return $translation?->description ?? $this->description;
    }

    /**
     * Get display name with emoji
     */
    public function getDisplayNameAttribute(): string
    {
        $name = $this->translated_name;
        return $this->emoji ? "{$this->emoji} {$name}" : $name;
    }

    /**
     * Scope to active actions
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by category
     */
    public function scopeInCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Get all actions organized by category
     */
    public static function getByCategory(): array
    {
        return static::active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->groupBy('category')
            ->toArray();
    }
}
