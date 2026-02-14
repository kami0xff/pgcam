<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;

class HomepageSection extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'type',
        'tags',
        'sort_order',
        'is_active',
        'max_models',
        'min_models',
    ];

    protected $casts = [
        'tags' => 'array',
        'is_active' => 'boolean',
        'max_models' => 'integer',
        'min_models' => 'integer',
    ];

    /**
     * Get translations for this section
     */
    public function translations(): HasMany
    {
        return $this->hasMany(HomepageSectionTranslation::class);
    }

    /**
     * Get translation for a specific locale
     */
    public function translation(?string $locale = null): ?HomepageSectionTranslation
    {
        $locale = $locale ?? App::getLocale();

        return $this->translations->firstWhere('locale', $locale)
            ?? $this->translations->firstWhere('locale', 'en');
    }

    /**
     * Get the localized title (falls back to base title)
     */
    public function getLocalizedTitleAttribute(): string
    {
        return $this->translation()?->title ?? $this->title;
    }

    /**
     * Scope: Active sections ordered by sort_order
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    /**
     * Scope: By type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get all active sections with their translations (cached)
     */
    public static function getActiveSections(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember('homepage_sections_active', 300, function () {
            return self::active()->with('translations')->get();
        });
    }

    /**
     * Clear the cached sections
     */
    public static function clearCache(): void
    {
        Cache::forget('homepage_sections_active');
    }

    /**
     * Boot method to clear cache on changes
     */
    protected static function booted(): void
    {
        static::saved(fn () => self::clearCache());
        static::deleted(fn () => self::clearCache());
    }
}
