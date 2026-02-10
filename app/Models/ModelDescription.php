<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\App;

class ModelDescription extends Model
{
    protected $fillable = [
        'model_id',
        'short_description',
        'long_description',
        'personality_traits',
        'specialties',
        'source',
        'is_approved',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'personality_traits' => 'array',
    ];

    /**
     * Get translations for this description
     */
    public function translations(): HasMany
    {
        return $this->hasMany(ModelDescriptionTranslation::class);
    }

    /**
     * Get translation for a specific locale
     */
    public function translation(?string $locale = null): ?ModelDescriptionTranslation
    {
        $locale = $locale ?? App::getLocale();
        
        if ($locale === 'en') {
            return null; // English is in the base model
        }
        
        return $this->translations()->where('locale', $locale)->first();
    }

    /**
     * Get description for a model in current locale
     */
    public static function getForModel(string $modelId, ?string $locale = null): ?array
    {
        $locale = $locale ?? App::getLocale();
        
        $description = static::where('model_id', $modelId)->first();
        
        if (!$description) {
            return null;
        }

        // Return English content from base model
        if ($locale === 'en') {
            return [
                'short_description' => $description->short_description,
                'long_description' => $description->long_description,
                'specialties' => $description->specialties,
                'personality_traits' => $description->traits_array,
                'source' => $description->source,
            ];
        }

        // Try to get translation
        $translation = $description->translation($locale);
        
        if ($translation) {
            return [
                'short_description' => $translation->short_description ?: $description->short_description,
                'long_description' => $translation->long_description ?: $description->long_description,
                'specialties' => $translation->specialties ?: $description->specialties,
                'personality_traits' => $description->traits_array,
                'source' => $description->source,
            ];
        }

        // Fall back to English
        return [
            'short_description' => $description->short_description,
            'long_description' => $description->long_description,
            'specialties' => $description->specialties,
            'personality_traits' => $description->traits_array,
            'source' => $description->source,
        ];
    }

    /**
     * Check if model has a description
     */
    public static function hasDescription(string $modelId): bool
    {
        return static::where('model_id', $modelId)->exists();
    }

    /**
     * Check if model has translation for a locale
     */
    public function hasTranslation(string $locale): bool
    {
        if ($locale === 'en') {
            return true; // English is always in base model
        }
        return $this->translations()->where('locale', $locale)->exists();
    }

    /**
     * Get all locales with translations
     */
    public function getAvailableLocalesAttribute(): array
    {
        $locales = ['en']; // English is always available
        $translatedLocales = $this->translations()->pluck('locale')->toArray();
        return array_merge($locales, $translatedLocales);
    }

    /**
     * Get personality traits as array
     */
    public function getTraitsArrayAttribute(): array
    {
        $traits = $this->personality_traits;
        
        if (is_array($traits)) {
            return $traits;
        }
        
        if (is_string($traits) && !empty($traits)) {
            $decoded = json_decode($traits, true);
            return is_array($decoded) ? $decoded : [];
        }
        
        return [];
    }

    /**
     * Scope to approved descriptions
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    /**
     * Scope by source
     */
    public function scopeBySource($query, string $source)
    {
        return $query->where('source', $source);
    }

    /**
     * Scope to descriptions without a specific translation
     */
    public function scopeWithoutTranslation($query, string $locale)
    {
        return $query->whereDoesntHave('translations', function ($q) use ($locale) {
            $q->where('locale', $locale);
        });
    }
}
