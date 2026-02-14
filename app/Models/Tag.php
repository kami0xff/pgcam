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
        return CamModel::on('cam')
            ->whereRaw("tags::text ILIKE ?", ['%"' . $this->name . '"%']);
    }

    /**
     * Get cam models with this tag in a specific niche
     */
    public function modelsInNiche(string $niche)
    {
        $fullTag = $niche . '/' . $this->name;
        return CamModel::on('cam')
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
     * Get a cached mapping of English slug → localized slug for the current locale.
     * Used by tag-link.blade.php and other views to generate localized URLs
     * without N+1 queries.
     *
     * @return array<string, string>  [english_slug => localized_slug]
     */
    public static function getSlugMap(?string $locale = null): array
    {
        $locale = $locale ?? App::getLocale();

        if ($locale === 'en') {
            return []; // No translation needed for English
        }

        return cache()->remember("tag_slug_map:{$locale}", 3600, function () use ($locale) {
            return TagTranslation::where('tag_translations.locale', $locale)
                ->join('tags', 'tags.id', '=', 'tag_translations.tag_id')
                ->select('tags.slug as en_slug', 'tag_translations.slug as loc_slug')
                ->get()
                ->pluck('loc_slug', 'en_slug')
                ->toArray();
        });
    }

    /**
     * Translate a single English tag slug to its localized slug.
     * Returns the original slug if no translation exists.
     */
    public static function localizeSlug(string $englishSlug, ?string $locale = null): string
    {
        $map = self::getSlugMap($locale);
        return $map[$englishSlug] ?? $englishSlug;
    }

    /**
     * Get a cached mapping of English slug → localized name for the current locale.
     *
     * @return array<string, string>  [english_slug => localized_name]
     */
    public static function getNameMap(?string $locale = null): array
    {
        $locale = $locale ?? App::getLocale();

        if ($locale === 'en') {
            return [];
        }

        return cache()->remember("tag_name_map:{$locale}", 3600, function () use ($locale) {
            return TagTranslation::where('tag_translations.locale', $locale)
                ->join('tags', 'tags.id', '=', 'tag_translations.tag_id')
                ->select('tags.slug as en_slug', 'tag_translations.name as loc_name')
                ->get()
                ->pluck('loc_name', 'en_slug')
                ->toArray();
        });
    }

    /**
     * Translate a single English tag slug to its localized display name.
     * Returns a formatted version of the slug if no translation exists.
     */
    public static function localizeName(string $englishSlug, ?string $locale = null): string
    {
        $map = self::getNameMap($locale);
        return $map[$englishSlug] ?? ucwords(str_replace(['-', '_'], ' ', $englishSlug));
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
