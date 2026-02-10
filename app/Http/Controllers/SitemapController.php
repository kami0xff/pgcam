<?php

namespace App\Http\Controllers;

use App\Models\CamModel;
use App\Models\Country;
use App\Models\Tag;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SitemapController extends Controller
{
    protected const CACHE_TTL = 3600; // 1 hour
    protected const MODELS_PER_SITEMAP = 10000;

    /**
     * Get priority locales for sitemaps (not all 50+, focus on high-traffic)
     */
    protected function getSitemapLocales(): array
    {
        return config('locales.priority', [
            'en', 'es', 'fr', 'de', 'pt', 'it', 'nl', 'pl', 'ru',
            'ja', 'ko', 'zh', 'ar', 'tr', 'pt-BR', 'es-MX',
        ]);
    }

    /**
     * Main sitemap index - lists all sub-sitemaps
     */
    public function index(): Response
    {
        $sitemaps = Cache::remember('sitemap:index', self::CACHE_TTL, function () {
            $items = [];
            $locales = $this->getSitemapLocales();

            // Static pages sitemap
            $items[] = [
                'loc' => url('/sitemap-static.xml'),
                'lastmod' => now()->toW3cString(),
            ];

            // Models sitemaps (paginated, per locale)
            $totalModels = CamModel::count();
            $modelPages = max(1, ceil($totalModels / self::MODELS_PER_SITEMAP));

            // Get most recent model update for lastmod
            $lastModelUpdate = CamModel::max('updated_at') ?? now();

            for ($page = 1; $page <= $modelPages; $page++) {
                $items[] = [
                    'loc' => url("/sitemap-models-{$page}.xml"),
                    'lastmod' => $lastModelUpdate,
                ];
            }

            // Localized model sitemaps
            foreach ($locales as $locale) {
                if ($locale === 'en') continue;
                for ($page = 1; $page <= $modelPages; $page++) {
                    $items[] = [
                        'loc' => url("/sitemap-models-{$locale}-{$page}.xml"),
                        'lastmod' => $lastModelUpdate,
                    ];
                }
            }

            // Tags sitemaps
            $lastTagUpdate = Tag::max('updated_at') ?? now();
            $items[] = [
                'loc' => url('/sitemap-tags.xml'),
                'lastmod' => $lastTagUpdate,
            ];
            foreach ($locales as $locale) {
                if ($locale === 'en') continue;
                $items[] = [
                    'loc' => url("/sitemap-tags-{$locale}.xml"),
                    'lastmod' => $lastTagUpdate,
                ];
            }

            // Countries sitemaps
            $lastCountryUpdate = Country::max('updated_at') ?? now();
            $items[] = [
                'loc' => url('/sitemap-countries.xml'),
                'lastmod' => $lastCountryUpdate,
            ];
            foreach ($locales as $locale) {
                if ($locale === 'en') continue;
                $items[] = [
                    'loc' => url("/sitemap-countries-{$locale}.xml"),
                    'lastmod' => $lastCountryUpdate,
                ];
            }

            // Niches sitemaps
            $items[] = [
                'loc' => url('/sitemap-niches.xml'),
                'lastmod' => now()->toW3cString(),
            ];
            foreach ($locales as $locale) {
                if ($locale === 'en') continue;
                $items[] = [
                    'loc' => url("/sitemap-niches-{$locale}.xml"),
                    'lastmod' => now()->toW3cString(),
                ];
            }

            // Image sitemap for SEO (model thumbnails)
            $items[] = [
                'loc' => url('/sitemap-images.xml'),
                'lastmod' => $lastModelUpdate,
            ];

            return $items;
        });

        return $this->xmlResponse($this->renderSitemapIndex($sitemaps));
    }

    /**
     * Static pages sitemap
     */
    public function staticPages(): Response
    {
        $urls = [];

        // Homepage - highest priority
        $urls[] = $this->buildUrl(url('/'), '1.0', 'always', $this->buildLocaleAlternates('/'));

        // Niche landing pages - very high priority (live content)
        foreach (['girls', 'guys', 'couples', 'trans'] as $niche) {
            $urls[] = $this->buildUrl(url("/{$niche}"), '0.95', 'always', $this->buildLocaleAlternates("/{$niche}"));
        }

        // Tags index
        $urls[] = $this->buildUrl(route('tags.index'), '0.8', 'daily', $this->buildLocaleAlternates('/tags'));

        // Countries index
        $urls[] = $this->buildUrl(route('countries.index'), '0.7', 'weekly', $this->buildLocaleAlternates('/countries'));

        return $this->xmlResponse($this->renderUrlset($urls));
    }

    /**
     * Models sitemap (paginated) - SEO optimized
     */
    public function models(int $page = 1, ?string $locale = null): Response
    {
        $cacheKey = "sitemap:models:{$locale}:{$page}";

        $urls = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($page, $locale) {
            $offset = ($page - 1) * self::MODELS_PER_SITEMAP;

            // Order by engagement metrics for better crawl priority
            $models = CamModel::select([
                    'id', 'username', 'updated_at', 'is_online', 
                    'viewers_count', 'rating', 'followers_count'
                ])
                ->orderByRaw('is_online DESC')
                ->orderByRaw('viewers_count DESC NULLS LAST')
                ->orderByRaw('followers_count DESC NULLS LAST')
                ->orderByRaw('rating DESC NULLS LAST')
                ->offset($offset)
                ->limit(self::MODELS_PER_SITEMAP)
                ->get();

            $urls = [];

            foreach ($models as $model) {
                $loc = $locale && $locale !== 'en'
                    ? url("/{$locale}/model/{$model->username}")
                    : route('cam-models.show', $model);

                // Calculate priority based on engagement
                $priority = $this->calculateModelPriority($model);

                // Change frequency based on status
                $changefreq = $model->is_online ? 'always' : 'daily';

                $urls[] = [
                    'loc' => $loc,
                    'lastmod' => $model->updated_at?->toW3cString() ?? now()->toW3cString(),
                    'changefreq' => $changefreq,
                    'priority' => $priority,
                    'alternates' => $this->buildModelAlternates($model),
                ];
            }

            return $urls;
        });

        return $this->xmlResponse($this->renderUrlset($urls));
    }

    /**
     * Calculate SEO priority based on model engagement
     */
    protected function calculateModelPriority(CamModel $model): string
    {
        $priority = 0.5; // Base priority

        // Online models get higher priority
        if ($model->is_online) {
            $priority += 0.3;
        }

        // High viewer count
        if ($model->viewers_count > 1000) {
            $priority += 0.1;
        } elseif ($model->viewers_count > 500) {
            $priority += 0.05;
        }

        // High rating
        if ($model->rating >= 4.5) {
            $priority += 0.05;
        }

        // Popular models (followers)
        if ($model->followers_count > 50000) {
            $priority += 0.05;
        }

        return number_format(min($priority, 1.0), 1);
    }

    /**
     * Image sitemap for models (helps with Google Images SEO)
     */
    public function images(): Response
    {
        $cacheKey = 'sitemap:images';

        $content = Cache::remember($cacheKey, self::CACHE_TTL, function () {
            // Get top models with images
            $models = CamModel::select(['username', 'preview_url', 'avatar_url', 'updated_at'])
                ->whereNotNull('preview_url')
                ->orderByRaw('is_online DESC')
                ->orderByRaw('viewers_count DESC NULLS LAST')
                ->limit(5000)
                ->get();

            $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" ';
            $xml .= 'xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

            foreach ($models as $model) {
                $xml .= "    <url>\n";
                $xml .= "        <loc>" . htmlspecialchars(route('cam-models.show', $model->username)) . "</loc>\n";
                
                // Main preview image
                if ($model->preview_url) {
                    $xml .= "        <image:image>\n";
                    $xml .= "            <image:loc>" . htmlspecialchars($model->preview_url) . "</image:loc>\n";
                    $xml .= "            <image:title>" . htmlspecialchars("{$model->username} - Live Cam") . "</image:title>\n";
                    $xml .= "            <image:caption>" . htmlspecialchars("Watch {$model->username} live webcam show") . "</image:caption>\n";
                    $xml .= "        </image:image>\n";
                }

                // Avatar image
                if ($model->avatar_url && $model->avatar_url !== $model->preview_url) {
                    $xml .= "        <image:image>\n";
                    $xml .= "            <image:loc>" . htmlspecialchars($model->avatar_url) . "</image:loc>\n";
                    $xml .= "            <image:title>" . htmlspecialchars("{$model->username} profile photo") . "</image:title>\n";
                    $xml .= "        </image:image>\n";
                }

                $xml .= "    </url>\n";
            }

            $xml .= '</urlset>';

            return $xml;
        });

        return $this->xmlResponse($content);
    }

    /**
     * Tags sitemap
     */
    public function tags(?string $locale = null): Response
    {
        $cacheKey = "sitemap:tags:{$locale}";

        $urls = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($locale) {
            // Order tags by model count for better prioritization
            $tags = Tag::with('translations')
                ->orderByDesc('models_count')
                ->get();
            
            $urls = [];
            $maxCount = $tags->max('models_count') ?: 1;

            foreach ($tags as $tag) {
                $slug = $tag->slug;
                if ($locale && $locale !== 'en') {
                    $translation = $tag->translations->where('locale', $locale)->first();
                    $slug = $translation?->slug ?? $tag->slug;
                }

                $loc = $locale && $locale !== 'en'
                    ? url("/{$locale}/tags/{$slug}")
                    : route('tags.show', $tag->slug);

                // Calculate priority based on popularity
                $priority = 0.5 + (0.4 * ($tag->models_count / $maxCount));

                $urls[] = [
                    'loc' => $loc,
                    'lastmod' => $tag->updated_at?->toW3cString() ?? now()->toW3cString(),
                    'changefreq' => 'daily',
                    'priority' => number_format($priority, 1),
                    'alternates' => $this->buildTagAlternates($tag),
                ];
            }

            // Tags index
            $indexUrl = $locale && $locale !== 'en'
                ? url("/{$locale}/tags")
                : route('tags.index');

            $urls[] = [
                'loc' => $indexUrl,
                'lastmod' => now()->toW3cString(),
                'changefreq' => 'daily',
                'priority' => '0.8',
                'alternates' => $this->buildLocaleAlternates('/tags'),
            ];

            return $urls;
        });

        return $this->xmlResponse($this->renderUrlset($urls));
    }

    /**
     * Countries sitemap
     */
    public function countries(?string $locale = null): Response
    {
        $cacheKey = "sitemap:countries:{$locale}";

        $urls = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($locale) {
            $countries = Country::with('translations')
                ->orderByDesc('models_count')
                ->get();
            
            $urls = [];
            $maxCount = $countries->max('models_count') ?: 1;

            foreach ($countries as $country) {
                $slug = $country->slug;
                if ($locale && $locale !== 'en') {
                    $translation = $country->translations->where('locale', $locale)->first();
                    $slug = $translation?->slug ?? $country->slug;
                }

                $loc = $locale && $locale !== 'en'
                    ? url("/{$locale}/countries/{$slug}")
                    : route('countries.show', $country->slug);

                // Priority based on number of models
                $priority = 0.4 + (0.4 * ($country->models_count / $maxCount));

                $urls[] = [
                    'loc' => $loc,
                    'lastmod' => $country->updated_at?->toW3cString() ?? now()->toW3cString(),
                    'changefreq' => 'daily',
                    'priority' => number_format($priority, 1),
                    'alternates' => $this->buildCountryAlternates($country),
                ];
            }

            // Countries index
            $indexUrl = $locale && $locale !== 'en'
                ? url("/{$locale}/countries")
                : route('countries.index');

            $urls[] = [
                'loc' => $indexUrl,
                'lastmod' => now()->toW3cString(),
                'changefreq' => 'weekly',
                'priority' => '0.7',
                'alternates' => $this->buildLocaleAlternates('/countries'),
            ];

            return $urls;
        });

        return $this->xmlResponse($this->renderUrlset($urls));
    }

    /**
     * Niches sitemap - includes niche + tag combinations
     */
    public function niches(?string $locale = null): Response
    {
        $cacheKey = "sitemap:niches:{$locale}";

        $urls = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($locale) {
            $niches = ['girls', 'guys', 'couples', 'trans'];
            $urls = [];

            foreach ($niches as $niche) {
                $loc = $locale && $locale !== 'en'
                    ? url("/{$locale}/{$niche}")
                    : url("/{$niche}");

                $urls[] = [
                    'loc' => $loc,
                    'lastmod' => now()->toW3cString(),
                    'changefreq' => 'always',
                    'priority' => '0.95',
                    'alternates' => $this->buildNicheAlternates($niche),
                ];

                // Top tags for this niche - with hreflang
                $topTags = Tag::with('translations')
                    ->orderByDesc('models_count')
                    ->limit(50)
                    ->get();

                foreach ($topTags as $tag) {
                    $tagSlug = $tag->slug;
                    if ($locale && $locale !== 'en') {
                        $translation = $tag->translations->where('locale', $locale)->first();
                        $tagSlug = $translation?->slug ?? $tag->slug;
                    }

                    $tagLoc = $locale && $locale !== 'en'
                        ? url("/{$locale}/{$niche}/{$tagSlug}")
                        : url("/{$niche}/{$tagSlug}");

                    $urls[] = [
                        'loc' => $tagLoc,
                        'lastmod' => now()->toW3cString(),
                        'changefreq' => 'hourly',
                        'priority' => '0.8',
                        'alternates' => $this->buildNicheTagAlternates($niche, $tag),
                    ];
                }
            }

            return $urls;
        });

        return $this->xmlResponse($this->renderUrlset($urls));
    }

    /**
     * Build URL entry
     */
    protected function buildUrl(string $loc, string $priority = '0.5', string $changefreq = 'weekly', ?array $alternates = null): array
    {
        $url = [
            'loc' => $loc,
            'lastmod' => now()->toW3cString(),
            'changefreq' => $changefreq,
            'priority' => $priority,
        ];

        if ($alternates) {
            $url['alternates'] = $alternates;
        }

        return $url;
    }

    /**
     * Build locale alternates for a path
     */
    protected function buildLocaleAlternates(string $path): array
    {
        $locales = $this->getSitemapLocales();
        $alternates = [];

        foreach ($locales as $locale) {
            $alternates[$locale] = $locale === 'en'
                ? url($path)
                : url("/{$locale}{$path}");
        }

        $alternates['x-default'] = url($path);

        return $alternates;
    }

    /**
     * Build alternates for a model
     */
    protected function buildModelAlternates(CamModel $model): array
    {
        $locales = $this->getSitemapLocales();
        $alternates = [];

        foreach ($locales as $locale) {
            $alternates[$locale] = $locale === 'en'
                ? route('cam-models.show', $model)
                : url("/{$locale}/model/{$model->username}");
        }

        $alternates['x-default'] = route('cam-models.show', $model);

        return $alternates;
    }

    /**
     * Build alternates for a tag (with translated slugs)
     */
    protected function buildTagAlternates(Tag $tag): array
    {
        $locales = $this->getSitemapLocales();
        $alternates = [];

        foreach ($locales as $locale) {
            if ($locale === 'en') {
                $alternates[$locale] = route('tags.show', $tag->slug);
            } else {
                $translation = $tag->translations->where('locale', $locale)->first();
                $slug = $translation?->slug ?? $tag->slug;
                $alternates[$locale] = url("/{$locale}/tags/{$slug}");
            }
        }

        $alternates['x-default'] = route('tags.show', $tag->slug);

        return $alternates;
    }

    /**
     * Build alternates for a country (with translated slugs)
     */
    protected function buildCountryAlternates(Country $country): array
    {
        $locales = $this->getSitemapLocales();
        $alternates = [];

        foreach ($locales as $locale) {
            if ($locale === 'en') {
                $alternates[$locale] = route('countries.show', $country->slug);
            } else {
                $translation = $country->translations->where('locale', $locale)->first();
                $slug = $translation?->slug ?? $country->slug;
                $alternates[$locale] = url("/{$locale}/countries/{$slug}");
            }
        }

        $alternates['x-default'] = route('countries.show', $country->slug);

        return $alternates;
    }

    /**
     * Build alternates for a niche
     */
    protected function buildNicheAlternates(string $niche): array
    {
        $locales = $this->getSitemapLocales();
        $alternates = [];

        foreach ($locales as $locale) {
            $alternates[$locale] = $locale === 'en'
                ? url("/{$niche}")
                : url("/{$locale}/{$niche}");
        }

        $alternates['x-default'] = url("/{$niche}");

        return $alternates;
    }

    /**
     * Build alternates for niche + tag combination
     */
    protected function buildNicheTagAlternates(string $niche, Tag $tag): array
    {
        $locales = $this->getSitemapLocales();
        $alternates = [];

        foreach ($locales as $locale) {
            if ($locale === 'en') {
                $alternates[$locale] = url("/{$niche}/{$tag->slug}");
            } else {
                $translation = $tag->translations->where('locale', $locale)->first();
                $slug = $translation?->slug ?? $tag->slug;
                $alternates[$locale] = url("/{$locale}/{$niche}/{$slug}");
            }
        }

        $alternates['x-default'] = url("/{$niche}/{$tag->slug}");

        return $alternates;
    }

    /**
     * Return XML response
     */
    protected function xmlResponse(string $content): Response
    {
        return response($content, 200)
            ->header('Content-Type', 'application/xml; charset=utf-8')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    /**
     * Render sitemap index XML
     */
    protected function renderSitemapIndex(array $sitemaps): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        foreach ($sitemaps as $sitemap) {
            $xml .= "    <sitemap>\n";
            $xml .= "        <loc>" . htmlspecialchars($sitemap['loc']) . "</loc>\n";
            $lastmod = $sitemap['lastmod'] instanceof \DateTimeInterface 
                ? $sitemap['lastmod']->toW3cString() 
                : $sitemap['lastmod'];
            $xml .= "        <lastmod>" . htmlspecialchars($lastmod) . "</lastmod>\n";
            $xml .= "    </sitemap>\n";
        }
        
        $xml .= '</sitemapindex>';
        
        return $xml;
    }

    /**
     * Render urlset XML
     */
    protected function renderUrlset(array $urls): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";
        
        foreach ($urls as $url) {
            $xml .= "    <url>\n";
            $xml .= "        <loc>" . htmlspecialchars($url['loc']) . "</loc>\n";
            
            if (isset($url['lastmod'])) {
                $xml .= "        <lastmod>" . htmlspecialchars($url['lastmod']) . "</lastmod>\n";
            }
            if (isset($url['changefreq'])) {
                $xml .= "        <changefreq>" . htmlspecialchars($url['changefreq']) . "</changefreq>\n";
            }
            if (isset($url['priority'])) {
                $xml .= "        <priority>" . htmlspecialchars($url['priority']) . "</priority>\n";
            }
            if (isset($url['alternates'])) {
                foreach ($url['alternates'] as $locale => $href) {
                    $xml .= '        <xhtml:link rel="alternate" hreflang="' . htmlspecialchars($locale) . '" href="' . htmlspecialchars($href) . '" />' . "\n";
                }
            }
            
            $xml .= "    </url>\n";
        }
        
        $xml .= '</urlset>';
        
        return $xml;
    }
}
