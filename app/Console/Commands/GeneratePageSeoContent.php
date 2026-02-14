<?php

namespace App\Console\Commands;

use App\Models\Country;
use App\Models\PageSeoContent;
use App\Models\Tag;
use App\Services\TranslationService;
use Illuminate\Console\Command;

class GeneratePageSeoContent extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'seo:generate-page-content
                            {--pages=* : Specific page keys to generate (home, tags_index, countries_index)}
                            {--locale=en : Locale to generate content for}
                            {--locales=* : Multiple locales to generate for}
                            {--group= : Locale group (europe_west, east_asia, etc.)}
                            {--priority : Use priority locales only}
                            {--all : Generate for all supported locales}
                            {--translate : Translate existing English content to other locales}
                            {--position=bottom : Position of content (top or bottom)}
                            {--tags : Generate content for individual tag pages}
                            {--countries : Generate content for individual country pages}
                            {--limit=50 : Limit for tag/country pages}
                            {--force : Overwrite existing content}';

    /**
     * The console command description.
     */
    protected $description = 'Generate SEO text content for pages using AI';

    protected TranslationService $translationService;

    public function __construct(TranslationService $translationService)
    {
        parent::__construct();
        $this->translationService = $translationService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $position = $this->option('position');
        $force = $this->option('force');
        $translate = $this->option('translate');

        // Determine which locales to process
        $locales = $this->getTargetLocales();
        
        $this->info("🔍 Generating SEO content for " . count($locales) . " locale(s)");

        // Generate for main pages
        $pages = $this->option('pages');
        if (empty($pages)) {
            $pages = ['home', 'tags_index', 'countries_index'];
        }

        foreach ($locales as $locale) {
            $this->newLine();
            $this->info("📍 Processing locale: {$locale}");
            
            // Handle translation mode
            if ($translate && $locale !== 'en') {
                $this->translateExistingContent($locale, $force);
                continue;
            }

            // Generate main page content
            foreach ($pages as $pageKey) {
                $this->generatePageContent($pageKey, $locale, $position, $force);
            }

            // Generate individual tag pages
            if ($this->option('tags')) {
                $this->generateTagPageContent($locale, $position, $force, $this->option('limit'));
            }

            // Generate individual country pages
            if ($this->option('countries')) {
                $this->generateCountryPageContent($locale, $position, $force, $this->option('limit'));
            }
            
            // Small delay between locales to respect rate limits
            if (count($locales) > 1) {
                usleep(500000);
            }
        }

        $this->info('✅ SEO content generation complete!');
        return 0;
    }

    /**
     * Get target locales based on options
     */
    protected function getTargetLocales(): array
    {
        // --all: all supported locales
        if ($this->option('all')) {
            return $this->translationService->getSupportedLocales();
        }

        // --priority: priority locales only
        if ($this->option('priority')) {
            return $this->translationService->getPriorityLocales();
        }

        // --group: specific locale group
        if ($group = $this->option('group')) {
            $locales = $this->translationService->getLocaleGroup($group);
            if (empty($locales)) {
                $this->error("Unknown locale group: {$group}");
                $this->info("Available groups: " . implode(', ', array_keys(config('locales.groups', []))));
                return ['en'];
            }
            return $locales;
        }

        // --locales: specific list
        $locales = $this->option('locales');
        if (!empty($locales)) {
            return $locales;
        }

        // --locale: single locale
        return [$this->option('locale')];
    }

    /**
     * Generate content for a main page
     */
    protected function generatePageContent(string $pageKey, string $locale, string $position, bool $force): void
    {
        // Check if content already exists
        $existing = PageSeoContent::where('page_key', $pageKey)
            ->where('locale', $locale)
            ->first();

        if ($existing && !$force) {
            $this->line("⏭️  Skipping {$pageKey} ({$locale}) - already exists");
            return;
        }

        $this->line("📝 Generating content for: {$pageKey} ({$locale})");

        try {
            $content = $this->translationService->generatePageSeoContent($pageKey, $locale);

            if (empty($content['content'])) {
                $this->error("   Failed to generate content for {$pageKey} - empty response");
                if ($this->option('verbose')) {
                    $this->line("   Response: " . json_encode($content));
                }
                return;
            }

            PageSeoContent::updateOrCreate(
                ['page_key' => $pageKey, 'locale' => $locale],
                [
                    'title' => $content['title'],
                    'content' => $content['content'],
                    'keywords' => $content['keywords'],
                    'position' => $position,
                    'is_active' => true,
                ]
            );

            $this->info("   ✓ Generated content for {$pageKey}");
        } catch (\Exception $e) {
            $this->error("   ✗ Error: " . $e->getMessage());
            if ($this->option('verbose')) {
                $this->line("   Stack trace: " . $e->getTraceAsString());
            }
        }
    }

    /**
     * Generate content for tag pages
     */
    protected function generateTagPageContent(string $locale, string $position, bool $force, int $limit): void
    {
        $this->info("📝 Generating tag page content...");

        $tags = Tag::where('models_count', '>', 10)
            ->orderByDesc('models_count')
            ->limit($limit)
            ->get();

        $bar = $this->output->createProgressBar($tags->count());
        $bar->start();

        foreach ($tags as $tag) {
            $pageKey = "tag_{$tag->slug}";
            
            // Check existing
            $existing = PageSeoContent::where('page_key', $pageKey)
                ->where('locale', $locale)
                ->first();

            if ($existing && !$force) {
                $bar->advance();
                continue;
            }

            try {
                $content = $this->translationService->generatePageSeoContent(
                    $pageKey,
                    $locale,
                    ['tag_name' => $tag->name]
                );

                if (!empty($content['content'])) {
                    PageSeoContent::updateOrCreate(
                        ['page_key' => $pageKey, 'locale' => $locale],
                        [
                            'title' => $content['title'] ?? "About {$tag->name} Cams",
                            'content' => $content['content'],
                            'keywords' => $content['keywords'],
                            'position' => $position,
                            'is_active' => true,
                        ]
                    );
                }
            } catch (\Exception $e) {
                // Continue on error
            }

            $bar->advance();
            usleep(500000); // Rate limiting
        }

        $bar->finish();
        $this->newLine();
    }

    /**
     * Generate content for country pages
     */
    protected function generateCountryPageContent(string $locale, string $position, bool $force, int $limit): void
    {
        $this->info("📝 Generating country page content...");

        $countries = Country::where('models_count', '>', 10)
            ->orderByDesc('models_count')
            ->limit($limit)
            ->get();

        $bar = $this->output->createProgressBar($countries->count());
        $bar->start();

        foreach ($countries as $country) {
            $pageKey = "country_{$country->code}";
            
            // Check existing
            $existing = PageSeoContent::where('page_key', $pageKey)
                ->where('locale', $locale)
                ->first();

            if ($existing && !$force) {
                $bar->advance();
                continue;
            }

            try {
                $content = $this->translationService->generatePageSeoContent(
                    $pageKey,
                    $locale,
                    [
                        'country_name' => $country->name,
                        'country_code' => $country->code,
                    ]
                );

                if (!empty($content['content'])) {
                    PageSeoContent::updateOrCreate(
                        ['page_key' => $pageKey, 'locale' => $locale],
                        [
                            'title' => $content['title'] ?? "About {$country->name} Cams",
                            'content' => $content['content'],
                            'keywords' => $content['keywords'],
                            'position' => $position,
                            'is_active' => true,
                        ]
                    );
                }
            } catch (\Exception $e) {
                // Continue on error
            }

            $bar->advance();
            usleep(500000); // Rate limiting
        }

        $bar->finish();
        $this->newLine();
    }

    /**
     * Translate existing English content to other locales
     */
    protected function translateExistingContent(string $targetLocale, bool $force): void
    {
        $this->info("🌐 Translating content to {$targetLocale}...");

        // Get all English content
        $englishContent = PageSeoContent::where('locale', 'en')
            ->where('is_active', true)
            ->get();

        $bar = $this->output->createProgressBar($englishContent->count());
        $bar->start();

        foreach ($englishContent as $content) {
            // Check if translation exists
            $existing = PageSeoContent::where('page_key', $content->page_key)
                ->where('locale', $targetLocale)
                ->first();

            if ($existing && !$force) {
                $bar->advance();
                continue;
            }

            try {
                $translated = $this->translationService->translatePageSeoContent(
                    $content->title,
                    $content->content,
                    $targetLocale
                );

                PageSeoContent::updateOrCreate(
                    ['page_key' => $content->page_key, 'locale' => $targetLocale],
                    [
                        'title' => $translated['title'],
                        'content' => $translated['content'],
                        'keywords' => $content->keywords, // Keep same keywords
                        'position' => $content->position,
                        'is_active' => true,
                    ]
                );
            } catch (\Exception $e) {
                // Continue on error
            }

            $bar->advance();
            usleep(300000); // Rate limiting
        }

        $bar->finish();
        $this->newLine();
    }
}
