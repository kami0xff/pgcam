<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\App;

class Country extends Model
{
    protected $fillable = [
        'code',
        'slug',
        'name',
        'flag',
        'models_count',
    ];

    /**
     * Get flag emoji (use stored value or generate from country code)
     */
    public function getFlagAttribute($value): string
    {
        if ($value) {
            return $value;
        }
        
        // Generate flag emoji from country code
        return \App\Helpers\FlagHelper::getFlag($this->code ?? '');
    }

    /**
     * Get translations for this country
     */
    public function translations(): HasMany
    {
        return $this->hasMany(CountryTranslation::class);
    }

    /**
     * Get cam models from this country
     */
    public function models()
    {
        return CamModel::on('cam')
            ->where('country', $this->code)
            ->orWhere('country', $this->name);
    }

    /**
     * Get translation for a specific locale
     */
    public function translation(?string $locale = null): ?CountryTranslation
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
     * Get localized URL
     */
    public function getUrlAttribute(): string
    {
        $locale = App::getLocale();
        $slug = $this->localized_slug;

        if (empty($slug)) {
            return route('countries.index');
        }
        
        if ($locale === 'en') {
            return route('countries.show', $slug);
        }
        
        return route('countries.show.localized', ['locale' => $locale, 'slug' => $slug]);
    }

    /**
     * Scope: With models
     */
    public function scopeWithModels($query)
    {
        return $query->where('models_count', '>', 0);
    }

    /**
     * Find by code
     */
    public static function findByCode(string $code): ?self
    {
        return self::where('code', strtoupper($code))->first();
    }

    /**
     * Find by localized slug
     */
    public static function findBySlug(string $slug, ?string $locale = null): ?self
    {
        $locale = $locale ?? App::getLocale();
        
        $translation = CountryTranslation::where('locale', $locale)
            ->where('slug', $slug)
            ->first();
        
        if ($translation) {
            return $translation->country;
        }
        
        return self::where('slug', $slug)->first();
    }

    /**
     * Get a cached mapping of English slug → localized slug for the current locale.
     *
     * @return array<string, string>  [english_slug => localized_slug]
     */
    public static function getSlugMap(?string $locale = null): array
    {
        $locale = $locale ?? App::getLocale();

        if ($locale === 'en') {
            return [];
        }

        return cache()->remember("country_slug_map:{$locale}", 3600, function () use ($locale) {
            return CountryTranslation::where('country_translations.locale', $locale)
                ->join('countries', 'countries.id', '=', 'country_translations.country_id')
                ->select('countries.slug as en_slug', 'country_translations.slug as loc_slug')
                ->get()
                ->pluck('loc_slug', 'en_slug')
                ->toArray();
        });
    }

    /**
     * Translate a single English country slug to its localized slug.
     */
    public static function localizeSlug(string $englishSlug, ?string $locale = null): string
    {
        $map = self::getSlugMap($locale);
        return $map[$englishSlug] ?? $englishSlug;
    }

    /**
     * Get all URLs for hreflang
     */
    public function getHreflangUrls(): array
    {
        $urls = [];
        
        foreach ($this->translations as $translation) {
            $urls[$translation->locale] = $translation->locale === 'en'
                ? route('countries.show', $translation->slug)
                : route('countries.show.localized', ['locale' => $translation->locale, 'slug' => $translation->slug]);
        }
        
        $urls['x-default'] = $urls['en'] ?? route('countries.show', $this->slug);
        
        return $urls;
    }
}
