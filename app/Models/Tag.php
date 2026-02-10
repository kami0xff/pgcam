<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\App;

class Tag extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'category',
        'models_count',
        'is_featured',
        'sort_order',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
    ];

    /**
     * Get translations for this tag
     */
    public function translations(): HasMany
    {
        return $this->hasMany(TagTranslation::class);
    }

    /**
     * Get cam models with this tag
     * Handles both simple tags ("young") and niche/tag format ("girls/young")
     */
    public function models()
    {
        return CamModel::on('camguru_pgsql')
            ->whereRaw("tags::text ILIKE ?", ['%"' . $this->name . '"%']);
    }

    /**
     * Get cam models with this tag in a specific niche
     */
    public function modelsInNiche(string $niche)
    {
        $fullTag = $niche . '/' . $this->name;
        return CamModel::on('camguru_pgsql')
            ->whereRaw("tags::text ILIKE ?", ['%"' . $fullTag . '"%']);
    }

    /**
     * Get translation for a specific locale
     */
    public function translation(?string $locale = null): ?TagTranslation
    {
        $locale = $locale ?? App::getLocale();
        
        return $this->translations->firstWhere('locale', $locale)
            ?? $this->translations->firstWhere('locale', 'en');
    }

    /**
     * Get localized name
     */
    public function getLocalizedNameAttribute(): string
    {
        return $this->translation()?->name ?? $this->name;
    }

    /**
     * Get localized slug
     */
    public function getLocalizedSlugAttribute(): string
    {
        return $this->translation()?->slug ?? $this->slug;
    }

    /**
     * Get localized URL - links to /girls/{tag} by default
     */
    public function getUrlAttribute(): string
    {
        $locale = App::getLocale();
        $slug = $this->localized_slug;
        
        // Link to girls niche by default (most common)
        if ($locale === 'en') {
            return route('niche.tag', ['niche' => 'girls', 'tagSlug' => $slug]);
        }
        
        return route('niche.tag.localized', ['locale' => $locale, 'niche' => 'girls', 'tagSlug' => $slug]);
    }

    /**
     * Get URL for all niches (tag page across all genders)
     */
    public function getAllNichesUrlAttribute(): string
    {
        $locale = App::getLocale();
        $slug = $this->localized_slug;
        
        if ($locale === 'en') {
            return route('tags.show', $slug);
        }
        
        return route('tags.show.localized', ['locale' => $locale, 'slug' => $slug]);
    }

    /**
     * Scope: Featured tags
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true)->orderBy('sort_order');
    }

    /**
     * Scope: With models
     */
    public function scopeWithModels($query)
    {
        return $query->where('models_count', '>', 0);
    }

    /**
     * Scope: By category
     */
    public function scopeInCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Find by localized slug
     */
    public static function findBySlug(string $slug, ?string $locale = null): ?self
    {
        $locale = $locale ?? App::getLocale();
        
        // First try the translations table
        $translation = TagTranslation::where('locale', $locale)
            ->where('slug', $slug)
            ->first();
        
        if ($translation) {
            return $translation->tag;
        }
        
        // Fall back to main slug
        return self::where('slug', $slug)->first();
    }

    /**
     * Get all URLs for hreflang
     */
    public function getHreflangUrls(): array
    {
        $urls = [];
        
        foreach ($this->translations as $translation) {
            $urls[$translation->locale] = $translation->locale === 'en'
                ? route('tags.show', $translation->slug)
                : route('tags.show.localized', ['locale' => $translation->locale, 'slug' => $translation->slug]);
        }
        
        // Add x-default
        $urls['x-default'] = $urls['en'] ?? route('tags.show', $this->slug);
        
        return $urls;
    }
}
