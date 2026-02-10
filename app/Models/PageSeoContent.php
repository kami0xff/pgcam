<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;

class PageSeoContent extends Model
{
    protected $table = 'page_seo_content';

    protected $fillable = [
        'page_key',
        'locale',
        'title',
        'content',
        'keywords',
        'position',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get SEO content for a specific page
     */
    public static function forPage(string $pageKey, ?string $locale = null): ?self
    {
        $locale = $locale ?? App::getLocale();
        $cacheKey = "page_seo:{$pageKey}:{$locale}";

        return Cache::remember($cacheKey, 3600, function () use ($pageKey, $locale) {
            // Try exact locale first
            $content = self::where('page_key', $pageKey)
                ->where('locale', $locale)
                ->where('is_active', true)
                ->first();

            // Fall back to English
            if (!$content && $locale !== 'en') {
                $content = self::where('page_key', $pageKey)
                    ->where('locale', 'en')
                    ->where('is_active', true)
                    ->first();
            }

            return $content;
        });
    }

    /**
     * Get top positioned content
     */
    public static function topContent(string $pageKey, ?string $locale = null): ?self
    {
        $content = self::forPage($pageKey, $locale);
        return $content?->position === 'top' ? $content : null;
    }

    /**
     * Get bottom positioned content
     */
    public static function bottomContent(string $pageKey, ?string $locale = null): ?self
    {
        $content = self::forPage($pageKey, $locale);
        return $content?->position === 'bottom' ? $content : null;
    }

    /**
     * Get keywords as array
     */
    public function getKeywordsArrayAttribute(): array
    {
        if (empty($this->keywords)) {
            return [];
        }
        return array_map('trim', explode(',', $this->keywords));
    }

    /**
     * Clear cache for a page
     */
    public static function clearCache(string $pageKey): void
    {
        $locales = ['en', 'es', 'fr', 'de', 'pt', 'it', 'nl', 'pl', 'ru', 'ja', 'ko', 'zh'];
        foreach ($locales as $locale) {
            Cache::forget("page_seo:{$pageKey}:{$locale}");
        }
    }
}
