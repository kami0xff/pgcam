<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Tag;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SitemapController extends Controller
{
    protected const CACHE_TTL = 86400;
    protected const TOP_POPULAR = 2500;
    protected const TOP_TAGS = 30;
    protected const TOP_COUNTRIES = 15;

    /**
     * Manually pinned models — always in sitemap + indexed, regardless of ranking.
     */
    public const PINNED_MODELS = [
        '893JPmafia',
        'Yura_yura_ch',
    ];

    protected function getSitemapLocales(): array
    {
        return config('locales.priority', [
            'en', 'es', 'fr', 'de', 'pt', 'it', 'nl', 'pl', 'ru',
            'ja', 'ko', 'zh', 'ar', 'tr', 'pt-BR', 'es-MX',
        ]);
    }

    /**
     * Single flat sitemap with a stable model list that does not rotate hourly.
     */
    public function index(): Response
    {
        $urls = Cache::remember('sitemap:flat', self::CACHE_TTL, function () {
            return array_merge(
                $this->staticUrls(),
                $this->stableModelUrls(),
                $this->topTagUrls(),
                $this->topCountryUrls(),
            );
        });

        return $this->xmlResponse($this->renderUrlset($urls));
    }

    /**
     * The stable set of model usernames used by both sitemap and noindex.
     * Top 500 by all-time favorites + all Japanese models + pinned models.
     */
    public static function getStableModelUsernames(): array
    {
        return Cache::remember('stable_model_usernames', 86400, function () {
            $deduped = "(SELECT DISTINCT ON (username) username, is_online, "
                . "viewers_count, rating, favorited_count, country "
                . "FROM cam_models "
                . "ORDER BY username, is_online DESC, viewers_count DESC NULLS LAST) AS deduped";

            $popular = DB::connection('cam')
                ->table(DB::raw($deduped))
                ->orderByRaw('favorited_count DESC NULLS LAST')
                ->limit(self::TOP_POPULAR)
                ->pluck('username');

            $japanese = DB::connection('cam')
                ->table(DB::raw($deduped))
                ->whereRaw("LOWER(country) IN ('japan', 'jp')")
                ->pluck('username');

            $pinned = collect(self::PINNED_MODELS);

            return $popular
                ->concat($japanese)
                ->concat($pinned)
                ->unique()
                ->values()
                ->all();
        });
    }

    protected function staticUrls(): array
    {
        $urls = [];

        $urls[] = $this->buildUrl(url('/'), '1.0', 'always', $this->buildLocaleAlternates('/'));

        foreach (['girls', 'men', 'couples', 'trans'] as $niche) {
            $urls[] = $this->buildUrl(url("/{$niche}"), '0.9', 'always', $this->buildLocaleAlternates("/{$niche}"));
        }

        $urls[] = $this->buildUrl(url('/tags'), '0.7', 'daily', $this->buildLocaleAlternates('/tags'));
        $urls[] = $this->buildUrl(url('/countries'), '0.6', 'weekly', $this->buildLocaleAlternates('/countries'));

        $urls[] = $this->buildUrl(url('/roulette'), '0.8', 'always', $this->buildLocaleAlternates('/roulette'));
        foreach (['girls', 'men', 'couples', 'trans'] as $cat) {
            $urls[] = $this->buildUrl(url("/roulette/{$cat}"), '0.7', 'always', $this->buildLocaleAlternates("/roulette/{$cat}"));
        }

        foreach (['about', 'contact', 'faq', 'good-causes', 'privacy', 'terms', 'dmca', '2257'] as $page) {
            $urls[] = $this->buildUrl(url("/{$page}"), '0.2', 'monthly');
        }

        return $urls;
    }

    protected function stableModelUrls(): array
    {
        $usernames = self::getStableModelUsernames();

        $deduped = "(SELECT DISTINCT ON (username) username, updated_at, is_online, "
            . "viewers_count, rating, favorited_count, country "
            . "FROM cam_models "
            . "ORDER BY username, is_online DESC, viewers_count DESC NULLS LAST) AS deduped";

        $models = DB::connection('cam')
            ->table(DB::raw($deduped))
            ->whereIn('username', $usernames)
            ->orderByRaw('favorited_count DESC NULLS LAST')
            ->get();

        $urls = [];

        foreach ($models as $model) {
            $urls[] = [
                'loc' => url("/model/{$model->username}"),
                'lastmod' => $model->updated_at
                    ? Carbon::parse($model->updated_at)->toW3cString()
                    : now()->toW3cString(),
                'changefreq' => $model->is_online ? 'always' : 'daily',
                'priority' => $this->calculateModelPriority($model),
                'alternates' => $this->buildModelAlternates($model),
            ];
        }

        return $urls;
    }

    protected function topTagUrls(): array
    {
        $tags = Tag::with('translations')
            ->orderByDesc('models_count')
            ->limit(self::TOP_TAGS)
            ->get();

        $urls = [];

        foreach ($tags as $tag) {
            if (empty($tag->slug)) continue;

            $urls[] = [
                'loc' => url("/tag/{$tag->slug}"),
                'lastmod' => $tag->updated_at?->toW3cString() ?? now()->toW3cString(),
                'changefreq' => 'daily',
                'priority' => '0.6',
                'alternates' => $this->buildTagAlternates($tag),
            ];
        }

        return $urls;
    }

    protected function topCountryUrls(): array
    {
        $countries = Country::with('translations')
            ->orderByDesc('models_count')
            ->limit(self::TOP_COUNTRIES)
            ->get();

        $urls = [];

        foreach ($countries as $country) {
            if (empty($country->slug)) continue;

            $urls[] = [
                'loc' => url("/country/{$country->slug}"),
                'lastmod' => $country->updated_at?->toW3cString() ?? now()->toW3cString(),
                'changefreq' => 'daily',
                'priority' => '0.5',
                'alternates' => $this->buildCountryAlternates($country),
            ];
        }

        return $urls;
    }

    protected function calculateModelPriority(object $model): string
    {
        $priority = 0.5;

        if ($model->is_online) $priority += 0.3;
        if ($model->viewers_count > 1000) $priority += 0.1;
        elseif ($model->viewers_count > 500) $priority += 0.05;
        if ($model->rating >= 4.5) $priority += 0.05;
        if (($model->favorited_count ?? 0) > 50000) $priority += 0.05;

        return number_format(min($priority, 1.0), 1);
    }

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

    protected function buildModelAlternates(object $model): array
    {
        $locales = $this->getSitemapLocales();
        $alternates = [];

        foreach ($locales as $locale) {
            $alternates[$locale] = $locale === 'en'
                ? url("/model/{$model->username}")
                : url("/{$locale}/model/{$model->username}");
        }

        $alternates['x-default'] = url("/model/{$model->username}");
        return $alternates;
    }

    protected function buildTagAlternates(Tag $tag): array
    {
        $locales = $this->getSitemapLocales();
        $alternates = [];

        foreach ($locales as $locale) {
            if ($locale === 'en') {
                $alternates[$locale] = url("/tag/{$tag->slug}");
            } else {
                $translation = $tag->translations->where('locale', $locale)->first();
                $slug = $translation?->slug ?? $tag->slug;
                $alternates[$locale] = url("/{$locale}/tag/{$slug}");
            }
        }

        $alternates['x-default'] = url("/tag/{$tag->slug}");
        return $alternates;
    }

    protected function buildCountryAlternates(Country $country): array
    {
        $locales = $this->getSitemapLocales();
        $alternates = [];

        foreach ($locales as $locale) {
            if ($locale === 'en') {
                $alternates[$locale] = url("/country/{$country->slug}");
            } else {
                $translation = $country->translations->where('locale', $locale)->first();
                $slug = $translation?->slug ?? $country->slug;
                $alternates[$locale] = url("/{$locale}/country/{$slug}");
            }
        }

        $alternates['x-default'] = url("/country/{$country->slug}");
        return $alternates;
    }

    protected function xmlResponse(string $content): Response
    {
        return response($content, 200)
            ->header('Content-Type', 'application/xml; charset=utf-8')
            ->header('Cache-Control', 'public, max-age=3600, s-maxage=3600');
    }

    protected function renderUrlset(array $urls): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<?xml-stylesheet type="text/xsl" href="/sitemap.xsl"?>' . "\n";
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
