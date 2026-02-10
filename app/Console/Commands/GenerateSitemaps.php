<?php

namespace App\Console\Commands;

use App\Models\CamModel;
use App\Models\Country;
use App\Models\Tag;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class GenerateSitemaps extends Command
{
    protected $signature = 'sitemap:generate
                            {--static : Generate static XML files in public directory}
                            {--warm-cache : Only warm the cache without generating files}';

    protected $description = 'Generate sitemaps and warm cache for all locales';

    protected const MODELS_PER_SITEMAP = 5000; // Reduced from 10000 for memory efficiency
    protected const CHUNK_SIZE = 500; // Process models in chunks

    protected function getSitemapLocales(): array
    {
        return config('locales.priority', [
            'en', 'es', 'fr', 'de', 'pt', 'it', 'nl', 'pl', 'ru',
            'ja', 'ko', 'zh', 'ar', 'tr', 'pt-BR', 'es-MX',
        ]);
    }

    public function handle(): int
    {
        $generateStatic = $this->option('static');

        $this->info('🗺️  Generating sitemaps...');
        $this->newLine();

        // Get counts
        $totalModels = DB::connection('cam')->table('cam_models')->count();
        $modelPages = max(1, ceil($totalModels / self::MODELS_PER_SITEMAP));
        $locales = $this->getSitemapLocales();

        $this->info("Found {$totalModels} models ({$modelPages} pages per locale)");
        $this->info("Processing " . count($locales) . " locales");
        $this->newLine();

        // Clear cache
        $this->clearSitemapCache();

        // Generate index
        $this->info('Generating sitemap index...');
        $this->generateIndex($generateStatic, $modelPages);

        // Generate static pages
        $this->info('Generating static pages sitemap...');
        $this->generateStaticPages($generateStatic);

        // Generate model sitemaps (memory-efficient)
        $this->info('Generating model sitemaps...');
        $this->generateAllModelSitemaps($generateStatic, $modelPages);

        // Generate tags sitemaps
        $this->info('Generating tag sitemaps...');
        $this->generateAllTagSitemaps($generateStatic);

        // Generate countries sitemaps
        $this->info('Generating country sitemaps...');
        $this->generateAllCountrySitemaps($generateStatic);

        // Generate niches sitemaps
        $this->info('Generating niche sitemaps...');
        $this->generateAllNichesSitemaps($generateStatic);

        $this->newLine();
        $this->info('✅ Sitemap generation complete!');

        if ($generateStatic) {
            $this->info('Static XML files written to: ' . public_path());
        }

        return 0;
    }

    protected function clearSitemapCache(): void
    {
        Cache::forget('sitemap:index');
        // Other cache keys will be overwritten
    }

    protected function generateIndex(bool $writeFile, int $modelPages): void
    {
        $locales = $this->getSitemapLocales();

        $filename = $writeFile ? public_path('sitemap.xml') : null;
        $handle = $filename ? fopen($filename, 'w') : null;

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        if ($handle) fwrite($handle, $xml);

        // Static
        $this->writeSitemapEntry($handle, url('/sitemap-static.xml'));

        // Models - English only for main pages
        for ($page = 1; $page <= $modelPages; $page++) {
            $this->writeSitemapEntry($handle, url("/sitemap-models-{$page}.xml"));
        }

        // Models - Other locales (only first few pages for non-English to save space)
        foreach ($locales as $locale) {
            if ($locale === 'en') continue;
            $maxPages = min(3, $modelPages); // Limit to first 3 pages for other locales
            for ($page = 1; $page <= $maxPages; $page++) {
                $this->writeSitemapEntry($handle, url("/sitemap-models-{$locale}-{$page}.xml"));
            }
        }

        // Tags
        $this->writeSitemapEntry($handle, url('/sitemap-tags.xml'));
        foreach ($locales as $locale) {
            if ($locale === 'en') continue;
            $this->writeSitemapEntry($handle, url("/sitemap-tags-{$locale}.xml"));
        }

        // Countries
        $this->writeSitemapEntry($handle, url('/sitemap-countries.xml'));
        foreach ($locales as $locale) {
            if ($locale === 'en') continue;
            $this->writeSitemapEntry($handle, url("/sitemap-countries-{$locale}.xml"));
        }

        // Niches
        $this->writeSitemapEntry($handle, url('/sitemap-niches.xml'));

        $xml = '</sitemapindex>';
        if ($handle) {
            fwrite($handle, $xml);
            fclose($handle);
        }
    }

    protected function writeSitemapEntry($handle, string $loc): void
    {
        $xml = "    <sitemap>\n";
        $xml .= "        <loc>" . htmlspecialchars($loc) . "</loc>\n";
        $xml .= "        <lastmod>" . now()->toW3cString() . "</lastmod>\n";
        $xml .= "    </sitemap>\n";

        if ($handle) fwrite($handle, $xml);
    }

    protected function generateStaticPages(bool $writeFile): void
    {
        $filename = $writeFile ? public_path('sitemap-static.xml') : null;
        $handle = $filename ? fopen($filename, 'w') : null;

        $this->writeUrlsetHeader($handle);

        // Homepage
        $this->writeUrl($handle, url('/'), '1.0', 'daily');

        // Tags index
        $this->writeUrl($handle, url('/tags'), '0.8', 'daily');

        // Countries index
        $this->writeUrl($handle, url('/countries'), '0.7', 'weekly');

        // Niches
        foreach (['girls', 'guys', 'couples', 'trans'] as $niche) {
            $this->writeUrl($handle, url("/{$niche}"), '0.9', 'hourly');
        }

        $this->writeUrlsetFooter($handle);
    }

    protected function generateAllModelSitemaps(bool $writeFile, int $modelPages): void
    {
        $locales = $this->getSitemapLocales();

        $bar = $this->output->createProgressBar($modelPages + count($locales) * min(3, $modelPages));
        $bar->start();

        // English sitemaps (all pages)
        for ($page = 1; $page <= $modelPages; $page++) {
            $this->generateModelSitemapStreaming($writeFile, $page, null);
            $bar->advance();
        }

        // Other locales (limited pages)
        foreach ($locales as $locale) {
            if ($locale === 'en') continue;
            $maxPages = min(3, $modelPages);
            for ($page = 1; $page <= $maxPages; $page++) {
                $this->generateModelSitemapStreaming($writeFile, $page, $locale);
                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine();
    }

    protected function generateModelSitemapStreaming(bool $writeFile, int $page, ?string $locale): void
    {
        $offset = ($page - 1) * self::MODELS_PER_SITEMAP;

        $filename = $writeFile
            ? public_path($locale ? "sitemap-models-{$locale}-{$page}.xml" : "sitemap-models-{$page}.xml")
            : null;
        $handle = $filename ? fopen($filename, 'w') : null;

        $this->writeUrlsetHeader($handle);

        // Use chunked query to avoid memory issues
        DB::connection('cam')
            ->table('cam_models')
            ->select(['username', 'updated_at', 'is_online'])
            ->orderByDesc('is_online')
            ->orderByDesc('viewers_count')
            ->offset($offset)
            ->limit(self::MODELS_PER_SITEMAP)
            ->chunk(self::CHUNK_SIZE, function ($models) use ($handle, $locale) {
                foreach ($models as $model) {
                    $loc = $locale
                        ? url("/{$locale}/model/{$model->username}")
                        : url("/model/{$model->username}");

                    $priority = $model->is_online ? '0.9' : '0.6';
                    $changefreq = $model->is_online ? 'hourly' : 'daily';
                    $lastmod = $model->updated_at ?? now()->toW3cString();

                    $this->writeUrl($handle, $loc, $priority, $changefreq, $lastmod);
                }
            });

        $this->writeUrlsetFooter($handle);
    }

    protected function generateAllTagSitemaps(bool $writeFile): void
    {
        $locales = $this->getSitemapLocales();

        $bar = $this->output->createProgressBar(count($locales));
        $bar->start();

        // English
        $this->generateTagSitemap($writeFile, null);
        $bar->advance();

        // Other locales
        foreach ($locales as $locale) {
            if ($locale === 'en') continue;
            $this->generateTagSitemap($writeFile, $locale);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }

    protected function generateTagSitemap(bool $writeFile, ?string $locale): void
    {
        $filename = $writeFile
            ? public_path($locale ? "sitemap-tags-{$locale}.xml" : 'sitemap-tags.xml')
            : null;
        $handle = $filename ? fopen($filename, 'w') : null;

        $this->writeUrlsetHeader($handle);

        // Tags index
        $indexUrl = $locale ? url("/{$locale}/tags") : url('/tags');
        $this->writeUrl($handle, $indexUrl, '0.8', 'daily');

        // Individual tags - load in chunks
        Tag::with(['translations' => function ($q) use ($locale) {
            if ($locale) $q->where('locale', $locale);
        }])->chunk(100, function ($tags) use ($handle, $locale) {
            foreach ($tags as $tag) {
                $slug = $tag->slug;
                if ($locale) {
                    $translation = $tag->translations->first();
                    $slug = $translation?->slug ?? $tag->slug;
                }

                $loc = $locale
                    ? url("/{$locale}/tags/{$slug}")
                    : url("/tags/{$tag->slug}");

                $this->writeUrl($handle, $loc, '0.7', 'daily');
            }
        });

        $this->writeUrlsetFooter($handle);
    }

    protected function generateAllCountrySitemaps(bool $writeFile): void
    {
        $locales = $this->getSitemapLocales();

        $bar = $this->output->createProgressBar(count($locales));
        $bar->start();

        // English
        $this->generateCountrySitemap($writeFile, null);
        $bar->advance();

        // Other locales
        foreach ($locales as $locale) {
            if ($locale === 'en') continue;
            $this->generateCountrySitemap($writeFile, $locale);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }

    protected function generateCountrySitemap(bool $writeFile, ?string $locale): void
    {
        $filename = $writeFile
            ? public_path($locale ? "sitemap-countries-{$locale}.xml" : 'sitemap-countries.xml')
            : null;
        $handle = $filename ? fopen($filename, 'w') : null;

        $this->writeUrlsetHeader($handle);

        // Countries index
        $indexUrl = $locale ? url("/{$locale}/countries") : url('/countries');
        $this->writeUrl($handle, $indexUrl, '0.7', 'weekly');

        // Individual countries
        Country::with(['translations' => function ($q) use ($locale) {
            if ($locale) $q->where('locale', $locale);
        }])->chunk(50, function ($countries) use ($handle, $locale) {
            foreach ($countries as $country) {
                $slug = $country->slug;
                if ($locale) {
                    $translation = $country->translations->first();
                    $slug = $translation?->slug ?? $country->slug;
                }

                $loc = $locale
                    ? url("/{$locale}/country/{$slug}")
                    : url("/country/{$country->slug}");

                $this->writeUrl($handle, $loc, '0.6', 'weekly');
            }
        });

        $this->writeUrlsetFooter($handle);
    }

    protected function generateAllNichesSitemaps(bool $writeFile): void
    {
        $filename = $writeFile ? public_path('sitemap-niches.xml') : null;
        $handle = $filename ? fopen($filename, 'w') : null;

        $this->writeUrlsetHeader($handle);

        $niches = ['girls', 'guys', 'couples', 'trans'];
        $topTags = Tag::orderByDesc('models_count')->limit(30)->pluck('slug');

        foreach ($niches as $niche) {
            // Niche main page
            $this->writeUrl($handle, url("/{$niche}"), '0.9', 'hourly');

            // Niche + tag combinations
            foreach ($topTags as $tagSlug) {
                $this->writeUrl($handle, url("/{$niche}/{$tagSlug}"), '0.7', 'daily');
            }
        }

        $this->writeUrlsetFooter($handle);
    }

    protected function writeUrlsetHeader($handle): void
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        if ($handle) fwrite($handle, $xml);
    }

    protected function writeUrlsetFooter($handle): void
    {
        if ($handle) {
            fwrite($handle, '</urlset>');
            fclose($handle);
        }
    }

    protected function writeUrl($handle, string $loc, string $priority = '0.5', string $changefreq = 'weekly', ?string $lastmod = null): void
    {
        $xml = "    <url>\n";
        $xml .= "        <loc>" . htmlspecialchars($loc) . "</loc>\n";
        if ($lastmod) {
            $xml .= "        <lastmod>" . htmlspecialchars($lastmod) . "</lastmod>\n";
        }
        $xml .= "        <changefreq>{$changefreq}</changefreq>\n";
        $xml .= "        <priority>{$priority}</priority>\n";
        $xml .= "    </url>\n";

        if ($handle) fwrite($handle, $xml);
    }
}
